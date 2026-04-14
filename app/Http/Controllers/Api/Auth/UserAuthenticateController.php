<?php

namespace App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ActivateTwoFaRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\TwoFaLoginRequest;
use App\Http\Requests\Api\Auth\TwoFaRegisterRequest;
use App\Models\User;
use App\Services\Api\AuthService;
use App\Services\Api\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PragmaRX\Google2FALaravel\Google2FA as Google2faValidator;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException;
use PragmaRX\Google2FAQRCode\Google2FA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserAuthenticateController extends Controller
{
    public function __construct(private readonly UserService $userService,
                                private readonly AuthService $authService)
    {
    }

    /**
     * @throws MissingQrCodeServiceException
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        if ($this->userService->store($request->except(['confirm_password', '_token']))){
            if ($token = JWTAuth::attempt($request->only('email', 'password'))){
                return response()->json([
                    'message' => 'Account created successfully',
                    'token' => $this->authService->getTokenData(
                        token: $token,
                        user: Auth::user()
                    ),
                ],
                    ResponseAlias::HTTP_CREATED
                );

            }
        }
       return response()->json([
           'message' => 'Account creation failed'
       ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function startLogin(TwoFaLoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request['email'])->first();
        if ($user && Hash::check($request['password'], $user['password'])) {
            if (!$user['google2fa_status']) {
                if ($token = JWTAuth::attempt($request->only('email', 'password'))) {

                    return response()->json([
                        'token' => $this->authService->getTokenData(
                            token: $token,
                            user: Auth::user()
                        ),
                    ],
                        ResponseAlias::HTTP_OK);
                }
            }

            return response()->json([],
                ResponseAlias::HTTP_ACCEPTED);
        }
        return response()->json([
            'message' => 'Unauthorized'
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function store(LoginRequest $request): JsonResponse
    {
        if ($token = JWTAuth::attempt($request->only('email', 'password'))) {

            $user = Auth::user();
            $google2fa = new Google2faValidator($request);

            if (!$google2fa->verifyGoogle2FA($user->google2fa_secret, $request['one_time_password'])) {
                return response()->json([
                    'message' => 'Invalid code'
                ],
                ResponseAlias::HTTP_BAD_REQUEST);
            }
            return response()->json([
                'token' => $this->authService->getTokenData(
                    token: $token,
                    user: Auth::user()
                ),
            ],
            ResponseAlias::HTTP_OK);
        }
        return response()->json([
            'message' => 'Unauthorized'
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }


    public function destroy(Request $request)
    {
        Auth::logout();
        return !Auth::check()
            ?response()->json([
                'message' => 'Logout success'
            ], ResponseAlias::HTTP_OK)
            :response()->json([
                'message' => 'Logout failed'
            ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function refreshToken(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'message' => 'Token not provided'
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        try {
            $refreshed = JWTAuth::refresh(JWTAuth::getToken());
            JWTAuth::setToken($refreshed)->toUser();
            return response()->json([
                'message' => 'Token refreshed successfully',
                'token' => $this->authService->getTokenData(
                    token: $refreshed,
                    user: Auth::user()),
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'message' => 'Could not refresh token'
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }
    }

    public function twoFaGet()
    {
        $twoFa = new Google2FA();
        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');
        $google2fa_secret = $google2fa->generateSecretKey();

        $QR_image = $twoFa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $google2fa_secret
        );

        if (!preg_match('/^data:image\/[a-z]+;base64,/', $QR_image)) {
            $QR_image = 'data:image/svg+xml;base64,' . base64_encode($QR_image);
        }


        return response()->json([
            'QR' => $QR_image,
            'secret' => $google2fa_secret
        ],
            ResponseAlias::HTTP_OK
        );
    }

    public function twoFaActivate(ActivateTwoFaRequest $request)
    {
        $dataToUpdate['google2fa_secret'] = $request->google2fa_secret;
        $dataToUpdate['google2fa_status'] = true;

        $one_time_password = $request->one_time_password;
        $auth_status = $this->userService->update(auth()->id(), $dataToUpdate);

        if ($auth_status){
            $google2fa = app(Google2FA::class);
            $test1 = $google2fa->getCurrentOtp($dataToUpdate['google2fa_secret']);
            if ($test1 != $one_time_password) {
                    $this->userService->update(auth()->id(), ['google2fa_secret' => '', 'google2fa_status' => false]);
                    return response()->json([
                        'message' => 'Invalid code'
                    ],
                        ResponseAlias::HTTP_BAD_REQUEST);
                } else {
                    Log::info('success');
                    return response()->json([
                        'message' => 'Two factor authentication successfully activated'
                    ], ResponseAlias::HTTP_CREATED);
                }

        }
        else {
            return response()->json([
                'message' => 'Two factor authentication not activated'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }


        /*return $this->userService->update(auth()->id(), $dataToUpdate)
            ?response()->json([
                'message' => 'Two factor authentication successfully activated'
            ], ResponseAlias::HTTP_CREATED)
            :response()->json([
                'message' => 'Two factor authentication not activated'
            ], ResponseAlias::HTTP_BAD_REQUEST);*/
    }

    public function twoFaDeactivate()
    {
        $dataToUpdate['google2fa_secret'] = '';
        $dataToUpdate['google2fa_status'] = false;
        return $this->userService->update(Auth::user()->id, $dataToUpdate)
            ?response()->json([
                'message' => 'Two factor authentication successfully deactivated'
            ], ResponseAlias::HTTP_CREATED)
            :response()->json([
                'message' => 'Two factor authentication not activated'
            ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function twoFaStatus()
    {
        return response()->json([
            'twoFAStatus' => Auth::user()->google2fa_status
        ]);
    }

    public function generateSecretKey()
    {
        $user = Auth::user();
        $secret_key = Str::random(16);
        $user->secret_key = $secret_key;
        $user->save();
        return response()->json([
            'secret_key' => $secret_key
        ]);
    }

}
