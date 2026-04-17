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

        $sync = false;

        $data = [
            'platform' => 'Discord',
            'result' => $sync,
            'user' => null
        ];

        if ($this->badgeService->auth(
            Socialite::driver('discord')->user(),
            IntegrationType::Discord)){
            $this->sync();

            $data['result'] = true;
            $data['user'] = Socialite::driver('discord')->user();
        }

        $client = new Client(config('broadcasting.connections.centrifugo.url'));
        $client->setApiKey(config('broadcasting.connections.centrifugo.api_key'));

        $channel = 'sync-platform';
        $client->publish($channel, $data);

        return redirect()->route('ambar', ['any' => '/trophy-room']);
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
