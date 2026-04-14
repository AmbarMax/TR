<?php

namespace App\Services\Admin;

use App\Enums\ExchangeStatus;
use App\Models\Chest;
use App\Models\Exchange;
use App\Models\Item;
use App\Models\Key;
use App\Models\Notification;
use App\Models\Trophy;
use App\Models\User;
use App\Repositories\Api\TrophyRepository;
use App\Services\AbstractServices\AbstractTrophyService;
use App\Services\Api\BalanceService;
use App\Services\Api\NotificationService;
use App\Services\FileService;
use Faker\Core\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminKeyService
{
    protected FileService $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    public function getAll()
    {
        return Key::all();
    }

    public function show($id)
    {
        return Key::find($id);
    }

    public function delete($id)
    {
        return Key::destroy($id);
    }

    public function update($data, $id){
        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $data['name'],
                'quantity' => $data['quantity'],
            ];
            if (isset($data['image'])) {
                $updateData['image'] = $this->fileService->saveKeyImage($data['image']);
            }

            Key::updateOrCreate(
                ['id' => $id],
                $updateData
            );

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

            Key::create([
                'name' => $data['name'],
                'quantity' => $data['quantity'],
                'image' => $this->fileService->saveKeyImage($data['image']),
                'hash_code' => Str::random(24),
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('AdminKeyService@create: ' . $e->getMessage());
            return false;
        }
    }



}
