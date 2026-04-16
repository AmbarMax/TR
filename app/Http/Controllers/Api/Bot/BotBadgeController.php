<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use App\Models\BadgeRule;
use App\Models\BadgeUser;
use Illuminate\Http\Request;

class BotBadgeController extends Controller
{
    public function grant(Request $request)
    {
        $request->validate([
            'tr_user_id' => 'required|exists:users,id',
            'badge_id'   => 'required|exists:badges,id',
            'metadata'   => 'sometimes|array',
        ]);

        $guildConnection = $request->attributes->get('guildConnection');

        $ruleExists = BadgeRule::where('guild_id', $guildConnection->guild_id)
            ->where('badge_id', $request->badge_id)
            ->where('active', true)
            ->exists();

        if (!$ruleExists) {
            return response()->json([
                'error' => 'Badge does not belong to an active rule for this guild.',
            ], 403);
        }

        $existing = BadgeUser::where('user_id', $request->tr_user_id)
            ->where('badge_id', $request->badge_id)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Badge already granted.',
                'badge_user' => $existing,
            ], 200);
        }

        $badgeUser = BadgeUser::create([
            'user_id'  => $request->tr_user_id,
            'badge_id' => $request->badge_id,
            'display'  => true,
            'is_share' => false,
        ]);

        return response()->json([
            'message'    => 'Badge granted successfully.',
            'badge_user' => $badgeUser,
        ], 201);
    }
}
