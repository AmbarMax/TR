<?php

namespace App\Http\Controllers\Api\Forge;

use App\Enums\ExchangeStatus;
use App\Models\Exchange;
use App\Models\Trophy;
use App\Models\User;
use App\Services\Admin\AdminTrophyService;
use App\Services\Api\BalanceService;
use App\Services\Api\TrophyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ForgeController
{
    public function __construct(
        private readonly AdminTrophyService $adminTrophyService,
        private readonly TrophyService      $trophyService,
        private readonly BalanceService     $balanceService,
    )
    {
    }

    public function index()
    {
        return response()->json([
            'trophies' => $this->adminTrophyService->getAllTrophiesWithAchievements()
        ]);
    }

    public function trophies()
    {
        $user = Auth::user()->getUserDataWithBalances();
        return $user ? response()->json([
            'trophies' => $user->trophies()->get()
        ], ResponseAlias::HTTP_OK) : response()->json([
            'message' => 'User not found'
        ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function availableTrophies()
    {
        $user = Auth::user()->getUserDataWithBalances();

        $trophies = $user->trophies;
        $unAvailableTrophies = $user->unAvailableTrophies;
        return $user ? response()->json([
            'trophies' => $trophies->merge($unAvailableTrophies)
        ], ResponseAlias::HTTP_OK) : response()->json([
            'message' => 'User not found'
        ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function claim($id, Request $request)
    {
        return response()->json(
            $this->trophyService->claimTrophy(id: $id)
        );
    }

    public function showcase($id)
    {
        return Auth::user()->trophies()->updateExistingPivot($id, ['display' => true]) ?
            response()->json([
                'message' => 'Badge successfully showcase',
                'type' => 'success'
            ], ResponseAlias::HTTP_OK) :
            response()->json([
                'message' => 'Badge not found',
                'type' => 'error'
            ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function remove($id)
    {
        return Auth::user()->trophies()->updateExistingPivot($id, ['display' => false]) ?
            response()->json([
                'message' => 'Badge successfully removed',
                'type' => 'success'
            ], ResponseAlias::HTTP_OK) :
            response()->json([
                'message' => 'Badge not found',
                'type' => 'error'
            ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function voucherSign(Request $request): JsonResponse
    {
        // NFT voucher signing was removed in TrophyRoom 2.0 — trophies are DB objects, not NFTs.
        return response()->json(
            ['message' => 'NFT voucher signing is no longer supported.'],
            ResponseAlias::HTTP_GONE
        );
    }

    public function getBalance($id)
    {
        $uru = collect(Auth::user()->getUserDataWithBalances()->balances)->where('currency.name', 'uru')->first();
        $this->balanceService->increaseBalance($uru->id, Trophy::find($id)->receive);
        $this->trophyService->claimTrophyUpdateBalance($id, Auth::id());

        DB::table('unavailable_trophy_user')->insert([
            'user_id' => Auth::id(),
            'trophy_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Uru successfully added',
            'type' => 'success'
        ], ResponseAlias::HTTP_OK);

    }
}
