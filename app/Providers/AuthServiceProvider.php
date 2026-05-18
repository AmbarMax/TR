<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 2026-05-18: Disabled — dead path. The 3 custom Notifications
        // (UserResetPasswordNotification, AdminResetPasswordNotification,
        // UserResetTwoFactorAuthNotification) build their reset URLs manually
        // in their respective controllers (Api/Admin/Auth/ForgetPasswordController +
        // ResetPasswordController) instead of going through the default Laravel
        // Password::sendResetLink() flow, so this callback was never invoked.
        // Re-enable if a future flow switches back to the default broker.
        //
        // use Illuminate\Auth\Notifications\ResetPassword;
        //
        // ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
        //     $adminGuard = request()->is('admin/forgot-password');
        //     if ($adminGuard) {
        //         return url(route('admin.password.reset', [
        //             'token' => $token,
        //             'email' => $notifiable->getEmailForPasswordReset(),
        //         ], false));
        //     }
        //     return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        // });
    }
}
