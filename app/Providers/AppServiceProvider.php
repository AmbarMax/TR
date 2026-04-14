<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::macro('image', fn ($asset) => $this->asset("resources/assets/images/$asset"));
        Vite::macro('svg', fn ($asset) => $this->asset("resources/assets/svg/$asset"));
        Vite::macro('apiMail', fn ($asset) => $this->asset("resources/views/api/mail/images/$asset"));
        Vite::macro('adminImage', fn ($asset) => $this->asset("resources/admin/images/$asset"));
    }
}
