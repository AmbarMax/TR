<?php

namespace App\Services\Api;

use App\Enums\CurrencyType;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Api\BalanceRepository;
use App\Repositories\Api\UserRepository;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FollowService
{
    const FOLLOW = 1;
    const NOT_FOLLOW = 2;
    const SUBSCRIBING_URU_BONUS = 200.00;
    const FOLLOWER_URU_BONUS = 1.00;

    static function checkSubscriptionStatus($authUser, $userId): ?int
    {
        return $authUser->id !== $userId ?
            (!$authUser->isFollower($userId, $authUser->id)
                ? self::NOT_FOLLOW : self::FOLLOW) : null;
    }

    static function action($request): array
    {
        $authUser = $request->user();

        if ($authUser->id == $request->id) {
            return self::subscriptionError('You cant subscribe to yourself.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $repository = App::make(BalanceRepository::class);
        $notification = App::make(NotificationService::class);

        $status = self::checkSubscriptionStatus($authUser, $request->id);

        return ($status == self::NOT_FOLLOW)
            ? self::subscribe($authUser, $request->id, $repository, $notification)
            : self::unsubscribe($authUser, $request->id, $repository, $notification);
    }

    private static function subscribe(User $authUser, $userId, BalanceRepository $repository, NotificationService $notification): array
    {
        $authUserAmbarBalance = $repository->firstByUserIdAndCurrency($authUser->id, CurrencyType::Ambar);

        if ($authUser->isFollower($userId, $authUser->id)) {
            return self::subscriptionError('You are already follow.', Response::HTTP_BAD_REQUEST);
        }

        if (!$authUserAmbarBalance)
        {
            return self::subscriptionError('You ambar balance not found.', Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            $authUserUruBalance = $repository->firstByUserIdAndCurrency($authUser->id, CurrencyType::Uru);
            $authUserUruBalance->amount += self::FOLLOWER_URU_BONUS;
            $authUserUruBalance->save();

//            $followedUserBalance = $repository->firstByUserIdAndCurrency($userId, CurrencyType::Uru);
//            $followedUserBalance->amount += self::FOLLOWER_URU_BONUS;
//            $followedUserBalance->save();

            $authUser->following()->attach($userId);

            $notification->createAndSendNotification(
                $userId,
                Notification::SUBSCRIBE_TYPE,
                $authUser->name. ' follow you.',
                null,
                $authUser->id,
                null
            );

            DB::commit();

            return [
                'message' => 'You are now following this user',
                'followStatus' => self::FOLLOW,
                'status' => Response::HTTP_OK
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('FollowService@actionSubscribe: ' . $e->getMessage());
            return self::subscriptionError($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private static function unsubscribe(User $authUser, $userId, BalanceRepository $repository, NotificationService $notification): array
    {
        if (!$authUser->isFollower($userId, $authUser->id)) {
            return self::subscriptionError('You are already unfollow.', Response::HTTP_BAD_REQUEST);
        }

        $authUserUruBalance = $repository->firstByUserIdAndCurrency($authUser->id, CurrencyType::Uru);

        if (!$authUserUruBalance)
        {
            return self::subscriptionError('You uru balance not found.', Response::HTTP_NOT_FOUND);
        }

        if ($authUserUruBalance->amount < self::FOLLOWER_URU_BONUS)
        {
            $notification->createAndSendNotification(
                $authUser->id,
                Notification::INSUFFICIENT_FUNDS_TYPE,
                Notification::INSUFFICIENT_FUNDS_TITLE,
                'To unsubscribe you need '.self::FOLLOWER_URU_BONUS.' '.CurrencyType::Uru,
                null,
                null
            );

            return self::subscriptionError('Your uru funds are not sufficient to unsubscribe.', Response::HTTP_PAYMENT_REQUIRED);
        }

        $userAmbarBalance = $repository->firstByUserIdAndCurrency($userId, CurrencyType::Ambar);

        if (!$userAmbarBalance)
        {
            return self::subscriptionError('User ambar balance not found.', Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            $authUserUruBalance->amount -= self::FOLLOWER_URU_BONUS;
            $authUserUruBalance->save();

            $authUser->following()->detach($userId);

            $notification->createAndSendNotification(
                $userId,
                Notification::UNSUBSCRIBE_TYPE,
                $authUser->name.' unfollowed you',
                null,
                $authUser->id,
                null
            );

            DB::commit();

            return [
                'message' => 'You have unfollowed this user',
                'followStatus' => self::NOT_FOLLOW,
                'status' => Response::HTTP_OK
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('FollowService@actionUnsubscribe: ' . $e->getMessage());
            return self::subscriptionError($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    static function delete($request): array
    {
        /** @var UserRepository $repository */
        $repository = App::make(UserRepository::class);
        /** @var NotificationService $notification */
        $notification = App::make(NotificationService::class);
        /** @var User $authUser */
        $authUser = $request->user();

        try {
            DB::beginTransaction();

            /** @var User $user */
            $user = $repository->findById($request->id);

            $user->following()->detach($request->user()->id);

            $notification->createAndSendNotification(
                $user->id,
                Notification::UNSUBSCRIBE_TYPE,
                $authUser->name.' remove you from followers',
                null,
                $authUser->id,
                null
            );

            DB::commit();

            return [
                'message' => 'Follower deleted',
                'status' => Response::HTTP_OK
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('FollowService@delete: ' . $e->getMessage());
            return self::subscriptionError($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private static function subscriptionError(string $message, int $status): array
    {
        return [
            'message' => $message,
            'followStatus' => null,
            'status' => $status
        ];
    }
}
