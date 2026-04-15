<?php

namespace App\Http\Apis\Integrations\Steam;

use App\Enums\BadgeType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteamApi
{
    private $id;
    private $key;

    private $urlApiOwnedGames = 'https://api.steampowered.com/IPlayerService/GetOwnedGames/v1?key=%s&steamid=%s&include_appinfo=1&include_played_free_games=1&format=json';
    private $urlApiPlayerAchievements = 'https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1?key=%s&steamid=%s&appid=%s&l=english';
    private $urlApiGameSchema = 'https://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2?key=%s&appid=%s&l=english';
    private $urlApiGlobalAchievementPct = 'https://api.steampowered.com/ISteamUserStats/GetGlobalAchievementPercentagesForApp/v2?gameid=%s&format=json';

    public function setUserId($id)
    {
        $this->id = $id;
        $this->key = config('services.steam.client_secret');
    }

    public function getBadges()
    {
        return $this->getListOfAchievements();
    }

    /**
     * Fetch all unlocked per-game achievements for the user.
     *
     * Queries the top 20 most-played games (playtime > 0), retrieves the
     * user's unlocked achievements for each, then enriches with display
     * names, descriptions, and icons from the game schema.
     *
     * @return array  Each entry: ['name', 'image', 'description', 'type']
     */
    private function getListOfAchievements(): array
    {
        $games = $this->getOwnedGames();
        if (empty($games)) {
            return [];
        }

        // Only consider games that have actually been played
        $games = array_filter($games, fn($g) => ($g['playtime_forever'] ?? 0) > 0);

        // Sort by most-played first and cap at 20 to stay rate-limit safe
        usort($games, fn($a, $b) => ($b['playtime_forever'] ?? 0) <=> ($a['playtime_forever'] ?? 0));
        $games = array_slice($games, 0, 20);

        $achievements = [];

        foreach ($games as $game) {
            $appid = (int) $game['appid'];

            $playerAchievements = $this->getPlayerAchievements($appid);
            if (empty($playerAchievements)) {
                continue;
            }

            // Only unlocked achievements
            $unlocked = array_filter($playerAchievements, fn($a) => ($a['achieved'] ?? 0) === 1);
            if (empty($unlocked)) {
                continue;
            }

            $schema = $this->getGameSchema($appid);
            if (empty($schema)) {
                continue;
            }

            // Index schema definitions by their api name for O(1) lookup
            $schemaByName = [];
            foreach ($schema as $def) {
                $schemaByName[$def['name']] = $def;
            }

            foreach ($unlocked as $achievement) {
                $apiname = $achievement['apiname'];
                $def = $schemaByName[$apiname] ?? null;
                if ($def === null) {
                    continue;
                }

                $achievements[] = [
                    'name'        => $def['displayName'] ?: $apiname,
                    'image'       => $def['icon'] ?? '',
                    'description' => $def['description'] ?? '',
                    'type'        => BadgeType::Common,
                ];
            }
        }

        return $achievements;
    }

    private function getApiOwnedGamesUrl(): string
    {
        return sprintf($this->urlApiOwnedGames, config('services.steam.client_secret'), $this->id);
    }

    private function getApiPlayerAchievementsUrl(int $appid): string
    {
        return sprintf($this->urlApiPlayerAchievements, config('services.steam.client_secret'), $this->id, $appid);
    }

    private function getApiGameSchemaUrl(int $appid): string
    {
        return sprintf($this->urlApiGameSchema, config('services.steam.client_secret'), $appid);
    }

    private function getApiGlobalAchievementPctUrl(int $appid): string
    {
        return sprintf($this->urlApiGlobalAchievementPct, $appid);
    }

    /**
     * Fetch all games owned by the user via IPlayerService/GetOwnedGames/v1
     *
     * @return array Array of games, each with: appid, name, playtime_forever, img_icon_url, img_logo_url
     *               Returns empty array on failure.
     */
    public function getOwnedGames(): array
    {
        try {
            $response = Http::get($this->getApiOwnedGamesUrl());

            if (!$response->ok()) {
                Log::error('SteamApi@getOwnedGames: HTTP ' . $response->status() . ' for steamid ' . $this->id);
                return [];
            }

            $games = $response->json()['response']['games'] ?? [];

            return array_map(function ($game) {
                $appid = $game['appid'];
                $iconHash = $game['img_icon_url'] ?? '';
                $logoHash = $game['img_logo_url'] ?? '';

                $game['img_icon_url'] = $iconHash
                    ? "https://media.steampowered.com/steamcommunity/public/images/apps/{$appid}/{$iconHash}.jpg"
                    : null;
                $game['img_logo_url'] = $logoHash
                    ? "https://media.steampowered.com/steamcommunity/public/images/apps/{$appid}/{$logoHash}.jpg"
                    : null;

                return $game;
            }, $games);
        } catch (\Exception $e) {
            Log::error('SteamApi@getOwnedGames: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch achievements unlocked by the user for a specific game
     * via ISteamUserStats/GetPlayerAchievements/v1
     *
     * @param int $appid Steam application ID
     * @return array|null Array of achievements each with: apiname, achieved (0/1), unlocktime (unix timestamp)
     *                    Returns null if game has no achievements or profile is private.
     */
    public function getPlayerAchievements(int $appid): ?array
    {
        try {
            $response = Http::get($this->getApiPlayerAchievementsUrl($appid));

            if ($response->ok()) {
                return $response->json()['playerstats']['achievements'] ?? null;
            }

            Log::debug('SteamApi@getPlayerAchievements: HTTP ' . $response->status() . ' for appid ' . $appid . ' (no achievements or private profile)');
            return null;
        } catch (\Exception $e) {
            Log::debug('SteamApi@getPlayerAchievements: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch achievement definitions (name, description, icons) for a game
     * via ISteamUserStats/GetSchemaForGame/v2
     *
     * @param int $appid Steam application ID
     * @return array|null Array of achievement definitions, each with: name, defaultvalue, displayName,
     *                    hidden, description, icon, icongray
     *                    Returns null on failure.
     */
    public function getGameSchema(int $appid): ?array
    {
        try {
            $response = Http::get($this->getApiGameSchemaUrl($appid));

            if (!$response->ok()) {
                Log::error('SteamApi@getGameSchema: HTTP ' . $response->status() . ' for appid ' . $appid);
                return null;
            }

            return $response->json()['game']['availableGameStats']['achievements'] ?? null;
        } catch (\Exception $e) {
            Log::error('SteamApi@getGameSchema: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch global unlock percentages for all achievements in a game
     * via ISteamUserStats/GetGlobalAchievementPercentagesForApp/v2
     *
     * @param int $appid Steam application ID
     * @return array Associative array keyed by achievement api_name => percent (float)
     *               Returns empty array on failure.
     */
    public function getGlobalAchievementPercentages(int $appid): array
    {
        try {
            $response = Http::get($this->getApiGlobalAchievementPctUrl($appid));

            if (!$response->ok()) {
                Log::error('SteamApi@getGlobalAchievementPercentages: HTTP ' . $response->status() . ' for appid ' . $appid);
                return [];
            }

            $achievements = $response->json()['achievementpercentages']['achievements'] ?? [];

            $result = [];
            foreach ($achievements as $achievement) {
                $result[$achievement['name']] = (float) $achievement['percent'];
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('SteamApi@getGlobalAchievementPercentages: ' . $e->getMessage());
            return [];
        }
    }

}
