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
use App\Http\Controllers\Api\Integrations\RiotController;
use App\Http\Controllers\Api\Integrations\StravaController;
use App\Http\Controllers\Api\Integrations\SteamController;
use App\Http\Controllers\Api\SteamAchievementController;
use App\Http\Controllers\Api\Integrations\StorageController;
use App\Http\Controllers\Api\ExchangeController;
use App\Http\Controllers\Api\RewardsController;
use App\Http\Controllers\Api\Forge\ChestController;
use App\Http\Controllers\Api\Key\KeyController;
use App\Http\Controllers\Api\Brand\BrandStatsController;
use App\Http\Controllers\Api\Brand\BrandChannelsController;
use App\Http\Controllers\Api\Brand\BrandRulesController;
use App\Http\Controllers\Api\Brand\BrandPollsController;
use App\Http\Controllers\Api\Brand\BrandEventsController;
use App\Http\Controllers\Api\Brand\BrandBadgesController;
use App\Http\Controllers\Api\Brand\BrandTrophiesController;
use App\Http\Controllers\Api\Brand\BrandGuildController;

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

// Brand Dashboard API — JWT + admin role required
Route::prefix('brand')->middleware([JwtMiddleware::class, 'role:brand_admin|tr_admin|tr_superadmin'])->group(function () {
    Route::get('/guild', [BrandGuildController::class, 'index']);
    Route::post('/guild/select', [BrandGuildController::class, 'select']);
    Route::delete('/guild', [BrandGuildController::class, 'disconnect']);
    Route::get('/stats', [BrandStatsController::class, 'index']);
    Route::get('/channels', [BrandChannelsController::class, 'index']);
    Route::get('/rules', [BrandRulesController::class, 'index']);
    Route::post('/rules', [BrandRulesController::class, 'store']);
    Route::put('/rules/{id}', [BrandRulesController::class, 'update']);
    Route::get('/badges', [BrandBadgesController::class, 'index']);
    Route::post('/badges', [BrandBadgesController::class, 'store']);
    Route::put('/badges/{id}', [BrandBadgesController::class, 'update']);
    Route::delete('/badges/{id}', [BrandBadgesController::class, 'destroy']);
    Route::get('/polls', [BrandPollsController::class, 'index']);
    Route::post('/polls', [BrandPollsController::class, 'store']);
    Route::post('/polls/{id}/close', [BrandPollsController::class, 'close']);
    Route::delete('/polls/{id}', [BrandPollsController::class, 'destroy']);
    Route::get('/polls/{id}/results', [BrandPollsController::class, 'results']);
    Route::get('/events', [BrandEventsController::class, 'index']);
    Route::post('/events', [BrandEventsController::class, 'store']);
    Route::post('/events/{id}/complete', [BrandEventsController::class, 'complete']);
    Route::delete('/events/{id}', [BrandEventsController::class, 'destroy']);

    // Trophies
    Route::get('/trophies', [BrandTrophiesController::class, 'index']);
    Route::post('/trophies', [BrandTrophiesController::class, 'store']);
    Route::put('/trophies/{id}', [BrandTrophiesController::class, 'update']);
    Route::delete('/trophies/{id}', [BrandTrophiesController::class, 'destroy']);
    Route::get('/trophies/{id}/stats', [BrandTrophiesController::class, 'stats']);
});

