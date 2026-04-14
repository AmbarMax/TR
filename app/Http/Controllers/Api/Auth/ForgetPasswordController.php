<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\SessionStatus;
use App\Http\Requests\Admin\Auth\AdminForgetPasswordRequest;
use App\Http\Controllers\Controller;
use App\Mail\UserResetPasswordMailer;
use App\Models\User;
use App\Notifications\AdminResetPasswordNotification;
use App\Notifications\UserResetPasswordNotification;
use App\Services\Api\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ForgetPasswordController extends Controller
{
    use SendsPasswordResetEmails;

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

    public function forgotPassword(AdminForgetPasswordRequest $request)
    {
        $email = $request->email;
        $user = $this->service->findUserByEmail($email);
        if ($user) {
            $token = Password::createToken($user);
            $resetLink = url("reset-password?token={$token}&email={$email}");
            Notification::send($user, new UserResetPasswordNotification($resetLink, $user->name));
            return response()->json([
                'message' => 'Email sent successfully'
            ], ResponseAlias::HTTP_CREATED);
        } else {
            return response()->json([
                'message' => 'Incorrect data'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }


    }


}
