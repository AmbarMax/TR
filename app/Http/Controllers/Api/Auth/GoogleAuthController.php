<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoogleAuthController
{
    /**
     * GET /login/google
     * Unauthenticated entry point. Redirects the browser to Google's OAuth
     * consent screen via Socialite.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * GET /api/google/callback
     * Google redirects back here after user consent. We exchange the code
     * for a user profile, find-or-create the User by email, issue a JWT,
     * and hand the token to the SPA via a minimal Blade page that writes
     * it to localStorage and redirects to /dashboard.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            Log::warning('GoogleAuth: OAuth exchange failed', [
                'error' => $e->getMessage(),
            ]);
            return redirect('/login?google_error=oauth_failed');
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));

        if ($email === '') {
            Log::warning('GoogleAuth: Google returned no email');
            return redirect('/login?google_error=no_email');
        }

        // Find existing user by email (covers organic signups, legacy imports,
        // and repeated Google logins). Create if not found.
        $user = User::where('email', $email)->first();

        if (!$user) {
            $displayName = $googleUser->getName();
            if (empty($displayName)) {
                $displayName = explode('@', $email)[0];
            }

            $user = User::create([
                'name'   => $displayName,
                'email'  => $email,
                'source' => 'google',
                // No password for OAuth-only users. username is nullable —
                // leaving null lets the user pick one from Settings later.
            ]);
        } elseif ($user->source === 'legacy_community') {
            // Legacy users logging in for the first time via Google: upgrade
            // their source so we can distinguish active vs dormant legacy.
            $user->source = 'google';
            $user->save();
        }

        $token = JWTAuth::fromUser($user);

        // Handoff: server-rendered Blade writes JWT to localStorage then
        // redirects to the SPA. Keeps the token out of URL history/logs.
        return response()->view('auth.oauth-success', [
            'token'    => $token,
            'redirect' => '/dashboard',
        ]);
    }
}
