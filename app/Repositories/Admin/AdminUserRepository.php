<?php

namespace App\Repositories\Admin;

use App\Models\User;
use App\Repositories\BaseRepository;

class AdminUserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(User::class);
    }

    public function findAllWithBalance()
    {
        return $this->model->newQuery()
            ->with(['balances','balances.currency'])
            ->get();
    }

    public function findByIdWithBalance(string $id){
        return $this->model->newQuery()
            ->with(['balances','balances.currency'])
            ->find($id);
    }

    public function findByIdWithRoles(string $id){
        return $this->model->newQuery()
            ->with('roles:id,name')
            ->find($id);
    }

    public function findByIdWithTrophies(string $id){
        return $this->model->newQuery()
            ->with(['trophies'])
            ->find($id);
    }

}
