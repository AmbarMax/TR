<?php

namespace App\Repositories\Api;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(User::class);
    }

    public function findByEmail(string $email): ?Model
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    public function findById(string $id): ?Model
    {
        return $this->model->newQuery()->where('id', $id)->first();
    }

    public function findByUserName(string $username): ?Model
    {
        return $this->model->newQuery()->where('username', $username)->first();
    }
}
