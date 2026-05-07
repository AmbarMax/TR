<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:
     *   ->middleware('account_status:active')
     *   ->middleware('account_status:active,suspended')  // multiple allowed values
     *
     * Requires JWT auth to have populated $request->user() upstream.
     * Staff (tr_admin, tr_superadmin) bypass this check.
     */
    public function handle(Request $request, Closure $next, ...$allowedStatuses): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Staff bypass
        if ($user->hasAnyRole(['tr_admin', 'tr_superadmin'])) {
            return $next($request);
        }

        // No allowed statuses passed = misconfigured route, fail closed
        if (empty($allowedStatuses)) {
            return response()->json([
                'message' => 'Misconfigured route: account_status middleware requires at least one allowed status.'
            ], 500);
        }

        if (!in_array($user->account_status, $allowedStatuses, true)) {
            return response()->json([
                'message' => 'Your account is not active yet. This action is locked until your account is approved.',
                'account_status' => $user->account_status,
            ], 403);
        }

        return $next($request);
    }
}
