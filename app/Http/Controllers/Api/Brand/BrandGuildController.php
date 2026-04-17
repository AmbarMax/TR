<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\GuildConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class BrandGuildController extends Controller
{
    public function index()
    {
        $guild = GuildConnection::where('org_id', Auth::id())
            ->where('active', true)
            ->first();

        return response()->json([
            'guild' => $guild ? [
                'id'         => $guild->id,
                'guild_id'   => $guild->guild_id,
                'guild_name' => $guild->guild_name,
            ] : null,
        ]);
    }

    public function connect(Request $request)
    {
        try {
            $user = JWTAuth::setToken($request->query('token'))->authenticate();
        } catch (\Exception $e) {
            return response('Unauthorized', 401);
        }

        $state  = 'brand_guild:' . $user->id;
        $params = http_build_query([
            'client_id'     => config('services.discord.client_id'),
            'redirect_uri'  => 'https://app.ambar.gg/api/discord/callback',
            'response_type' => 'code',
            'scope'         => 'guilds identify',
            'state'         => $state,
        ]);

        return redirect('https://discord.com/api/oauth2/authorize?' . $params);
    }

    public function handleCallback(Request $request)
    {
        $state  = $request->input('state', '');
        $userId = Str::after($state, 'brand_guild:');
        $code   = $request->input('code');

        if (!$code || !$userId) {
            return redirect('/brand-dashboard?guild_error=1');
        }

        $tokenRes = Http::asForm()->post('https://discord.com/api/oauth2/token', [
            'client_id'     => config('services.discord.client_id'),
            'client_secret' => config('services.discord.client_secret'),
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => 'https://app.ambar.gg/api/discord/callback',
        ]);

        if ($tokenRes->failed()) {
            Log::error('BrandGuildController@handleCallback token exchange failed: ' . $tokenRes->body());
            return redirect('/brand-dashboard?guild_error=1');
        }

        $accessToken = $tokenRes->json('access_token');

        $guildsRes = Http::withToken($accessToken)->get('https://discord.com/api/users/@me/guilds');

        if ($guildsRes->failed()) {
            Log::error('BrandGuildController@handleCallback guilds fetch failed: ' . $guildsRes->body());
            return redirect('/brand-dashboard?guild_error=1');
        }

        $ADMINISTRATOR = 0x8;
        $guilds = collect($guildsRes->json())
            ->filter(fn($g) => ((int) ($g['permissions'] ?? 0) & $ADMINISTRATOR) === $ADMINISTRATOR)
            ->map(fn($g) => ['id' => $g['id'], 'name' => $g['name']])
            ->values()
            ->all();

        $payload = base64_encode(json_encode([
            'user_id' => $userId,
            'guilds'  => $guilds,
        ]));

        return redirect('/brand-dashboard?guilds=' . urlencode($payload));
    }

    public function select(Request $request)
    {
        $request->validate([
            'guild_id'   => 'required|string',
            'guild_name' => 'required|string',
        ]);

        $userId   = Auth::id();
        $existing = GuildConnection::where('guild_id', $request->guild_id)->first();

        if ($existing) {
            $existing->update(['org_id' => $userId, 'active' => true]);
            $guild = $existing->fresh();
        } else {
            $guild = GuildConnection::create([
                'guild_id'    => $request->guild_id,
                'guild_name'  => $request->guild_name,
                'org_id'      => $userId,
                'bot_api_key' => Str::random(48),
                'active'      => true,
            ]);
        }

        return response()->json([
            'guild' => [
                'id'         => $guild->id,
                'guild_id'   => $guild->guild_id,
                'guild_name' => $guild->guild_name,
            ],
        ]);
    }

    public function disconnect()
    {
        $guild = GuildConnection::where('org_id', Auth::id())
            ->where('active', true)
            ->first();

        if ($guild) {
            $guild->update(['org_id' => null]);
        }

        return response()->json(['message' => 'Disconnected.']);
    }
}
