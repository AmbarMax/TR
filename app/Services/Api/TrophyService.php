<?php

namespace App\Services\Api;

use App\Models\Trophy;
use App\Models\User;
use App\Repositories\Api\TrophyRepository;
use App\Services\AbstractServices\AbstractTrophyService;
use App\Web3\Pinata;
use App\Web3\TrophyNFT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrophyService extends AbstractTrophyService
{
    protected BalanceService $balanceService;

    public function __construct(TrophyNFT $trophyNFT, Pinata $pinata)
    {
        parent::__construct(
            trophyRepository: new TrophyRepository(),
            trophyNFT: $trophyNFT,
            pinata: $pinata
        );
        $this->balanceService = new BalanceService();
    }

    public function claimTrophy($id, ?string $tokenId, ?string $owner): array {
        /** @var User $user */
        $user = Auth::user();
        /** @var Trophy $trophy */
        $trophy = $this->trophyRepository->find($id);
        if ($trophy->is_nft) {
            if (!$tokenId || !$owner) {
                return [
                    'message' => 'Token id or owner is missing',
                    'type' => 'error'
                ];
            }
            if (!$this->trophyNFT->checkOwnership($tokenId, $owner)) {
                return [
                    'message' => 'You don\'t own this trophy',
                    'type' => 'error'
                ];
            }
            $trophy->increment('minted');
        }

        if ($user->trophies->contains('id', $trophy->id)) {
            return [
                'message' => "This trophy has already been claimed",
                'type' => "warning"
            ];
        }

        if ($this->checkEligibility($user, $trophy)) {
            try {
                DB::beginTransaction();
                $this->balanceService->claimTrophyUpdateBalance($id, $user->id);

                if (!$user->trophies()->pluck('trophy_id')->contains($id)) {
                    $user->trophies()->attach($id);
                }

                DB::commit();
                return [
                    'message' => "The trophy has been successfully claimed",
                    'type' => "success"
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                return [
                    'message' => "There was an error claiming the trophy",
                    'type' => "error"
                ];
            }
        } else {
            return [
                'message' => "There are not enough resources to claim the trophy",
                'type' => "warning"
            ];
        }
    }

    public function checkEligibility(User $user, string|Trophy $trophy): bool
    {
        if (is_string($trophy)) {
            $trophy = $this->trophyRepository->find($trophy);
        }
        $ambarAmount = optional($user->getUserDataWithBalances()
            ->balances->firstWhere('currency.name', 'ambar'))->amount ?? 0;

        $userBadges = $user->badges->pluck('id')->toArray();
        $trophyBadges = $trophy->badges->pluck('id')->toArray();

        $userHasTrophyBadges = collect($trophyBadges)->diff($userBadges)->isEmpty();

        return $userHasTrophyBadges && $ambarAmount >= $trophy->price;
    }

    public function claimTrophyUpdateBalance($trophy_id, $user_id)
    {
        $trophy = Trophy::find($trophy_id);
        $ambar = collect(User::find($user_id)->getUserDataWithBalances()->balances)->where('currency.name', 'ambar')->first();
        $this->balanceService->decreaseBalance($ambar->id, $trophy->price);
    }
}
