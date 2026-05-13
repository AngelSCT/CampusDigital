<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use Illuminate\Http\Request;

class SaldoMonederoApiController extends Controller
{
    // GET /api/saldo-monederos
    public function index(Request $request)
    {
        $query = SaldoMonedero::with('usuario')->whereNull('deleted_at');

        if ($request->filled('usuario_id')) $query->where('usuario_id', $request->usuario_id);

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    // GET /api/saldo-monederos/{id}
    public function show($id)
    {
        return response()->json(
            SaldoMonedero::with('usuario')->whereNull('deleted_at')->findOrFail($id)
        );
    }

    // GET /api/saldo-monederos/usuario/{usuario_id}  ← consulta directa por usuario
    public function porUsuario($usuario_id)
    {
        $monedero = SaldoMonedero::with('usuario')
            ->whereNull('deleted_at')
            ->where('usuario_id', $usuario_id)
            ->first();

        if (!$monedero) {
            return response()->json(['message' => 'El usuario no tiene monedero registrado.'], 404);
        }

        return response()->json($monedero);
    }

    // POST /api/saldo-monederos  ← crear monedero para un usuario nuevo
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuario,id|unique:saldo_monedero,usuario_id',
        ]);

        $monedero = SaldoMonedero::create([
            'usuario_id'       => $request->usuario_id,
            'saldo_disponible' => 0.00,
            'saldo_retenido'   => 0.00,
        ]);

        return response()->json($monedero->load('usuario'), 201);
    }
}