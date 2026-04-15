<?php

namespace App\Services\Api;

use App\Models\Chest;
use App\Models\Key;
use App\Models\Trophy;
use App\Models\User;
use App\Services\Admin\AdminTrophyService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChestService
{

    public function __construct(private readonly TrophyService $trophyService)
    {
    }

    public function index()
    {
        $userChestIds = User::find(Auth::id())->chests->pluck('id');
        $user = Auth::user();

        $chests = Chest::where('quantity', '>', 0)
            ->where('expiration_date', '>', Carbon::now())
            ->whereNotIn('id', $userChestIds)
            ->get();

        //TODO get access to the chest through the key from web3
        foreach ($chests as $i => $chest) {
            $chest->availability = false;
//            $chestUser = $user->chests()->where('chest_id', $chest->id)->first();
//            $chest->opened = $chestUser ? (bool) $chestUser->pivot->is_open : false;
        }

        return $chests;
    }

    public function getUserTokenIds()
    {
        $user = Auth::user();
        return DB::table('key_user')
            ->where('user_id', $user->id)
            ->get();
    }

    public function userChests($id)
    {
        return User::find($id)->chests;
    }

    public function open($chestId)
    {
        try {
            DB::beginTransaction();

            $chest = Chest::find($chestId);
            $chest->quantity--;
            $chest->save();

            $key = $chest->key;
            $key->quantity--;
            $key->save();

            DB::table('chest_user')->insert([
                'is_open' => true,
                'chest_id' => $chestId,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('FollowService@actionSubscribe: ' . $e->getMessage());
            return false;
        }
    }

    public function get($id, $tokenId)
    {
        try {
            DB::beginTransaction();

            Auth::user()->unAvailableTrophies()->attach($id);
            $key_id = Trophy::find($id)->key->id;

            $key = Key::find($key_id);
            $key->quantity--;
            $key->save();
            $this->trophyService->claimTrophyUpdateBalance($id, Auth::id());

            if (!Auth::user()->keys()->pluck('key_id')->contains($key_id)) {
                DB::table('key_user')->insert([
                    'key_id' => $key_id,
                    'user_id' => Auth::user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'token_id' => $tokenId ?? null,
                ]);
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('FollowService@actionSubscribe: ' . $e->getMessage());
            return false;
        }

    }

}
