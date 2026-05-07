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

    /**
     * GET /api/brand/analytics/campaigns
     *
     * Lista de "campaigns" (= trophies del brand) para la tabla del dashboard.
     * Query param ?sort acepta: created_at|pursuers|forges|conversion (default created_at desc).
     * v.2 devuelve hasta 10 sin paginación.
     *
     * Cache strategy: una sola entrada por brand con el dataset SIN sortear.
     * El sort vive en el endpoint público — evita 4x cache waste por sort
     * y deja la invalidación futura (Cache::forget) con un solo key.
     */
    public function campaigns(Request $request): JsonResponse
    {
        $user = Auth::user();
        abort_unless(
            $user->account_type === 'brand' || $user->hasAnyRole(['tr_admin', 'tr_superadmin']),
            403,
            'Brand account required'
        );

        $allowedSorts = ['created_at', 'pursuers', 'forges', 'conversion'];
        $sort = $request->query('sort', 'created_at');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $brandId = $user->id;
        $items = Cache::remember(
            "brand:{$brandId}:analytics:campaigns",
            now()->addMinutes(2),
            fn () => $this->buildCampaignsItems($brandId)
        );

        $sorted = match ($sort) {
            'pursuers' => collect($items)->sortByDesc('pursuers'),
            'forges' => collect($items)->sortByDesc('forges'),
            'conversion' => collect($items)->sortByDesc('conversion_percent'),
            default => collect($items)->sortByDesc('created_at'),
        };

        return response()->json([
            'data' => $sorted->values()->take(10)->all(),
            'meta' => [
                'total' => count($items),
                'per_page' => 10,
                'current_page' => 1,
            ],
        ]);
    }

    /**
     * Items del brand para la tabla campaigns. Devuelve array sin sortear
     * y sin meta — el sort y la pagination viven en el endpoint público.
     */
    private function buildCampaignsItems(string $brandId): array
    {
        // Single query con LEFT JOINs + COUNT(DISTINCT). El cross-product
        // entre trophy_user y pursuits no infla el count gracias al DISTINCT.
        // Para v.2 con ~10 trofeos por brand, performance OK.
        $rows = DB::table('trophies as t')
            ->leftJoin('trophy_user as tu', function ($join) {
                $join->on('tu.trophy_id', '=', 't.id')
                     ->whereNull('tu.deleted_at');
            })
            ->leftJoin('pursuits as p', 'p.trophy_id', '=', 't.id')
            ->where('t.user_id', $brandId)
            ->whereNull('t.deleted_at')
            ->select(
                't.id as trophy_id',
                't.name',
                't.published_at',
                't.created_at',
                't.image as thumbnail_url',
                DB::raw('COUNT(DISTINCT p.user_id) as pursuers'),
                DB::raw('COUNT(DISTINCT tu.id) as forges')
            )
            ->groupBy('t.id', 't.name', 't.published_at', 't.created_at', 't.image')
            ->get();

        return $rows->map(function ($r) {
            $pursuers = (int) $r->pursuers;
            $forges = (int) $r->forges;
            $conversion = $pursuers === 0
                ? 0
                : round(($forges / $pursuers) * 100, 2);

            return [
                'trophy_id' => $r->trophy_id,
                'name' => $r->name,
                // 'expired' status no implementado en v.2: schema no tiene
                // deadline column. Pendiente para deuda menor del cleanup.
                'status' => $r->published_at !== null ? 'active' : 'draft',
                'created_at' => Carbon::parse($r->created_at)
                    ->setTimezone('UTC')
                    ->format('Y-m-d\TH:i:s.u\Z'),
                'pursuers' => $pursuers,
                'forges' => $forges,
                'conversion_percent' => $conversion,
                'thumbnail_url' => (string) $r->thumbnail_url,
            ];
        })->all();
    }

    /**
     * GET /api/brand/analytics/activity
     *
     * Activity feed cronológico. Query param ?limit (default 20, range 1-50).
     * Cache TTL 30s para sensación "live".
     */
    public function activity(Request $request): JsonResponse
    {
        $user = Auth::user();
        abort_unless(
            $user->account_type === 'brand' || $user->hasAnyRole(['tr_admin', 'tr_superadmin']),
            403,
            'Brand account required'
        );

        $rawLimit = $request->query('limit', 20);
        if (!is_numeric($rawLimit)) {
            $limit = 20;
        } else {
            $limit = (int) $rawLimit;
            if ($limit < 1) $limit = 1;
            if ($limit > 50) $limit = 50;
        }

        $brandId = $user->id;
        $items = Cache::remember(
            "brand:{$brandId}:analytics:activity:{$limit}",
            now()->addSeconds(30),
            fn () => $this->buildActivityItems($brandId, $limit)
        );

        return response()->json(['data' => $items]);
    }

    /**
     * Mezcla de los 4 tipos de eventos del activity feed.
     *
     * Approach B: cada source query trae hasta $limit rows, después en PHP
     * unimos todas, ordenamos por timestamp desc, y truncamos al $limit final.
     * Memoria max: ~4 × $limit rows (≤200).
     *
     * Limitación conocida: si un source es mucho más activo que los demás
     * (ej. miles de grants vs decenas de pursuits), el limit por source
     * pierde eventos viejos del activo. Aceptable para v.2; deuda para v.3
     * (probablemente requiera UNION ALL en SQL con sort global).
     */
    private function buildActivityItems(string $brandId, int $limit): array
    {
        $trophyIds = Trophy::where('user_id', $brandId)->pluck('id');
        $badgeIds = DB::table('badge_trophy')
            ->whereIn('trophy_id', $trophyIds)
            ->whereNull('deleted_at')
            ->pluck('badge_id');
        $brandUserIds = DB::table('badge_user as bu')
            ->join('users as u', 'u.id', '=', 'bu.user_id')
            ->whereIn('bu.badge_id', $badgeIds)
            ->whereNull('bu.deleted_at')
            ->whereNull('u.deleted_at')
            ->distinct()
            ->pluck('bu.user_id');

        $events = collect()
            ->concat($this->fetchForgeEvents($trophyIds, $limit))
            ->concat($this->fetchGrantEvents($badgeIds, $limit))
            ->concat($this->fetchPursuitEvents($trophyIds, $limit))
            ->concat($this->fetchCrossHitEvents($brandUserIds, $badgeIds, $brandId, $limit));

        return $events
            ->sortByDesc('timestamp')
            ->values()
            ->take($limit)
            ->all();
    }

    private function fetchForgeEvents($trophyIds, int $limit): array
    {
        if ($trophyIds->isEmpty()) return [];

        $rows = DB::table('trophy_user as tu')
            ->join('users as u', 'u.id', '=', 'tu.user_id')
            ->join('trophies as t', 't.id', '=', 'tu.trophy_id')
            ->whereIn('tu.trophy_id', $trophyIds)
            ->whereNull('tu.deleted_at')
            ->whereNull('u.deleted_at')
            ->whereNull('t.deleted_at')
            ->select('tu.id', 'tu.created_at', 'u.username', 'u.avatar', 't.id as trophy_id', 't.name as trophy_name')
            ->orderByDesc('tu.created_at')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => $this->formatEvent(
            "evt_forge_{$r->id}",
            'trophy_forged',
            'T',
            $r,
            ['trophy_name' => $r->trophy_name, 'trophy_id' => $r->trophy_id]
        ))->all();
    }

    private function fetchGrantEvents($badgeIds, int $limit): array
    {
        if ($badgeIds->isEmpty()) return [];

        $rows = DB::table('badge_user as bu')
            ->join('users as u', 'u.id', '=', 'bu.user_id')
            ->join('badges as b', 'b.id', '=', 'bu.badge_id')
            ->join('integrations as i', 'i.id', '=', 'b.integration_id')
            ->whereIn('bu.badge_id', $badgeIds)
            ->whereNull('bu.deleted_at')
            ->whereNull('u.deleted_at')
            ->whereNull('b.deleted_at')
            ->whereNull('i.deleted_at')
            ->select('bu.id', 'bu.created_at', 'u.username', 'u.avatar', 'b.name as badge_name', 'i.name as platform')
            ->orderByDesc('bu.created_at')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => $this->formatEvent(
            "evt_grant_{$r->id}",
            'badge_granted',
            'B',
            $r,
            ['badge_name' => $r->badge_name, 'platform' => $r->platform]
        ))->all();
    }

    private function fetchPursuitEvents($trophyIds, int $limit): array
    {
        if ($trophyIds->isEmpty()) return [];

        // pursuits no tiene deleted_at en schema; las otras tablas sí.
        $rows = DB::table('pursuits as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->join('trophies as t', 't.id', '=', 'p.trophy_id')
            ->whereIn('p.trophy_id', $trophyIds)
            ->whereNull('u.deleted_at')
            ->whereNull('t.deleted_at')
            ->select('p.id', 'p.created_at', 'u.username', 'u.avatar', 't.id as trophy_id', 't.name as trophy_name')
            ->orderByDesc('p.created_at')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => $this->formatEvent(
            "evt_pursuit_{$r->id}",
            'pursuer_started',
            'P',
            $r,
            ['trophy_name' => $r->trophy_name, 'trophy_id' => $r->trophy_id]
        ))->all();
    }

    private function fetchCrossHitEvents($brandUserIds, $badgeIds, string $brandId, int $limit): array
    {
        if ($brandUserIds->isEmpty()) return [];

        $rows = DB::table('badge_user as bu')
            ->join('users as actor', 'actor.id', '=', 'bu.user_id')
            ->join('badges as b', 'b.id', '=', 'bu.badge_id')
            ->join('badge_trophy as bt', 'bt.badge_id', '=', 'b.id')
            ->join('trophies as t', 't.id', '=', 'bt.trophy_id')
            ->join('users as ob', 'ob.id', '=', 't.user_id')
            ->whereIn('bu.user_id', $brandUserIds)
            ->whereNotIn('bu.badge_id', $badgeIds)
            ->where('ob.account_type', 'brand')
            ->where('ob.id', '!=', $brandId)
            ->whereNull('bu.deleted_at')
            ->whereNull('actor.deleted_at')
            ->whereNull('b.deleted_at')
            ->whereNull('bt.deleted_at')
            ->whereNull('t.deleted_at')
            ->whereNull('ob.deleted_at')
            ->select('bu.id', 'bu.created_at', 'actor.username', 'actor.avatar', 'ob.username as other_brand')
            ->orderByDesc('bu.created_at')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => $this->formatEvent(
            "evt_xhit_{$r->id}",
            'cross_hall_hit',
            'X',
            $r,
            ['other_brand' => $r->other_brand]
        ))->all();
    }

    /**
     * Shape común de un evento del activity feed. $row debe tener
     * username + avatar + created_at.
     */
    private function formatEvent(string $id, string $type, string $icon, $row, array $target): array
    {
        $createdAt = Carbon::parse($row->created_at);

        return [
            'id' => $id,
            'type' => $type,
            'icon' => $icon,
            'actor' => [
                'username' => $row->username,
                'avatar_url' => (string) $row->avatar,
            ],
            'target' => $target,
            'timestamp' => $createdAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z'),
            'human_time' => $createdAt->diffForHumans(),
        ];
    }

    private function buildPerformancePayload(string $brandId): array
    {
        $trophyIds = Trophy::where('user_id', $brandId)->pluck('id');
        $badgeIds = DB::table('badge_trophy')
            ->whereIn('trophy_id', $trophyIds)
            ->whereNull('deleted_at')
            ->pluck('badge_id');

        $now = Carbon::now();
        $start7d = $now->copy()->subDays(7);
        $start14d = $now->copy()->subDays(14);
        $start30d = $now->copy()->subDays(30);
        $start60d = $now->copy()->subDays(60);

        $pursuers7d = $this->countActivePursuers($trophyIds, $badgeIds, $start7d, $now);
        $pursuersPrev7d = $this->countActivePursuers($trophyIds, $badgeIds, $start14d, $start7d);
        $delta7d = $this->percentDelta($pursuersPrev7d, $pursuers7d);

        $forgesAll = DB::table('trophy_user')
            ->whereIn('trophy_id', $trophyIds)
            ->whereNull('deleted_at')
            ->count();
        $forges30d = DB::table('trophy_user')
            ->whereIn('trophy_id', $trophyIds)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $start30d)
            ->count();
        $forgesPrev30d = DB::table('trophy_user')
            ->whereIn('trophy_id', $trophyIds)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $start60d)
            ->where('created_at', '<', $start30d)
            ->count();
        $delta30dForges = $this->percentDelta($forgesPrev30d, $forges30d);

        $sparkline = $this->buildForgesSparkline($trophyIds, 30);

        $grantsAll = DB::table('badge_user')
            ->whereIn('badge_id', $badgeIds)
            ->whereNull('deleted_at')
            ->count();
        $grants30d = DB::table('badge_user')
            ->whereIn('badge_id', $badgeIds)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $start30d)
            ->count();
        $grantsPrev30d = DB::table('badge_user')
            ->whereIn('badge_id', $badgeIds)
            ->whereNull('deleted_at')
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
                ->whereNull('deleted_at')
                ->where('created_at', '>=', $from)
                ->where('created_at', '<', $to)
                ->pluck('user_id')
        );

        $userIds = $userIds->concat(
            DB::table('trophy_user')
                ->whereIn('trophy_id', $trophyIds)
                ->whereNull('deleted_at')
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
            ->whereNull('deleted_at')
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
