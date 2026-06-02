<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecargaResource;
use App\Models\Recarga;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecargaApiController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // GET /api/recargas
    // Lista todas las recargas con filtros opcionales
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Recarga::with(['usuario', 'saldoMovimiento'])
            ->whereNull('deleted_at');

        if ($request->filled('usuario_id'))  $query->where('usuario_id', $request->usuario_id);
        if ($request->filled('estado'))      $query->where('estado', $request->estado);
        if ($request->filled('metodo_pago')) $query->where('metodo_pago', $request->metodo_pago);
        if ($request->filled('desde'))       $query->where('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))       $query->where('created_at', '<=', $request->hasta);

        return RecargaResource::collection(
            $query->orderByDesc('created_at')->paginate($request->get('per_page', 15))
        );
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/recargas/{id}
    // Detalle de una recarga específica
    // ─────────────────────────────────────────────────────────────
    public function show($id)
    {
        return new RecargaResource(
            Recarga::with(['usuario', 'saldoMovimiento'])
                ->whereNull('deleted_at')
                ->findOrFail($id)
        );
    }

    // ─────────────────────────────────────────────────────────────
    // POST /api/recargas
    //
    // US2: Validar el resultado del proceso de pago para garantizar
    //      que solo se acrediten recargas exitosas.
    //
    // Flujo:
    //   1. Validar los datos de entrada
    //   2. Registrar la recarga en estado "pendiente"
    //   3. Simular/verificar el resultado del pago externo
    //   4. Si el pago fue EXITOSO  → abonar saldo al monedero
    //   5. Si el pago fue FALLIDO  → marcar como fallido, NO abonar
    //   6. Devolver el resultado con el estado final
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id'      => 'required|exists:usuario,id',
            'monto'           => 'required|numeric|min:1',
            'metodo_pago'     => 'required|in:tarjeta,transferencia,efectivo',
            'referencia_pago' => 'nullable|string|max:100',
            // resultado_pago: lo envía la pasarela de pago o el sistema externo
            // valores aceptados: "exitoso" o "fallido"
            'resultado_pago'  => 'required|in:exitoso,fallido',
            'razon_fallo'     => 'nullable|string|max:500',
            'meta_json'       => 'nullable|array',
        ]);

        return DB::transaction(function () use ($request) {

            // ── PASO 1: Registrar la recarga en estado pendiente ──
            $recarga = Recarga::create([
                'usuario_id'      => $request->usuario_id,
                'monto'           => $request->monto,
                'metodo_pago'     => $request->metodo_pago,
                'estado'          => Recarga::ESTADO_PENDIENTE,
                'referencia_pago' => $request->referencia_pago,
                'meta_json'       => $request->meta_json ?? [],
            ]);

            // ── PASO 2: Validar el resultado del pago (US2) ───────
            //
            // En un sistema real, aquí se consultaría la pasarela de
            // pago (Stripe, Conekta, MercadoPago, etc.) para verificar
            // si el cobro fue confirmado.
            //
            // En este caso recibimos el resultado directamente desde
            // el campo "resultado_pago" del request.

            if ($request->resultado_pago === 'fallido') {

                // ── PAGO FALLIDO: NO se acredita saldo ─────────────
                $recarga->update([
                    'estado'      => Recarga::ESTADO_FALLIDO,
                    'razon_fallo' => $request->razon_fallo ?? 'El pago no fue autorizado.',
                ]);

                return response()->json([
                    'mensaje'    => 'El pago no fue autorizado. No se acreditó saldo.',
                    'recarga'    => new RecargaResource($recarga->fresh()->load('usuario')),
                    'acreditado' => false,
                ], 422);
            }

            // ── PAGO EXITOSO: Acreditar saldo al monedero ──────────

            // Obtener o crear el monedero del usuario
            $monedero = SaldoMonedero::where('usuario_id', $request->usuario_id)
                ->whereNull('deleted_at')
                ->lockForUpdate()  // Bloqueo para evitar condiciones de carrera
                ->first();

            if (!$monedero) {
                // Si el usuario no tiene monedero, se crea automáticamente
                $monedero = SaldoMonedero::create([
                    'usuario_id'       => $request->usuario_id,
                    'saldo_disponible' => 0.00,
                    'saldo_retenido'   => 0.00,
                ]);
            }

            // Registrar el movimiento de abono en el historial
            $saldoAnterior            = $monedero->saldo_disponible;
            $monedero->saldo_disponible += $request->monto;
            $monedero->save();

            $movimiento = SaldoMovimiento::create([
                'usuario_id'        => $request->usuario_id,
                'saldo_monedero_id' => $monedero->id,
                'tipo'              => SaldoMovimiento::TIPO_ABONO,
                'monto'             => $request->monto,
                'saldo_anterior'    => $saldoAnterior,
                'saldo_nuevo'       => $monedero->saldo_disponible,
                'modulo'            => 'recarga',
                'concepto'          => 'Recarga de saldo vía ' . $request->metodo_pago,
                'referencia_tabla'  => 'recarga',
                'referencia_id'     => $recarga->id,
                'meta_json'         => $request->meta_json ?? [],
            ]);

            // Actualizar la recarga como exitosa y vincularla al movimiento
            $recarga->update([
                'estado'              => Recarga::ESTADO_EXITOSO,
                'saldo_movimiento_id' => $movimiento->id,
            ]);

            return response()->json([
                'mensaje'          => 'Recarga exitosa. Saldo acreditado correctamente.',
                'recarga'          => new RecargaResource($recarga->fresh()->load(['usuario', 'saldoMovimiento'])),
                'saldo_disponible' => $monedero->saldo_disponible,
                'acreditado'       => true,
            ], 201);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/recargas/usuario/{usuario_id}
    // Historial de recargas de un usuario específico
    // ─────────────────────────────────────────────────────────────
    public function porUsuario($usuario_id)
    {
        $recargas = Recarga::with(['saldoMovimiento'])
            ->whereNull('deleted_at')
            ->where('usuario_id', $usuario_id)
            ->orderByDesc('created_at')
            ->paginate(15);

        if ($recargas->isEmpty()) {
            return response()->json(['message' => 'El usuario no tiene recargas registradas.'], 404);
        }

        return RecargaResource::collection($recargas);
    }
}
