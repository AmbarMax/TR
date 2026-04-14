<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminResetPasswordRequest;
use App\Models\Admin;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

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
        $admin = Admin::where('email', $request->email)->first();
        if (Password::tokenExists($admin, $request->token)) {
            Admin::where('email', $request->email)
                ->update(['password' => Hash::make($request->password)])
                ? $request->session()->flash('notification' ,[
                'type' => SessionStatus::Success,
                'title' => 'Reset Password',
                'message' => 'Account password updated'
            ])
                : $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Reset Password',
                'message' => 'Account password not updated'
            ]);
        } else {
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Reset Password',
                'message' => 'Account password not updated'
            ]);
        }

        return redirect()->route('admin.login.index');
    }
}
