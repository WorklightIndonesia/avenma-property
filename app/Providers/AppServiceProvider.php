<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password as PasswordRule;

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
        PasswordRule::defaults(
            fn () => PasswordRule::min(8)->mixedCase()->numbers()->symbols()
        );

        Event::listen(Login::class, fn (Login $event) => AuditLog::recordAuth('login', $event->user));
        Event::listen(Logout::class, fn (Logout $event) => AuditLog::recordAuth('logout', $event->user));
        Event::listen(Failed::class, fn (Failed $event) => AuditLog::recordAuth(
            'login_failed',
            $event->user,
            ['email' => $event->credentials['email'] ?? null],
        ));
    }
}
