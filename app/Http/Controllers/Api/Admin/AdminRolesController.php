<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminRolesController extends Controller
{
    private const ASSIGNABLE_ROLES = ['tr_moderator', 'tr_admin', 'tr_superadmin'];

    public function searchForRole(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = User::query();
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('username', 'like', "%{$q}%")
                  ->orWhere('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('username')->limit(20)->get();

        return response()->json([
            'data' => $users->map(fn (User $u) => $this->serialize($u))->values(),
        ]);
    }

    public function assignRole(AssignRoleRequest $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        $role = $request->validated()['role'];

        if ($user->hasRole($role)) {
            // Idempotent — do not error, just return current state.
            return response()->json([
                'message' => "User already has the {$role} role.",
                'data'    => $this->serialize($user),
            ]);
        }

        $user->assignRole($role);

        return response()->json([
            'data' => $this->serialize($user->fresh()),
        ], Response::HTTP_CREATED);
    }

    public function revokeRole(string $username, string $role): JsonResponse
    {
        if (! in_array($role, self::ASSIGNABLE_ROLES, true)) {
            return response()->json([
                'message' => 'Only tr_moderator, tr_admin, and tr_superadmin can be revoked via this endpoint.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('username', $username)->firstOrFail();

        if (! $user->hasRole($role)) {
            return response()->json([
                'message' => "User does not have the {$role} role.",
            ], Response::HTTP_NOT_FOUND);
        }

        if ($role === 'tr_superadmin') {
            // Mirror of the assign rule: only tr_superadmin can revoke
            // tr_superadmin. Otherwise a tr_admin could demote a peer
            // and effectively seize control of role management.
            if (! Auth::user()?->hasRole('tr_superadmin')) {
                return response()->json([
                    'message' => 'Only tr_superadmin can revoke the tr_superadmin role.',
                ], Response::HTTP_FORBIDDEN);
            }

            // Foot-gun protection: do not let the system end up with zero
            // tr_superadmins. That would lock everyone out of role
            // management since both assign+revoke require staff roles.
            $superCount = User::role('tr_superadmin')->count();
            if ($superCount <= 1) {
                return response()->json([
                    'message' => 'Cannot revoke the last tr_superadmin — the system would be left without an admin to manage roles.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $user->removeRole($role);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function serialize(User $user): array
    {
        return [
            'id'           => $user->id,
            'username'     => $user->username,
            'name'         => $user->name,
            'email'        => $user->email,
            'avatar'       => $user->avatar,
            'account_type' => $user->account_type,
            'roles'        => $user->getRoleNames()->values()->all(),
        ];
    }
}
