<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminResetPasswordRequest;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\UserResetPasswordNotification;
use App\Notifications\UserResetTwoFactorAuthNotification;
use App\Providers\RouteServiceProvider;
use App\Services\Api\UserService;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    public function __construct(private readonly UserService $service)
    {
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }

    protected function broker()
    {
        return Password::broker('admins');
    }

    public function passwordResetView(Request $request){
        $token = $request->route()->parameter('token');

        return view('auth.admin.reset-password')->with([
            'token' => $token,
            'email' => $request->email
            ]);
    }

    public function passwordReset(AdminResetPasswordRequest $request){
        $user = $this->service->findUserByEmail($request->email);
        if (Password::tokenExists($user, $request->token)) {
            $user->update(['password' => Hash::make($request->password)]);
            return response()->json([
                'message' => 'Password successfully updated'
            ], ResponseAlias::HTTP_CREATED);
        } else {
            return response()->json([
                'message' => 'Incorrect data'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function sendDisableAuthMail(Request $request){
        $email = $request->email;
        $user = $this->service->findUserByEmail($email);
        if ($user) {
            $token = Password::createToken($user);
            $resetLink = url("reset-2fa?token={$token}&email={$email}");
            Notification::send($user, new UserResetTwoFactorAuthNotification($resetLink, $user->name));
            return response()->json([
                'message' => 'Email sent successfully'
            ], ResponseAlias::HTTP_CREATED);
        } else {
            return response()->json([
                'message' => 'Incorrect data'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function ResetTwoFactorAuth(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user->secret_key == $request->secret_key) {
            $user->update(['google2fa_secret' => '', 'google2fa_status' => false, 'secret_key' => '']);
            return response()->json([
                'message' => 'Success!'
            ], ResponseAlias::HTTP_OK);
        }
        else {
            return response()->json([
                'message' => 'Invalid credentials'
            ],  ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
