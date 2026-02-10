<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\LogAuthentication;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            [LogAuthentication::class, 'handleRegistered'],
        ],
        Login::class => [
            [LogAuthentication::class, 'handleLogin'],
        ],
        Failed::class => [
            [LogAuthentication::class, 'handleFailed'],
        ],
        Logout::class => [
            [LogAuthentication::class, 'handleLogout'],
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}