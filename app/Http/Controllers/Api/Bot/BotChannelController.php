<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use App\Models\GuildChannel;
use Illuminate\Http\Request;

class BotChannelController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'channels'              => 'required|array',
            'channels.*.channel_id' => 'required|string',
            'channels.*.name'       => 'required|string',
            'channels.*.type'       => 'required|string',
            'channels.*.category'   => 'nullable|string',
        ]);

        $guildConnection = $request->attributes->get('guildConnection');

        GuildChannel::where('guild_id', $guildConnection->guild_id)->delete();

        $channels = collect($request->channels)->map(fn ($ch) => array_merge($ch, [
            'guild_id' => $guildConnection->guild_id,
        ]))->toArray();

        foreach ($channels as $channel) {
            GuildChannel::create($channel);
        }

        $guildConnection->update(['channel_cache_updated_at' => now()]);

        return response()->json(['message' => 'Channels synced successfully.'], 200);
    }

    public function index(Request $request)
    {
        $guildConnection = $request->attributes->get('guildConnection');

        $channels = GuildChannel::where('guild_id', $guildConnection->guild_id)->get();

        return response()->json(['channels' => $channels], 200);
    }
}
