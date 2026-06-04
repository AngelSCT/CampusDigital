<?php

namespace App\Services\Pedidos;

use App\Models\Pedido;
use App\Models\PedidoHistorial;
use App\Models\ActividadBitacora;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Service: cancelar un Pedido creado desde checkout del M4.4.
 *
 * Casos de uso:
 *  - El cobro de saldo se rechaza después del commit (conciliación fallida)
 *  - El M4.4 revierte el carrito y necesita cancelar el pedido asociado
 *
 * Busca el pedido por carrito_uuid y lo cancela con motivo + historial.
 * Si el pedido ya está cancelado o entregado, no hace nada (idempotente).
 */
class CancelarPedidoDesdeCheckout
{
    /**
     * Cancelar un pedido asociado a un carrito UUID.
     *
     * @param string $carritoUuid  UUID del carrito del M4.4
     * @param string $motivo       Razón de la cancelación
     * @return Pedido|null         El pedido cancelado, o null si no existe
     */
    public function ejecutar(string $carritoUuid, string $motivo = 'Cancelado desde checkout'): ?Pedido
    {
        $pedido = Pedido::where('carrito_uuid', $carritoUuid)->first();

        if (!$pedido) {
            return null; // No existe pedido para ese carrito
        }

        // Si ya está en estado terminal, no hacer nada (idempotente)
        if (in_array($pedido->estado, ['cancelado', 'entregado'])) {
            return $pedido->load('items', 'historial');
        }

        return DB::transaction(function () use ($pedido, $motivo) {
            $estadoAnterior = $pedido->estado;

            // Cambiar estado a cancelado
            $pedido->update([
                'estado' => 'cancelado',
                'notas'  => $motivo,
            ]);

            // Registrar en historial
            PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => 'cancelado',
                'usuario_id'      => $pedido->usuario_id,
                'notas'           => $motivo,
            ]);

            // Reembolsar saldo al usuario
            try {
                $monedero = \App\Models\SaldoMonedero::obtenerOCrear($pedido->usuario_id);
                $monedero->abonar(
                    (float) $pedido->total,
                    "Reembolso por cancelación de pedido {$pedido->numero_folio}",
                    $pedido->modulo
                );
            } catch (\Throwable $e) {
                \Log::warning('No se pudo reembolsar saldo', [
                    'pedido_id' => $pedido->id,
                    'error'     => $e->getMessage(),
                ]);
            }

            // Registrar en bitácora
            try {
                ActividadBitacora::create([
                    'usuario_id'  => $pedido->usuario_id,
                    'accion'      => 'pedido.cancelado_desde_checkout',
                    'descripcion' => "Pedido {$pedido->numero_folio} cancelado: {$motivo}",
                    'ip'          => Request::ip() ?? '127.0.0.1',
                    'meta_json'   => [
                        'pedido_id'    => $pedido->id,
                        'carrito_uuid' => $pedido->carrito_uuid,
                    ],
                ]);
            } catch (\Throwable $e) {
                // No rompe la cancelación
            }

            return $pedido->load('items', 'historial');
        });
    }
}