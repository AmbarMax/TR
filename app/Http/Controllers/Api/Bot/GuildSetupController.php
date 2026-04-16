<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use App\Models\GuildConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuildSetupController extends Controller
{
    public function redirectToDiscord(Request $request)
    {
        $orgId = $request->query('org_id');

        if (!$orgId) {
            return response()->json(['error' => 'org_id is required.'], 422);
        }

        $state = Crypt::encryptString($orgId);

        $query = http_build_query([
            'client_id'     => config('services.discord_bot.client_id'),
            'permissions'   => '1099511627776',
            'scope'         => 'bot applications.commands',
            'redirect_uri'  => config('services.discord_bot.redirect'),
            'response_type' => 'code',
            'state'         => $state,
        ]);

        return redirect('https://discord.com/api/oauth2/authorize?' . $query);
    }

    public function handleCallback(Request $request)
    {
        $code        = $request->query('code');
        $guildId     = $request->query('guild_id');
        $stateParam  = $request->query('state');

        if (!$code || !$guildId || !$stateParam) {
            Log::warning('GuildSetupController@handleCallback: missing parameters', $request->query());
            return redirect(config('app.admin_url') . '?bot_setup=error&reason=missing_params');
        }

        try {
            $orgId = Crypt::decryptString($stateParam);
        } catch (\Exception $e) {
            Log::error('GuildSetupController@handleCallback: state decryption failed — ' . $e->getMessage());
            return redirect(config('app.admin_url') . '?bot_setup=error&reason=invalid_state');
        }

        $botApiKey = Str::random(64);

        GuildConnection::updateOrCreate(
            ['guild_id' => $guildId],
            [
                'org_id'           => $orgId,
                'bot_api_key'      => $botApiKey,
                'bot_connected_at' => now(),
                'active'           => true,
            ]
        );

        Log::info("GuildSetupController@handleCallback: guild {$guildId} connected for org {$orgId}");

        return redirect(config('app.admin_url') . '?bot_setup=success&guild_id=' . $guildId);
    }
}
