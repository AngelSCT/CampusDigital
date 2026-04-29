<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\ConciliacionPendiente;
use App\Models\Cart\ItemCarrito;
use App\Modules\Cart\Exceptions\CartBusinessException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Exceptions\SaldoInsufficientFundsException;
use App\Modules\Cart\Exceptions\SaldoUnavailableException;

class CheckoutService
{
    public function __construct(
        private readonly CarritoService $carritoService,
        private readonly SaldoClient $saldoClient,
    ) {}

    /**
     * Confirma el carrito.
     *
     * Flujo según sección C5 del documento v1.1:
     *  - requiere_saldo=false → confirma directo.
     *  - requiere_saldo=true  → llama a SaldoClient:
     *      · Confirmed            → estado='confirmado'
     *      · InsufficientFunds    → lanza SaldoInsufficientFundsException (→ 402)
     *      · Unavailable + categoría sin permite_pago_diferido → SaldoUnavailableException (→ 503)
     *      · Unavailable + permite_pago_diferido + dentro de topes → 'confirmado_pendiente_conciliacion'
     *      · Unavailable + permite_pago_diferido + tope rebasado  → SaldoUnavailableException (→ 503)
     *
     * @throws CartStateException
     * @throws CartBusinessException
     * @throws SaldoInsufficientFundsException
     * @throws SaldoUnavailableException
     */
    public function confirmar(Carrito $carrito, array $data): Carrito
    {
        $this->carritoService->assertOperable($carrito);

        $items = ItemCarrito::where('carrito_id', $carrito->id)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->with('categoria.reglas')
            ->get();

        if ($items->isEmpty()) {
            throw new CartBusinessException('No se puede hacer checkout de un carrito vacío.');
        }

        if (!$carrito->requiere_saldo) {
            return $this->confirmarDirecto($carrito, $data);
        }

        // ── Integración con Saldo ──────────────────────────────────────────
        $total = (float) $carrito->total;

        $result = $this->saldoClient->reservar($carrito->usuario_ref, $total, $carrito->uuid);

        if ($result instanceof SaldoConfirmed) {
            return $this->confirmarDirecto($carrito, $data);
        }

        if ($result instanceof SaldoInsufficientFunds) {
            throw new SaldoInsufficientFundsException('Saldo insuficiente para completar el pago.');
        }

        // SaldoUnavailable — determinar si la categoría permite pago diferido.
        // Regla: TODAS las categorías de items activos deben tener permite_pago_diferido=true.
        $permiteDiferido = $items->every(function ($item) {
            $regla = $item->categoria->reglas->firstWhere('clave', 'permite_pago_diferido');
            return $regla ? $regla->valorCasteado() : false;
        });

        if (!$permiteDiferido) {
            throw new SaldoUnavailableException('Servicio de saldo no disponible, intenta más tarde.');
        }

        // Verificar topes de exposición (sección 5.3 del cambio C5).
        $topeUsuario  = (float) config('cart.saldo.tope_pendiente_por_usuario', 200);
        $topeGlobal   = (float) config('cart.saldo.tope_pendiente_global', 50000);

        $pendienteUsuario = (float) ConciliacionPendiente::where('usuario_ref', $carrito->usuario_ref)
            ->where('estado_conciliacion', ConciliacionPendiente::ESTADO_PENDIENTE)
            ->sum('monto');

        if ($pendienteUsuario + $total > $topeUsuario) {
            throw new SaldoUnavailableException('Se ha alcanzado el tope de crédito diferido para este usuario.');
        }

        $pendienteGlobal = (float) ConciliacionPendiente::where('estado_conciliacion', ConciliacionPendiente::ESTADO_PENDIENTE)
            ->sum('monto');

        if ($pendienteGlobal + $total > $topeGlobal) {
            throw new SaldoUnavailableException('El sistema no puede procesar más pagos diferidos en este momento.');
        }

        return $this->confirmarPendienteConciliacion($carrito, $data, $total);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function confirmarDirecto(Carrito $carrito, array $data): Carrito
    {
        $carrito->update([
            'estado'       => Carrito::ESTADO_CONFIRMADO,
            'confirmed_at' => now(),
            'metadata'     => array_merge(
                $carrito->metadata ?? [],
                ['metadata_checkout' => $data['metadata_checkout'] ?? null]
            ),
        ]);

        Bitacora::create([
            'accion'       => Bitacora::ACCION_CARRITO_CHECKOUT,
            'modulo_id'    => $carrito->modulo_id,
            'carrito_uuid' => $carrito->uuid,
            'payload'      => ['total' => $carrito->total, 'tipo' => 'directo'],
        ]);

        return $carrito->fresh();
    }

    private function confirmarPendienteConciliacion(Carrito $carrito, array $data, float $total): Carrito
    {
        $carrito->update([
            'estado'       => Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION,
            'confirmed_at' => now(),
            'metadata'     => array_merge(
                $carrito->metadata ?? [],
                ['metadata_checkout' => $data['metadata_checkout'] ?? null]
            ),
        ]);

        ConciliacionPendiente::create([
            'carrito_uuid'        => $carrito->uuid,
            'modulo_id'           => $carrito->modulo_id,
            'usuario_ref'         => $carrito->usuario_ref,
            'monto'               => $total,
            'intentos'            => 0,
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_PENDIENTE,
            'proximo_intento_at'  => now()->addMinutes(ConciliacionPendiente::BACKOFF_MINUTOS[0]),
        ]);

        Bitacora::create([
            'accion'       => Bitacora::ACCION_CARRITO_CHECKOUT,
            'modulo_id'    => $carrito->modulo_id,
            'carrito_uuid' => $carrito->uuid,
            'payload'      => ['total' => $total, 'tipo' => 'pendiente_conciliacion'],
        ]);

        return $carrito->fresh();
    }
}
