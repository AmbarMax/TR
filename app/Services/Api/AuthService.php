<?php

namespace App\Services\Api;


use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct()
    {
    }

    public function getTokenData($token, $user)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'centrifugo_token' => $user->generateCentrifugeToken()
        ];
    }
}
