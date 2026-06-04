<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaldoMovimientoResource;
use App\Models\SaldoMovimiento;
use App\Models\SaldoMonedero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaldoMovimientoApiController extends Controller
{
    // GET /api/saldo-movimientos
    public function index(Request $request)
    {
        $query = SaldoMovimiento::with(['usuario', 'monedero', 'operador', 'tarjetaLectura'])
            ->whereNull('deleted_at');

        if ($request->filled('usuario_id'))       $query->where('usuario_id', $request->usuario_id);
        if ($request->filled('tipo'))             $query->where('tipo', $request->tipo);
        if ($request->filled('modulo'))           $query->where('modulo', $request->modulo);
        if ($request->filled('saldo_monedero_id'))$query->where('saldo_monedero_id', $request->saldo_monedero_id);
        if ($request->filled('desde'))            $query->where('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))            $query->where('created_at', '<=', $request->hasta);

        return SaldoMovimientoResource::collection($query->orderByDesc('created_at')->paginate($request->get('per_page', 20)));
    }

    // GET /api/saldo-movimientos/{id}
    public function show($id)
    {
        return new SaldoMovimientoResource(
            SaldoMovimiento::with(['usuario', 'monedero', 'operador', 'tarjetaLectura'])
                ->whereNull('deleted_at')
                ->findOrFail($id)
        );
    }

    // POST /api/saldo-movimientos  ← registrar un abono o cargo
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id'       => 'required|exists:usuario,id',
            'tipo'             => 'required|in:abono,cargo',
            'monto'            => 'required|numeric|min:0.01',
            'modulo'           => 'required|string|max:50',
            'concepto'         => 'required|string|max:255',
            'operador_usuario_id' => 'nullable|exists:usuario,id',
            'tarjeta_lectura_id'  => 'nullable|exists:tarjeta_lectura,id',
        ]);

        return DB::transaction(function () use ($request) {

            $monedero = SaldoMonedero::where('usuario_id', $request->usuario_id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->firstOrFail();

            $saldoAnterior = $monedero->saldo_disponible;

            if ($request->tipo === 'cargo') {
                if ($monedero->saldo_disponible < $request->monto) {
                    return response()->json(['message' => 'Saldo insuficiente.'], 422);
                }
                $monedero->saldo_disponible -= $request->monto;
            } else {
                $monedero->saldo_disponible += $request->monto;
            }

            $monedero->save();

            $movimiento = SaldoMovimiento::create([
                'usuario_id'          => $request->usuario_id,
                'saldo_monedero_id'   => $monedero->id,
                'tipo'                => $request->tipo,
                'monto'               => $request->monto,
                'saldo_anterior'      => $saldoAnterior,
                'saldo_nuevo'         => $monedero->saldo_disponible,
                'modulo'              => $request->modulo,
                'concepto'            => $request->concepto,
                'referencia_tabla'    => $request->referencia_tabla,
                'referencia_id'       => $request->referencia_id,
                'operador_usuario_id' => $request->operador_usuario_id,
                'tarjeta_lectura_id'  => $request->tarjeta_lectura_id,
                'meta_json'           => $request->meta_json ?? [],
            ]);

            return (new SaldoMovimientoResource($movimiento->load('usuario')))->additional([
                'saldo_disponible' => $monedero->saldo_disponible,
            ])->response()->setStatusCode(201);
        });
    }
}