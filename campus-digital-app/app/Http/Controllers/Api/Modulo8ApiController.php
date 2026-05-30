<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recarga;
use App\Models\SaldoMonedero;
use App\Models\Usuario;
use App\Services\WalletService;
use Illuminate\Http\Request;

class Modulo8ApiController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    // ── STATUS SIMPLE ─────────────────────────────────────────────────────────
    // GET /api/modulo8/pagos/{id}/status
    // Para el módulo carrito — solo necesita saber si el pago fue aprobado
    public function statusPago($id)
    {
        $recarga = Recarga::find($id);

        if (!$recarga) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Pago no encontrado.',
            ], 404);
        }

        return response()->json([
            'ok'         => true,
            'pago_id'    => $recarga->id,
            'estado'     => $recarga->estado,
            'aprobado'   => $recarga->estado === 'exitosa',
            'referencia' => $recarga->referencia,
            'actualizado'=> $recarga->updated_at,
        ]);
    }

    // ── DETALLE COMPLETO ──────────────────────────────────────────────────────
    // GET /api/modulo8/pagos/{id}/detalle
    // Para módulos que necesitan cantidad, usuario, saldo, etc.
    public function detallePago($id)
    {
        $recarga = Recarga::with('usuario')->find($id);

        if (!$recarga) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Pago no encontrado.',
            ], 404);
        }

        $saldo = SaldoMonedero::where('usuario_id', $recarga->usuario_id)->first();

        return response()->json([
            'ok'   => true,
            'pago' => [
                'id'          => $recarga->id,
                'estado'      => $recarga->estado,
                'aprobado'    => $recarga->estado === 'exitosa',
                'monto'       => floatval($recarga->monto),
                'metodo_pago' => $recarga->metodo_pago,
                'referencia'  => $recarga->referencia,
                'razon_fallo' => $recarga->razon_fallo,
                'fecha_pago'  => $recarga->created_at,
                'actualizado' => $recarga->updated_at,
            ],
            'usuario' => [
                'id'     => $recarga->usuario->id,
                'nombre' => $recarga->usuario->nombre . ' ' . $recarga->usuario->apellido,
                'email'  => $recarga->usuario->email,
            ],
            'saldo_actual' => [
                'disponible' => floatval($saldo?->saldo_disponible ?? 0),
                'retenido'   => floatval($saldo?->saldo_retenido   ?? 0),
            ],
        ]);
    }

    // ── INICIAR RECARGA ───────────────────────────────────────────────────────
    // POST /api/modulo8/recargas/iniciar
    // Para que otros módulos inicien una recarga en nombre de un usuario
    // Body: { usuario_id, monto, metodo_pago }
    public function iniciarRecarga(Request $request)
    {
        $validated = $request->validate([
            'usuario_id'  => 'required|integer|exists:usuario,id',
            'monto'       => 'required|numeric|min:1|max:5000',
            'metodo_pago' => 'required|in:tarjeta,transferencia,efectivo',
        ]);

        $usuario = Usuario::find($validated['usuario_id']);

        try {
            $resultado = $this->walletService->recargar($usuario, [
                'monto'       => $validated['monto'],
                'metodo_pago' => $validated['metodo_pago'],
            ]);

            return response()->json([
                'ok'         => $resultado['exitosa'],
                'estado'     => $resultado['recarga']->estado,
                'pago_id'    => $resultado['recarga']->id,
                'referencia' => $resultado['recarga']->referencia,
                'monto'      => floatval($resultado['recarga']->monto),
                'mensaje'    => $resultado['mensaje'],
            ], $resultado['exitosa'] ? 200 : 422);

        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    // ── PAGOS POR USUARIO ─────────────────────────────────────────────────────
    // GET /api/modulo8/usuarios/{usuario_id}/pagos
    // Historial de pagos de un usuario con su saldo actual
    public function pagosPorUsuario($usuario_id)
    {
        $recargas = Recarga::where('usuario_id', $usuario_id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'estado'      => $r->estado,
                'aprobado'    => $r->estado === 'exitosa',
                'monto'       => floatval($r->monto),
                'metodo_pago' => $r->metodo_pago,
                'referencia'  => $r->referencia,
                'fecha'       => $r->created_at,
            ]);

        $saldo = SaldoMonedero::where('usuario_id', $usuario_id)->first();

        return response()->json([
            'ok'          => true,
            'usuario_id'  => (int) $usuario_id,
            'saldo_actual'=> floatval($saldo?->saldo_disponible ?? 0),
            'total_pagos' => $recargas->count(),
            'pagos'       => $recargas,
        ]);
    }
}
