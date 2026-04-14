<?php

namespace App\Repositories\Api;

use App\Models\Balance;
use App\Repositories\BaseRepository;

class BalanceRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(Balance::class);
    }

    public function firstByUserIdAndCurrency($userId, $currencyName)
    {
        return $this->model->join('currencies', 'balances.currency_id', '=', 'currencies.id')
            ->where('balances.user_id', $userId)
            ->where('currencies.name', $currencyName)
            ->select('balances.*')
            ->first();
    }
}
