<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\BotPoll;
use App\Models\GuildConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandPollsController extends Controller
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
            return response()->json(['polls' => []], 200);
        }

        $polls = BotPoll::where('guild_id', $guildConnection->guild_id)
            ->with('badgeRule:id,name,trigger_type,trigger_config')
            ->latest()
            ->get();

        return response()->json(['polls' => $polls], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'options'        => 'required|array|min:2',
            'options.*'      => 'required|string',
            'channel_id'     => 'required|string',
            'duration_hours' => 'required|numeric|min:0.1',
            'badge_id'       => 'nullable|string|exists:badges,id',
        ]);

        $guildConnection = $this->getGuildConnection();

        if (!$guildConnection) {
            return response()->json(['error' => 'No guild connected.'], 404);
        }

        $poll = BotPoll::create([
            'guild_id'       => $guildConnection->guild_id,
            'title'          => $request->title,
            'options'        => $request->options,
            'channel_id'     => $request->channel_id,
            'duration_hours' => $request->duration_hours,
            'status'         => 'draft',
        ]);

        return response()->json(['poll' => $poll], 201);
    }

    public function close(Request $request, $id)
    {
        $guildConnection = $this->getGuildConnection();

        if (!$guildConnection) {
            return response()->json(['error' => 'No guild connected.'], 404);
        }

        $poll = BotPoll::where('id', $id)
            ->where('guild_id', $guildConnection->guild_id)
            ->firstOrFail();

        $poll->update(['status' => 'closed']);

        return response()->json(['message' => 'Poll closed.', 'poll' => $poll], 200);
    }
}
