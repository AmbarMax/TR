<?php

namespace App\Services\Api;

use App\Enums\ExchangeStatus;
use App\Models\Badge;
use App\Models\Exchange;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Api\TrophyRepository;
use App\Repositories\Api\UserRepository;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExchangeService
{

    public function __construct(private readonly BalanceService $balanceService){}

    public function index($id, Request $request)
    {
        if ($request->input('filter') === 'All'){
            $paginator = Exchange::where('user_id', $id)->orderBy('created_at', 'desc')->paginate(8, ['*'], 'page', $request->input('page', 1));
        } else {
            $paginator = Exchange::where('user_id', $id)->where('status', ExchangeStatus::getKey($request->input('filter')))->orderBy('created_at', 'desc')->paginate(8, ['*'], 'page', $request->input('page', 1));
        }

        $formattedData = $paginator->getCollection()->map(function ($exchange) {
            return [
                'id' => $exchange->id,
                'inputAmount' => $exchange->input_amount,
                'outputAmount' => rtrim(rtrim($exchange->output_amount, '0'), '.'),
                'outputCurrency' => $exchange->output_currency,
                'status' => ExchangeStatus::getName($exchange->status),
                'walletNumber' => $exchange->wallet_number,
            ];
        });
        $paginator->setCollection($formattedData);
        return $paginator;
    }


    public function store($id, $data)
    {
        try {
            DB::beginTransaction();

            $uru = collect(User::find($id)->getUserDataWithBalances()->balances)->where('currency.name', 'uru')->first();

            if ($this->balanceService->decreaseBalance($uru->id, $data['input_amount'])) {
                Exchange::create([
                    'user_id' => $id,
                    'input_amount' => $data['input_amount'],
                    'input_currency' => $data['input_currency'],
                    'output_amount' => $data['output_amount'],
                    'output_currency' => $data['output_currency'],
                    'wallet_number' => $data['wallet_number'],
                    'status' => ExchangeStatus::PENDING,
                ]);
            }

            DB::commit();
            return true;
        }catch (\Exception $e){
            Log::error('ExchangeService@error: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
    }



}
