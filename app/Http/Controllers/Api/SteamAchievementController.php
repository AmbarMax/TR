<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncSteamAchievementsJob;
use App\Models\AuthIntegration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SteamAchievementController extends Controller
{
    /**
     * Trigger a full Steam achievement sync for the authenticated user.
     * Dispatches async job. Returns immediately.
     *
     * POST /api/steam/achievements/sync
     */
    public function sync(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find the user's Steam ID from auth_integrations
        $steamIntegration = AuthIntegration::where('user_id', $user->id)
            ->whereNotNull('integration_id')
            ->whereHas('user') // ensure user exists
            ->first();

        // Also check: the integration record should be for steam
        // Since auth_integrations stores the platform name in 'name' field,
        // let's filter by that too. But first check what's actually stored.
        // Based on the audit, 'name' might be null or might be the platform name.
        // SafeApproach: also look up the Integration model to confirm it's steam.
        // For now, since Steam is the only integration that stores a steamid64 as integration_id,
        // and we need the steamid64 value, let's find it by checking all auth_integrations for this user
        // and picking the one that looks like a Steam ID (numeric, 17 digits).

        if (!$steamIntegration || !$steamIntegration->integration_id) {
            // Try alternative: check if Steam ID was passed in the request (for fresh OAuth flows)
            return response()->json([
                'error' => 'Steam account not connected. Please connect Steam first.',
                'code' => 'steam_not_connected',
            ], 400);
        }

        $steamId = $steamIntegration->integration_id;

        // Dispatch async job
        SyncSteamAchievementsJob::dispatch($user->id, $steamId);

        return response()->json([
            'message' => 'Steam achievement sync started. This may take a few minutes.',
            'status' => 'queued',
        ], 202);
    }

    /**
     * Get the authenticated user's Steam games with achievement counts.
     *
     * GET /api/steam/achievements/games
     */
    public function games(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $games = $user->steamGames()
            ->withCount(['achievements as total_achievements'])
            ->withCount(['achievements as unlocked_achievements' => function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }])
            ->withPivot('playtime_minutes')
            ->orderBy('name')
            ->get()
            ->map(function ($game) {
                return [
                    'id' => $game->id,
                    'appid' => $game->appid,
                    'name' => $game->name,
                    'img_icon_url' => $game->img_icon_url,
                    'img_logo_url' => $game->img_logo_url,
                    'playtime_minutes' => $game->pivot->playtime_minutes,
                    'total_achievements' => $game->total_achievements,
                    'unlocked_achievements' => $game->unlocked_achievements,
                ];
            });

        return response()->json(['games' => $games]);
    }

    /**
     * Get achievements for a specific game for the authenticated user.
     *
     * GET /api/steam/achievements/games/{gameId}
     */
    public function gameAchievements(Request $request, string $gameId): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verify user owns this game
        $game = $user->steamGames()->where('steam_games.id', $gameId)->first();

        if (!$game) {
            return response()->json(['error' => 'Game not found in your library'], 404);
        }

        $achievements = $game->achievements()
            ->leftJoin('steam_user_achievements', function ($join) use ($user) {
                $join->on('steam_achievements.id', '=', 'steam_user_achievements.steam_achievement_id')
                     ->where('steam_user_achievements.user_id', '=', $user->id);
            })
            ->select([
                'steam_achievements.id',
                'steam_achievements.api_name',
                'steam_achievements.display_name',
                'steam_achievements.description',
                'steam_achievements.icon_url',
                'steam_achievements.icon_gray_url',
                'steam_achievements.global_percent',
                'steam_user_achievements.unlocked_at',
            ])
            ->selectRaw('CASE WHEN steam_user_achievements.user_id IS NOT NULL THEN 1 ELSE 0 END as unlocked')
            ->orderByDesc('unlocked')
            ->orderBy('steam_achievements.display_name')
            ->get();

        return response()->json([
            'game' => [
                'id' => $game->id,
                'appid' => $game->appid,
                'name' => $game->name,
            ],
            'achievements' => $achievements,
        ]);
    }
}