// Bot API routes — authenticated via bot_api_key, no JWT required
Route::prefix('bot')->middleware('bot.api')->group(function () {
    // Badge granting
    Route::post('/badges/grant', [App\Http\Controllers\Api\Bot\BotBadgeController::class, 'grant']);

    // Rules
    Route::get('/rules', [App\Http\Controllers\Api\Bot\BotRuleController::class, 'index']);

    // Channels
    Route::post('/channels/sync', [App\Http\Controllers\Api\Bot\BotChannelController::class, 'sync']);
    Route::get('/channels', [App\Http\Controllers\Api\Bot\BotChannelController::class, 'index']);

    // User linking
    Route::post('/users/link', [App\Http\Controllers\Api\Bot\BotUserController::class, 'link']);
    Route::get('/users/lookup/{discord_user_id}', [App\Http\Controllers\Api\Bot\BotUserController::class, 'lookup']);

    // Polls
    Route::get('/polls/pending', [App\Http\Controllers\Api\Bot\BotPollController::class, 'pending']);
    Route::post('/polls/{id}/published', [App\Http\Controllers\Api\Bot\BotPollController::class, 'markPublished']);
    Route::post('/polls/{id}/close', [App\Http\Controllers\Api\Bot\BotPollController::class, 'close']);
    Route::post('/polls/{id}/vote', [App\Http\Controllers\Api\Bot\BotPollController::class, 'recordVote']);

    // Events
    Route::get('/events/pending', [App\Http\Controllers\Api\Bot\BotEventController::class, 'pending']);
    Route::post('/events/{id}/scheduled', [App\Http\Controllers\Api\Bot\BotEventController::class, 'markScheduled']);
    Route::post('/events/{id}/complete', [App\Http\Controllers\Api\Bot\BotEventController::class, 'complete']);
});

// Guild setup OAuth — outside JWT middleware so Discord can redirect back freely
Route::get('bot/setup/authorize', [App\Http\Controllers\Api\Bot\GuildSetupController::class, 'redirectToDiscord'])->name('bot.setup.authorize');
Route::get('bot/setup/callback', [App\Http\Controllers\Api\Bot\GuildSetupController::class, 'handleCallback'])->name('bot.setup.callback');

