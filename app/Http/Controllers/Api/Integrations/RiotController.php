<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Http\Apis\Integrations\Riot\RiotAdapter;
use App\Services\Api\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RiotController
{
    public function __construct(private readonly BadgeService $badgeService)
    {
        $this->badgeService->setApiIntegration(new RiotAdapter());
    }

    /**
     * Sync LoL badges (Challenges + Champion Mastery) for the authenticated user.
     *
     * POST /api/riot/sync
     * Body: { "riot_id": "GameName#TagLine" }
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'riot_id' => ['required', 'string', 'regex:/^.+#.+$/'],
        ]);

        $riotId = $request->input('riot_id');

        $synced = $this->badgeService->syncBadges($riotId);

        if ($synced) {
            return response()->json(
                ['message' => 'Riot badges successfully synchronized'],
                ResponseAlias::HTTP_CREATED
            );
        }

        return response()->json(
            ['message' => 'Riot badges could not be synchronized. Check your Riot ID or try again later.'],
            ResponseAlias::HTTP_BAD_REQUEST
        );
    }
}
