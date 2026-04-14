<?php

namespace App\Services\Api;

use App\Http\Apis\Integrations\Steam\SteamApi;
use App\Models\SteamGame;
use App\Models\SteamAchievement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SteamAchievementSyncService
{
    private SteamApi $steamApi;

    public function __construct()
    {
        $this->steamApi = new SteamApi();
    }

    /**
     * Full sync: games + achievements for a user.
     *
     * @param User $user
     * @param string $steamId The user's steamid64
     * @return array Summary of what was synced: ['games' => int, 'achievements' => int, 'errors' => string[]]
     */
    public function syncForUser(User $user, string $steamId): array
    {
        $this->steamApi->setUserId($steamId);

        $summary = ['games' => 0, 'achievements' => 0, 'errors' => []];

        // Step 1: Get all owned games
        $ownedGames = $this->steamApi->getOwnedGames();

        if (empty($ownedGames)) {
            $summary['errors'][] = 'No games found or profile is private';
            return $summary;
        }

        foreach ($ownedGames as $gameData) {
            try {
                $result = $this->syncGame($user, $gameData);
                $summary['games']++;
                $summary['achievements'] += $result;
            } catch (\Exception $e) {
                $summary['errors'][] = "Game {$gameData['appid']}: {$e->getMessage()}";
                Log::warning("SteamAchievementSync: failed syncing game {$gameData['appid']} for user {$user->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("SteamAchievementSync: completed for user {$user->id}", $summary);

        return $summary;
    }

    /**
     * Sync a single game and its achievements.
     *
     * @param User $user
     * @param array $gameData Raw game data from Steam API
     * @return int Number of achievements synced for this game
     */
    private function syncGame(User $user, array $gameData): int
    {
        $appid = $gameData['appid'];

        // Upsert the game record
        $game = SteamGame::updateOrCreate(
            ['appid' => $appid],
            [
                'name' => $gameData['name'] ?? "App {$appid}",
                'img_icon_url' => $gameData['img_icon_url'] ?? null,
                'img_logo_url' => $gameData['img_logo_url'] ?? null,
            ]
        );

        // Attach game to user with playtime (update playtime if already attached)
        $user->steamGames()->syncWithoutDetaching([
            $game->id => ['playtime_minutes' => $gameData['playtime_forever'] ?? 0]
        ]);

        // Get player achievements for this game
        $playerAchievements = $this->steamApi->getPlayerAchievements($appid);

        if ($playerAchievements === null) {
            // Game has no achievements or profile is private for this game
            return 0;
        }

        // Get schema (display names, icons) and global percentages
        $schema = $this->steamApi->getGameSchema($appid);
        $globalPcts = $this->steamApi->getGlobalAchievementPercentages($appid);

        // Index schema by apiname for fast lookup
        $schemaMap = [];
        if ($schema) {
            foreach ($schema as $def) {
                $schemaMap[$def['name']] = $def;
            }
        }

        // Sync achievements
        $achievementCount = 0;

        foreach ($playerAchievements as $pa) {
            $apiName = $pa['apiname'];
            $achieved = (bool) ($pa['achieved'] ?? false);
            $unlockTime = ($pa['unlocktime'] ?? 0) > 0 ? $pa['unlocktime'] : null;

            // Get metadata from schema
            $def = $schemaMap[$apiName] ?? [];

            // Upsert achievement definition
            $achievement = SteamAchievement::updateOrCreate(
                [
                    'steam_game_id' => $game->id,
                    'api_name' => $apiName,
                ],
                [
                    'display_name' => $def['displayName'] ?? $apiName,
                    'description' => $def['description'] ?? null,
                    'icon_url' => $def['icon'] ?? null,
                    'icon_gray_url' => $def['icongray'] ?? null,
                    'global_percent' => $globalPcts[$apiName] ?? null,
                ]
            );

            // Only attach if achieved
            if ($achieved) {
                $user->steamAchievements()->syncWithoutDetaching([
                    $achievement->id => [
                        'unlocked_at' => $unlockTime ? \Carbon\Carbon::createFromTimestamp($unlockTime) : null,
                    ]
                ]);
                $achievementCount++;
            }
        }

        return $achievementCount;
    }
}
