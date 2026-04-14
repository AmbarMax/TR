<?php

namespace App\Services\Admin;

use App\Enums\ExchangeStatus;
use App\Models\Chest;
use App\Models\Exchange;
use App\Models\Item;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Api\TrophyRepository;
use App\Services\AbstractServices\AbstractTrophyService;
use App\Services\Api\BalanceService;
use App\Services\Api\NotificationService;
use App\Services\FileService;
use App\Web3\Pinata;
use App\Web3\TrophyNFT;
use Carbon\Carbon;
use Faker\Core\File;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminChestService
{

    public function __construct(private readonly FileService $fileService)
    {

    }

    public function getAll()
    {
        return Chest::all();
    }

    public function show($id)
    {
        return Chest::where('id', $id)->with(['items'])->first();
    }

    public function delete($id)
    {
        return Chest::destroy($id);
    }

    public function update($data, $id){
        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $data['name'],
                'quantity' => $data['quantity'],
                'description' => $data['description'],
                'key_id' => $data['key'],
            ];

            if (isset($data['image_closed'])) {
                $updateData['image_closed'] = $this->fileService->saveChestImage($data['image_closed']);
            }

            if (isset($data['image_opened'])) {
                $updateData['image_open'] = $this->fileService->saveChestImage($data['image_opened']);
            }

            if (!isset($data['expiration_date'])){
                $updateData['expiration_date'] = null;
            } else {
                $updateData['expiration_date'] = Carbon::parse($data['expiration_date']);
            }

            Chest::updateOrCreate(
                ['id' => $id],
                $updateData
            );

            $chest = Chest::find($id);
            $chest->items()->detach();
            foreach ($data['items'] as $item) {
                $chest->items()->attach($item);
            }

            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::info('AbstractTrophyService@create: ' . $e->getMessage());
            return false;
        }
    }

    public function store($data)
    {
        try {
            DB::beginTransaction();
            if (isset($data['image_closed'])) {
                $data['image_closed'] = $this->fileService->saveChestImage($data['image_closed']);
            } else {
                $data['image_closed'] = null;
            }

            if (isset($data['image_opened'])) {
                $data['image_open'] = $this->fileService->saveChestImage($data['image_opened']);
            } else {
                $data['image_open'] = null;
            }

            if (!isset($data['expiration_date'])){
                $data['expiration_date'] = null;
            } else {
                $data['expiration_date'] = Carbon::parse($data['expiration_date']);
            }

            $chest = Chest::create([
                'name' => $data['name'],
                'image_closed' => $data['image_closed'],
                'image_open' => $data['image_open'],
                'quantity' => $data['max_supply'],
                'is_nft' => true,
                'key_id' => $data['key'],
                'description' => $data['description'],
                'expiration_date' => $data['expiration_date']
            ]);

            foreach ($data['items'] as $item) {
                $chest->items()->attach($item);
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('AbstractTrophyService@create: ' . $e->getMessage());
            return false;
        }
    }



}
