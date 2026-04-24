<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pursuit;
use App\Models\Trophy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PursuitController extends Controller
{
    public function index(): JsonResponse
    {
        $pursuits = Auth::user()
            ->pursuits()
            ->with('trophy')
            ->latest('id')
            ->paginate(15);

        return response()->json($pursuits);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trophy_id' => ['required', 'string', 'exists:trophies,id'],
        ]);

        $user = Auth::user();
        $trophyId = $validated['trophy_id'];

        $alreadyOwned = DB::table('trophy_user')
            ->where('user_id', $user->id)
            ->where('trophy_id', $trophyId)
            ->whereNull('deleted_at')
            ->exists();

        if ($alreadyOwned) {
            return response()->json([
                'message' => 'Already conquered this trophy',
            ], Response::HTTP_CONFLICT);
        }

        $existing = Pursuit::where('user_id', $user->id)
            ->where('trophy_id', $trophyId)
            ->first();

        if ($existing) {
            $existing->load('trophy');
            return response()->json(['data' => $this->serialize($existing)], Response::HTTP_OK);
        }

        $pursuit = Pursuit::create([
            'user_id' => $user->id,
            'trophy_id' => $trophyId,
        ]);
        $pursuit->load('trophy');

        return response()->json(['data' => $this->serialize($pursuit)], Response::HTTP_CREATED);
    }

    public function destroy(string $trophyId): JsonResponse
    {
        $pursuit = Pursuit::where('user_id', Auth::id())
            ->where('trophy_id', $trophyId)
            ->first();

        if (! $pursuit) {
            return response()->json(['message' => 'Pursuit not found'], Response::HTTP_NOT_FOUND);
        }

        $pursuit->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function serialize(Pursuit $pursuit): array
    {
        return [
            'trophy_id'   => $pursuit->trophy_id,
            'trophy_name' => $pursuit->trophy?->name,
            'created_at'  => optional($pursuit->created_at)->toIso8601String(),
            'is_pursuing' => true,
        ];
    }
}
