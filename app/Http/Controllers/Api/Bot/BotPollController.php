<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use App\Models\BotPoll;
use Illuminate\Http\Request;

class BotPollController extends Controller
{
    public function pending(Request $request)
    {
        $guildConnection = $request->attributes->get('guildConnection');

        $polls = BotPoll::where('guild_id', $guildConnection->guild_id)
            ->where('status', 'draft')
            ->with('badgeRule:id,name,trigger_type,trigger_config')
            ->get();

        return response()->json(['polls' => $polls], 200);
    }

    public function markPublished(Request $request, $id)
    {
        $request->validate([
            'discord_message_id' => 'required|string',
        ]);

        $guildConnection = $request->attributes->get('guildConnection');

        $poll = BotPoll::where('id', $id)
            ->where('guild_id', $guildConnection->guild_id)
            ->firstOrFail();

        $poll->update([
            'status'             => 'active',
            'discord_message_id' => $request->discord_message_id,
        ]);

        return response()->json(['message' => 'Poll marked as published.', 'poll' => $poll], 200);
    }

    public function close(Request $request, $id)
    {
        $guildConnection = $request->attributes->get('guildConnection');

        $poll = BotPoll::where('id', $id)
            ->where('guild_id', $guildConnection->guild_id)
            ->firstOrFail();

        $poll->update(['status' => 'closed']);

        return response()->json(['message' => 'Poll closed.', 'poll' => $poll], 200);
    }
}
