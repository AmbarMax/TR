<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use App\Models\UserLink;
use Illuminate\Http\Request;

class BotUserController extends Controller
{
    public function link(Request $request)
    {
        $request->validate([
            'discord_user_id'  => 'required|string',
            'tr_user_id'       => 'required|exists:users,id',
            'discord_username' => 'sometimes|string',
        ]);

        $guildConnection = $request->attributes->get('guildConnection');

        $userLink = UserLink::updateOrCreate(
            [
                'discord_user_id' => $request->discord_user_id,
                'guild_id'        => $guildConnection->guild_id,
            ],
            [
                'tr_user_id'       => $request->tr_user_id,
                'discord_username' => $request->discord_username ?? null,
                'linked_at'        => now(),
            ]
        );

        return response()->json([
            'message'   => 'User linked successfully.',
            'user_link' => $userLink,
        ], 201);
    }

    public function lookup($discord_user_id, Request $request)
    {
        $guildConnection = $request->attributes->get('guildConnection');

        $userLink = UserLink::where('discord_user_id', $discord_user_id)
            ->where('guild_id', $guildConnection->guild_id)
            ->first();

        if (!$userLink) {
            return response()->json(['error' => 'User link not found.'], 404);
        }

        return response()->json([
            'tr_user_id'       => $userLink->tr_user_id,
            'discord_username' => $userLink->discord_username,
        ], 200);
    }
}
