<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HallResource;
use App\Models\User;

class HallController extends Controller
{
    public function show(string $username): HallResource
    {
        $user = User::where('username', $username)->firstOrFail();

        return new HallResource($user);
    }
}
