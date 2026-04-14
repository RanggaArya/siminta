<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ResetPassword::createUrlUsing(function ($notifiable, string $token) {
        //     $panelId   = 'admin';
        //     $routeName = "filament.{$panelId}.auth.password-reset.reset";

        //     $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        //     return URL::temporarySignedRoute(
        //         $routeName,
        //         now()->addMinutes($expire),
        //         [
        //             'email' => $notifiable->getEmailForPasswordReset(),
        //             'token' => $token,
        //         ]
        //     );
        // });
    }
}
