<?php

namespace App\Services\Api;


use App\Enums\CurrencyType;
use App\Models\Currency;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Trophy;
use App\Models\User;
use App\Repositories\Api\BalanceRepository;
use App\Repositories\BaseRepository;
use App\Services\AbstractServices\AbstractBalanceService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BalanceService extends AbstractBalanceService
{
    private BaseRepository $postRepository;
    private BaseRepository $userRepository;

    const IMPORT_BADGE = 50.00;


    public function __construct()
    {
        parent::__construct(
            new BalanceRepository()
        );
        $this->postRepository = new BaseRepository(Post::class);
        $this->userRepository = new BaseRepository(User::class);

    }

    public function increaseBalance($balanceId, $amount)
    {
        try {
            DB::beginTransaction();
                $balance = $this->balanceRepository->find($balanceId);
                $balance['amount'] += $amount;
                $balance->save();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            Log::error('BalanceService@increaseBalance: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
    }

    public function decreaseBalance($balanceId, $amount)
    {
        try {
            DB::beginTransaction();

            $balance = $this->balanceRepository->find($balanceId);

            if ($balance['amount'] >= $amount) {
                $balance['amount'] -= $amount;
                $balance->save();
                DB::commit();
                return true;
            } else {
                DB::rollBack();
                return false;
            }
        }catch (\Exception $e) {
            Log::error('BalanceService@increaseBalance: ' . $e->getMessage());
                DB::rollBack();
            return false;
        }
    }


    public function claimTrophyUpdateBalance($trophyId, $userId)
    {
        try {
            DB::beginTransaction();

            $trophy = Trophy::find($trophyId);
            $user = User::find($userId);

            $trophyPrice = $trophy['price'];

            $ambar = collect($user->getUserDataWithBalances()->balances)->where('currency.name', 'ambar')->first();

            $decreaseFlag = $this->decreaseBalance($ambar->id, $trophyPrice);

            DB::commit();
        }catch (\Exception $e) {
            Log::error('BalanceService@claimTrophyUpdateBalance: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
        return $decreaseFlag;
    }


    public function donateOnPost($id, $user_id, $amount): array
    {
        $payer = $this->userRepository->find($user_id);

        $payerBallance = collect($payer->getUserDataWithBalances()->balances)
            ->where('currency.name', 'ambar')->first();

        if($amount > $payerBallance->amount){
            return ['message' => 'You dont have enough ambars for donate.', 'status' => Response::HTTP_PAYMENT_REQUIRED];
        }

        try {
            DB::beginTransaction();
            $post = $this->postRepository->find($id);
            $receiver = $post->user;

            $receiverBallance = collect($receiver->getUserDataWithBalances()->balances)
                ->where('currency.name', 'ambar')->first();

            if ($payer->id !== $receiver->id
                && $payerBallance->amount >= $amount
                && $amount > 0){
                $this->decreaseBalance($payerBallance->id, $amount);
                $this->increaseBalance($receiverBallance->id, $amount);
                $post->donations()->create([
                    'payer_id' => $payer->id,
                    'receiver_id' => $receiver->id,
                    'amount' => $amount,
                    'currency_id' => Currency::where('name', 'ambar')->first()->id,
                ]);

                $notification = App::make(NotificationService::class);


                $notification->createAndSendNotification(
                    $receiver->id,
                    Notification::DONATE_TYPE,
                    $payer->name.' sent you a donation in the amount:  '.$amount.' '.CurrencyType::Ambar,
                    null,
                    $payer->id,
                    null
                );

            }else{
                return ['message' => 'ERROR', 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
            }
            DB::commit();
            return ['message' => 'Donate success', 'status' => Response::HTTP_OK];

        }catch (\Exception $e){
            Log::error('BallanceService@donate: ' . $e->getMessage());
            DB::rollBack();
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function increaseBalanceForImportBadges($user, $badgesCount)
    {
        try {
            DB::beginTransaction();
            $balance = collect($user->getUserDataWithBalances()->balances)
                ->where('currency.name', 'ambar')->first();
            $this->increaseBalance($balance->id, $badgesCount * self::IMPORT_BADGE);
            DB::commit();
            return true;
        }catch (\Exception $e) {
            Log::error('BalanceService@increaseBalanceForBadges: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
    }
}
