<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trophy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * POST /api/onboarding/claim-welcome-trophy
     *
     * Final step of the onboarding flow: user claims their Welcome Trophy.
     * Inserts a record in the trophy_user pivot table linking the user
     * to the system-owned Welcome Trophy. Marks the trophy_claimed step
     * and the full onboarding as completed.
     *
     * Idempotent: if the user already claimed it, returns the existing
     * record without error.
     */
    public function claimWelcomeTrophy(): JsonResponse
    {
        $welcomeTrophyId = '00000000-0000-4000-8000-000000000001';

        $user = Auth::user();

        $trophy = Trophy::find($welcomeTrophyId);
        if (!$trophy) {
            Log::error('Welcome Trophy not found in DB', [
                'expected_id' => $welcomeTrophyId,
            ]);
            return response()->json([
                'error' => 'Welcome Trophy is not configured. Please contact support.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $alreadyClaimed = DB::table('trophy_user')
            ->where('user_id', $user->id)
            ->where('trophy_id', $welcomeTrophyId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$alreadyClaimed) {
            // trophy_user has auto-increment id (bigint) — do NOT pass an id.
            DB::table('trophy_user')->insert([
                'user_id'    => $user->id,
                'trophy_id'  => $welcomeTrophyId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $trophy->increment('minted');
        }

        $steps = $user->onboarding_steps ?? [];
        $steps['trophy_claimed'] = now()->toIso8601String();
        $user->onboarding_steps = $steps;
        $user->onboarding_completed = true;
        $user->save();

        return response()->json([
            'trophy' => $trophy->only(['id', 'name', 'image', 'description', 'receive']),
            'already_claimed' => $alreadyClaimed,
        ]);
    }
}
