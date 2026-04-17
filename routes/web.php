<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Api\Integrations\SteamController;
use App\Http\Controllers\Api\Integrations\GithubController;
use App\Http\Controllers\Api\Integrations\BadgeController;
use App\Http\Controllers\Api\Integrations\DiscordController;
use App\Http\Controllers\Api\Brand\BrandGuildController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/login/steam', [SteamController::class, 'redirectToSteam']);
Route::get('/login/github', [GithubController::class, 'redirectToGithub']);
Route::get('/login/discord', [DiscordController::class, 'redirectToDiscord']);

Route::get('/api/steam/callback', [SteamController::class, 'handleSteamCallback']);
Route::get('/api/github/callback', [GithubController::class, 'handleGithubCallback']);
Route::get('/api/discord/callback', [DiscordController::class, 'handleDiscordCallback']);

Route::get('/badges/sync/{id}/{achievements}', [BadgeController::class, 'sync']);

Route::get('/api/brand/guild/connect', [BrandGuildController::class, 'connect']);

//Route::get('/test', [DiscordController::class, 'sync']);


Route::get('/bot/link', [App\Http\Controllers\Api\Bot\BotLinkController::class, 'link']);

Route::get('/{any}', function () {
    return view('layouts.web');
})->where('any', '.*')->name('ambar');
