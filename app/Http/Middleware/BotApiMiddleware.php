<?php

namespace App\Http\Middleware;

use App\Models\GuildConnection;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BotApiMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $guildConnection = GuildConnection::where('bot_api_key', $token)
            ->where('active', true)
            ->first();

        if (!$guildConnection) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->merge(['guildConnection' => $guildConnection]);
        $request->attributes->set('guildConnection', $guildConnection);

        return $next($request);
    }
}
