<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Enums\IntegrationType;
use App\Http\Apis\Integrations\Steam\SteamAdapter;
use App\Services\Api\BadgeService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use phpcent\Client;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class SteamController
{
    public function __construct(private readonly BadgeService $badgeService)
    {
        $this->badgeService->setApiIntegration(new SteamAdapter());
    }

    public function redirectToSteam()
    {
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

        // Dispatch async achievement sync
        // auth()->id() is null here (unprotected web route, auth stub does not log user in),
        // so resolve the TrophyRoom user via the AuthIntegration record keyed by steamid64.
        $steamId = $steamUser->getId();
        $authIntegration = \App\Models\AuthIntegration::where('integration_id', $steamId)->first();

        if ($authIntegration && $authIntegration->user_id) {
            \App\Jobs\SyncSteamAchievementsJob::dispatch($authIntegration->user_id, $steamId);
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
