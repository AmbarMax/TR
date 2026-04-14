<?php

namespace App\Http\Middleware;

use App\Services\Api\AuthService;
use Closure;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class JwtMiddleware extends BaseMiddleware
{
    public function __construct(\Tymon\JWTAuth\JWTAuth $auth, private readonly AuthService $authService)
    {
        parent::__construct($auth);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                    'message' => 'This token is invalid. Please Login'
                ], ResponseAlias::HTTP_UNAUTHORIZED);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'message' => 'Token expired'
                ], ResponseAlias::HTTP_UNAUTHORIZED);
            } else {
                return response()->json([
                    'message' => 'Authorization Token not found'
                ], ResponseAlias::HTTP_UNAUTHORIZED);
            }
        }
        return $next($request);
    }
}


//namespace App\Http\Middleware;
//
//use App\Services\Api\AuthService;
//use Closure;
//use Exception;
//use Tymon\JWTAuth\Exceptions\JWTException;
//use Tymon\JWTAuth\Facades\JWTAuth;
//use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
//use Symfony\Component\HttpFoundation\Response as ResponseAlias;
//
//
//class JwtMiddleware extends BaseMiddleware
//{
//    public function __construct(\Tymon\JWTAuth\JWTAuth $auth, private readonly AuthService $authService)
//    {
//        parent::__construct($auth);
//    }
//
//    /**
//     * Handle an incoming request.
//     *
//     * @param \Illuminate\Http\Request $request
//     * @param \Closure $next
//     * @return mixed
//     */
//    public function handle($request, Closure $next)
//    {
//        try {
//            $user = JWTAuth::parseToken()->authenticate();
//        } catch (Exception $e) {
//            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
//                return response()->json([
//                    'message' => 'This token is invalid. Please Login'
//                ], ResponseAlias::HTTP_UNAUTHORIZED);
//            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
//                try {
//                    return response()->json([
//                        'message' => 'Token expired.'
//                    ], ResponseAlias::HTTP_EARLY_HINTS);
//                } catch (JWTException $e) {
//                    return response()->json([
//                        'message' => 'Token cannot be refreshed, please Login again'
//                    ], ResponseAlias::HTTP_EARLY_HINTS);
//                }
//            } else {
//                return response()->json([
//                    'message' => 'Authorization Token not found'
//                ], ResponseAlias::HTTP_UNAUTHORIZED);
//
//            }
//        }
//        return $next($request);
//    }
//}
