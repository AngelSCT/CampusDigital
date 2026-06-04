<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\ConciliacionPendiente;
use App\Models\Cart\ItemCarrito;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;
use App\Modules\Cart\Exceptions\CartBusinessException;
use App\Modules\Cart\Exceptions\CheckoutRevertidoException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Exceptions\SaldoInsufficientFundsException;
use App\Modules\Cart\Exceptions\SaldoUnavailableException;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private readonly CarritoService         $carritoService,
        private readonly SaldoClient            $saldoClient,
        private readonly PedidoCreatorInterface $pedidoCreator,
    ) {}

    /**
     * Confirma el carrito con protección contra doble checkout concurrente.
     *
     * Flujo de dos transacciones cortas (sin HTTP dentro de TX):
     *
     *  TX1 (corta): lockForUpdate → assertOperable → set 'procesando_checkout'
     *
     *  Fuera de TX:
     *    requiere_saldo = false → confirmarSinSaldo()
     *    requiere_saldo = true  → SaldoClient::reservar() [HTTP]
     *        SaldoConfirmed         → confirmarConReserva()
     *        SaldoInsufficientFunds → revertirAbierto + 402
     *        SaldoUnavailable       → evaluar diferido:
     *            permitido  → confirmarPendienteConciliacion()
     *            bloqueado  → revertirAbierto + 503
     *
     *  Manejo de excepciones inesperadas:
     *    Sin reservaId → revertirAbierto (sin side effect externo)
     *    Con reservaId → intentar liberar; OK → abierto, falla → revertido
     *    delegated = true → confirmarConReserva ya compensó; solo propagar
     *
     * @throws CartStateException
     * @throws CartBusinessException
     * @throws SaldoInsufficientFundsException
     * @throws SaldoUnavailableException
     * @throws CheckoutRevertidoException
     */
    public function confirmar(Carrito $carrito, array $data): Carrito
    {
        // ── TX1: lock + assertOperable + marcar procesando_checkout ──────────
        $carritoFresh = DB::transaction(function () use ($carrito) {
            $locked = Carrito::where('id', $carrito->id)->lockForUpdate()->firstOrFail();
            $this->carritoService->assertOperable($locked); // bloquea si ya está procesando u otro estado
            $locked->update(['estado' => Carrito::ESTADO_PROCESANDO_CHECKOUT]);
            return $locked->fresh();
        });

        // ── Todo lo que sigue está dentro del try/catch principal ─────────────
        // Si cualquier operación falla antes de obtener reservaId, el outer catch
        // llama revertirAbierto() porque $reservaId === null en ese punto.
        $reservaId = null;
        $delegated  = false; // true para métodos que manejan su propia compensación

        try {
            // Cargar ítems activos (dentro del try — error aquí → revertirAbierto)
            $items = ItemCarrito::where('carrito_id', $carritoFresh->id)
                ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
                ->with('categoria.reglas')
                ->get();

            if ($items->isEmpty()) {
                $this->revertirAbierto($carritoFresh);
                throw new CartBusinessException('No se puede hacer checkout de un carrito vacío.');
            }

            // Path sin Saldo — confirmarSinSaldo maneja su propia compensación
            if (!$carritoFresh->requiere_saldo) {
                $delegated = true;
                return $this->confirmarSinSaldo($carritoFresh, $data);
            }

            // Integración con Saldo [HTTP, FUERA de TX]
            $total      = (float) $carritoFresh->total;
            $moduloSlug = $carritoFresh->modulo?->slug ?? '';

            $result = $this->saldoClient->reservar(
                $carritoFresh->usuario_ref, $total, $carritoFresh->uuid, $moduloSlug, 'checkout',
            );

            if ($result instanceof SaldoConfirmed) {
                $reservaId = $result->reservaId;
                $delegated  = true;
                return $this->confirmarConReserva($carritoFresh, $data, $reservaId);
            }

            if ($result instanceof SaldoInsufficientFunds) {
                $this->revertirAbierto($carritoFresh);
                throw new SaldoInsufficientFundsException('Saldo insuficiente para completar el pago.');
            }

            // SaldoUnavailable — verificar pago diferido
            $permiteDiferido = $items->every(function ($item) {
                $regla = $item->categoria->reglas->firstWhere('clave', 'permite_pago_diferido');
                return $regla ? $regla->valorCasteado() : false;
            });

            if (!$permiteDiferido) {
                $this->revertirAbierto($carritoFresh);
                throw new SaldoUnavailableException('Servicio de saldo no disponible, intenta más tarde.');
            }

            $topeUsuario = (float) config('cart.saldo.tope_pendiente_por_usuario', 200);
            $topeGlobal  = (float) config('cart.saldo.tope_pendiente_global', 50000);

            $pendienteUsuario = (float) ConciliacionPendiente::where('usuario_ref', $carritoFresh->usuario_ref)
                ->where('estado_conciliacion', ConciliacionPendiente::ESTADO_PENDIENTE)
                ->sum('monto');

            if ($pendienteUsuario + $total > $topeUsuario) {
                $this->revertirAbierto($carritoFresh);
                throw new SaldoUnavailableException('Se ha alcanzado el tope de crédito diferido para este usuario.');
            }

            $pendienteGlobal = (float) ConciliacionPendiente::where('estado_conciliacion', ConciliacionPendiente::ESTADO_PENDIENTE)
                ->sum('monto');

            if ($pendienteGlobal + $total > $topeGlobal) {
                $this->revertirAbierto($carritoFresh);
                throw new SaldoUnavailableException('El sistema no puede procesar más pagos diferidos en este momento.');
            }

            // NO $delegated = true aquí: confirmarPendienteConciliacion no maneja
            // su propia compensación. Si lanza, el outer catch con $reservaId = null
            // llamará revertirAbierto() correctamente (no hay side effect de Saldo).
            return $this->confirmarPendienteConciliacion($carritoFresh, $data, $total);

        } catch (SaldoInsufficientFundsException | SaldoUnavailableException | CartBusinessException $e) {
            throw $e; // Manejados arriba con revertirAbierto; solo propagar
        } catch (CheckoutRevertidoException $e) {
            throw $e; // confirmarConReserva ya compensó; solo propagar
        } catch (\Throwable $e) {
            if (!$delegated) {
                // Excepción inesperada antes de delegar — compensar según side effect
                $liberacionOk = false;
                if ($reservaId !== null) {
                    try { $liberacionOk = $this->saldoClient->liberar($reservaId); } catch (\Throwable) {}
                }

                if ($reservaId === null || $liberacionOk) {
                    $this->revertirAbierto($carritoFresh);
                } else {
                    // Reserva activa no liberada → NO reabrir automáticamente
                    try {
                        $carritoFresh->update(['estado' => Carrito::ESTADO_REVERTIDO]);
                        Bitacora::create([
                            'accion'       => Bitacora::ACCION_CARRITO_REVERTIDO,
                            'modulo_id'    => $carritoFresh->modulo_id,
                            'carrito_uuid' => $carritoFresh->uuid,
                            'payload'      => ['motivo' => 'liberar_reserva_fallido', 'reserva_id' => $reservaId],
                        ]);
                    } catch (\Throwable) {}
                }
            }
            // Si $delegated = true, confirmarConReserva ya manejó la compensación; solo propagar
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Path sin Saldo. Carrito parte de 'procesando_checkout'.
     *
     * Si la TX falla → carrito = 'revertido' (no reintentar: estado del Pedido desconocido).
     */
    private function confirmarSinSaldo(Carrito $carrito, array $data): Carrito
    {
        try {
            DB::transaction(function () use ($carrito, $data) {
                $this->confirmarDirecto($carrito, $data);
                $this->pedidoCreator->crearDesdeCarrito($carrito);
                Bitacora::create([
                    'accion'       => Bitacora::ACCION_PEDIDO_CREADO,
                    'modulo_id'    => $carrito->modulo_id,
                    'carrito_uuid' => $carrito->uuid,
                    'payload'      => ['usuario_ref' => $carrito->usuario_ref, 'tipo' => 'directo'],
                ]);
            });
        } catch (\Throwable $e) {
            try {
                $carrito->update(['estado' => Carrito::ESTADO_REVERTIDO]);
                Bitacora::create([
                    'accion'       => Bitacora::ACCION_CARRITO_REVERTIDO,
                    'modulo_id'    => $carrito->modulo_id,
                    'carrito_uuid' => $carrito->uuid,
                    'payload'      => ['motivo' => 'pedido_creation_failed'],
                ]);
            } catch (\Throwable) {}
            throw $e;
        }

        return $carrito->fresh();
    }

    /**
     * Path con reserva de Saldo confirmada.
     *
     * Si los ítems tienen es_regalo=true → escrow: saldo queda retenido hasta
     * que el destinatario acepte o rechace vía CartController::aceptarRegaloBeta/rechazarRegaloBeta.
     *
     * TX2 (BD): assertCheckoutInProgress → confirmarDirecto → crearDesdeCarrito → Bitacora
     * HTTP post-commit: confirmar(reservaId) → si falla → compensarPostCommit
     */
    private function confirmarConReserva(Carrito $carrito, array $data, ?string $reservaId): Carrito
    {
        // ── Path regalo (escrow): no confirmar saldo, no crear Pedido todavía ─
        if ($this->carritoTieneRegalo($carrito)) {
            return $this->confirmarEscrowDirecto($carrito, $data, $reservaId);
        }

        $transaccionCompletada = false;

        try {
            DB::transaction(function () use ($carrito, $data) {
                $this->confirmarDirecto($carrito, $data);
                $this->pedidoCreator->crearDesdeCarrito($carrito);
                Bitacora::create([
                    'accion'       => Bitacora::ACCION_PEDIDO_CREADO,
                    'modulo_id'    => $carrito->modulo_id,
                    'carrito_uuid' => $carrito->uuid,
                    'payload'      => ['usuario_ref' => $carrito->usuario_ref, 'tipo' => 'con_saldo'],
                ]);
            });
            $transaccionCompletada = true;

            $ok = $this->saldoClient->confirmar($reservaId, $carrito->uuid);

            if (!$ok) {
                $this->compensarPostCommit($carrito, $reservaId, 'confirmar_409');
                throw new CheckoutRevertidoException('La confirmación del cobro fue rechazada; el checkout fue revertido.');
            }

            return $carrito->fresh();

        } catch (CheckoutRevertidoException $e) {
            throw $e;
        } catch (\Throwable $e) {
            if (!$transaccionCompletada) {
                try { $this->saldoClient->liberar($reservaId); } catch (\Throwable) {}
                try {
                    $carrito->update(['estado' => Carrito::ESTADO_REVERTIDO]);
                    Bitacora::create([
                        'accion'       => Bitacora::ACCION_CARRITO_REVERTIDO,
                        'modulo_id'    => $carrito->modulo_id,
                        'carrito_uuid' => $carrito->uuid,
                        'payload'      => ['reserva_id' => $reservaId, 'motivo' => 'pedido_creation_failed'],
                    ]);
                } catch (\Throwable) {}
                throw $e;
            } else {
                try { $this->compensarPostCommit($carrito, $reservaId, 'confirmar_excepcion'); } catch (\Throwable) {}
                throw new CheckoutRevertidoException('Error al confirmar el cobro; el checkout fue revertido.');
            }
        }
    }

    /**
     * Compensación post-commit: Pedido+Carrito en BD, cobro de Saldo falló.
     */
    private function compensarPostCommit(Carrito $carrito, ?string $reservaId, string $motivo): void
    {
        try {
            $this->pedidoCreator->cancelarPedidoDeCarrito((string) $carrito->uuid);
            Bitacora::create([
                'accion'       => Bitacora::ACCION_PEDIDO_CANCELADO,
                'modulo_id'    => $carrito->modulo_id,
                'carrito_uuid' => $carrito->uuid,
                'payload'      => ['motivo' => $motivo],
            ]);
        } catch (\Throwable) {}

        if ($reservaId) {
            try {
                $this->saldoClient->liberar($reservaId);
            } catch (\Throwable $e) {
                try {
                    Bitacora::create([
                        'accion'       => Bitacora::ACCION_SALDO_LIBERAR_FALLIDO,
                        'modulo_id'    => $carrito->modulo_id,
                        'carrito_uuid' => $carrito->uuid,
                        'payload'      => ['reserva_id' => $reservaId, 'motivo' => $motivo, 'error' => $e->getMessage()],
                    ]);
                } catch (\Throwable) {}
            }
        }

        try {
            $carrito->update(['estado' => Carrito::ESTADO_REVERTIDO]);
            Bitacora::create([
                'accion'       => Bitacora::ACCION_CARRITO_REVERTIDO,
                'modulo_id'    => $carrito->modulo_id,
                'carrito_uuid' => $carrito->uuid,
                'payload'      => ['reserva_id' => $reservaId, 'motivo' => $motivo],
            ]);
        } catch (\Throwable) {}
    }

    /**
     * Transición interna PROCESANDO_CHECKOUT → CONFIRMADO.
     *
     * Debe llamarse DENTRO de una DB::transaction activa.
     * Usa assertCheckoutInProgress (no assertOperable) porque el carrito
     * ya no está en 'abierto'.
     */
    private function confirmarDirecto(Carrito $carrito, array $data): Carrito
    {
        // Lock dentro de la TX activa + guard de estado
        $carritoFresh = Carrito::where('id', $carrito->id)->lockForUpdate()->firstOrFail();
        $this->carritoService->assertCheckoutInProgress($carritoFresh);

        $carritoFresh->update([
            'estado'       => Carrito::ESTADO_CONFIRMADO,
            'confirmed_at' => now(),
            'metadata'     => array_merge(
                $carritoFresh->metadata ?? [],
                ['metadata_checkout' => $data['metadata_checkout'] ?? null]
            ),
        ]);

        Bitacora::create([
            'accion'       => Bitacora::ACCION_CARRITO_CHECKOUT,
            'modulo_id'    => $carritoFresh->modulo_id,
            'carrito_uuid' => $carritoFresh->uuid,
            'payload'      => ['total' => $carritoFresh->total, 'tipo' => 'directo'],
        ]);

        return $carritoFresh->fresh();
    }

    /**
     * Path de pago diferido (SaldoUnavailable + permite_pago_diferido).
     *
     * El Pedido NO se crea aquí: el cobro aún no está confirmado.
     * ReintentaConciliacion crea el Pedido cuando confirme el cargo.
     */
    /**
     * Path de pago diferido. Atomicidad garantizada:
     *   estado=confirmado_pendiente_conciliacion + ConciliacionPendiente + Bitacora
     * en una sola TX. Si ConciliacionPendiente::create falla, el carrito
     * NO queda en el estado diferido sin el registro de cobro pendiente.
     */
    private function confirmarPendienteConciliacion(Carrito $carrito, array $data, float $total): Carrito
    {
        return DB::transaction(function () use ($carrito, $data, $total) {
            // Lock + assertCheckoutInProgress dentro de la TX
            $carritoFresh = Carrito::where('id', $carrito->id)->lockForUpdate()->firstOrFail();
            $this->carritoService->assertCheckoutInProgress($carritoFresh);

            $carritoFresh->update([
                'estado'       => Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION,
                'confirmed_at' => now(),
                'metadata'     => array_merge(
                    $carritoFresh->metadata ?? [],
                    ['metadata_checkout' => $data['metadata_checkout'] ?? null]
                ),
            ]);

            ConciliacionPendiente::create([
                'carrito_uuid'        => $carritoFresh->uuid,
                'modulo_id'           => $carritoFresh->modulo_id,
                'usuario_ref'         => $carritoFresh->usuario_ref,
                'monto'               => $total,
                'intentos'            => 0,
                'estado_conciliacion' => ConciliacionPendiente::ESTADO_PENDIENTE,
                'proximo_intento_at'  => now()->addMinutes(ConciliacionPendiente::BACKOFF_MINUTOS[0]),
            ]);

            Bitacora::create([
                'accion'       => Bitacora::ACCION_CARRITO_CHECKOUT,
                'modulo_id'    => $carritoFresh->modulo_id,
                'carrito_uuid' => $carritoFresh->uuid,
                'payload'      => ['total' => $total, 'tipo' => 'pendiente_conciliacion'],
            ]);

            return $carritoFresh->fresh();
        });
    }

    /**
     * Revierte el carrito a 'abierto'.
     * Llamar SOLO cuando no existe side effect externo no compensado.
     */
    private function revertirAbierto(Carrito $carrito): void
    {
        try {
            $carrito->update(['estado' => Carrito::ESTADO_ABIERTO]);
        } catch (\Throwable) {}
    }

    // ─── Soporte de regalo (escrow) ───────────────────────────────────────────

    /**
     * Retorna true si algún ítem activo del carrito tiene es_regalo=true en metadata.
     * Se carga desde la BD para no depender del estado del objeto en memoria.
     */
    private function carritoTieneRegalo(Carrito $carrito): bool
    {
        $items = ItemCarrito::where('carrito_id', $carrito->id)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->get(['metadata']);

        return $items->some(fn($i) => $i->metadata['es_regalo'] ?? false);
    }

    /**
     * Confirma el carrito en modo escrow: saldo queda RETENIDO (reservar ya lo hizo),
     * el Pedido se crea cuando el destinatario acepte.
     *
     * Guarda reserva_id + destinatario_id en metadata para que el controlador
     * de aceptar/rechazar pueda actuar sobre ellos.
     */
    private function confirmarEscrowDirecto(Carrito $carrito, array $data, ?string $reservaId): Carrito
    {
        // Obtener destinatario_id del primer ítem regalo
        $items = ItemCarrito::where('carrito_id', $carrito->id)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->get(['metadata']);

        $destinatarioId = null;
        $mensaje        = null;
        foreach ($items as $item) {
            if ($item->metadata['es_regalo'] ?? false) {
                $destinatarioId = $item->metadata['destinatario_id'] ?? null;
                $mensaje        = $item->metadata['mensaje'] ?? null;
                break;
            }
        }

        DB::transaction(function () use ($carrito, $data, $reservaId, $destinatarioId, $mensaje) {
            $carritoFresh = Carrito::where('id', $carrito->id)->lockForUpdate()->firstOrFail();
            $this->carritoService->assertCheckoutInProgress($carritoFresh);

            $carritoFresh->update([
                'estado'       => Carrito::ESTADO_CONFIRMADO_REGALO_ESCROW,
                'confirmed_at' => now(),
                'metadata'     => array_merge($carritoFresh->metadata ?? [], [
                    'metadata_checkout' => $data['metadata_checkout'] ?? null,
                    'regalo_escrow'     => [
                        'reserva_id'     => $reservaId,
                        'destinatario_id'=> (string) $destinatarioId,
                        'mensaje'        => $mensaje,
                    ],
                ]),
            ]);

            Bitacora::create([
                'accion'       => 'carrito.regalo_escrow',
                'modulo_id'    => $carritoFresh->modulo_id,
                'carrito_uuid' => $carritoFresh->uuid,
                'payload'      => [
                    'total'           => $carritoFresh->total,
                    'destinatario_id' => $destinatarioId,
                    'reserva_id'      => $reservaId,
                ],
            ]);
        });

        return $carrito->fresh();
    }
}
