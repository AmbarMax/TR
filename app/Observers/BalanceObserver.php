<?php

namespace App\Observers;

use App\Models\Balance;
use App\Repositories\BaseRepository;
use App\Repositories\CentrifugoRepository;
use Illuminate\Support\Facades\Log;

class BalanceObserver
{
    private BaseRepository $balanceRepository;
    private $channel = 'balance';

    public function __construct(private readonly CentrifugoRepository $centrifugoRepository)
    {
        $this->balanceRepository = new BaseRepository(Balance::class);
    }


    /**
     * Handle the Balance "created" event.
     */
    public function created(Balance $balance): void
    {
        //
    }

    /**
     * Handle the Balance "updated" event.
     */
    public function updated(Balance $balance): void
    {
        $this->setCurrencyChannel($balance->currency->name, $balance->user_id);
        $this->centrifugoRepository->publish(['amount' => $balance->amount]);
    }

    /**
     * Handle the Balance "deleted" event.
     */
    public function deleted(Balance $balance): void
    {
        //
    }

    /**
     * Handle the Balance "restored" event.
     */
    public function restored(Balance $balance): void
    {
        //
    }

    /**
     * Handle the Balance "force deleted" event.
     */
    public function forceDeleted(Balance $balance): void
    {
        //
    }

    private function setCurrencyChannel($currency, $id)
    {
        $this->centrifugoRepository->setChannel($this->channel .'-'. $currency .'-'. $id);
    }
}
