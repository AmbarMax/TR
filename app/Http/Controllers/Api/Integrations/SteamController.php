<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Enums\IntegrationType;
use App\Http\Apis\Integrations\Steam\SteamAdapter;
use App\Services\Api\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use phpcent\Client;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class SteamController
{
    public function __construct(private readonly BadgeService $badgeService)
    {
        $this->badgeService->setApiIntegration(new SteamAdapter());
    }

    public function redirectToSteam(Request $request)
    {
        // If a JWT token is provided (user initiating OAuth from the SPA),
        // decode it to get the user ID and store in session for the callback.
        $token = $request->query('token');
        if ($token) {
            try {
                $user = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->authenticate();
                if ($user) {
                    session(['steam_connect_user_id' => $user->id]);
                }
            } catch (\Exception $e) {
                // Token invalid or expired — proceed without user context
                Log::debug('SteamController@redirectToSteam: JWT decode failed', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return Socialite::driver('steam')->redirect();
    }

    public function handleSteamCallback(Request $request){

        $sync = false;

        $steamUser = Socialite::driver('steam')->user();

        $data = [
            'platform' => 'Steam',
            'result' => $sync,
            'user' => $steamUser
        ];

        if ($this->badgeService->auth($steamUser, IntegrationType::Steam)){
            $this->sync($steamUser);

            $data['result'] = true;
        }

        $client = new Client(config('broadcasting.connections.centrifugo.url'));
        $client->setApiKey(config('broadcasting.connections.centrifugo.api_key'));

        $channel = 'sync-platform';
        $client->publish($channel, $data);

        // Bridge: connect TrophyRoom user to their Steam account
        $steamId = $steamUser->getId();
        $userId = session('steam_connect_user_id');
        session()->forget('steam_connect_user_id'); // clean up

        if ($userId) {
            // Create or update the AuthIntegration record
            \App\Models\AuthIntegration::updateOrCreate(
                ['user_id' => $userId, 'name' => IntegrationType::Steam],
                ['integration_id' => $steamId]
            );

            // Dispatch async achievement sync
            \App\Jobs\SyncSteamAchievementsJob::dispatch($userId, $steamId);

            Log::info("Steam connected for user {$userId}, steamid {$steamId}");
        } else {
            Log::warning("Steam callback without user context — steamid {$steamId}");
        }

        return redirect()->route('ambar', ['any' => '/trophy-room']);
    }

    public function sync($steamUser){
        $this->badgeService->syncBadges($steamUser->getId())
            ?response()->json([
            'message' => 'Steam badges successfully synchronized'
        ], ResponseAlias::HTTP_CREATED)
            :response()->json([
            'message' => 'Steam badges not synchronized'
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }

}
