<?php

namespace App\Console\Commands\Cart;

use App\Jobs\ReintentaConciliacion;
use App\Models\Cart\Carrito;
use App\Models\Cart\ConciliacionPendiente;
use Illuminate\Console\Command;

class ReconciliarSaldoCommand extends Command
{
    protected $signature   = 'carrito:reconciliar-saldo {--limit=50 : Número máximo de filas a procesar}';
    protected $description = 'Dispara conciliaciones pendientes y limpia estados huérfanos (procesando, procesando_checkout).';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        // ── 1. Limpiar conciliaciones huérfanas en 'procesando' ──────────────
        // Si un job murió entre TX1 y TX2, la conciliación quedó en 'procesando'.
        // No se puede revertir a 'pendiente': el cargo pudo haberse ejecutado.
        // Enviar a revisión manual para que el equipo consulte Módulo 4.2.
        $ttlConciliacion = (int) config('cart.saldo.procesando_ttl_minutos', 10);

        $huerfanasConciliacion = ConciliacionPendiente::where('estado_conciliacion', ConciliacionPendiente::ESTADO_PROCESANDO)
            ->where('updated_at', '<', now()->subMinutes($ttlConciliacion))
            ->get();

        if ($huerfanasConciliacion->isNotEmpty()) {
            $this->warn("Limpiando {$huerfanasConciliacion->count()} conciliación(es) huérfana(s) en 'procesando' → requiere_revision_manual.");
            foreach ($huerfanasConciliacion as $c) {
                $c->update(['estado_conciliacion' => ConciliacionPendiente::ESTADO_REQUIERE_REVISION]);
                $this->line("  → Conciliación carrito {$c->carrito_uuid}: procesando → requiere_revision_manual");
            }
        }

        // ── 2. Limpiar carritos huérfanos en 'procesando_checkout' ───────────
        // Si un proceso murió después de TX1 (marcó procesando_checkout) pero antes de TX2,
        // el carrito quedó bloqueado. Revertir a 'abierto' para que el usuario pueda
        // intentar el checkout nuevamente.
        //
        // IMPORTANTE: este TTL debe ser MAYOR que el TTL de reserva de Módulo 4.2.
        // Si el checkout murió después de reservar() pero antes de liberar(), la reserva
        // en Módulo 4.2 ya habrá expirado cuando este command limpie el carrito.
        // Fase 0: se usa updated_at; Fase 1: agregar columna checkout_started_at.
        $ttlCheckout = (int) config('cart.checkout.procesando_ttl_minutos', 10);

        $huerfanasCarrito = Carrito::where('estado', Carrito::ESTADO_PROCESANDO_CHECKOUT)
            ->where('updated_at', '<', now()->subMinutes($ttlCheckout))
            ->get();

        if ($huerfanasCarrito->isNotEmpty()) {
            $this->warn("Limpiando {$huerfanasCarrito->count()} carrito(s) huérfano(s) en 'procesando_checkout' → abierto.");
            foreach ($huerfanasCarrito as $c) {
                $c->update(['estado' => Carrito::ESTADO_ABIERTO]);
                $this->line("  → Carrito {$c->uuid}: procesando_checkout → abierto");
            }
        }

        // ── 3. Disparar jobs para conciliaciones pendientes ──────────────────
        $pendientes = ConciliacionPendiente::where('estado_conciliacion', ConciliacionPendiente::ESTADO_PENDIENTE)
            ->where(function ($q) {
                $q->whereNull('proximo_intento_at')
                  ->orWhere('proximo_intento_at', '<=', now());
            })
            ->limit($limit)
            ->get();

        if ($pendientes->isEmpty()) {
            $this->info('No hay conciliaciones pendientes listas para procesar.');
            return self::SUCCESS;
        }

        $this->info("Procesando {$pendientes->count()} conciliación(es)...");

        foreach ($pendientes as $conciliacion) {
            ReintentaConciliacion::dispatch($conciliacion);
            $this->line("  → Carrito {$conciliacion->carrito_uuid} encolado.");
        }

        $this->info('Listo.');

        return self::SUCCESS;
    }
}
