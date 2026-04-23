<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Enums\IntegrationType;
use App\Http\Apis\Integrations\Discord\DiscordAdapter;
use App\Http\Controllers\Api\Brand\BrandGuildController;
use App\Models\Badge;
use App\Models\User;
use App\Services\Api\BadgeService;
use App\Services\DiscordSFTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use phpcent\Client;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;

class DiscordController
{

    public function __construct(private readonly BadgeService $badgeService,
                                private readonly DiscordSFTPService $discordSFTPService)
    {
        $this->badgeService->setApiIntegration(new DiscordAdapter());
    }

    public function redirectToDiscord()
    {
        return Socialite::with('discord')->scopes([
            'email', 'identify', 'guilds', 'guilds.join', 'guilds.members.read'
        ])->redirect();
    }

    public function handleDiscordCallback(Request $request)
    {
        if (Str::startsWith($request->input('state', ''), 'brand_guild:')) {
            return app(BrandGuildController::class)->handleCallback($request);
        }

        try {
            $discordUser = Socialite::driver('discord')->user();
        } catch (\Throwable $e) {
            Log::warning('DiscordAuth: OAuth exchange failed', [
                'error' => $e->getMessage(),
            ]);
            return redirect('/login?discord_error=oauth_failed');
        }

        $email = strtolower(trim((string) $discordUser->getEmail()));

        if ($email === '') {
            Log::warning('DiscordAuth: Discord returned no email');
            return redirect('/login?discord_error=no_email');
        }

        // Discord allows unverified emails; matching an existing account by an
        // unverified email would let anyone claim another user's account just
        // by registering a Discord app with their address.
        $emailVerified = (bool) ($discordUser->user['verified'] ?? false);
        if (!$emailVerified) {
            Log::warning('DiscordAuth: Discord email not verified', ['email' => $email]);
            return redirect('/login?discord_error=email_unverified');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $displayName = $discordUser->getName() ?: $discordUser->getNickname();
            if (empty($displayName)) {
                $displayName = explode('@', $email)[0];
            }

            $user = User::create([
                'name'   => $displayName,
                'email'  => $email,
                'source' => 'discord',
            ]);
        } elseif ($user->source === 'legacy_community') {
            $user->source = 'discord';
            $user->save();
        }

        $token = JWTAuth::fromUser($user);

        return response()->view('auth.oauth-success', [
            'token'    => $token,
            'redirect' => '/dashboard',
        ]);
    }

    public function sync()
    {
        $this->badgeService->syncBadges(Socialite::driver('discord')->user()->user)
            ?response()->json([
            'message' => 'Github badges successfully synchronized'
        ], ResponseAlias::HTTP_CREATED)
            :response()->json([
            'message' => 'Github badges not synchronized'
        ], ResponseAlias::HTTP_BAD_REQUEST);

    }

}
