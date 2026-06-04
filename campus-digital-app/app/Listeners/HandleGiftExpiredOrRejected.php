<?php

namespace App\Listeners;

use App\Events\GiftExpiredOrRejected;
use App\Services\ExternalModulesService;

/**
 * Reacciona a GiftExpiredOrRejected disparando las llamadas
 * de reembolso e inventario hacia los módulos externos.
 *
 * Cuando las APIs reales de tus compañeros estén listas,
 * reemplaza los dummies en ExternalModulesService sin tocar este listener.
 */
class HandleGiftExpiredOrRejected
{
    public function __construct(
        private readonly ExternalModulesService $modules,
    ) {}

    public function handle(GiftExpiredOrRejected $event): void
    {
        $item    = $event->item;
        $motivo  = $event->motivo;
        $producto = $item->producto;
        $monto   = (float) $producto->precio * $item->cantidad;

        // ── 1. Reembolso al comprador (Módulo 4.5 Monedero) ─────────────────
        $this->modules->reembolsarSaldo(
            usuarioId: $item->usuario_id,
            monto:     $monto,
            concepto:  "Reembolso por regalo {$motivo}: {$producto->nombre}"
        );

        // ── 2. Recuperar stock (Módulo 4.2 Inventario) ──────────────────────
        $this->modules->liberarStock(
            productoId: $item->producto_id,
            cantidad:   $item->cantidad
        );

        // ── 3. Actualizar estado del ítem ────────────────────────────────────
        $item->update([
            'estado_regalo' => $motivo,   // 'rechazado' | 'expirado'
            'regalo_hash'   => null,       // invalida el enlace de regalo
        ]);
    }
}
