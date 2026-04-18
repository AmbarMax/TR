<?php

namespace App\Services\Api;

use App\Models\BattlePassLevel;
use App\Models\ShopItem;
use App\Models\ShopPurchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RewardsService
{
    private BalanceService $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    public function getBattlePass(User $user): array
    {
        $levels = BattlePassLevel::orderBy('number')->get();
        $ownedIds = $user->battlePassLevels()->pluck('battle_pass_levels.id')->toArray();

        return $levels->map(function ($level) use ($ownedIds) {
            return [
                'id'       => $level->id,
                'number'   => $level->number,
                'name'     => $level->name,
                'cost_uru' => $level->cost_uru,
                'rewards'  => $level->rewards,
                'owned'    => in_array($level->id, $ownedIds),
            ];
        })->toArray();
    }

    public function buyLevel(User $user, string $levelId): array
    {
        $level = BattlePassLevel::find($levelId);
        if (!$level) {
            return ['success' => false, 'message' => 'Level not found'];
        }

        if ($user->battlePassLevels()->where('battle_pass_levels.id', $levelId)->exists()) {
            return ['success' => false, 'message' => 'Level already owned'];
        }

        $uruBalance = collect($user->getUserDataWithBalances()->balances)
            ->where('currency.name', 'uru')->first();

        if (!$uruBalance || $uruBalance->amount < $level->cost_uru) {
            return ['success' => false, 'message' => 'Insufficient Uru balance'];
        }

        try {
            DB::beginTransaction();
            $decreased = $this->balanceService->decreaseBalance($uruBalance->id, $level->cost_uru);
            if (!$decreased) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Failed to deduct balance'];
            }
            $user->battlePassLevels()->attach($levelId);
            DB::commit();
            return ['success' => true, 'message' => 'Level unlocked'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RewardsService@buyLevel: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Purchase failed'];
        }
    }

    public function convertAmbarToUru(User $user, int $ambarAmount): array
    {
        if ($ambarAmount <= 0) {
            return ['success' => false, 'message' => 'Amount must be positive'];
        }

        $userData = $user->getUserDataWithBalances();
        $ambarBalance = collect($userData->balances)->where('currency.name', 'ambar')->first();
        $uruBalance   = collect($userData->balances)->where('currency.name', 'uru')->first();

        if (!$ambarBalance || $ambarBalance->amount < $ambarAmount) {
            return ['success' => false, 'message' => 'Insufficient Ambar balance'];
        }

        $uruAmount = (int) floor($ambarAmount / 10);
        if ($uruAmount < 1) {
            return ['success' => false, 'message' => 'Minimum 10 Ambar required'];
        }

        try {
            DB::beginTransaction();
            $this->balanceService->decreaseBalance($ambarBalance->id, $ambarAmount);
            $this->balanceService->increaseBalance($uruBalance->id, $uruAmount);
            DB::commit();
            return ['success' => true, 'uru_received' => $uruAmount];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RewardsService@convertAmbarToUru: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Conversion failed'];
        }
    }

    public function getShopItems(): array
    {
        return ShopItem::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function buyShopItem(User $user, string $itemId): array
    {
        $item = ShopItem::find($itemId);
        if (!$item || !$item->is_active) {
            return ['success' => false, 'message' => 'Item not available'];
        }

        if ($item->stock === 0) {
            return ['success' => false, 'message' => 'Out of stock'];
        }

        $uruBalance = collect($user->getUserDataWithBalances()->balances)
            ->where('currency.name', 'uru')->first();

        if (!$uruBalance || $uruBalance->amount < $item->price_uru) {
            return ['success' => false, 'message' => 'Insufficient Uru balance'];
        }

        try {
            DB::beginTransaction();
            $decreased = $this->balanceService->decreaseBalance($uruBalance->id, $item->price_uru);
            if (!$decreased) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Failed to deduct balance'];
            }

            ShopPurchase::create([
                'user_id'      => $user->id,
                'shop_item_id' => $item->id,
                'price_paid'   => $item->price_uru,
                'status'       => 'pending',
            ]);

            if ($item->stock > 0) {
                $item->decrement('stock');
            }

            DB::commit();
            return ['success' => true, 'message' => 'Purchase successful'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RewardsService@buyShopItem: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Purchase failed'];
        }
    }

    public function getPurchaseHistory(User $user): array
    {
        return ShopPurchase::with('item')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'item_name'  => $p->item?->name,
                'price_paid' => $p->price_paid,
                'status'     => $p->status,
                'created_at' => $p->created_at,
            ])
            ->toArray();
    }
}
