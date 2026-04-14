<?php

namespace App\Services\Admin;

use App\Enums\ExchangeStatus;
use App\Models\Exchange;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Api\TrophyRepository;
use App\Services\AbstractServices\AbstractTrophyService;
use App\Services\Api\BalanceService;
use App\Services\Api\NotificationService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminExchangeService
{

    public function __construct(private readonly BalanceService $balanceService)
    {
    }
    public function getAll()
    {
        $exchanges = Exchange::orderBy('created_at', 'desc')->get();
        foreach ($exchanges as $exchange)
        {
            $exchange->statusName = ExchangeStatus::getName($exchange->status);
            $exchange->userName = $exchange->user->username;
        }
        return $exchanges;
    }

    public function getById($id)
    {
        return Exchange::find($id);
    }

    public function update($id, $data)
    {
        try {
            DB::beginTransaction();

            Exchange::where('id', $id)->update(['status' => $data['status']]);
            $exchange = Exchange::find($id);
            $notification = App::make(NotificationService::class);

            if ($data['status'] == ExchangeStatus::CANCELED){
                $balance = collect(User::find($exchange['user_id'])->getUserDataWithBalances()->balances)
                    ->where('currency.name', 'uru')->first();
                $this->balanceService->increaseBalance($balance->id, $exchange->input_amount);

                $notification->createAndSendNotification(
                    $exchange['user_id'],
                    Notification::DONATE_TYPE,
                    'Administrator canceled your exchange (' . $exchange['input_amount'] . ' ' . $exchange['input_currency']. ' -> ' . $exchange['output_amount'] . ' ' . $exchange['output_currency'] . ')',
                    null,
                    null,
                    $exchange->id
                );

            }
            if ($data['status'] == ExchangeStatus::PAID){
                $notification->createAndSendNotification(
                    $exchange['user_id'],
                    Notification::DONATE_TYPE,
                    'Administrator paid your exchange (' . $exchange['input_amount'] . ' ' . $exchange['input_currency']. ' -> ' .$exchange['output_amount'] . ' ' . $exchange['output_currency'] . ')',
                    null,
                    null,
                    $exchange->id
                );
            }


            DB::commit();
            return true;
        }catch (\Exception $e) {
            Log::error('AdminUserService@updateUserBalance: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
    }



}
