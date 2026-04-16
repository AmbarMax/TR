<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\BotEvent;
use App\Models\GuildConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandEventsController extends Controller
{
    private function getGuildConnection()
    {
        return GuildConnection::where('org_id', Auth::id())
            ->where('active', true)
            ->first();
    }

    public function index(Request $request)
    {
        $guildConnection = $this->getGuildConnection();

        if (!$guildConnection) {
            return response()->json(['error' => 'No guild connected.'], 404);
        }

        $events = BotEvent::where('guild_id', $guildConnection->guild_id)
            ->with('badgeRule:id,name,trigger_type,trigger_config')
            ->latest()
            ->get();

        return response()->json(['events' => $events], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'channel_id'  => 'required|string',
            'starts_at'   => 'required|date',
            'ends_at'     => 'required|date|after:starts_at',
            'badge_id'    => 'nullable|string|exists:badges,id',
        ]);

        $guildConnection = $this->getGuildConnection();

        if (!$guildConnection) {
            return response()->json(['error' => 'No guild connected.'], 404);
        }

        $event = BotEvent::create([
            'guild_id'    => $guildConnection->guild_id,
            'title'       => $request->title,
            'description' => $request->description ?? '',
            'channel_id'  => $request->channel_id,
            'starts_at'   => $request->starts_at,
            'ends_at'     => $request->ends_at,
            'status'      => 'draft',
        ]);

        return response()->json(['event' => $event], 201);
    }

    public function complete(Request $request, $id)
    {
        $guildConnection = $this->getGuildConnection();

        if (!$guildConnection) {
            return response()->json(['error' => 'No guild connected.'], 404);
        }

        $event = BotEvent::where('id', $id)
            ->where('guild_id', $guildConnection->guild_id)
            ->firstOrFail();

        $event->update(['status' => 'completed']);

        return response()->json(['message' => 'Event marked as completed.', 'event' => $event], 200);
    }
}
