<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Enums\IntegrationType;
use App\Http\Apis\Integrations\Strava\StravaAdapter;
use App\Models\AuthIntegration;
use App\Services\Api\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use phpcent\Client;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;

class StravaController
{
    public function __construct(private readonly BadgeService $badgeService)
    {
        $this->badgeService->setApiIntegration(new StravaAdapter());
    }

    /**
     * Redirect the user to Strava's OAuth authorization page.
     * Accepts an optional ?token= JWT query param to link the OAuth flow to
     * the authenticated TrophyRoom user (same pattern as Steam).
     *
     * GET /api/strava/authorize?token=<jwt>
     */
    public function redirectToStrava(Request $request)
    {
        $token = $request->query('token');
        if ($token) {
            try {
                $user = JWTAuth::setToken($token)->authenticate();
                if ($user) {
                    session(['strava_connect_user_id' => $user->id]);
                }
            } catch (\Exception $e) {
                Log::debug('StravaController@redirectToStrava: JWT decode failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $query = http_build_query([
            'client_id'      => config('services.strava.client_id'),
            'redirect_uri'   => config('services.strava.redirect'),
            'response_type'  => 'code',
            'approval_prompt' => 'auto',
            'scope'          => 'read,activity:read',
        ]);

        return redirect('https://www.strava.com/oauth/authorize?' . $query);
    }

    /**
     * Handle the OAuth callback from Strava.
     * Exchanges the authorization code for access/refresh tokens,
     * stores them in auth_integrations, then triggers a badge sync.
     *
     * GET /api/strava/callback?code=...
     */
    public function handleStravaCallback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            Log::warning('StravaController@handleStravaCallback: no code in callback');
            return redirect()->route('ambar', ['any' => '/trophy-room']);
        }

        // Exchange authorization code for tokens
        $response = Http::post('https://www.strava.com/oauth/token', [
            'client_id'     => config('services.strava.client_id'),
            'client_secret' => config('services.strava.client_secret'),
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ]);

        if (!$response->ok()) {
            Log::error('StravaController@handleStravaCallback: token exchange HTTP ' . $response->status());
            return redirect()->route('ambar', ['any' => '/trophy-room']);
        }

        $payload     = $response->json();
        $athleteId   = $payload['athlete']['id'] ?? null;
        $accessToken = $payload['access_token'] ?? null;
        $refreshToken = $payload['refresh_token'] ?? null;
        $expiresAt   = $payload['expires_at'] ?? 0;

        if (!$accessToken || !$refreshToken || !$athleteId) {
            Log::error('StravaController@handleStravaCallback: incomplete token response');
            return redirect()->route('ambar', ['any' => '/trophy-room']);
        }

        $userId = session('strava_connect_user_id');
        session()->forget('strava_connect_user_id');

        $stored = json_encode([
            'at' => $accessToken,
            'rt' => $refreshToken,
            'ea' => $expiresAt,
            'id' => $athleteId,
        ]);

        $syncResult = false;
        if ($userId) {
            AuthIntegration::updateOrCreate(
                ['user_id' => $userId, 'name' => IntegrationType::Strava],
                ['integration_id' => $stored]
            );

            $syncResult = (bool) $this->badgeService->syncBadges($accessToken);
            Log::info("Strava connected for user {$userId}, athlete {$athleteId}");
        } else {
            Log::warning("Strava callback without user context — athlete {$athleteId}");
        }

        // Notify the SPA via Centrifugo (same pattern as Steam/Discord/GitHub)
        try {
            $client = new Client(config('broadcasting.connections.centrifugo.url'));
            $client->setApiKey(config('broadcasting.connections.centrifugo.api_key'));
            $client->publish('sync-platform', [
                'platform' => 'Strava',
                'result'   => $syncResult,
                'user'     => null,
            ]);
        } catch (\Exception $e) {
            Log::warning('StravaController@handleStravaCallback: Centrifugo publish failed — ' . $e->getMessage());
        }

        return redirect()->route('ambar', ['any' => '/trophy-room']);
    }

    /**
     * Re-sync Strava badges for the authenticated user.
     * Reads the stored access token, refreshes it if expired, then syncs.
     *
     * GET /api/strava/sync
     */
    public function sync(): JsonResponse
    {
        $user = Auth::user();

        $authIntegration = AuthIntegration::where('user_id', $user->id)
            ->where('name', IntegrationType::Strava)
            ->first();

        if (!$authIntegration) {
            return response()->json(
                ['message' => 'Strava is not connected. Please authorize via /api/strava/authorize.'],
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }

        $tokenData = json_decode($authIntegration->integration_id, true);

        if (!$tokenData || empty($tokenData['at'])) {
            return response()->json(
                ['message' => 'Strava token data is corrupt. Please reconnect via /api/strava/authorize.'],
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }

        // Refresh the token if it has expired (with a 60-second buffer)
        if ((int) ($tokenData['ea'] ?? 0) < time() + 60) {
            $tokenData = $this->refreshTokenData($tokenData);
            if (!$tokenData) {
                return response()->json(
                    ['message' => 'Strava token has expired and could not be refreshed. Please reconnect.'],
                    ResponseAlias::HTTP_UNAUTHORIZED
                );
            }
            $authIntegration->update(['integration_id' => json_encode($tokenData)]);
        }

        $synced = $this->badgeService->syncBadges($tokenData['at']);

        if ($synced) {
            return response()->json(
                ['message' => 'Strava badges successfully synchronized'],
                ResponseAlias::HTTP_CREATED
            );
        }

        return response()->json(
            ['message' => 'Strava badges could not be synchronized. Please try again later.'],
            ResponseAlias::HTTP_BAD_REQUEST
        );
    }

    // -------------------------------------------------------------------------
    // Token refresh
    // -------------------------------------------------------------------------

    /**
     * Exchange a Strava refresh token for a new access token.
     * Returns the updated token data array, or null on failure.
     *
     * @param  array{at: string, rt: string, ea: int, id: int}  $tokenData
     * @return array{at: string, rt: string, ea: int, id: int}|null
     */
    private function refreshTokenData(array $tokenData): ?array
    {
        try {
            $response = Http::post('https://www.strava.com/oauth/token', [
                'client_id'     => config('services.strava.client_id'),
                'client_secret' => config('services.strava.client_secret'),
                'refresh_token' => $tokenData['rt'],
                'grant_type'    => 'refresh_token',
            ]);

            if (!$response->ok()) {
                Log::error('StravaController@refreshTokenData: HTTP ' . $response->status());
                return null;
            }

            $data = $response->json();
            return [
                'at' => $data['access_token'],
                'rt' => $data['refresh_token'],
                'ea' => $data['expires_at'],
                'id' => $tokenData['id'], // athlete ID doesn't change on refresh
            ];
        } catch (\Exception $e) {
            Log::error('StravaController@refreshTokenData: ' . $e->getMessage());
            return null;
        }
    }
}
