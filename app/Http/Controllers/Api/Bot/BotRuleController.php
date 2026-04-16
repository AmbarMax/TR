<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BotRuleController extends Controller
{
    public function index(Request $request)
    {
        $guildConnection = $request->attributes->get('guildConnection');

        $rules = $guildConnection->badgeRules()
            ->where('active', true)
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
                    'badge'          => [
                        'id'    => $rule->badge->id,
                        'name'  => $rule->badge->name,
                        'image' => $rule->badge->image,
                    ],
                ];
            });

        return response()->json(['rules' => $rules], 200);
    }
}
