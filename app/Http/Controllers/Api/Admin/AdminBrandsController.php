<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AdminBrandsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q     = trim((string) $request->query('q', ''));
        $sort  = (string) $request->query('sort', 'created_at');
        $order = strtolower((string) $request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['created_at', 'verified_at', 'is_featured', 'username'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $query = User::query()->where('account_type', 'brand');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('username', 'like', "%{$q}%")
                  ->orWhere('name', 'like', "%{$q}%");
            });
        }

        $brands = $query->orderBy($sort, $order)->paginate(20);

        $items = collect($brands->items())->map(fn (User $u) => $this->serialize($u))->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $brands->currentPage(),
                'last_page'    => $brands->lastPage(),
                'per_page'     => $brands->perPage(),
                'total'        => $brands->total(),
            ],
        ]);
    }

    public function searchablePlayers(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = User::query()
            ->where(function ($w) {
                $w->where('account_type', 'player')->orWhereNull('account_type');
            });

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('username', 'like', "%{$q}%")
                  ->orWhere('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('username')->limit(20)->get();

        return response()->json([
            'data' => $users->map(fn (User $u) => [
                'id'           => $u->id,
                'username'     => $u->username,
                'name'         => $u->name,
                'email'        => $u->email,
                'avatar'       => $u->avatar,
                'account_type' => $u->account_type,
            ])->values(),
        ]);
    }

    public function promote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'string', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->account_type === 'brand') {
            return response()->json(
                ['message' => 'User is already a brand.'],
                Response::HTTP_CONFLICT
            );
        }

        $user->forceFill(['account_type' => 'brand'])->save();
        $user->assignRole('brand_admin');

        return response()->json(['data' => $this->serialize($user->fresh())]);
    }

    public function update(UpdateBrandRequest $request, string $username): JsonResponse
    {
        $brand = User::where('username', $username)
            ->where('account_type', 'brand')
            ->firstOrFail();

        $payload = $request->validated();

        if (array_key_exists('verified_at', $payload) && $payload['verified_at'] === '') {
            $payload['verified_at'] = null;
        }

        $brand->forceFill($payload)->save();

        return response()->json(['data' => $this->serialize($brand->fresh())]);
    }

    public function demote(string $username): JsonResponse
    {
        $brand = User::where('username', $username)
            ->where('account_type', 'brand')
            ->firstOrFail();

        $brand->forceFill(['account_type' => 'player'])->save();
        if ($brand->hasRole('brand_admin')) {
            $brand->removeRole('brand_admin');
        }

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function serialize(User $user): array
    {
        $activeTrophies = DB::table('trophies')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->count();

        $followers = DB::table('hall_followers')
            ->where('hall_user_id', $user->id)
            ->count();

        return [
            'id'             => $user->id,
            'username'       => $user->username,
            'name'           => $user->name,
            'email'          => $user->email,
            'avatar'         => $user->avatar,
            'accent_color'   => $user->accent_color,
            'tagline'        => $user->tagline,
            'verified_at'    => optional($user->verified_at)->toIso8601String(),
            'is_verified'    => (bool) $user->verified_at,
            'is_featured'    => (bool) $user->is_featured,
            'banner'         => $user->background,
            'created_at'     => optional($user->created_at)->toIso8601String(),
            'active_trophies' => $activeTrophies,
            'followers'      => $followers,
        ];
    }
}
