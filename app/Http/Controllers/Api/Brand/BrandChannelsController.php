<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\GuildConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandChannelsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $guildConnection = GuildConnection::where('org_id', $user->id)
            ->where('active', true)
            ->first();

        if (!$guildConnection) {
            return response()->json(['channels' => []], 200);
        }

        $channels = $guildConnection->channels()->get();

        return response()->json(['channels' => $channels], 200);
    }
}
