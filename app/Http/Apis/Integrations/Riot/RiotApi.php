<?php

namespace App\Http\Apis\Integrations\Riot;

use App\Enums\BadgeType;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RiotApi
{
    private string $gameName;
    private string $tagLine;
    private string $platformRegion = 'la1';
    private ?string $puuid = null;

    // Cluster used for account lookups (region-agnostic)
    private const ACCOUNT_CLUSTER = 'americas';

    // Challenge levels that represent real achievement (exclude NONE)
    private const ACHIEVED_LEVELS = [
        'IRON', 'BRONZE', 'SILVER', 'GOLD', 'PLATINUM',
        'DIAMOND', 'MASTER', 'GRANDMASTER', 'CHALLENGER',
    ];

    private const MAX_CHALLENGE_BADGES = 50;
    private const MASTERY_MIN_LEVEL    = 5;
    private const MAX_MASTERY_BADGES   = 20;

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Called by RiotAdapter::setAuthData().
     * Accepts "GameName#TagLine|region" (region optional; falls back to config).
     */
    public function setAuthData(string $riotId): void
    {
        // Split off optional region suffix: "Name#Tag|la2" → ["Name#Tag", "la2"]
        [$riotPart, $regionPart] = explode('|', $riotId, 2) + [null, null];

        [$this->gameName, $this->tagLine] = explode('#', $riotPart, 2) + ['', ''];
        $this->platformRegion = $regionPart ?? config('services.riot.region', 'la1');
        $this->puuid = null; // reset on new identity
    }

    /**
     * Resolve and return the PUUID for the current Riot ID.
     * Returns null if the account cannot be found.
     */
    public function resolvePlayerPUUID(): ?string
    {
        $this->resolvePUUID();
        return $this->puuid;
    }

    /**
     * Fetch the raw challenge progression array for the current player.
     * Returns null on failure.
     *
     * @return array<int,array{challengeId:int,percentile:float,level:string,value:float,achievedTime:int|null}>|null
     */
    public function getChallenges(): ?array
    {
        if (!$this->resolvePUUID()) {
            return null;
        }

        $url = sprintf(
            'https://%s.api.riotgames.com/lol/challenges/v1/player-data/by-puuid/%s',
            $this->region(),
            $this->puuid
        );

        try {
            $response = $this->get($url);
            if (!$response->ok()) {
                Log::error('RiotApi@getChallenges: HTTP ' . $response->status() . ' puuid=' . $this->puuid);
                return null;
            }
            return $response->json('challenges') ?? null;
        } catch (\Exception $e) {
            Log::error('RiotApi@getChallenges: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch champion mastery data for the current player, sorted by points desc.
     * Returns null on failure.
     *
     * @return array<int,array{championId:int,championLevel:int,championPoints:int}>|null
     */
    public function getChampionMastery(): ?array
    {
        if (!$this->resolvePUUID()) {
            return null;
        }

        $url = sprintf(
            'https://%s.api.riotgames.com/lol/champion-mastery/v4/champion-masteries/by-puuid/%s',
            $this->region(),
            $this->puuid
        );

        try {
            $response = $this->get($url);
            if (!$response->ok()) {
                Log::error('RiotApi@getChampionMastery: HTTP ' . $response->status() . ' puuid=' . $this->puuid);
                return null;
            }
            return $response->json() ?? null;
        } catch (\Exception $e) {
            Log::error('RiotApi@getChampionMastery: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Returns all unlocked LoL achievements (challenges + champion mastery)
     * formatted as badge records for BadgeService::synchronize().
     *
     * Each record: ['name', 'image', 'description', 'type']
     */
    public function getBadges(): array
    {
        if (!$this->resolvePUUID()) {
            return [];
        }

        return array_merge(
            $this->getChallengeBadges(),
            $this->getMasteryBadges()
        );
    }

    // -------------------------------------------------------------------------
    // Badge builders
    // -------------------------------------------------------------------------

    private function getChallengeBadges(): array
    {
        $playerChallenges = $this->getChallenges();
        if (empty($playerChallenges)) {
            return [];
        }

        // Keep only challenges the player has actually achieved
        $achieved = array_filter(
            $playerChallenges,
            fn($c) => in_array($c['level'] ?? '', self::ACHIEVED_LEVELS, true)
        );

        if (empty($achieved)) {
            return [];
        }

        // Fetch all challenge config definitions (names, descriptions)
        $configs    = $this->getAllChallengeConfigs();
        $configById = [];
        if (!empty($configs)) {
            foreach ($configs as $cfg) {
                $configById[(int) $cfg['id']] = $cfg;
            }
        }

        $badges = [];
        foreach ($achieved as $challenge) {
            if (count($badges) >= self::MAX_CHALLENGE_BADGES) {
                break;
            }

            $id    = (int) $challenge['challengeId'];
            $level = $challenge['level'];
            $cfg   = $configById[$id] ?? null;

            $name = $cfg['localizedNames']['en_US']['name']
                ?? $cfg['localizedNames'][array_key_first($cfg['localizedNames'] ?? [])][ 'name']
                ?? 'Challenge #' . $id;

            $desc = $cfg['localizedNames']['en_US']['shortDescription']
                ?? $cfg['localizedNames'][array_key_first($cfg['localizedNames'] ?? [])]['shortDescription']
                ?? '';

            $badges[] = [
                'name'        => $name,
                'image'       => $this->challengeTokenUrl($id, $level),
                'description' => $desc,
                'type'        => BadgeType::Common,
            ];
        }

        return $badges;
    }

    private function getMasteryBadges(): array
    {
        $masteries = $this->getChampionMastery();
        if (empty($masteries)) {
            return [];
        }

        // API returns sorted by points desc; filter to level 5+ and cap
        $masteries = array_filter($masteries, fn($m) => ($m['championLevel'] ?? 0) >= self::MASTERY_MIN_LEVEL);
        $masteries = array_slice(array_values($masteries), 0, self::MAX_MASTERY_BADGES);

        if (empty($masteries)) {
            return [];
        }

        // Champion ID → name lookup from Community Dragon (no version needed)
        $championNames = $this->getChampionNameMap();

        $badges = [];
        foreach ($masteries as $mastery) {
            $championId   = (int) $mastery['championId'];
            $level        = (int) $mastery['championLevel'];
            $points       = (int) ($mastery['championPoints'] ?? 0);
            $championName = $championNames[$championId] ?? ('Champion #' . $championId);

            $badges[] = [
                'name'        => "Mastery {$level} — {$championName}",
                'image'       => $this->championIconUrl($championId),
                'description' => number_format($points) . ' mastery points',
                'type'        => BadgeType::Common,
            ];
        }

        return $badges;
    }

    // -------------------------------------------------------------------------
    // Riot API helpers
    // -------------------------------------------------------------------------

    private function resolvePUUID(): bool
    {
        if ($this->puuid !== null) {
            return true;
        }

        $url = sprintf(
            'https://%s.api.riotgames.com/riot/account/v1/accounts/by-riot-id/%s/%s',
            self::ACCOUNT_CLUSTER,
            rawurlencode($this->gameName),
            rawurlencode($this->tagLine)
        );

        try {
            $response = $this->get($url);
            if (!$response->ok()) {
                Log::error('RiotApi@resolvePUUID: HTTP ' . $response->status()
                    . ' for ' . $this->gameName . '#' . $this->tagLine);
                return false;
            }
            $this->puuid = $response->json('puuid');
            return $this->puuid !== null;
        } catch (\Exception $e) {
            Log::error('RiotApi@resolvePUUID: ' . $e->getMessage());
            return false;
        }
    }

    private function getAllChallengeConfigs(): ?array
    {
        $url = sprintf(
            'https://%s.api.riotgames.com/lol/challenges/v1/challenges/config?locale=en_US',
            $this->region()
        );

        try {
            $response = $this->get($url);
            if (!$response->ok()) {
                Log::error('RiotApi@getAllChallengeConfigs: HTTP ' . $response->status());
                return null;
            }
            return $response->json() ?? null;
        } catch (\Exception $e) {
            Log::error('RiotApi@getAllChallengeConfigs: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch champion ID → display name from Community Dragon.
     * Uses numeric champion IDs directly so no Data Dragon version needed.
     *
     * @return array<int,string>  e.g. [1 => "Annie", 412 => "Thresh"]
     */
    private function getChampionNameMap(): array
    {
        try {
            $response = Http::get(
                'https://raw.communitydragon.org/latest/plugins/rcp-be-lol-game-data/global/default/v1/champion-summary.json'
            );
            if (!$response->ok()) {
                return [];
            }
            $map = [];
            foreach ($response->json() ?? [] as $champ) {
                if (isset($champ['id'], $champ['name'])) {
                    $map[(int) $champ['id']] = $champ['name'];
                }
            }
            return $map;
        } catch (\Exception $e) {
            Log::error('RiotApi@getChampionNameMap: ' . $e->getMessage());
            return [];
        }
    }

    // -------------------------------------------------------------------------
    // URL builders
    // -------------------------------------------------------------------------

    /**
     * Challenge level token image from Data Dragon CDN.
     * e.g. https://ddragon.leagueoflegends.com/cdn/img/challenges-images/101100-GOLD.png
     */
    private function challengeTokenUrl(int $challengeId, string $level): string
    {
        return "https://ddragon.leagueoflegends.com/cdn/img/challenges-images/{$challengeId}-{$level}.png";
    }

    /**
     * Champion square icon from Community Dragon (stable, no version required).
     * e.g. https://raw.communitydragon.org/.../champion-icons/412.png
     */
    private function championIconUrl(int $championId): string
    {
        return 'https://raw.communitydragon.org/latest/plugins/rcp-be-lol-game-data/global/default/v1/champion-icons/'
            . $championId . '.png';
    }

    // -------------------------------------------------------------------------
    // Infrastructure
    // -------------------------------------------------------------------------

    private function region(): string
    {
        return $this->platformRegion;
    }

    private function get(string $url): Response
    {
        return Http::withHeaders([
            'X-Riot-Token' => config('services.riot.api_key'),
        ])->get($url);
    }
}
