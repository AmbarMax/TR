<?php

namespace App\Services\AbstractServices;

use App\Repositories\RepositoryInterface;
use App\Services\FileService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FAQRCode\Google2FA;

abstract class AbstractUserService
{
    protected FileService $fileService;
    protected RepositoryInterface $userRepository;

    public function __construct(RepositoryInterface $userRepository)
    {
        $this->fileService = new FileService();
        $this->userRepository = $userRepository;
    }

    public function delete($id)
    {
        return $this->userRepository->remove($id);
    }

    public function getById($id)
    {
        return $this->userRepository->find($id);
    }

    public function getAllUsers()
    {
        return $this->userRepository->findAll();
    }

    public function update($id, $data)
    {
        if (array_key_exists('country_id', $data) && $data['country_id'] === 0) {
            unset($data['country_id']);
        }
        $user = $this->userRepository->find($id);
        $changedData = [];
        foreach ($data as $key => $value) {
            if ($key == 'password'){
                if ($value != null) {
                    $changedData[$key] = Hash::make($value);
                }
                continue;
            }
            if ($value != $user->$key){
                $changedData[$key] = $value;
            }
        }
        return $this->userRepository->update($id, $changedData);
    }

    public function store(array $data)
    {
        try {
            $data['password'] = Hash::make($data['password']);
            return $this->userRepository->create($data);
        }catch (\Exception $e) {
            Log::error('AbstractUserService@store: ' . $e->getMessage());
            return false;
        }
    }

}
