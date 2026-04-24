<?php

namespace App\Providers;

use App\Models\Balance;
use App\Models\User;
use App\Observers\BalanceObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \SocialiteProviders\Discord\DiscordExtendSocialite::class.'@handle',
            \SocialiteProviders\Steam\SteamExtendSocialite::class.'@handle',
        ],
        \Spatie\Permission\Events\RoleAttached::class => [
            \App\Listeners\LogRoleChange::class.'@handleAttached',
        ],
        \Spatie\Permission\Events\RoleDetached::class => [
            \App\Listeners\LogRoleChange::class.'@handleDetached',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Balance::observe(BalanceObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
