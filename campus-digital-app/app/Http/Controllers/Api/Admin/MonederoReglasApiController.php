<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaldoRegla;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonederoReglasApiController extends Controller
{
    /**
     * GET /api/admin/monedero/reglas
     * Listar todas las reglas
     */
    public function index(Request $request)
    {
        $query = SaldoRegla::query();

        if ($request->activas) {
            $query->activas();
        }

        if ($request->usuario_id) {
            $query->porUsuario($request->usuario_id);
        }

        if ($request->tipo_limite) {
            $query->porTipo($request->tipo_limite);
        }

        if ($request->modulo) {
            $query->porModulo($request->modulo);
        }

        $reglas = $query->paginate(15);

        return response()->json($reglas);
    }

    /**
     * GET /api/admin/monedero/reglas/{id}
     * Obtener una regla específica
     */
    public function show(SaldoRegla $regla)
    {
        return response()->json($regla->load('usuario'));
    }

    /**
     * POST /api/admin/monedero/reglas
     * Crear nueva regla
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|integer|exists:usuario,id',
            'tipo_limite' => 'required|in:diario,semanal,mensual',
            'monto_limite' => 'required|numeric|min:0.01',
            'modulo' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $regla = SaldoRegla::create($validated);

        return response()->json($regla, 201);
    }

    /**
     * PUT /api/admin/monedero/reglas/{id}
     * Actualizar regla
     */
    public function update(Request $request, SaldoRegla $regla)
    {
        $validated = $request->validate([
            'usuario_id' => 'integer|exists:usuario,id',
            'tipo_limite' => 'in:diario,semanal,mensual',
            'monto_limite' => 'numeric|min:0.01',
            'modulo' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $regla->update($validated);

        return response()->json($regla);
    }

    /**
     * DELETE /api/admin/monedero/reglas/{id}
     * Eliminar regla
     */
    public function destroy(SaldoRegla $regla)
    {
        $regla->delete();

        return response()->json(['mensaje' => 'Regla eliminada'], 204);
    }
}
