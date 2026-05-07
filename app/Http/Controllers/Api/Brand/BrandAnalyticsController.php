<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\Trophy;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BrandAnalyticsController extends Controller
{
    /**
     * GET /api/brand/analytics/performance
     *
     * Hero cards del Performance Overview: active_pursuers (7d), trophies_forged
     * (total + sparkline 30d), badges_granted (total), cpt (locked v.3).
     */
    public function performance(Request $request): JsonResponse
    {
        $user = Auth::user();
        abort_unless(
            $user->account_type === 'brand' || $user->hasAnyRole(['tr_admin', 'tr_superadmin']),
            403,
            'Brand account required'
        );

        $brandId = $user->id;
        $payload = Cache::remember(
            "brand:{$brandId}:analytics:performance",
            now()->addMinutes(5),
            fn () => $this->buildPerformancePayload($brandId)
        );

        return response()->json($payload);
    }

    /**
     * GET /api/brand/analytics/secondary-metrics
     *
     * Strip de 4 datos secundarios: total_badges_granted, cross_hall_overlap,
     * multi_platform_users_percent, achievement_velocity.
     */
    public function secondaryMetrics(Request $request): JsonResponse
    {
        $user = Auth::user();
        abort_unless(
            $user->account_type === 'brand' || $user->hasAnyRole(['tr_admin', 'tr_superadmin']),
            403,
            'Brand account required'
        );

        $brandId = $user->id;
        $payload = Cache::remember(
            "brand:{$brandId}:analytics:secondary",
            now()->addMinutes(5),
            fn () => $this->buildSecondaryMetricsPayload($brandId)
        );

        return response()->json($payload);
    }

    private function buildSecondaryMetricsPayload(string $brandId): array
    {
        $trophyIds = Trophy::where('user_id', $brandId)->pluck('id');
        $badgeIds = DB::table('badge_trophy')
            ->whereIn('trophy_id', $trophyIds)
            ->pluck('badge_id');

        // Users que tienen ≥1 badge del brand. Base para cross_hall y multi_platform.
        // Filtra grants y users soft-deleted en la fuente para que el denominador
        // de cross_hall_overlap y multi_platform_users_percent no se infle con
        // entidades disabled.
        $brandUserIds = DB::table('badge_user as bu')
            ->join('users as u', 'u.id', '=', 'bu.user_id')
            ->whereIn('bu.badge_id', $badgeIds)
            ->whereNull('bu.deleted_at')
            ->whereNull('u.deleted_at')
            ->distinct()
            ->pluck('bu.user_id');

        $totalGranted = DB::table('badge_user')
            ->whereIn('badge_id', $badgeIds)
            ->whereNull('deleted_at')
            ->count();

        return [
            'total_badges_granted' => [
                'value' => $totalGranted,
                'label' => 'verified actions',
            ],
            'cross_hall_overlap' => $this->buildCrossHallOverlap($brandId, $brandUserIds),
            'multi_platform_users_percent' => $this->multiPlatformPercent($brandUserIds),
            'achievement_velocity' => [
                'value' => $this->achievementVelocity($trophyIds, $badgeIds),
                'label' => 'per pursuer per day',
            ],
        ];
    }

    /**
     * Top 3 brands con mayor % de users compartidos con el brand actual.
     * Excluye brands con overlap = 0 (INNER JOINs naturalmente).
     * Single query con GROUP BY para evitar N+1.
     */
    private function buildCrossHallOverlap(string $brandId, $brandUserIds): array
    {
        if ($brandUserIds->isEmpty()) {
            return [];
        }

        $denominator = $brandUserIds->count();

        $rows = DB::table('badge_user as bu')
            ->join('badges as b', 'b.id', '=', 'bu.badge_id')
            ->join('badge_trophy as bt', 'bt.badge_id', '=', 'b.id')
            ->join('trophies as t', 't.id', '=', 'bt.trophy_id')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where('u.account_type', 'brand')
            ->where('u.id', '!=', $brandId)
            ->whereIn('bu.user_id', $brandUserIds)
            ->whereNull('u.deleted_at')
            ->whereNull('t.deleted_at')
            ->whereNull('b.deleted_at')
            ->whereNull('bu.deleted_at')
            ->select('u.username', DB::raw('COUNT(DISTINCT bu.user_id) as overlap_count'))
            ->groupBy('u.id', 'u.username')
            ->orderByDesc('overlap_count')
            ->limit(3)
            ->get();

        return $rows->map(fn ($r) => [
            'brand_username' => $r->username,
            'overlap_percent' => (int) round(($r->overlap_count / $denominator) * 100),
        ])->values()->all();
    }

    /**
     * % de users del brand con ≥2 providers DISTINTOS en auth_integrations.
     */
    private function multiPlatformPercent($brandUserIds): int
    {
        if ($brandUserIds->isEmpty()) {
            return 0;
        }

        $multiCount = DB::table('auth_integrations')
            ->whereIn('user_id', $brandUserIds)
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(DISTINCT name) >= 2')
            ->get()
            ->count();

        return (int) round(($multiCount / $brandUserIds->count()) * 100);
    }

    /**
     * Velocity: grants_30d / pursuers_30d / 30. Float 1 decimal.
     */
    private function achievementVelocity($trophyIds, $badgeIds): float
    {
        $now = Carbon::now();
        $start30d = $now->copy()->subDays(30);

        $grants30d = DB::table('badge_user')
            ->whereIn('badge_id', $badgeIds)
            ->where('created_at', '>=', $start30d)
            ->count();

        $pursuers30d = $this->countActivePursuers($trophyIds, $badgeIds, $start30d, $now);

        if ($pursuers30d === 0 || $grants30d === 0) {
            return 0.0;
        }

        return round($grants30d / $pursuers30d / 30, 1);
    }

    /**
     * GET /api/brand/analytics/audience
     *
     * 4 cards: platforms_breakdown, top_achievements,
     * keywords_cross_discord (always [] in v.2), funnel.
     */
    public function audience(Request $request): JsonResponse
    {
        $user = Auth::user();
        abort_unless(
            $user->account_type === 'brand' || $user->hasAnyRole(['tr_admin', 'tr_superadmin']),
            403,
            'Brand account required'
        );

        $brandId = $user->id;
        $payload = Cache::remember(
            "brand:{$brandId}:analytics:audience",
            now()->addMinutes(5),
            fn () => $this->buildAudiencePayload($brandId)
        );

        return response()->json($payload);
    }

    private function buildAudiencePayload(string $brandId): array
    {
        $trophyIds = Trophy::where('user_id', $brandId)->pluck('id');
        $badgeIds = DB::table('badge_trophy')
            ->whereIn('trophy_id', $trophyIds)
            ->pluck('badge_id');

        $brandUserIds = DB::table('badge_user as bu')
            ->join('users as u', 'u.id', '=', 'bu.user_id')
            ->whereIn('bu.badge_id', $badgeIds)
            ->whereNull('bu.deleted_at')
            ->whereNull('u.deleted_at')
            ->distinct()
            ->pluck('bu.user_id');

        return [
            'platforms_breakdown' => $this->buildPlatformsBreakdown($brandUserIds),
            'top_achievements' => $this->buildTopAchievements($badgeIds),
            // TODO: implement when Discord message ingestion is in place.
            // v.2 returns empty by design — frontend renders "Connect Discord to unlock".
            'keywords_cross_discord' => [],
            'funnel' => $this->buildFunnel($trophyIds, $badgeIds),
        ];
    }

    /**
     * Distribución de users del brand por su provider PRIMARIO (más antiguo
     * por created_at). Single query + group-by en PHP — más simple y portable
     * que window functions, y el dataset es chico (~brandUserIds × ~2 rows).
     */
    private function buildPlatformsBreakdown($brandUserIds): array
    {
        if ($brandUserIds->isEmpty()) {
            return [];
        }

        $rows = DB::table('auth_integrations')
            ->whereIn('user_id', $brandUserIds)
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->get(['user_id', 'name']);

        $primary = [];
        foreach ($rows as $row) {
            if (!isset($primary[$row->user_id])) {
                $primary[$row->user_id] = $row->name;
            }
        }

        if (empty($primary)) {
            return [];
        }

        $counts = array_count_values($primary);
        $total = array_sum($counts);
        arsort($counts);

        return collect($counts)->map(fn ($count, $name) => [
            'platform' => $name,
            'user_count' => $count,
            'percent' => round(($count / $total) * 100, 1),
        ])->values()->all();
    }

    /**
     * Top 3 badges del brand por count de grants.
     * INNER JOIN a integrations excluye badges cuya integration está soft-deleted.
     */
    private function buildTopAchievements($badgeIds): array
    {
        if ($badgeIds->isEmpty()) {
            return [];
        }

        $rows = DB::table('badges as b')
            ->join('badge_user as bu', 'bu.badge_id', '=', 'b.id')
            ->join('integrations as i', 'i.id', '=', 'b.integration_id')
            ->whereIn('b.id', $badgeIds)
            ->whereNull('b.deleted_at')
            ->whereNull('bu.deleted_at')
            ->whereNull('i.deleted_at')
            ->select(
                'b.id as badge_id',
                'b.name as badge_name',
                'i.name as platform',
                DB::raw('COUNT(bu.id) as grants')
            )
            ->groupBy('b.id', 'b.name', 'i.name')
            ->orderByDesc('grants')
            ->limit(3)
            ->get();

        return $rows->map(fn ($r) => [
            'badge_id' => $r->badge_id,
            'badge_name' => $r->badge_name,
            'grants' => (int) $r->grants,
            'platform' => $r->platform,
        ])->values()->all();
    }

    /**
     * 3 etapas del funnel + conversion %.
     * pursuits no tiene deleted_at en schema → no filter de soft-delete ahí.
     * Las otras dos pivots sí filtran.
     */
    private function buildFunnel($trophyIds, $badgeIds): array
    {
        $startedPursuit = DB::table('pursuits')
            ->whereIn('trophy_id', $trophyIds)
            ->count();

        $earnedFirstBadge = DB::table('badge_user')
            ->whereIn('badge_id', $badgeIds)
            ->whereNull('deleted_at')
            ->select(DB::raw('COUNT(DISTINCT user_id) as cnt'))
            ->value('cnt');

        $forgedTrophy = DB::table('trophy_user')
            ->whereIn('trophy_id', $trophyIds)
            ->whereNull('deleted_at')
            ->count();

        $conversion = $startedPursuit === 0
            ? 0.0
            : round(($forgedTrophy / $startedPursuit) * 100, 2);

        return [
            'started_pursuit' => $startedPursuit,
            'earned_first_badge' => (int) $earnedFirstBadge,
            'forged_trophy' => $forgedTrophy,
            'conversion_start_to_forge_percent' => $conversion,
        ];
    }

    private function buildPerformancePayload(string $brandId): array
    {
        $trophyIds = Trophy::where('user_id', $brandId)->pluck('id');
        $badgeIds = DB::table('badge_trophy')
            ->whereIn('trophy_id', $trophyIds)
            ->pluck('badge_id');

        $now = Carbon::now();
        $start7d = $now->copy()->subDays(7);
        $start14d = $now->copy()->subDays(14);
        $start30d = $now->copy()->subDays(30);
        $start60d = $now->copy()->subDays(60);

        $pursuers7d = $this->countActivePursuers($trophyIds, $badgeIds, $start7d, $now);
        $pursuersPrev7d = $this->countActivePursuers($trophyIds, $badgeIds, $start14d, $start7d);
        $delta7d = $this->percentDelta($pursuersPrev7d, $pursuers7d);

        $forgesAll = DB::table('trophy_user')->whereIn('trophy_id', $trophyIds)->count();
        $forges30d = DB::table('trophy_user')
            ->whereIn('trophy_id', $trophyIds)
            ->where('created_at', '>=', $start30d)
            ->count();
        $forgesPrev30d = DB::table('trophy_user')
            ->whereIn('trophy_id', $trophyIds)
            ->where('created_at', '>=', $start60d)
            ->where('created_at', '<', $start30d)
            ->count();
        $delta30dForges = $this->percentDelta($forgesPrev30d, $forges30d);

        $sparkline = $this->buildForgesSparkline($trophyIds, 30);

        $grantsAll = DB::table('badge_user')->whereIn('badge_id', $badgeIds)->count();
        $grants30d = DB::table('badge_user')
            ->whereIn('badge_id', $badgeIds)
            ->where('created_at', '>=', $start30d)
            ->count();
        $grantsPrev30d = DB::table('badge_user')
            ->whereIn('badge_id', $badgeIds)
            ->where('created_at', '>=', $start60d)
            ->where('created_at', '<', $start30d)
            ->count();
        $delta30dGrants = $this->percentDelta($grantsPrev30d, $grants30d);

        return [
            'active_pursuers' => [
                'value' => $pursuers7d,
                'delta_7d' => $delta7d,
                'delta_label' => $delta7d === null
                    ? 'New (no prior data)'
                    : sprintf('%+.1f%% vs last 7d', $delta7d),
            ],
            'trophies_forged' => [
                'value' => $forgesAll,
                'delta_30d' => $delta30dForges,
                'sparkline' => $sparkline,
            ],
            'badges_granted' => [
                'value' => $grantsAll,
                'delta_30d' => $delta30dGrants,
            ],
            'cpt' => [
                'locked' => true,
                'label' => 'Coming soon',
                'tooltip' => 'Cost per trophy will be available with billing in v.3',
            ],
        ];
    }

    /**
     * Distinct user_ids con interacción en trofeos del brand entre $from y $to.
     * Interacción = badge granted | trophy forged | pursuit started.
     * Boundary: [$from, $to) — inclusive en el inicio, exclusivo en el fin.
     * Esto hace la función segura para reusar en períodos contiguos sin doble conteo.
     */
    private function countActivePursuers($trophyIds, $badgeIds, Carbon $from, Carbon $to): int
    {
        $userIds = collect();

        $userIds = $userIds->concat(
            DB::table('badge_user')
                ->whereIn('badge_id', $badgeIds)
                ->where('created_at', '>=', $from)
                ->where('created_at', '<', $to)
                ->pluck('user_id')
        );

        $userIds = $userIds->concat(
            DB::table('trophy_user')
                ->whereIn('trophy_id', $trophyIds)
                ->where('created_at', '>=', $from)
                ->where('created_at', '<', $to)
                ->pluck('user_id')
        );

        $userIds = $userIds->concat(
            DB::table('pursuits')
                ->whereIn('trophy_id', $trophyIds)
                ->where('created_at', '>=', $from)
                ->where('created_at', '<', $to)
                ->pluck('user_id')
        );

        return $userIds->unique()->count();
    }

    /**
     * Array de N enteros con count de forges por día, oldest first.
     * Excluye el día actual (parcial) — rango es [hoy-N, hoy-1].
     */
    private function buildForgesSparkline($trophyIds, int $days): array
    {
        $startDay = Carbon::now()->subDays($days)->startOfDay();
        $endDay = Carbon::now()->subDay()->endOfDay();

        $rows = DB::table('trophy_user')
            ->whereIn('trophy_id', $trophyIds)
            ->whereBetween('created_at', [$startDay, $endDay])
            ->selectRaw('DATE(created_at) as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $sparkline = [];
        for ($i = $days; $i >= 1; $i--) {
            $day = Carbon::now()->subDays($i)->format('Y-m-d');
            $sparkline[] = (int) ($rows[$day] ?? 0);
        }

        return $sparkline;
    }

    private function percentDelta(int $previous, int $current): ?float
    {
        if ($previous === 0) {
            return $current > 0 ? null : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
