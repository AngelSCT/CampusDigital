<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UbicacionResource;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class UbicacionApiController extends Controller
{
    // GET /api/ubicaciones
    public function index()
    {
        return UbicacionResource::collection(Ubicacion::whereNull('deleted_at')->get());
    }

    // GET /api/ubicaciones/{id}
    public function show($id)
    {
        return new UbicacionResource(Ubicacion::whereNull('deleted_at')->findOrFail($id));
    }

    // POST /api/ubicaciones
    public function store(Request $request)
    {
        $request->validate([
            'edificio'          => 'required|string|max:120',
            'aula_departamento' => 'required|string|max:120',
        ]);

        $ubicacion = Ubicacion::create($request->only(['edificio', 'aula_departamento']));

        return (new UbicacionResource($ubicacion))->response()->setStatusCode(201);
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

        return new UbicacionResource($ubicacion->fresh());
    }

    // DELETE /api/ubicaciones/{id}
    public function destroy($id)
    {
        $ubicacion = Ubicacion::whereNull('deleted_at')->findOrFail($id);
        $ubicacion->delete();

        return response()->json(['message' => 'Ubicación eliminada correctamente.']);
    }
}
