<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\BadgeRule;
use App\Models\GuildConnection;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class BrandBadgesController extends Controller
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
            return response()->json(['badges' => []], 200);
        }

        $badgeIds = BadgeRule::where('guild_id', $guildConnection->guild_id)
            ->pluck('badge_id');

        $badges = Badge::whereIn('id', $badgeIds)->get();

        return response()->json(['badges' => $badges], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'image'       => 'required|image|max:2048',
            'description' => 'nullable|string',
            'type'        => 'nullable|integer',
        ]);

        $storagePath = 'app/public/integrations/brand';

        try {
            if (!File::exists(storage_path($storagePath))) {
                File::makeDirectory(storage_path($storagePath), 0777, true, true);
            }

            $filename = Str::random(10) . '.png';
            $file = Image::make($request->file('image'));
            $file->save(storage_path($storagePath . '/' . $filename));
        } catch (\Exception $e) {
            Log::error('BrandBadgesController@store: ' . $e->getMessage());
            return response()->json(['error' => 'Image upload failed.'], 500);
        }

        $integration = Integration::firstOrCreate(
            ['name' => 'brand'],
            ['active' => true]
        );

        $badge = Badge::create([
            'integration_id' => $integration->id,
            'name'           => $request->name,
            'image'          => 'public/integrations/brand/' . $filename,
            'description'    => $request->description ?? '',
            'type'           => $request->input('type', 3),
        ]);

        return response()->json(['badge' => $badge], 201);
    }
}
