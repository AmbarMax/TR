<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\BadgeRule;
use App\Models\GuildConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandRulesController extends Controller
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
            return response()->json(['rules' => []], 200);
        }

        $rules = $guildConnection->badgeRules()
            ->with('badge:id,name,image')
            ->get()
            ->map(function ($rule) {
                return [
                    'id'             => $rule->id,
                    'name'           => $rule->name,
                    'description'    => $rule->description,
                    'platform'       => $rule->platform,
                    'trigger_type'   => $rule->trigger_type,
                    'trigger_config' => $rule->trigger_config,
                    'threshold'      => $rule->trigger_config['threshold'] ?? null,
                    'active'         => $rule->active,
                    'badge'          => $rule->badge ? [
                        'id'    => $rule->badge->id,
                        'name'  => $rule->badge->name,
                        'image' => $rule->badge->image,
                    ] : null,
                ];
            });

        return response()->json(['rules' => $rules], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'trigger_type' => 'required|in:voice_minutes,message_count,reaction,event_join,poll_answer,role_obtain',
            'channel_id'   => 'nullable|string',
            'threshold'    => 'nullable|integer',
            'badge_id'     => 'required|string|exists:badges,id',
            'active'       => 'boolean',
        ]);

        $guildConnection = $this->getGuildConnection();

        if (!$guildConnection) {
            return response()->json(['error' => 'No guild connected.'], 404);
        }

        $rule = BadgeRule::create([
            'guild_id'       => $guildConnection->guild_id,
            'badge_id'       => $request->badge_id,
            'platform'       => 'discord',
            'trigger_type'   => $request->trigger_type,
            'trigger_config' => array_filter([
                'channel_id' => $request->channel_id,
                'threshold'  => $request->threshold,
            ]),
            'name'           => $request->name ?? $request->trigger_type,
            'description'    => $request->description ?? '',
            'active'         => $request->boolean('active', true),
        ]);

        $rule->load('badge:id,name,image');

        return response()->json(['rule' => $rule], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'active' => 'required|boolean',
        ]);

        $guildConnection = $this->getGuildConnection();

        if (!$guildConnection) {
            return response()->json(['error' => 'No guild connected.'], 404);
        }

        $rule = BadgeRule::where('id', $id)
            ->where('guild_id', $guildConnection->guild_id)
            ->firstOrFail();

        $rule->update(['active' => $request->boolean('active')]);

        return response()->json(['rule' => $rule], 200);
    }
}
