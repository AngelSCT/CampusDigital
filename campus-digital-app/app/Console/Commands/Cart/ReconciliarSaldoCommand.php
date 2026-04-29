<?php

namespace App\Console\Commands\Cart;

use App\Jobs\ReintentaConciliacion;
use App\Models\Cart\ConciliacionPendiente;
use Illuminate\Console\Command;

class ReconciliarSaldoCommand extends Command
{
    protected $signature   = 'carrito:reconciliar-saldo {--limit=50 : Número máximo de filas a procesar}';
    protected $description = 'Dispara el job de reconciliación para las conciliaciones pendientes. Útil tras recuperación del módulo Saldo.';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

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
