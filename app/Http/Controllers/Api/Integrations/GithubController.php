<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Enums\IntegrationType;
use App\Http\Apis\Integrations\Github\GithubAdapter;
use App\Services\Api\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use phpcent\Client;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GithubController
{

    public function __construct(private readonly BadgeService $badgeService)
    {
        $this->badgeService->setApiIntegration(new GithubAdapter());
    }

    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGithubCallback(Request $request)
    {
        $sync = false;

        $data = [
            'platform' => 'GitHub',
            'result' => $sync,
            'user' => null
        ];

        if ($this->badgeService->auth(
            Socialite::driver('github')->user(),
            IntegrationType::Github)){
            $this->sync();
            session()->flash('notification', 'Notification message goes here');

            $data['result'] = true;
            $data['user'] = Socialite::driver('github')->user();
        }

        $client = new Client(config('broadcasting.connections.centrifugo.url'));
        $client->setApiKey(config('broadcasting.connections.centrifugo.api_key'));

        $channel = 'sync-platform';
        $client->publish($channel, $data);

        return redirect()->route('ambar', ['any' => '/trophy-room']);
    }

    public function sync()
    {
        $this->badgeService->syncBadges(Socialite::driver('github')->user()->nickname)
            ?response()->json([
            'message' => 'Github badges successfully synchronized'
        ], ResponseAlias::HTTP_CREATED)
            :response()->json([
            'message' => 'Github badges not synchronized'
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }
}
