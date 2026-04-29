<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class OverwolfAuthController
{
    /**
     * GET /login/overwolf
     * Generates PKCE code_verifier + state, caches them, and redirects
     * the browser to Overwolf's OIDC authorization endpoint.
     */
    public function redirectToOverwolf()
    {
        $clientId    = config('services.overwolf.client_id');
        $redirectUri = config('services.overwolf.redirect');
        $authEndpoint= config('services.overwolf.authorize_endpoint');

        // PKCE: generate code_verifier (43-128 chars unreserved) + code_challenge (S256)
        $codeVerifier  = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);

        // CSRF state
        $state = Str::random(40);

        // Cache code_verifier keyed by state for 10 minutes (max user time on Overwolf consent)
        Cache::put('overwolf_pkce_' . $state, $codeVerifier, now()->addMinutes(10));

        $params = http_build_query([
            'response_type'         => 'code',
            'client_id'             => $clientId,
            'redirect_uri'          => $redirectUri,
            'scope'                 => 'openid profile email',
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
            'state'                 => $state,
        ]);

        return redirect($authEndpoint . '?' . $params);
    }

    /**
     * GET /api/overwolf/callback
     * Overwolf redirects back here with ?code=...&state=...
     * Exchange code for access_token using PKCE, fetch userinfo,
     * find-or-create User, issue JWT, hand it to the SPA via Blade view.
     */
    public function handleOverwolfCallback(Request $request)
    {
        $state = $request->query('state');
        $code  = $request->query('code');

        if (!$state || !$code) {
            Log::warning('OverwolfAuth: missing state or code in callback');
            return redirect('/login?overwolf_error=invalid_response');
        }

        $codeVerifier = Cache::pull('overwolf_pkce_' . $state);

        if (!$codeVerifier) {
            Log::warning('OverwolfAuth: state mismatch or expired', ['state' => $state]);
            return redirect('/login?overwolf_error=state_expired');
        }

        $client = new Client(['timeout' => 10]);

        try {
            $tokenResponse = $client->post(config('services.overwolf.token_endpoint'), [
                'form_params' => [
                    'grant_type'    => 'authorization_code',
                    'code'          => $code,
                    'redirect_uri'  => config('services.overwolf.redirect'),
                    'client_id'     => config('services.overwolf.client_id'),
                    'client_secret' => config('services.overwolf.client_secret'),
                    'code_verifier' => $codeVerifier,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $tokenData = json_decode((string) $tokenResponse->getBody(), true);
        } catch (\Throwable $e) {
            Log::warning('OverwolfAuth: token exchange failed', [
                'error' => $e->getMessage(),
            ]);
            return redirect('/login?overwolf_error=token_exchange_failed');
        }

        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            Log::warning('OverwolfAuth: no access_token in response');
            return redirect('/login?overwolf_error=no_token');
        }

        try {
            $userInfoResponse = $client->get(config('services.overwolf.userinfo_endpoint'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            $userInfo = json_decode((string) $userInfoResponse->getBody(), true);
        } catch (\Throwable $e) {
            Log::warning('OverwolfAuth: userinfo fetch failed', [
                'error' => $e->getMessage(),
            ]);
            return redirect('/login?overwolf_error=userinfo_failed');
        }

        // Overwolf userinfo claims (per OIDC spec + Overwolf docs):
        //   sub:     unique Overwolf user ID (always present with openid scope)
        //   email:   email address (only with email scope)
        //   nickname or preferred_username: display name (with profile scope)
        //   picture: avatar URL (with profile scope)
        $overwolfId       = $userInfo['sub'] ?? null;
        $overwolfUsername = $userInfo['nickname'] ?? $userInfo['preferred_username'] ?? null;
        $email            = isset($userInfo['email']) ? strtolower(trim((string) $userInfo['email'])) : null;
        $picture          = $userInfo['picture'] ?? null;

        if (!$overwolfId) {
            Log::warning('OverwolfAuth: no sub in userinfo');
            return redirect('/login?overwolf_error=no_id');
        }

        // Find user by overwolf_id first (returning user)
        $user = User::where('overwolf_id', $overwolfId)->first();

        // Fallback: try to find by email (account linking for users who originally signed up otherwise)
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->overwolf_id       = $overwolfId;
                $user->overwolf_username = $overwolfUsername;
                if (!$user->source) {
                    $user->source = 'overwolf';
                }
                $user->save();
            }
        }

        if (!$user) {
            $username = $this->resolveUsername($overwolfUsername, $overwolfId);

            $user = User::create([
                'name'              => $overwolfUsername ?: $username,
                'username'          => $username,
                'email'             => $email,
                'avatar'            => $picture,
                'source'            => 'overwolf',
                'overwolf_id'       => $overwolfId,
                'overwolf_username' => $overwolfUsername,
                'account_type'      => 'player',
            ]);
        }

        $token = JWTAuth::fromUser($user);

        return response()->view('auth.oauth-success', [
            'token'    => $token,
            'redirect' => '/dashboard',
        ]);
    }

    /**
     * Generate a cryptographically random code_verifier (43-128 chars, URL-safe).
     */
    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
    }

    /**
     * Generate code_challenge using S256 method: BASE64URL(SHA256(verifier))
     */
    private function generateCodeChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    /**
     * Resolve a unique username for a new Overwolf user.
     * Strategy: try the Overwolf nickname first; if taken or empty,
     * append a numeric suffix; final fallback is "ow_<short-id>".
     */
    private function resolveUsername(?string $nickname, string $overwolfId): string
    {
        $candidate = $nickname ? $this->sanitizeUsername($nickname) : null;

        if ($candidate && !User::where('username', $candidate)->exists()) {
            return $candidate;
        }

        if ($candidate) {
            for ($i = 1; $i <= 10; $i++) {
                $try = $candidate . $i;
                if (!User::where('username', $try)->exists()) {
                    return $try;
                }
            }
        }

        return 'ow_' . substr(md5($overwolfId), 0, 8);
    }

    /**
     * Sanitize username: lowercase, alphanumeric + underscore, max 32 chars.
     */
    private function sanitizeUsername(string $raw): string
    {
        $clean = preg_replace('/[^a-z0-9_]/i', '', strtolower($raw));
        $clean = substr($clean, 0, 32);
        return $clean ?: '';
    }
}
