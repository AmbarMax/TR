<?php

namespace App\Repositories\Api;

use App\Models\AuthIntegration;
use App\Repositories\BaseRepository;

class AuthRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(AuthIntegration::class);
    }

    public function findIntegration($id, $type)
    {
        return AuthIntegration::where('integration_id', $id)->where('name', $type)->get();
    }

}