// OAuth callbacks — must be outside JWT middleware so Strava/Steam can redirect back freely
Route::get('steam/authorize', [SteamController::class, 'redirectToSteam'])->name('steam.authorize');
Route::get('steam/callback', [SteamController::class, 'handleSteamCallback'])->name('steam.callback');
Route::get('strava/authorize', [StravaController::class, 'redirectToStrava'])->name('strava.authorize');
Route::get('strava/callback', [StravaController::class, 'handleStravaCallback'])->name('strava.callback');

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
            Route::get('/public', [BadgeController::class, 'publicBadges'])->name('public');
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
        Route::put('/update-virtual-hall', [ProfileController::class, 'updateVirtualHall'])->name('update-virtual-hall');
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
            Route::get('/sync', [SteamController::class, 'sync'])->name('sync');
            // Steam Achievements
            Route::post('/achievements/sync', [SteamAchievementController::class, 'sync'])->name('achievements.sync');
            Route::get('/achievements/games', [SteamAchievementController::class, 'games'])->name('achievements.games');
            Route::get('/achievements/games/{gameId}', [SteamAchievementController::class, 'gameAchievements'])->name('achievements.game');
        });

    Route::prefix('riot')
        ->name('riot.')
        ->group(function () {
            Route::post('/sync', [RiotController::class, 'sync'])->name('sync');
        });

    Route::prefix('strava')
        ->name('strava.')
        ->group(function () {
            Route::get('/sync', [StravaController::class, 'sync'])->name('sync');
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
            Route::get('/available-trophies', [ForgeController::class, 'availableTrophies'])->name('available');
            Route::post('/vouchers/sign', [ForgeController::class, 'voucherSign'])->name('voucher.sign');
            Route::post('/claim/{id}', [ForgeController::class, 'claim'])->name('claim');
            Route::get('/{id}/showcase', [ForgeController::class, 'showcase'])->name('showcase');
            Route::get('/{id}/getBalance', [ForgeController::class, 'getBalance'])->name('balance');
            Route::get('/{id}/remove', [ForgeController::class, 'remove'])->name('remove');
        });

    Route::prefix('chests')
        ->name('chests.')
        ->group( function(){
            Route::get('/', [ChestController::class, 'index'])->name('index');
            Route::get('/user', [ChestController::class, 'userChests'])->name('user');
            Route::post('/{id}/get', [ChestController::class, 'get'])->name('get');
            Route::get('/{id}/open', [ChestController::class, 'open'])->name('open');
            Route::get('/{id}/view', [ChestController::class, 'view'])->name('view');
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
            Route::post('/create-achievement', [FeedController::class, 'createAchievement'])->name('createAchievement');
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

    Route::prefix('rewards')
        ->name('rewards.')
        ->group(function () {
            Route::get('/battle-pass', [RewardsController::class, 'battlePass'])->name('battle-pass');
            Route::post('/buy-level', [RewardsController::class, 'buyLevel'])->name('buy-level');
            Route::post('/convert', [RewardsController::class, 'convert'])->name('convert');
            Route::get('/shop-items', [RewardsController::class, 'shopItems'])->name('shop-items');
            Route::post('/buy-shop-item', [RewardsController::class, 'buyShopItem'])->name('buy-shop-item');
            Route::get('/purchase-history', [RewardsController::class, 'purchaseHistory'])->name('purchase-history');
        });

    Route::prefix('notification')
        ->name('notification.')
        ->group( function(){
            Route::get('/index', [NotificationController::class, 'index'])->name('index');
        });

    Route::post('/bot/link/confirm', [App\Http\Controllers\Api\Bot\BotLinkController::class, 'confirmLink']);

    Route::get('/getAllCountries', [ProfileController::class, 'allCountries'])->name('all-countries');

    Route::post('2fa-get', [UserAuthenticateController::class, 'twoFaGet']);
    Route::get('2fa-status', [UserAuthenticateController::class, 'twoFaStatus']);

    Route::post('2fa-activate', [UserAuthenticateController::class, 'twoFaActivate']);
    Route::post('2fa-deactivate', [UserAuthenticateController::class, 'twoFaDeactivate']);
    Route::get('generate-secret-key', [UserAuthenticateController::class, 'generateSecretKey']);

    // Hall — pursuits and follow (JWT-protected)
    Route::get('/pursuits', [App\Http\Controllers\Api\PursuitController::class, 'index'])->name('pursuits.index');
    Route::post('/pursuits', [App\Http\Controllers\Api\PursuitController::class, 'store'])->name('pursuits.store');
    Route::delete('/pursuits/{trophyId}', [App\Http\Controllers\Api\PursuitController::class, 'destroy'])->name('pursuits.destroy');
    Route::post('/users/{username}/follow', [App\Http\Controllers\Api\HallController::class, 'follow'])->name('users.follow');
    Route::delete('/users/{username}/follow', [App\Http\Controllers\Api\HallController::class, 'unfollow'])->name('users.unfollow');

});

Route::get('/storage/{path}', [StorageController::class, 'getImage']);
Route::post('forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [ResetPasswordController::class, 'passwordReset']);

Route::get('/virtual-hall/{username}', [VirtualHallController::class, 'show']);

// Polymorphic Hall endpoint — player or brand resolved via account_type on the user row
Route::get('/users/{username}', [App\Http\Controllers\Api\HallController::class, 'show'])
    ->name('users.show');
Route::get('/users/{username}/conquerors', [App\Http\Controllers\Api\HallController::class, 'conquerors'])
    ->name('users.conquerors');
Route::get('/users/{username}/active-items', [App\Http\Controllers\Api\HallController::class, 'activeItems'])
    ->name('users.active-items');

Route::post('sendDisableAuthMail', [ResetPasswordController::class, 'sendDisableAuthMail']);
Route::post('reset-2fa', [ResetPasswordController::class, 'ResetTwoFactorAuth']);

Route::get('/virtual-hall/{username}', [VirtualHallController::class, 'show']);

Route::get('/get-current-email', [StorageController::class, 'getCurrentEmail']);
