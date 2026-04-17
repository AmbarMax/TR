<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\BotPoll;
use App\Models\BotPollVote;
use App\Models\GuildConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            return response()->json(['poll' => null, 'error' => 'No guild connected.'], 200);
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

    public function results(Request $request, $id)
    {
        $guildConnection = $this->getGuildConnection();

        if (!$guildConnection) {
            return response()->json(['results' => [], 'total' => 0], 200);
        }

        $poll = BotPoll::where('id', $id)
            ->where('guild_id', $guildConnection->guild_id)
            ->firstOrFail();

        $voteCounts = BotPollVote::where('poll_id', $poll->id)
            ->select('answer', DB::raw('COUNT(*) as count'))
            ->groupBy('answer')
            ->pluck('count', 'answer');

        $options = collect($poll->options)->map(function ($opt) use ($voteCounts) {
            $label = is_array($opt) ? ($opt['label'] ?? $opt['value']) : $opt;
            $value = is_array($opt) ? ($opt['value'] ?? $opt['label']) : $opt;
            return [
                'label' => $label,
                'value' => $value,
                'count' => (int) ($voteCounts[$value] ?? 0),
            ];
        });

        return response()->json([
            'poll_id' => $poll->id,
            'title'   => $poll->title,
            'results' => $options,
            'total'   => $voteCounts->sum(),
        ], 200);
    }

    public function close(Request $request, $id)
    {
        $guildConnection = $this->getGuildConnection();

        if (!$guildConnection) {
            return response()->json(['poll' => null, 'error' => 'No guild connected.'], 200);
        }

        $poll = BotPoll::where('id', $id)
            ->where('guild_id', $guildConnection->guild_id)
            ->firstOrFail();

        $poll->update(['status' => 'closed']);

        return response()->json(['message' => 'Poll closed.', 'poll' => $poll], 200);
    }
}
