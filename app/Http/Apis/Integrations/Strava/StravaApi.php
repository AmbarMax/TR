<?php

namespace App\Http\Apis\Integrations\Strava;

use App\Enums\BadgeType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StravaApi
{
    private string $accessToken = '';

    private const BASE_URL = 'https://www.strava.com/api/v3';

    // Icon used for all Strava badges (stable Strava app icon)
    private const STRAVA_ICON = 'https://www.strava.com/apple-touch-icon.png';

    // Total running distance milestones in meters
    private const RUN_DISTANCE_MILESTONES = [
        50_000    => '50km Runner',
        100_000   => '100km Runner',
        500_000   => '500km Runner',
        1_000_000 => '1000km Runner',
        5_000_000 => '5000km Runner',
    ];

    // Total cycling distance milestones in meters
    private const RIDE_DISTANCE_MILESTONES = [
        100_000    => '100km Cyclist',
        500_000    => '500km Cyclist',
        1_000_000  => '1000km Cyclist',
        5_000_000  => '5000km Cyclist',
        10_000_000 => '10000km Cyclist',
    ];

    // Total activity count milestones (runs + rides + swims)
    private const ACTIVITY_COUNT_MILESTONES = [
        10  => '10 Activities',
        50  => '50 Activities',
        100 => '100 Activities',
        500 => '500 Activities',
    ];

    // Total elevation gain milestones in meters (runs + rides combined)
    private const ELEVATION_MILESTONES = [
        1_000  => '1000m Climber',
        5_000  => '5000m Climber',
        10_000 => '10000m Climber',
        50_000 => 'Summit Seeker',
    ];

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    public function setAuthData(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Fetch Strava athlete stats and derive milestone achievement badges.
     *
     * @return array<int, array{name: string, image: string, description: string, type: BadgeType}>
     */
    public function getBadges(): array
    {
        $athlete = $this->getAthlete();
        if (!$athlete) {
            return [];
        }

        $stats = $this->getAthleteStats((int) $athlete['id']);
        if (!$stats) {
            return [];
        }

        return array_merge(
            $this->getRunDistanceBadges($stats),
            $this->getRideDistanceBadges($stats),
            $this->getActivityCountBadges($stats),
            $this->getElevationBadges($stats),
            $this->getLongestActivityBadges($stats),
        );
    }

    /**
     * Fetch the authenticated athlete's profile.
     * Returns null on failure.
     */
    public function getAthlete(): ?array
    {
        try {
            $response = $this->get('/athlete');
            if (!$response->ok()) {
                Log::error('StravaApi@getAthlete: HTTP ' . $response->status());
                return null;
            }
            return $response->json();
        } catch (\Exception $e) {
            Log::error('StravaApi@getAthlete: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch all-time activity stats for an athlete.
     * Returns null on failure.
     */
    public function getAthleteStats(int $athleteId): ?array
    {
        try {
            $response = $this->get("/athletes/{$athleteId}/stats");
            if (!$response->ok()) {
                Log::error('StravaApi@getAthleteStats: HTTP ' . $response->status());
                return null;
            }
            return $response->json();
        } catch (\Exception $e) {
            Log::error('StravaApi@getAthleteStats: ' . $e->getMessage());
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Badge builders
    // -------------------------------------------------------------------------

    private function getRunDistanceBadges(array $stats): array
    {
        $total = (float) ($stats['all_run_totals']['distance'] ?? 0);
        $badges = [];
        foreach (self::RUN_DISTANCE_MILESTONES as $meters => $name) {
            if ($total >= $meters) {
                $km = number_format($meters / 1000);
                $badges[] = [
                    'name'        => $name,
                    'image'       => self::STRAVA_ICON,
                    'description' => "Ran a total of {$km}km or more on Strava",
                    'type'        => BadgeType::Common,
                ];
            }
        }
        return $badges;
    }

    private function getRideDistanceBadges(array $stats): array
    {
        $total = (float) ($stats['all_ride_totals']['distance'] ?? 0);
        $badges = [];
        foreach (self::RIDE_DISTANCE_MILESTONES as $meters => $name) {
            if ($total >= $meters) {
                $km = number_format($meters / 1000);
                $badges[] = [
                    'name'        => $name,
                    'image'       => self::STRAVA_ICON,
                    'description' => "Cycled a total of {$km}km or more on Strava",
                    'type'        => BadgeType::Common,
                ];
            }
        }
        return $badges;
    }

    private function getActivityCountBadges(array $stats): array
    {
        $total = (int) ($stats['all_run_totals']['count'] ?? 0)
               + (int) ($stats['all_ride_totals']['count'] ?? 0)
               + (int) ($stats['all_swim_totals']['count'] ?? 0);

        $badges = [];
        foreach (self::ACTIVITY_COUNT_MILESTONES as $threshold => $name) {
            if ($total >= $threshold) {
                $badges[] = [
                    'name'        => $name,
                    'image'       => self::STRAVA_ICON,
                    'description' => "Completed {$threshold} or more activities on Strava",
                    'type'        => BadgeType::Common,
                ];
            }
        }
        return $badges;
    }

    private function getElevationBadges(array $stats): array
    {
        $total = (float) ($stats['all_run_totals']['elevation_gain'] ?? 0)
               + (float) ($stats['all_ride_totals']['elevation_gain'] ?? 0);

        $badges = [];
        foreach (self::ELEVATION_MILESTONES as $meters => $name) {
            if ($total >= $meters) {
                $formatted = number_format($meters);
                $badges[] = [
                    'name'        => $name,
                    'image'       => self::STRAVA_ICON,
                    'description' => "Climbed {$formatted}m or more in total elevation on Strava",
                    'type'        => BadgeType::Common,
                ];
            }
        }
        return $badges;
    }

    private function getLongestActivityBadges(array $stats): array
    {
        $badges = [];

        $longestRun = (float) ($stats['biggest_run_distance'] ?? 0);
        if ($longestRun >= 42_195) {
            $badges[] = [
                'name'        => 'Marathon Finisher',
                'image'       => self::STRAVA_ICON,
                'description' => 'Completed a full marathon distance (42.195km) in a single run',
                'type'        => BadgeType::Common,
            ];
        } elseif ($longestRun >= 21_097) {
            $badges[] = [
                'name'        => 'Half Marathon Finisher',
                'image'       => self::STRAVA_ICON,
                'description' => 'Completed a half marathon distance (21.1km) in a single run',
                'type'        => BadgeType::Common,
            ];
        }

        $longestRide = (float) ($stats['biggest_ride_distance'] ?? 0);
        if ($longestRide >= 100_000) {
            $badges[] = [
                'name'        => 'Century Rider',
                'image'       => self::STRAVA_ICON,
                'description' => 'Completed a 100km+ ride in a single activity',
                'type'        => BadgeType::Common,
            ];
        }

        return $badges;
    }

    // -------------------------------------------------------------------------
    // Infrastructure
    // -------------------------------------------------------------------------

    private function get(string $endpoint)
    {
        return Http::withToken($this->accessToken)->get(self::BASE_URL . $endpoint);
    }
}
