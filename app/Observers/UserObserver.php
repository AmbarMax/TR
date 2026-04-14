<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Models\Balance;

class UserObserver
{
    private BaseRepository $balanceRepository;
    private BaseRepository $currencyRepository;

    public function __construct()
    {
        $this->balanceRepository = new BaseRepository(Balance::class);
        $this->currencyRepository = new BaseRepository(Currency::class);
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $currencies = $this->currencyRepository->findAll()->pluck('id')->toArray();
        foreach ($currencies as $currency) {
            $this->balanceRepository->create([
                'user_id' => $user->id,
                'currency_id' => $currency,
                'amount' => 0
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
