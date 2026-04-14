<?php

namespace App\Http\Controllers\Admin\Auth;
use App\Http\Controllers\Controller;
use \App\Http\Requests\Admin\Auth\AdminLoginRequest;
use App\Models\Admin;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AdminAuthenticateController extends Controller
{

    public function index()
    {
        return view('auth.admin.login');
    }

    public function login(AdminLoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard.index'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(route('admin.login.index'));
    }
}
