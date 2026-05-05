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
use Illuminate\Support\Facades\Schema;

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
            ->whereBetween('created_at', [$start60d, $start30d])
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
            ->whereBetween('created_at', [$start60d, $start30d])
            ->count();
        $delta30dGrants = $this->percentDelta($grantsPrev30d, $grants30d);

        return [
            'active_pursuers' => [
                'value' => $pursuers7d,
                'delta_7d' => $delta7d,
                'delta_label' => sprintf('%+.1f%% vs last 7d', $delta7d),
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
     */
    private function countActivePursuers($trophyIds, $badgeIds, Carbon $from, Carbon $to): int
    {
        $userIds = collect();

        $userIds = $userIds->concat(
            DB::table('badge_user')
                ->whereIn('badge_id', $badgeIds)
                ->whereBetween('created_at', [$from, $to])
                ->pluck('user_id')
        );

        $userIds = $userIds->concat(
            DB::table('trophy_user')
                ->whereIn('trophy_id', $trophyIds)
                ->whereBetween('created_at', [$from, $to])
                ->pluck('user_id')
        );

        if (Schema::hasTable('pursuits')) {
            $userIds = $userIds->concat(
                DB::table('pursuits')
                    ->whereIn('trophy_id', $trophyIds)
                    ->whereBetween('created_at', [$from, $to])
                    ->pluck('user_id')
            );
        }

        return $userIds->unique()->count();
    }

    /**
     * Array de N enteros con count de forges por día, oldest first.
     */
    private function buildForgesSparkline($trophyIds, int $days): array
    {
        $start = Carbon::now()->subDays($days)->startOfDay();
        $rows = DB::table('trophy_user')
            ->whereIn('trophy_id', $trophyIds)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $sparkline = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->format('Y-m-d');
            $sparkline[] = (int) ($rows[$day] ?? 0);
        }

        return $sparkline;
    }

    private function percentDelta(int $previous, int $current): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
