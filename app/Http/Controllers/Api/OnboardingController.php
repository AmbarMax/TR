<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    /**
     * GET /api/onboarding/state
     * Returns the current user's onboarding progress: which steps are
     * done, whether the wizard is complete, whether they ever skipped.
     */
    public function state(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'steps'        => $user->onboarding_steps ?? [],
            'completed'    => (bool) $user->onboarding_completed,
            'skipped_at'   => $user->onboarding_skipped_at,
            // Helper flag: user is in "new signup" mode if their account
            // is fresh (< 24h) AND they haven't completed onboarding.
            'is_new_user'  => $user->created_at->gt(now()->subHours(24))
                              && !$user->onboarding_completed,
        ]);
    }

    /**
     * POST /api/onboarding/step
     * Marks a step as done. Body: { step: "platform_connected" }
     * Valid step keys: platform_connected, hall_personalized, hall_explored,
     * trophy_claimed (extensible — any string is accepted, validation is
     * permissive intentionally so frontend can add steps without backend
     * changes).
     */
    public function step(Request $request): JsonResponse
    {
        $request->validate([
            'step' => 'required|string|max:64',
        ]);

        $user  = Auth::user();
        $steps = $user->onboarding_steps ?? [];

        $steps[$request->step] = now()->toIso8601String();

        $user->onboarding_steps = $steps;
        $user->save();

        return response()->json([
            'steps' => $steps,
        ]);
    }

    /**
     * POST /api/onboarding/complete
     * Marks the entire onboarding flow as completed. Called when user
     * finishes the full wizard or claims their welcome trophy (TBD in
     * session 2).
     */
    public function complete(): JsonResponse
    {
        $user = Auth::user();
        $user->onboarding_completed = true;
        $user->save();

        return response()->json([
            'completed' => true,
        ]);
    }

    /**
     * POST /api/onboarding/skip
     * User chose "I'll explore first". Records the skip moment for
     * analytics. Does NOT mark onboarding_completed — user still sees
     * persistent cards on next login (handled in session 2).
     */
    public function skip(): JsonResponse
    {
        $user = Auth::user();
        $user->onboarding_skipped_at = now();
        $user->save();

        return response()->json([
            'skipped_at' => $user->onboarding_skipped_at,
        ]);
    }
}
