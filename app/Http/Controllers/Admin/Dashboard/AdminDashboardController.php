<?php

namespace App\Http\Controllers\Admin\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Http\Requests\Admin\Auth\AdminLoginRequest;


class AdminDashboardController extends Controller
{

    public function index()
    {
        return view('admin.dashboard');
    }
}
