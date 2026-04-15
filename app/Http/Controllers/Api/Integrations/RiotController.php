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

    private const VALID_REGIONS = [
        'br1', 'eun1', 'euw1', 'jp1', 'kr', 'la1', 'la2',
        'na1', 'oc1', 'ph2', 'ru', 'sg2', 'th2', 'tr1', 'tw2', 'vn2',
    ];

    /**
     * Sync LoL badges (Challenges + Champion Mastery) for the authenticated user.
     *
     * POST /api/riot/sync
     * Body: { "riot_id": "GameName#TagLine", "region": "la2" }
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'riot_id' => ['required', 'string', 'regex:/^.+#.+$/'],
            'region'  => ['required', 'string', 'in:' . implode(',', self::VALID_REGIONS)],
        ]);

        // Encode both pieces into a single auth string for BadgeService::syncBadges().
        // RiotApi::setAuthData() knows how to parse "GameName#TagLine|region".
        $authString = $request->input('riot_id') . '|' . $request->input('region');

        $synced = $this->badgeService->syncBadges($authString);

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
