<?php

namespace App\Services\Api;

use App\Enums\CurrencyType;
use App\Models\Achievement;
use App\Models\Balance;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Api\AchievementRepository;
use App\Services\FileService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\ArrayShape;
use phpcent\Client;
use Symfony\Component\HttpFoundation\Response;

class AchievementService
{
    public function __construct(private readonly AchievementRepository $achievementRepository){}

    public function create($request): array
    {
        /** @var User $user */
        $user = $request->user();

        $proof = $request->input('proof');
        $proofUserId = $request->input('proofUserId');

        $ambarBallance = Balance::where('user_id', $user->id)
            ->whereHas('currency', function ($query) {
                $query->where('name', CurrencyType::Ambar);
            })->first();

        if ($proof == Achievement::RUNE_PROOF) {

            $runeBallance = Balance::where('user_id', $user->id)
                ->whereHas('currency', function ($query) {
                    $query->where('name', CurrencyType::Rune);
                })->first();

            if ($runeBallance->amount < Achievement::RUNE_PROOF_COST) {
                return ['message' => 'You dont have enough runes', 'status' => Response::HTTP_PAYMENT_REQUIRED];
            }
        } else{
            if (!$proofUserId) {
                return ['message' => 'For proof via social media proofUserId required', 'status' => Response::HTTP_UNPROCESSABLE_ENTITY];
            }

            if ($ambarBallance->amount < Achievement::AMBAR_COST) {
                return ['message' => 'For social approve you need 200 ambars', 'status' => Response::HTTP_PAYMENT_REQUIRED];
            }
        }

        try {
            DB::beginTransaction();

            $fileService = App::make(FileService::class);

            $image = $fileService->saveAchievementImage($request->file('image'));

            $achievement = new Achievement();
            $achievement->user_id = $request->user()->id;
            $achievement->status = $proof == Achievement::RUNE_PROOF ? Achievement::VALIDATED : Achievement::NOT_VALIDATED;
            $achievement->image = $image;
            $achievement->name = $request->input('name');
            $achievement->description = $request->input('description');
            $achievement->save();

            if ($proof == Achievement::RUNE_PROOF) {
                $runeBallance->amount -= Achievement::RUNE_PROOF_COST;
                $runeBallance->save();
                $ambarBallance->amount += Achievement::AMBAR_CREATE_ACHIEVEMENT;
                $ambarBallance->save();
            } else{
                $notification = App::make(NotificationService::class);

                $ambarBallance->amount -= Achievement::AMBAR_COST;
                $ambarBallance->save();
                foreach ($proofUserId as $id){
                    $notification->createAndSendNotification(
                        $id,
                        Notification::PROOF_TYPE,
                        $user->name.' asks you to approve achievement.',
                        null,
                        $user->id,
                        $achievement->id
                    );
                }

            }

            DB::commit();

            return ['message' => 'Achievement successfully created!', 'status' => Response::HTTP_OK];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('AchievementService@create: ' . $e->getMessage());
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function getAchievements($request): LengthAwarePaginator
    {
        $page = $request->input('page', 1);
        $count = $request->input('count', 10);
        $status = $request->input('status', null);

        return $this->achievementRepository->getAchievementsPaginated($request->user(), $page, $count, $status);
    }

    public function share($request): array
    {
        try {
            /** @var User $user */
            $user =$request->user();

            DB::beginTransaction();

            /** @var Achievement $ach */
            $ach = Achievement::where('id', $request->input('id'))
                ->where('user_id', $user->id)
                ->where('is_share', 0)
                ->first();

            if ($ach){

                $ach->is_share = 1;
                $ach->save();

                $ach = $ach->posts();
                if (!$ach->where('user_id', $user->id)->exists()){
                    $ach->create([
                        'user_id' => $user->id
                    ]);
                }
            }else{
                return ['message' => 'Achievement already share!', 'status' => Response::HTTP_OK];
            }
            DB::commit();
            return ['message' => 'Achievement share!', 'status' => Response::HTTP_OK];
        }catch (\Exception $e){
            Log::error('AchievementService@share: ' . $e->getMessage());
            DB::rollBack();
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function delete($request): array
    {
        try {
            DB::beginTransaction();
            /** @var Achievement $ach */
            $ach = Achievement::where('id', $request->input('id'))->where('user_id', $request->user()->id)->first();
            $path = 'achievements/'.$ach->image;
            $ach->posts()->delete();
            Notification::where('entity_id', $ach->id)->delete();
            Storage::disk('public')->delete($path);
            $ach->delete();
            DB::commit();
            return ['message' => 'Achievement delete success!', 'status' => Response::HTTP_OK];

        }catch (\Exception $e){
            Log::error('AchievementService@remove: ' . $e->getMessage());
            DB::rollBack();
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function approve($request): array
    {
        $user = $request->user();
        $runesAmount = $request->input('amount');
        /** @var Achievement $ach */
        $ach = Achievement::where('id', $request->input('id'))->where('status', Achievement::NOT_VALIDATED)->first();
        $runeBallance = collect($user->getUserDataWithBalances()->balances)
            ->where('currency.name', CurrencyType::Rune)->first();

        if ($runeBallance->amount < $runesAmount) {
            return ['message' => 'You dont have enough runes', 'status' => Response::HTTP_PAYMENT_REQUIRED];
        }

        try {
            DB::beginTransaction();
            $runeBallance->amount -= $runesAmount;
            $runeBallance->save();
            $notification = Notification::where('entity_id', $ach->id)->first();
            $notification->entity_id = null;
            $notification->save();

            $achCreatorRunBalance = Balance::where('user_id', $ach->user_id)
                ->whereHas('currency', function ($query) {
                    $query->where('name', CurrencyType::Rune);
                })->first();

            $achCreatorRunBalance->amount += $runesAmount;
            $achCreatorRunBalance->save();

            $ach->amount += $runesAmount;

            if ($ach->amount + $runesAmount >= Achievement::RUNE_PROOF_COST) {
                    $ach->status = Achievement::VALIDATED;
            }

            $ach->save();

            $client = new Client(config('broadcasting.connections.centrifugo.url'));
            $client->setApiKey(config('broadcasting.connections.centrifugo.api_key'));
            $channel = 'proof-'.$user->id;
            $client->publish($channel, []);

            DB::commit();
            return ['message' => 'Achievement approve success!', 'status' => Response::HTTP_OK];

        }catch (\Exception $e){
            Log::error('AchievementService@socialApprove: ' . $e->getMessage());
            DB::rollBack();
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function reject($request): array
    {
        try {
            /** @var Achievement $ach */
            $ach = Achievement::where('id', $request->input('id'))->first();

            $ach->status = Achievement::PENDING_STATUS;
            $ach->save();

            $notification = Notification::where('entity_id', $ach->id)->first();
            $notification->entity_id = null;
            $notification->save();

            return ['message' => 'Achievement reject success', 'status' => Response::HTTP_OK];
        }catch (\Exception $e){
            Log::error('AchievementService@reject: ' . $e->getMessage());
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function showcase($id)
    {
        try {
            DB::beginTransaction();
            $achievement = Achievement::where('id', $id)->first();
            $achievement['display'] = true;
            $achievement->save();
            DB::commit();
            return ['message' => 'Achievement has been successfully added to the virtual hall', 'status' => Response::HTTP_OK];
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('AchievementService@reject: ' . $e->getMessage());
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function removeShowcase($id)
    {
        try {
            DB::beginTransaction();
            $achievement = Achievement::where('id', $id)->first();
            $achievement['display'] = false;
            $achievement->save();
            DB::commit();
            return ['message' => 'Achievement has been successfully removed from the virtual hall', 'status' => Response::HTTP_OK];
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('AchievementService@reject: ' . $e->getMessage());
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function socialApproveReject($request)
    {
        try {
            $notification = Notification::where('id', $request->input('id'))->first();
            $notification->entity_id = null;
            $notification->save();

            $client = new Client(config('broadcasting.connections.centrifugo.url'));
            $client->setApiKey(config('broadcasting.connections.centrifugo.api_key'));
            $channel = 'proof-'.$request->user()->id;
            $client->publish($channel, []);

            return ['message' => 'Social approve rejected', 'status' => Response::HTTP_OK];
        }catch (\Exception $e){
            Log::error('AchievementService@reject: ' . $e->getMessage());
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function revalidate($request): array
    {
        /** @var User $user */
        $user = $request->user();

        try {
            DB::beginTransaction();

            /** @var Achievement $achievement */
            $achievement = Achievement::where('id', $request->input('id'))->first();

            $runeBallance = Balance::where('user_id', $user->id)
                ->whereHas('currency', function ($query) {
                    $query->where('name', CurrencyType::Rune);
                })->first();

            if ($runeBallance->amount < Achievement::RUNE_PROOF_COST) {
                return ['message' => 'You dont have enough runes', 'status' => Response::HTTP_PAYMENT_REQUIRED];
            }

            $achievement->status = Achievement::VALIDATED;
            $achievement->save();

            $runeBallance->amount -= Achievement::RUNE_PROOF_COST;
            $runeBallance->save();

            $notifications = Notification::where('entity_id', $achievement->id)->get();

            if ($notifications->isNotEmpty()) {

                $notifications->each(function ($notification) {
                    $notification->delete();
                });
            }

            DB::commit();
            return ['message' => 'Achievement has been successfully revalidate', 'status' => Response::HTTP_OK];
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('AchievementService@revalidate: ' . $e->getMessage());
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function revalidateSocial($request): array
    {
        /** @var User $user */
        $user = $request->user();

        try {
            DB::beginTransaction();

            /** @var Achievement $achievement */
            $achievement = Achievement::where('id', $request->input('id'))->first();

            $notification = App::make(NotificationService::class);

            $ambarBallance = Balance::where('user_id', $user->id)
                ->whereHas('currency', function ($query) {
                    $query->where('name', CurrencyType::Ambar);
                })->first();

            if ($ambarBallance->amount < Achievement::AMBAR_COST) {
                return ['message' => 'For social approve you need 200 ambars', 'status' => Response::HTTP_PAYMENT_REQUIRED];
            }
            $proofUserId = $request->input('proofUserId');

            $ambarBallance->amount -= Achievement::AMBAR_COST;
            $ambarBallance->save();

            foreach ($proofUserId as $id){
                $notification->createAndSendNotification(
                    $id,
                    Notification::PROOF_TYPE,
                    $user->name.' asks you to approve achievement.',
                    null,
                    $user->id,
                    $achievement->id
                );
            }

            DB::commit();
            return ['message' => 'Social proof revalidation success', 'status' => Response::HTTP_OK];
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('AchievementService@revalidate: ' . $e->getMessage());
            return ['message' => $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }
}
