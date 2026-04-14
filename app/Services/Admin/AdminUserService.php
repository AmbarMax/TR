<?php

namespace App\Services\Admin;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Admin\AdminUserRepository;
use App\Repositories\BaseRepository;
use App\Services\AbstractServices\AbstractUserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class AdminUserService extends AbstractUserService
{
    public function __construct()
    {
        parent::__construct(
            new AdminUserRepository()
        );
    }

    public function store($data)
    {
        try {
            $data['password'] = Hash::make($data['password']);
           if($avatar = $data['avatar']){
                unset($data['avatar']);
            }
            if($background = $data['background']){
                unset($data['background']);
            }
            $roleData = $data['role'] ?? null;
            unset($data['role']);
            if ($user = $this->userRepository->create($data)){

                if ($roleData) {
                    $role = Role::where('id', $roleData)->first();

                    if ($role) {
                        $user->roles()->attach($role->id);
                    }
                }

                return $this->update($user->id, [
                    'avatar' => $avatar ? $this->fileService->setAvatar($user->id, $avatar) : null,
                    'background' => $background ? $this->fileService->setBackground($user->id, $background) : null,
                    'roles' => $roleData,
                ]);
            }
            return false;
        }catch (\Exception $e) {
            Log::error('AdminUserService@store: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        $this->fileService->deleteDirectory($id);
        return $this->userRepository->remove($id);
    }

    public function update($id, $data)
    {
        $userModel = $this->userRepository->find($id);
        $user = $userModel->toArray();
        if ($data['avatar'] instanceof UploadedFile){
            if ($user['avatar']){
                $this->fileService->deleteAvatarDirectory($id);
            }
            $data['avatar'] = $this->fileService->setAvatar($id, $data['avatar']);
        }
        if ($data['background'] instanceof UploadedFile){
            if ($user['background']){
                $this->fileService->deleteBackgroundDirectory($id);
            }
            $data['background'] = $this->fileService->setBackground($id, $data['background']);
        }

        $roleData = $data['roles'] ?? null;
        unset($data['roles']);

        if ($roleData){
            $role = Role::where('id', $roleData)->first();

            if ($role && !$userModel->roles->contains($role->id)) {
                $userModel->roles()->attach($role->id);
            }
        } else{
            $userModel->roles()->detach();
        }

        $changedData = [];
        foreach ($data as $key => $value) {
            if ($key == 'password'){
                if ($value != null) {
                    $changedData[$key] = Hash::make($value);
                }
                continue;
            }
            if ($key == 'avatar' || $key == 'background'){
                if ($value == null){
                    continue;
                }
            }
            if ($value != $user[$key]){
                $changedData[$key] = $value;
            }
        }
        return $this->userRepository->update($id, $changedData);
    }

    public function getById($id)
    {
        return $this->userRepository->findByIdWithRoles($id);
    }

    public function findAllWithBalance(){
        return $this->formatUsersBalanceOutput(
            $this->userRepository->findAllWithBalance()
        );
    }

    public function findByIdWithBalance($id)
    {
        return $this->formatUserBalanceOutput(
            $this->userRepository->findByIdWithBalance($id)
        );
    }

    public function findByIdWithTrophies($id)
    {
        return $this->formatUserTrophyOutput(
            $this->userRepository->findByIdWithTrophies($id)
        );
    }

    public function updateUserBalance($id, $data){
        try {
            DB::beginTransaction();
            $user = $this->userRepository->findByIdWithBalance($id);
            foreach ($user->balances as $balance) {
                if (isset($data[$balance->currency->name])){
                    $balance->amount = floatval($data[$balance->currency->name]);
                    $balance->save();
                }else{
                    return false;
                }
            }
            DB::commit();
            return true;
        }catch (\Exception $e) {
            Log::error('AdminUserService@updateUserBalance: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
    }


    private function formatUsersBalanceOutput($data) {
        return collect($data)->map(function ($user) {
            return [
                'id' => $user->id,
                'short_id' => $user->short_id,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'balance' => collect($user['balances'])
                    ->keyBy('currency.name')
                    ->map(function ($balance) {
                    return [
                        'id' => $balance['id'],
                        'amount' => $balance['amount'],
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    private function formatUserBalanceOutput($user) {
        return [
            'id' => $user->id,
            'short_id' => $user->short_id,
            'username' => $user->username,
            'avatar' => $user->avatar,
            'balance' => collect($user->balances)
                ->keyBy('currency.name')
                ->map(function ($balance) {
                    return [
                        'id' => $balance->id,
                        'amount' => $balance->amount,
                    ];
                })->toArray(),
        ];
    }

    private function formatUserTrophyOutput($user) {
        return [
            'id' => $user->id,
            'short_id' => $user->short_id,
            'username' => $user->username,
            'avatar' => $user->avatar,
            'trophies' => $user->trophies,
        ];
    }

    public function updateUserTrophies($data, $id) {
        $user = $this->userRepository->find($id);
        if ($data == []){
            $user->trophies()->sync($data);
        } else {
            $user->trophies()->sync($data['trophies']);
        }
    }
}
