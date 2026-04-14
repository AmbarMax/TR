<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Enums\SessionStatus;
use App\Http\Requests\Admin\Auth\AdminForgetPasswordRequest;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgetPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    protected function guard()
    {
        return Auth::guard('admin');
    }

    protected function broker()
    {
        return Password::broker('admins');
    }
    public function forgotPasswordView(Request $request)
    {
        return view('auth.admin.forgot-password');
    }

    public function forgotPassword(AdminForgetPasswordRequest $request)
    {
        $admin = Admin::where('email', $request->email)->first();
        if ($admin) {
            $token = Password::createToken($admin);
            $resetLink = url("admin/password-reset?token={$token}&email={$request->email}");
            Notification::send($admin, new AdminResetPasswordNotification($resetLink));
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Info,
                'title' => 'Reset Password',
                'message' => 'Reset link sent to your email'
            ]);
        } else {
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Reset Password',
                'message' => 'Reset link not sent to your email'
            ]);
        }
        return redirect()->route('admin.login.index');
    }


}
