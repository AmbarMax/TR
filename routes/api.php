<?php


use App\Http\Controllers\Api\Forge\ForgeController;
use App\Http\Controllers\Api\User\FollowerController;
use App\Http\Controllers\Api\User\NotificationController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\User\VirtualHallController;
use App\Http\Controllers\Api\Validate\AchievementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\UserAuthenticateController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\Integrations\GithubController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\Api\Auth\ForgetPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Feed\FeedController;
use App\Http\Controllers\Api\Integrations\BadgeController;
use App\Http\Controllers\Api\Integrations\DiscordController;
use App\Http\Controllers\Api\Integrations\SteamController;
use App\Http\Controllers\Api\SteamAchievementController;
use App\Http\Controllers\Api\Integrations\StorageController;
use App\Http\Controllers\Api\ExchangeController;
use App\Http\Controllers\Api\Forge\ChestController;
use App\Http\Controllers\Api\Key\KeyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('startLogin', [UserAuthenticateController::class, 'startLogin']);
Route::post('login', [UserAuthenticateController::class, 'store']);
Route::post('register', [UserAuthenticateController::class, 'register']);
Route::post('refresh-token', [UserAuthenticateController::class, 'refreshToken'])->name('refresh-token');

Route::middleware([JwtMiddleware::class])->group(function () {

    Route::post('logout', [UserAuthenticateController::class, 'destroy'])->name('logout');


    Route::prefix('badges')
        ->name('badges.')
        ->group( function(){
            Route::get('/', [BadgeController::class, 'badges'])->name('badges');
            Route::get('/public', [BadgeController::class, 'publicBadges'])->name('badges');
            Route::get('/{id}/destroy', [BadgeController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/showcase', [BadgeController::class, 'showcase'])->name('showcase');
            Route::get('/{id}/remove', [BadgeController::class, 'remove'])->name('remove');
            Route::post('/share', [BadgeController::class, 'share'])->name('share');

        });

    Route::prefix('users')
        ->name('users.')
        ->group( function(){
            Route::get('/', [UserController::class, 'index'])->name('users');

        });

    Route::prefix('profile')
        ->name('profile.')
        ->group( function(){
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/balances', [ProfileController::class, 'balances'])->name('balances');
        Route::post('/update-avatar', [ProfileController::class, 'updateAvatar'])->name('update-avatar');
        Route::post('/update-background', [ProfileController::class, 'updateBackground'])->name('update-background');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::put('/update-password', [ProfileController::class, 'updatePassword'])->name('update-password');
    });

    Route::prefix('github')
        ->name('github.')
        ->group( function(){
            Route::get('/authorize', [GithubController::class, 'redirectToGithub'])->name('authorize');
            Route::get('/callback', [GithubController::class, 'handleGithubCallback'])->name('callback');
            Route::get('/sync', [GithubController::class, 'sync'])->name('sync');
        });


    Route::prefix('steam')
        ->name('steam.')
        ->group( function(){
            Route::get('/authorize', [SteamController::class, 'redirectToSteam'])->name('authorize');
            Route::get('/callback', [SteamController::class, 'handleSteamCallback'])->name('callback');
            Route::get('/sync', [SteamController::class, 'sync'])->name('sync');
            // Steam Achievements
            Route::post('/achievements/sync', [SteamAchievementController::class, 'sync'])->name('achievements.sync');
            Route::get('/achievements/games', [SteamAchievementController::class, 'games'])->name('achievements.games');
            Route::get('/achievements/games/{gameId}', [SteamAchievementController::class, 'gameAchievements'])->name('achievements.game');
        });

    Route::prefix('discord')
        ->name('discord.')
        ->group( function(){
            Route::get('/authorize', [DiscordController::class, 'redirectToDiscord'])->name('authorize');
            Route::get('/callback', [DiscordController::class, 'handleDiscordCallback'])->name('callback');
            Route::get('/sync', [DiscordController::class, 'sync'])->name('sync');
        });

    Route::prefix('forge')
        ->name('forge.')
        ->group( function(){
            Route::get('/', [ForgeController::class, 'index'])->name('index');
            Route::get('/trophies', [ForgeController::class, 'trophies'])->name('trophies');
            Route::get('/available-trophies', [ForgeController::class, 'availableTrophies'])->name('trophies');
            Route::post('/vouchers/sign', [ForgeController::class, 'voucherSign'])->name('voucher.sign');
            Route::post('/claim/{id}', [ForgeController::class, 'claim'])->name('claim');
            Route::get('/{id}/showcase', [ForgeController::class, 'showcase'])->name('showcase');
            Route::get('/{id}/getBalance', [ForgeController::class, 'getBalance'])->name('showcase');
            Route::get('/{id}/remove', [ForgeController::class, 'remove'])->name('remove');
        });

    Route::prefix('chests')
        ->name('chests.')
        ->group( function(){
            Route::get('/', [ChestController::class, 'index'])->name('index');
            Route::get('/user', [ChestController::class, 'userChests'])->name('index');
            Route::post('/{id}/get', [ChestController::class, 'get'])->name('index');
            Route::get('/{id}/open', [ChestController::class, 'open'])->name('index');
            Route::get('/{id}/view', [ChestController::class, 'view'])->name('index');
        });

    Route::prefix('keys')
        ->name('keys.')
        ->group( function(){
            Route::get('/', [KeyController::class, 'index'])->name('index');
        });

    Route::prefix('feed')
        ->name('feed.')
        ->group(function (){
            Route::get('/', [FeedController::class, 'index'])->name('index');
            Route::post('/share', [FeedController::class, 'share'])->name('share');
            Route::get('/my-posts', [FeedController::class, 'getMyFeed'])->name('my-posts');
            Route::get('/posts', [FeedController::class, 'followingFeed'])->name('posts');
            Route::post('/donate', [FeedController::class, 'donate'])->name('donate');
            Route::post('/remove', [FeedController::class, 'remove'])->name('remove');
            Route::post('/comment', [FeedController::class, 'createComment'])->name('createComment');
            Route::get('/comments/{id}', [FeedController::class, 'getComments'])->name('getPostComments');
            /** Only for moderator **/
            Route::post('/destroy', [FeedController::class, 'destroy'])->name('destroy');
            Route::post('/comments/destroy', [FeedController::class, 'destroyComment']);
        });

    Route::prefix('achievement')
        ->name('achievement.')
        ->group(function (){
            Route::get('/', [AchievementController::class, 'index'])->name('index');
            Route::post('/', [AchievementController::class, 'achievementCreate'])->name('create');
            Route::post('/revalidate', [AchievementController::class, 'revalidate'])->name('revalidate');
            Route::post('/revalidate-social', [AchievementController::class, 'revalidateSocial'])->name('revalidateSocial');
            Route::post('/delete', [AchievementController::class, 'delete'])->name('delete');
            Route::post('/share', [AchievementController::class, 'share'])->name('share');
            Route::post('/reject', [AchievementController::class, 'reject'])->name('reject');
            Route::post('/social-approve', [AchievementController::class, 'socialApprove'])->name('approve');
            Route::post('/social-approve/reject', [AchievementController::class, 'socialApproveReject'])->name('socialApproveReject');
            Route::get('/{id}/showcase', [AchievementController::class, 'showcase'])->name('showcase');
            Route::get('/{id}/info', [AchievementController::class, 'info'])->name('info');
            Route::get('/{id}/removeShowcase', [AchievementController::class, 'removeShowcase'])->name('removeShowcase');
        });

    Route::prefix('follow')
        ->name('follow.')
        ->group( function(){
            Route::get('/index', [FollowerController::class, 'index'])->name('index');
            Route::get('/following', [FollowerController::class, 'following'])->name('following');
            Route::get('/followers', [FollowerController::class, 'followers'])->name('followers');
            Route::post('/action', [FollowerController::class, 'action'])->name('action');
            Route::post('/destroy', [FollowerController::class, 'destroy'])->name('destroy');
        });

    Route::prefix('exchange')
        ->name('exchange.')
        ->group( function(){
            Route::get('/index', [ExchangeController::class, 'index'])->name('index');
            Route::post('/store', [ExchangeController::class, 'store'])->name('store');
        });

    Route::prefix('notification')
        ->name('notification.')
        ->group( function(){
            Route::get('/index', [NotificationController::class, 'index'])->name('index');
        });

    Route::get('/getAllCountries', [ProfileController::class, 'allCountries'])->name('all-countries');

    Route::post('2fa-get', [UserAuthenticateController::class, 'twoFaGet']);
    Route::get('2fa-status', [UserAuthenticateController::class, 'twoFaStatus']);

    Route::post('2fa-activate', [UserAuthenticateController::class, 'twoFaActivate']);
    Route::post('2fa-deactivate', [UserAuthenticateController::class, 'twoFaDeactivate']);
    Route::get('generate-secret-key', [UserAuthenticateController::class, 'generateSecretKey']);

});

Route::get('/storage/{path}', [StorageController::class, 'getImage']);
Route::post('forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [ResetPasswordController::class, 'passwordReset']);

Route::get('/virtual-hall/{username}', [VirtualHallController::class, 'show']);

Route::post('sendDisableAuthMail', [ResetPasswordController::class, 'sendDisableAuthMail']);
Route::post('reset-2fa', [ResetPasswordController::class, 'ResetTwoFactorAuth']);

Route::get('/virtual-hall/{username}', [VirtualHallController::class, 'show']);

Route::get('/get-current-email', [StorageController::class, 'getCurrentEmail']);
