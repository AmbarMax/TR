<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Models\Trophy;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BrandTrophiesController extends Controller
{
    private FileService $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function scopedTrophy(string $id): Trophy
    {
        return Trophy::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    private function format(Trophy $trophy): array
    {
        $trophy->loadMissing('badges');

        return [
            'id'           => $trophy->id,
            'name'         => $trophy->name,
            'description'  => $trophy->description,
            'image'        => $trophy->image,
            'image_url'    => $trophy->image ? asset('storage/trophies/' . $trophy->image) : null,
            'type'         => $trophy->type,
            'price'        => (float) $trophy->price,
            'receive'      => (float) $trophy->receive,
            'weight'       => (int) $trophy->weight,
            'availability' => $trophy->max_supply,
            'forged_count' => $trophy->users()->count(),
            'badges'       => $trophy->badges->map(fn ($b) => [
                'id'    => $b->id,
                'name'  => $b->name,
                'image' => $b->image,
            ])->values(),
        ];
    }

    // ─── GET /api/brand/trophies ─────────────────────────────────────────────

    public function index()
    {
        $trophies = Trophy::where('user_id', Auth::id())
            ->with('badges')
            ->latest()
            ->get()
            ->map(fn ($t) => $this->format($t));

        return response()->json(['trophies' => $trophies], 200);
    }

    // ─── POST /api/brand/trophies ────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'image'        => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'type'         => 'in:trophy,key',
            'price'        => 'required|numeric|min:0',
            'receive'      => 'required|numeric|min:0',
            'weight'       => 'nullable|integer|min:0',
            'availability' => 'nullable|integer|min:1',
            'badge_ids'    => 'nullable|array',
            'badge_ids.*'  => 'exists:badges,id',
        ]);

        DB::beginTransaction();
        try {
            $filename = $this->fileService->saveTrophyImage($request->file('image'));

            if (!$filename) {
                DB::rollBack();
                return response()->json(['error' => 'Image upload failed.'], 500);
            }

            $trophy = Trophy::create([
                'user_id'     => Auth::id(),
                'name'        => $request->name,
                'description' => $request->description ?? '',
                'image'       => $filename,
                'type'        => $request->input('type', 'trophy'),
                'price'       => $request->price,
                'receive'     => $request->receive,
                'weight'      => $request->input('weight', 0),
                'max_supply'  => $request->availability,
            ]);

            if ($request->filled('badge_ids')) {
                $trophy->badges()->attach($request->badge_ids);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BrandTrophiesController@store: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create trophy.'], 500);
        }

        return response()->json(['trophy' => $this->format($trophy)], 201);
    }

    // ─── PUT /api/brand/trophies/{id} ────────────────────────────────────────

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'         => 'string|max:255',
            'description'  => 'nullable|string',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'type'         => 'in:trophy,key',
            'price'        => 'numeric|min:0',
            'receive'      => 'numeric|min:0',
            'weight'       => 'nullable|integer|min:0',
            'availability' => 'nullable|integer|min:1',
            'badge_ids'    => 'nullable|array',
            'badge_ids.*'  => 'exists:badges,id',
        ]);

        $trophy = $this->scopedTrophy($id);

        DB::beginTransaction();
        try {
            $data = $request->only(['name', 'description', 'type', 'price', 'receive', 'weight']);

            if ($request->hasFile('image')) {
                $filename = $this->fileService->saveTrophyImage($request->file('image'));
                if (!$filename) {
                    DB::rollBack();
                    return response()->json(['error' => 'Image upload failed.'], 500);
                }
                $data['image'] = $filename;
            }

            if ($request->has('availability')) {
                $data['max_supply'] = $request->availability;
            }

            $trophy->update($data);

            if ($request->has('badge_ids')) {
                $trophy->badges()->sync($request->badge_ids);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BrandTrophiesController@update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update trophy.'], 500);
        }

        return response()->json(['trophy' => $this->format($trophy->fresh())], 200);
    }

    // ─── DELETE /api/brand/trophies/{id} ─────────────────────────────────────

    public function destroy(string $id)
    {
        $trophy = $this->scopedTrophy($id);
        $trophy->delete();

        return response()->json(['success' => true], 200);
    }

    // ─── GET /api/brand/trophies/{id}/stats ──────────────────────────────────

    public function stats(string $id)
    {
        $trophy = $this->scopedTrophy($id);

        $forgedCount = $trophy->users()->count();
        $remaining   = $trophy->max_supply !== null
            ? max(0, $trophy->max_supply - $forgedCount)
            : null;

        $recentClaimers = DB::table('trophy_user')
            ->join('users', 'users.id', '=', 'trophy_user.user_id')
            ->where('trophy_user.trophy_id', $trophy->id)
            ->orderByDesc('trophy_user.created_at')
            ->limit(10)
            ->select('users.username', 'users.avatar', 'trophy_user.created_at')
            ->get();

        return response()->json([
            'forged_count'    => $forgedCount,
            'remaining'       => $remaining,
            'recent_claimers' => $recentClaimers,
        ], 200);
    }
}
