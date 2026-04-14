<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\Api\Profile\UpdateAvatarRequest;
use App\Http\Requests\Api\Profile\UpdateBackgroundRequest;
use App\Http\Requests\Api\Profile\UpdatePasswordRequest;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Models\Country;
use App\Models\User;
use App\Services\Api\UserService;
use http\Env\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function index()
    {
        return response()->json([
            'users' => $this->userService->getAllUsers(),
        ], ResponseAlias::HTTP_OK);
    }

}
