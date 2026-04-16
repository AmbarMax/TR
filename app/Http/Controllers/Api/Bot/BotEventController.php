<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use App\Models\BotEvent;
use Illuminate\Http\Request;

class BotEventController extends Controller
{
    public function pending(Request $request)
    {
        $guildConnection = $request->attributes->get('guildConnection');

        $events = BotEvent::where('guild_id', $guildConnection->guild_id)
            ->where('status', 'draft')
            ->with('badgeRule:id,name,trigger_type,trigger_config')
            ->get();

        return response()->json(['events' => $events], 200);
    }

    public function markScheduled(Request $request, $id)
    {
        $request->validate([
            'discord_event_id' => 'required|string',
        ]);

        $guildConnection = $request->attributes->get('guildConnection');

        $event = BotEvent::where('id', $id)
            ->where('guild_id', $guildConnection->guild_id)
            ->firstOrFail();

        $event->update([
            'status'           => 'scheduled',
            'discord_event_id' => $request->discord_event_id,
        ]);

        return response()->json(['message' => 'Event marked as scheduled.', 'event' => $event], 200);
    }

    public function complete(Request $request, $id)
    {
        $guildConnection = $request->attributes->get('guildConnection');

        $event = BotEvent::where('id', $id)
            ->where('guild_id', $guildConnection->guild_id)
            ->firstOrFail();

        $event->update(['status' => 'completed']);

        return response()->json(['message' => 'Event marked as completed.', 'event' => $event], 200);
    }
}
