<?php

namespace App\Providers;

use App\Modules\Cart\Contracts\PedidoCreatorInterface;
use App\Modules\Cart\Services\EloquentPedidoCreator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PedidoCreatorInterface::class, EloquentPedidoCreator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
