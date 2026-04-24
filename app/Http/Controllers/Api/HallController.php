<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HallResource;
use App\Models\HallFollow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HallController extends Controller
{
    public function show(string $username): HallResource
    {
        $user = User::where('username', $username)->firstOrFail();

        return new HallResource($user);
    }

    public function conquerors(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        if ($user->account_type !== 'brand') {
            return response()->json(
                ['message' => 'Conquerors only available for brand Halls'],
                Response::HTTP_NOT_FOUND
            );
        }

        $top = DB::table('trophy_user')
            ->join('trophies', 'trophies.id', '=', 'trophy_user.trophy_id')
            ->join('users', 'users.id', '=', 'trophy_user.user_id')
            ->where('trophies.user_id', $user->id)
            ->whereNull('trophy_user.deleted_at')
            ->whereNull('trophies.deleted_at')
            ->select(
                'users.id',
                'users.username',
                'users.name',
                'users.avatar',
                DB::raw('COUNT(trophy_user.id) as conquest_count')
            )
            ->groupBy('users.id', 'users.username', 'users.name', 'users.avatar')
            ->orderByDesc('conquest_count')
            ->limit(10)
            ->get();

        $latest = DB::table('trophy_user')
            ->join('trophies', 'trophies.id', '=', 'trophy_user.trophy_id')
            ->join('users', 'users.id', '=', 'trophy_user.user_id')
            ->where('trophies.user_id', $user->id)
            ->whereNull('trophy_user.deleted_at')
            ->whereNull('trophies.deleted_at')
            ->select(
                'users.id as user_id',
                'users.username',
                'users.name',
                'users.avatar',
                'trophies.id as trophy_id',
                'trophies.name as trophy_name',
                'trophies.image as trophy_image',
                'trophy_user.created_at'
            )
            ->orderByDesc('trophy_user.created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'top' => $top,
                'latest' => $latest,
            ],
        ]);
    }

    public function activeItems(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        if ($user->account_type !== 'brand') {
            return response()->json(
                ['message' => 'Active items only available for brand Halls'],
                Response::HTTP_NOT_FOUND
            );
        }

        // Trophies "active" = not soft-deleted. The trophies table has no
        // is_active/expiration_date column, so soft-delete is the only
        // sensible liveness signal today.
        $trophies = DB::table('trophies')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->select('id', 'name', 'image', 'description', 'type', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($row) => (array) $row + ['type' => 'trophy']);

        // TODO Step 10+: include chests once they carry a brand foreign key.
        // Today `chests` has no user_id / brand association, only a key_id
        // that links to the reward key — we cannot filter by brand yet.
        $chests = collect();

        $items = $trophies->concat($chests)->values();

        return response()->json(['data' => $items]);
    }

    public function follow(string $username): JsonResponse
    {
        $target = User::where('username', $username)->firstOrFail();
        $follower = Auth::user();

        if ($follower->id === $target->id) {
            return response()->json(
                ['message' => 'Cannot follow yourself'],
                Response::HTTP_BAD_REQUEST
            );
        }

        HallFollow::firstOrCreate([
            'follower_id'  => $follower->id,
            'hall_user_id' => $target->id,
        ]);

        return response()->json(
            ['message' => 'Following', 'username' => $target->username],
            Response::HTTP_CREATED
        );
    }

    public function unfollow(string $username): JsonResponse
    {
        $target = User::where('username', $username)->firstOrFail();

        HallFollow::where('follower_id', Auth::id())
            ->where('hall_user_id', $target->id)
            ->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
