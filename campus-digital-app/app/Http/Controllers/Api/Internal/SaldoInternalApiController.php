<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use App\Models\SaldoReserva;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Endpoints internos de Saldo para el módulo 4.4 (Carrito/Checkout).
 *
 * Protegidos por InternalTokenMiddleware (header X-Internal-Token).
 * NO exponer públicamente ni documentar en Scribe.
 *
 * Endpoints:
 *   POST /api/internal/saldo/reservar               → reservar()
 *   POST /api/internal/saldo/confirmar/{reserva_id} → confirmar()
 *   POST /api/internal/saldo/liberar/{reserva_id}   → liberar()
 *   POST /api/internal/saldo/cargo-forzoso          → cargoForzoso()
 */
class SaldoInternalApiController extends Controller
{
    // ── POST /api/internal/saldo/reservar ────────────────────────────────────
    //
    // Solicita una reserva (hold) de fondos.
    // Mueve el monto de saldo_disponible → saldo_retenido.
    //
    // Body: { usuario_ref, monto, carrito_uuid, modulo_slug, concepto }
    //
    // Respuestas:
    //   200 → { ok, reserva_id, saldo_disponible_post_reserva, expira_at }
    //   402 → fondos insuficientes
    //   422 → validación fallida
    //   503 → usuario o monedero no encontrado
    // ─────────────────────────────────────────────────────────────────────────
    public function reservar(Request $request)
    {
        $request->validate([
            'usuario_ref'  => 'required|string',
            'monto'        => 'required|numeric|min:0.01',
            'carrito_uuid' => 'required|string|max:36',
            'modulo_slug'  => 'required|string|max:50',
            'concepto'     => 'required|string|max:255',
        ]);

        // usuario_ref es el usuario_id del módulo 4.2 (enviado como string)
        $usuario = Usuario::find((int) $request->usuario_ref);

        if (!$usuario) {
            return response()->json([
                'ok'      => false,
                'error'   => 'USUARIO_NO_ENCONTRADO',
                'mensaje' => 'El usuario referenciado no existe en este módulo.',
            ], 503);
        }

        try {
            return DB::transaction(function () use ($request, $usuario) {

                $monedero = SaldoMonedero::where('usuario_id', $usuario->id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                if (!$monedero) {
                    return response()->json([
                        'ok'      => false,
                        'error'   => 'MONEDERO_NO_ENCONTRADO',
                        'mensaje' => 'El usuario no tiene monedero registrado.',
                    ], 503);
                }

                $monto = (float) $request->monto;

                // Verificar saldo suficiente
                if ($monedero->saldo_disponible < $monto) {
                    return response()->json([
                        'ok'                => false,
                        'error'             => 'SALDO_INSUFICIENTE',
                        'saldo_disponible'  => (float) $monedero->saldo_disponible,
                        'monto_solicitado'  => $monto,
                    ], 402);
                }

                // Mover fondos: disponible → retenido
                $monedero->saldo_disponible -= $monto;
                $monedero->saldo_retenido   += $monto;
                $monedero->save();

                // Crear registro de reserva
                $reserva = SaldoReserva::create([
                    'usuario_id'        => $usuario->id,
                    'saldo_monedero_id' => $monedero->id,
                    'monto'             => $monto,
                    'carrito_uuid'      => $request->carrito_uuid,
                    'modulo_slug'       => $request->modulo_slug,
                    'concepto'          => $request->concepto,
                    'estado'            => SaldoReserva::ESTADO_PENDIENTE,
                ]);

                return response()->json([
                    'ok'                           => true,
                    'reserva_id'                   => $reserva->uuid,
                    'saldo_disponible_post_reserva' => (float) $monedero->saldo_disponible,
                    'expira_at'                    => $reserva->expira_at->toISOString(),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'error'   => 'ERROR_INTERNO',
                'mensaje' => 'Error al procesar la reserva.',
            ], 503);
        }
    }

    // ── POST /api/internal/saldo/confirmar/{reserva_id} ──────────────────────
    //
    // Confirma (ejecuta) una reserva existente.
    // Descuenta de saldo_retenido y registra el movimiento de cargo.
    //
    // Body: { carrito_uuid }
    //
    // Respuestas:
    //   200 → { ok, movimiento_id }
    //   409 → RESERVA_EXPIRADA | RESERVA_YA_CONSUMIDA | CARRITO_UUID_MISMATCH
    //   404 → reserva no encontrada
    // ─────────────────────────────────────────────────────────────────────────
    public function confirmar(Request $request, string $reservaId)
    {
        $request->validate([
            'carrito_uuid' => 'required|string|max:36',
        ]);

        try {
            return DB::transaction(function () use ($request, $reservaId) {

                $reserva = SaldoReserva::where('uuid', $reservaId)
                    ->lockForUpdate()
                    ->first();

                if (!$reserva) {
                    return response()->json([
                        'ok'    => false,
                        'error' => 'RESERVA_NO_ENCONTRADA',
                    ], 404);
                }

                // Validar que el carrito_uuid coincide
                if ($reserva->carrito_uuid !== $request->carrito_uuid) {
                    return response()->json([
                        'ok'    => false,
                        'error' => 'CARRITO_UUID_MISMATCH',
                    ], 409);
                }

                // Verificar estado
                if ($reserva->estado !== SaldoReserva::ESTADO_PENDIENTE) {
                    $codigoError = match ($reserva->estado) {
                        SaldoReserva::ESTADO_CONFIRMADA => 'RESERVA_YA_CONSUMIDA',
                        SaldoReserva::ESTADO_LIBERADA   => 'RESERVA_LIBERADA',
                        default                         => 'RESERVA_ESTADO_INVALIDO',
                    };
                    return response()->json(['ok' => false, 'error' => $codigoError], 409);
                }

                // Verificar vigencia
                if ($reserva->expira_at->isPast()) {
                    $reserva->update(['estado' => SaldoReserva::ESTADO_EXPIRADA]);
                    return response()->json(['ok' => false, 'error' => 'RESERVA_EXPIRADA'], 409);
                }

                $monedero = SaldoMonedero::where('id', $reserva->saldo_monedero_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Ejecutar el cargo: liberar del retenido (ya no se devuelve al disponible)
                $monedero->saldo_retenido -= (float) $reserva->monto;
                $monedero->save();

                // Registrar movimiento de cargo
                $movimiento = SaldoMovimiento::create([
                    'usuario_id'        => $reserva->usuario_id,
                    'saldo_monedero_id' => $monedero->id,
                    'tipo'              => SaldoMovimiento::TIPO_CARGO,
                    'monto'             => $reserva->monto,
                    'saldo_anterior'    => $monedero->saldo_disponible + $reserva->monto,
                    'saldo_nuevo'       => $monedero->saldo_disponible,
                    'modulo'            => $reserva->modulo_slug ?? 'carrito',
                    'concepto'          => $reserva->concepto,
                    'referencia_tabla'  => 'saldo_reserva',
                    'referencia_id'     => $reserva->id,
                    'meta_json'         => ['carrito_uuid' => $reserva->carrito_uuid],
                ]);

                $reserva->update([
                    'estado'              => SaldoReserva::ESTADO_CONFIRMADA,
                    'saldo_movimiento_id' => $movimiento->id,
                ]);

                return response()->json([
                    'ok'            => true,
                    'movimiento_id' => $movimiento->id,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'error'   => 'ERROR_INTERNO',
                'mensaje' => 'Error al confirmar la reserva.',
            ], 503);
        }
    }

    // ── POST /api/internal/saldo/liberar/{reserva_id} ────────────────────────
    //
    // Libera (cancela) una reserva devolviendo los fondos retenidos.
    //
    // Respuestas:
    //   200 → { ok }
    //   409 → reserva ya consumida o liberada
    //   404 → reserva no encontrada
    // ─────────────────────────────────────────────────────────────────────────
    public function liberar(Request $request, string $reservaId)
    {
        try {
            return DB::transaction(function () use ($reservaId) {

                $reserva = SaldoReserva::where('uuid', $reservaId)
                    ->lockForUpdate()
                    ->first();

                if (!$reserva) {
                    return response()->json([
                        'ok'    => false,
                        'error' => 'RESERVA_NO_ENCONTRADA',
                    ], 404);
                }

                // Idempotente: si ya está liberada, retornar 200
                if ($reserva->estado === SaldoReserva::ESTADO_LIBERADA) {
                    return response()->json(['ok' => true, 'nota' => 'ya_liberada']);
                }

                if ($reserva->estado !== SaldoReserva::ESTADO_PENDIENTE) {
                    return response()->json([
                        'ok'    => false,
                        'error' => 'RESERVA_YA_CONSUMIDA',
                        'estado'=> $reserva->estado,
                    ], 409);
                }

                $monedero = SaldoMonedero::where('id', $reserva->saldo_monedero_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Devolver fondos: retenido → disponible
                $monedero->saldo_retenido   -= (float) $reserva->monto;
                $monedero->saldo_disponible += (float) $reserva->monto;
                $monedero->save();

                $reserva->update(['estado' => SaldoReserva::ESTADO_LIBERADA]);

                return response()->json(['ok' => true]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'error'   => 'ERROR_INTERNO',
                'mensaje' => 'Error al liberar la reserva.',
            ], 503);
        }
    }

    // ── POST /api/internal/saldo/cargo-forzoso ────────────────────────────────
    //
    // Registra un cargo forzoso cuando el saldo no pudo verificarse en el
    // momento del checkout (pago diferido / conciliación pendiente del módulo 4.4).
    //
    // PUEDE dejar saldo_disponible en negativo (deuda diferida).
    //
    // Body: { usuario_ref, monto, carrito_uuid, concepto, carrito_estado? }
    //
    // Respuestas:
    //   200 → { ok } (CargoForzosoCobrado)
    //   402/422 → rechazo explícito (CargoForzosoRechazado)
    //   409/5xx → resultado ambiguo (CargoForzosoDesconocido — no revertir)
    // ─────────────────────────────────────────────────────────────────────────
    public function cargoForzoso(Request $request)
    {
        $request->validate([
            'usuario_ref'    => 'required|string',
            'monto'          => 'required|numeric|min:0.01',
            'carrito_uuid'   => 'required|string|max:36',
            'concepto'       => 'required|string|max:255',
            'carrito_estado' => 'nullable|string|max:50',
        ]);

        $usuario = Usuario::find((int) $request->usuario_ref);

        if (!$usuario) {
            return response()->json([
                'ok'      => false,
                'error'   => 'USUARIO_NO_ENCONTRADO',
                'mensaje' => 'El usuario referenciado no existe.',
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $usuario) {

                $monedero = SaldoMonedero::where('usuario_id', $usuario->id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                if (!$monedero) {
                    // Si no tiene monedero, crearlo con saldo 0
                    $monedero = SaldoMonedero::create([
                        'usuario_id'       => $usuario->id,
                        'saldo_disponible' => 0.00,
                        'saldo_retenido'   => 0.00,
                    ]);
                }

                $monto         = (float) $request->monto;
                $saldoAnterior = (float) $monedero->saldo_disponible;

                // Cargo forzoso — puede dejar saldo negativo (deuda diferida)
                $monedero->saldo_disponible -= $monto;
                $monedero->save();

                SaldoMovimiento::create([
                    'usuario_id'        => $usuario->id,
                    'saldo_monedero_id' => $monedero->id,
                    'tipo'              => SaldoMovimiento::TIPO_CARGO,
                    'monto'             => $monto,
                    'saldo_anterior'    => $saldoAnterior,
                    'saldo_nuevo'       => (float) $monedero->saldo_disponible,
                    'modulo'            => 'carrito',
                    'concepto'          => $request->concepto,
                    'referencia_tabla'  => 'carrito',
                    'referencia_id'     => null,
                    'meta_json'         => [
                        'carrito_uuid'   => $request->carrito_uuid,
                        'carrito_estado' => $request->carrito_estado,
                        'cargo_forzoso'  => true,
                    ],
                ]);

                return response()->json(['ok' => true]);
            });
        } catch (\Exception $e) {
            // 409 / 5xx → CargoForzosoDesconocido (el módulo 4.4 NO debe revertir)
            return response()->json([
                'ok'      => false,
                'error'   => 'ERROR_INTERNO',
                'mensaje' => 'Error al registrar el cargo forzoso.',
            ], 409);
        }
    }
}
