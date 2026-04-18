<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\RewardsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RewardsController extends Controller
{
    public function __construct(private readonly RewardsService $rewardsService) {}

    public function battlePass(): JsonResponse
    {
        $data = $this->rewardsService->getBattlePass(Auth::user());
        return response()->json($data);
    }

    public function buyLevel(Request $request): JsonResponse
    {
        $request->validate(['level_id' => 'required|string']);
        $result = $this->rewardsService->buyLevel(Auth::user(), $request->level_id);
        $status = $result['success'] ? ResponseAlias::HTTP_OK : ResponseAlias::HTTP_UNPROCESSABLE_ENTITY;
        return response()->json($result, $status);
    }

    public function convert(Request $request): JsonResponse
    {
        $request->validate(['ambar_amount' => 'required|integer|min:1']);
        $result = $this->rewardsService->convertAmbarToUru(Auth::user(), $request->ambar_amount);
        $status = $result['success'] ? ResponseAlias::HTTP_OK : ResponseAlias::HTTP_UNPROCESSABLE_ENTITY;
        return response()->json($result, $status);
    }

    public function shopItems(): JsonResponse
    {
        return response()->json($this->rewardsService->getShopItems());
    }

    public function buyShopItem(Request $request): JsonResponse
    {
        $request->validate(['item_id' => 'required|string']);
        $result = $this->rewardsService->buyShopItem(Auth::user(), $request->item_id);
        $status = $result['success'] ? ResponseAlias::HTTP_OK : ResponseAlias::HTTP_UNPROCESSABLE_ENTITY;
        return response()->json($result, $status);
    }

    public function purchaseHistory(): JsonResponse
    {
        return response()->json($this->rewardsService->getPurchaseHistory(Auth::user()));
    }
}
