<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\BadgeUser;
use App\Models\GuildConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandStatsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $guildConnection = GuildConnection::where('org_id', $user->id)
            ->where('active', true)
            ->first();

        if (!$guildConnection) {
            return response()->json(['error' => 'No guild connected.'], 404);
        }

        $linkedUsers   = $guildConnection->userLinks()->count();
        $activeRules   = $guildConnection->badgeRules()->where('active', true)->count();
        $syncedChannels = $guildConnection->channels()->count();

        $badgeIds = $guildConnection->badgeRules()->pluck('badge_id');
        $badgesGranted = BadgeUser::whereIn('badge_id', $badgeIds)->count();

        $recentActivity = $guildConnection->userLinks()
            ->latest('linked_at')
            ->limit(10)
            ->get(['discord_username', 'linked_at'])
            ->map(fn ($link) => [
                'type'    => 'user_linked',
                'user'    => $link->discord_username,
                'date'    => $link->linked_at,
            ]);

        return response()->json([
            'linked_users'    => $linkedUsers,
            'active_rules'    => $activeRules,
            'synced_channels' => $syncedChannels,
            'badges_granted'  => $badgesGranted,
            'recent_activity' => $recentActivity,
        ], 200);
    }
}
