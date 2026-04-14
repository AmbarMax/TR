<?php

namespace App\Services\Api;


use App\Repositories\Api\UserRepository;
use App\Services\AbstractServices\AbstractUserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;

class UserService extends AbstractUserService
{

    public function __construct()
    {
        parent::__construct(
            new UserRepository()
        );
    }

    public function updateAvatar($id, $data)
    {
        return $this->update($id, [
                'avatar' => $this->fileService->setAvatar($id, $data['avatar'])
            ]);
    }

    public function updateBackground($id, $data)
    {
        return $this->update($id, [
                'background' => $this->fileService->setBackground($id, $data['background'])
            ]);
    }

    public function findUserByEmail($email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function findById($id)
    {
        return $this->userRepository->findById($id);
    }

    public function findByUserName($username)
    {
        return $this->userRepository->findByUserName($username);
    }

    public function updatePassword($id, $data)
    {
        if (Hash::check($data['old_password'], Auth::user()->password))  {
            return $this->update($id, [
                'password' => $data['new_password']
            ]);
        }
    }



}
