<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use App\Models\GuildConnection;
use App\Models\UserLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BotLinkController extends Controller
{
    public function link(Request $request)
    {
        $discordUserId  = $request->query('discord_user_id');
        $guildId        = $request->query('guild_id');
        $discordUsername = $request->query('discord_username');

        $guildExists = GuildConnection::where('guild_id', $guildId)
            ->where('active', true)
            ->exists();

        if (!$guildExists) {
            return redirect('/#/link-discord?error=guild_not_found');
        }

        $query = http_build_query([
            'discord_user_id'  => $discordUserId,
            'guild_id'         => $guildId,
            'discord_username' => $discordUsername,
        ]);

        return redirect('/#/link-discord?' . $query);
    }

    public function confirmLink(Request $request)
    {
        $request->validate([
            'discord_user_id'  => 'required|string',
            'guild_id'         => 'required|string',
            'discord_username' => 'nullable|string',
        ]);

        $guildExists = GuildConnection::where('guild_id', $request->guild_id)
            ->where('active', true)
            ->exists();

        if (!$guildExists) {
            return response()->json(['error' => 'Guild not found or inactive.'], 404);
        }

        $existing = UserLink::where('discord_user_id', $request->discord_user_id)
            ->where('guild_id', $request->guild_id)
            ->first();

        if ($existing) {
            if ($existing->tr_user_id === Auth::user()->id) {
                return response()->json(['message' => 'Already linked.', 'user_link' => $existing], 200);
            }

            return response()->json(['error' => 'This Discord account is already linked to another user.'], 409);
        }

        $userLink = UserLink::create([
            'discord_user_id'  => $request->discord_user_id,
            'guild_id'         => $request->guild_id,
            'tr_user_id'       => Auth::user()->id,
            'discord_username' => $request->discord_username,
            'linked_at'        => now(),
        ]);

        return response()->json(['message' => 'Discord account linked successfully.', 'user_link' => $userLink], 201);
    }
}
