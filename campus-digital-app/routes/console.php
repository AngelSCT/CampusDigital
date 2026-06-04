<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Módulo 4.4 Carrito ────────────────────────────────────────────────────────
// Limpia estados huérfanos (procesando, procesando_checkout) y dispara
// ReintentaConciliacion para los pagos diferidos pendientes.
// Frecuencia: cada 5 minutos en producción; ajustar si se aumenta el TTL de reserva.
Schedule::command('carrito:reconciliar-saldo')->everyFiveMinutes();
