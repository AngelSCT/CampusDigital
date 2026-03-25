<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Recarga;
use App\Models\Pago;
use App\Observers\RecargaObserver;
use App\Observers\PagoObserver;

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
    public function boot()
    {
        Recarga::observe(RecargaObserver::class);
        Pago::observe(PagoObserver::class);
    }
}
