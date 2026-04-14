<?php

namespace App\Services\Admin;

use App\Enums\ExchangeStatus;
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
use Faker\Core\File;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminItemService
{

    public function __construct(private readonly FileService $fileService)
    {

    }

    public function getAll()
    {
        return Item::all();
    }

    public function show($id)
    {
        return Item::find($id);
    }

    public function delete($id)
    {
        return Item::destroy($id);
    }

    public function update($data, $id){
        try {
            DB::beginTransaction();
            if (isset($data['image'])){
                $data['image'] = $this->fileService->saveTrophyImage($data['image']);
                Item::updateOrCreate(
                    ['id' => $id],
                    [
                        'name' => $data['name'],
                        'image' => $data['image'],
                        'type' => $data['status'],
                        'description' => $data['description'],
                    ]);
            } else {
                Item::updateOrCreate(
                    ['id' => $id],
                    [
                        'name' => $data['name'],
                        'type' => $data['status'],
                        'description' => $data['description'],
                    ]);
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
            $data['image'] = $this->fileService->saveTrophyImage($data['image']);
            Item::create([
                'name' => $data['name'],
                'image' => $data['image'],
                'type' => $data['status'],
                'description' => $data['description'],
            ]);
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('AbstractTrophyService@create: ' . $e->getMessage());
            return false;
        }
    }



}
