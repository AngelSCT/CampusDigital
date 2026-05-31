<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class UbicacionApiController extends Controller
{
    // GET /api/ubicaciones
    public function index()
    {
        return response()->json(
            Ubicacion::whereNull('deleted_at')->get()
        );
    }

    // GET /api/ubicaciones/{id}
    public function show($id)
    {
        $ubicacion = Ubicacion::whereNull('deleted_at')->findOrFail($id);

        return response()->json($ubicacion);
    }

    // POST /api/ubicaciones
    public function store(Request $request)
    {
        $request->validate([
            'edificio'          => 'required|string|max:120',
            'aula_departamento' => 'required|string|max:120',
        ]);

        $ubicacion = Ubicacion::create($request->only(['edificio', 'aula_departamento']));

        return response()->json($ubicacion, 201);
    }

    // PUT /api/ubicaciones/{id}
    public function update(Request $request, $id)
    {
        $ubicacion = Ubicacion::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'edificio'          => 'required|string|max:120',
            'aula_departamento' => 'required|string|max:120',
        ]);

        $ubicacion->update($request->only(['edificio', 'aula_departamento']));

        return response()->json($ubicacion->fresh());
    }

    // DELETE /api/ubicaciones/{id}
    public function destroy($id)
    {
        $ubicacion = Ubicacion::whereNull('deleted_at')->findOrFail($id);
        $ubicacion->delete();

        return response()->json(['message' => 'Ubicación eliminada correctamente.']);
    }
}
