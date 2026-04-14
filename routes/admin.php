<?php

use App\Http\Controllers\Admin\Auth\AdminAuthenticateController;
use App\Http\Controllers\Admin\Dashboard\AdminController;
use App\Http\Controllers\Admin\Dashboard\AdminUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard\AdminDashboardController;
use App\Http\Controllers\Admin\Dashboard\AdminTrophyController;
use App\Http\Controllers\Admin\Auth\ForgetPasswordController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\Dashboard\AdminBalanceController;
use App\Http\Controllers\Admin\Dashboard\AdminAssignTrophyController;
use App\Http\Controllers\Admin\Dashboard\AdminExchangeController;
use App\Http\Controllers\Admin\AdminItemController;
use App\Http\Controllers\Admin\AdminChestController;
use App\Http\Controllers\Admin\AdminKeyController;
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('login', [AdminAuthenticateController::class, 'index'])->name('login.index');

Route::get('forgot-password', [ForgetPasswordController::class, 'forgotPasswordView'])->name('password.request');
Route::post('forgot-password', [ForgetPasswordController::class, 'forgotPassword'])->name('password.email');

Route::get('password-reset', [ResetPasswordController::class, 'passwordResetView'])->name('password.reset');
Route::post('password-reset', [ResetPasswordController::class, 'passwordReset'])->name('password.update');


Route::prefix('/auth')
    ->name('auth.')
    ->group(function () {
        Route::post('login', [AdminAuthenticateController::class, 'login'])->name('login');

        Route::post('logout', [AdminAuthenticateController::class, 'logout'])->name('logout');
    });

Route::middleware(['auth:admin'])
    ->group(function () {
        Route::prefix('/dashboard')
            ->name('dashboard.')
            ->group(function () {
                Route::get('', [AdminDashboardController::class, 'index'])->name('index');
            });

        Route::prefix('/users')
            ->name('users.')
            ->group(function () {
                Route::get('', [AdminUserController::class, 'index'])->name('index');
                Route::get('show/{id}', [AdminUserController::class, 'show'])->name('show');
                Route::post('store', [AdminUserController::class, 'store'])->name('store');
                Route::post('update/{id}', [AdminUserController::class, 'update'])->name('update');
                Route::get('edit/{id}', [AdminUserController::class, 'edit'])->name('edit');
                Route::get('disable2fa/{id}', [AdminUserController::class, 'disable2fa'])->name('disable2fa');
                Route::delete('delete/{id}', [AdminUserController::class, 'delete'])->name('delete');
            });

        Route::prefix('/admins')
            ->name('admins.')
            ->middleware('auth.superAdmin')
            ->group(function () {
                Route::get('', [AdminController::class, 'index'])->name('index');
                Route::post('store', [AdminController::class, 'store'])->name('store');
                Route::post('update/{id}', [AdminController::class, 'update'])->name('update');
                Route::get('edit/{id}', [AdminController::class, 'edit'])->name('edit');
                Route::delete('delete/{id}', [AdminController::class, 'delete'])->name('delete');
            });

        Route::prefix('/trophies')
            ->name('trophies.')
            ->group(function () {
                Route::get('', [AdminTrophyController::class, 'index'])->name('index');
                Route::get('show/{id}', [AdminTrophyController::class, 'show'])->name('show');
                Route::post('store', [AdminTrophyController::class, 'store'])->name('store');
                Route::post('update/{id}', [AdminTrophyController::class, 'update'])->name('update');
                Route::get('delete/{id}', [AdminTrophyController::class, 'delete'])->name('delete');
            });

        Route::prefix('/exchanges')
            ->name('exchanges.')
            ->group(function () {
                Route::get('', [AdminExchangeController::class, 'index'])->name('index');
                Route::get('edit/{id}', [AdminExchangeController::class, 'edit'])->name('edit');
                Route::post('update/{id}', [AdminExchangeController::class, 'update'])->name('update');
            });

        Route::prefix('/balances')
            ->name('balances.')
            ->group(function () {
                Route::get('', [AdminBalanceController::class, 'index'])->name('index');
                Route::post('update/{id}', [AdminBalanceController::class, 'update'])->name('update');
                Route::get('edit/{id}', [AdminBalanceController::class, 'edit'])->name('edit');

            });

        Route::prefix('/assignment-of-trophies')
            ->name('assignment-of-trophies.')
            ->group(function () {
                Route::get('', [AdminAssignTrophyController::class, 'index'])->name('index');
                Route::post('update/{id}', [AdminAssignTrophyController::class, 'update'])->name('update');
                Route::get('edit/{id}', [AdminAssignTrophyController::class, 'edit'])->name('edit');

            });

        Route::prefix('/items')
            ->name('items.')
            ->group(function () {
                Route::get('', [AdminItemController::class, 'index'])->name('index');
                Route::get('show/{id}', [AdminItemController::class, 'show'])->name('show');
                Route::post('store', [AdminItemController::class, 'store'])->name('store');
                Route::get('edit/{id}', [AdminItemController::class, 'edit'])->name('edit');
                Route::post('update/{id}', [AdminItemController::class, 'update'])->name('update');
                Route::get('delete/{id}', [AdminItemController::class, 'delete'])->name('delete');
            });

        Route::prefix('/chests')
            ->name('chests.')
            ->group(function () {
                Route::get('', [AdminChestController::class, 'index'])->name('index');
                Route::get('show/{id}', [AdminChestController::class, 'show'])->name('show');
                Route::post('store', [AdminChestController::class, 'store'])->name('store');
                Route::get('edit/{id}', [AdminChestController::class, 'edit'])->name('edit');
                Route::post('update/{id}', [AdminChestController::class, 'update'])->name('update');
                Route::delete('delete/{id}', [AdminChestController::class, 'delete'])->name('delete');
            });

        Route::prefix('/keys')
            ->name('keys.')
            ->group(function () {
                Route::get('', [AdminKeyController::class, 'index'])->name('index');
                Route::get('show/{id}', [AdminKeyController::class, 'show'])->name('show');
                Route::post('store', [AdminKeyController::class, 'store'])->name('store');
                Route::get('edit/{id}', [AdminKeyController::class, 'edit'])->name('edit');
                Route::post('update/{id}', [AdminKeyController::class, 'update'])->name('update');
                Route::delete('delete/{id}', [AdminKeyController::class, 'delete'])->name('delete');
            });

    });



