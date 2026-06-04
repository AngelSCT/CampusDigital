<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InsumoResource;
use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoApiController extends Controller
{
    // GET /api/insumos
    public function index()
    {
        return InsumoResource::collection(
            Insumo::whereNull('deleted_at')->orderBy('nombre_insumo')->get()
        );
    }

    // GET /api/insumos/{id}
    public function show($id)
    {
        return new InsumoResource(Insumo::whereNull('deleted_at')->findOrFail($id));
    }

    // POST /api/insumos
    public function store(Request $request)
    {
        $request->validate([
            'nombre_insumo' => 'required|string|max:255',
            'stock_actual'  => 'required|integer|min:0',
        ]);

        $insumo = Insumo::create($request->only(['nombre_insumo', 'stock_actual']));

        return (new InsumoResource($insumo))->response()->setStatusCode(201);
    }

    // PUT /api/insumos/{id}
    public function update(Request $request, $id)
    {
        $insumo = Insumo::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'nombre_insumo' => 'required|string|max:255',
            'stock_actual'  => 'required|integer|min:0',
        ]);

        $insumo->update($request->only(['nombre_insumo', 'stock_actual']));

        return new InsumoResource($insumo->fresh());
    }

    // DELETE /api/insumos/{id}
    public function destroy($id)
    {
        $insumo = Insumo::whereNull('deleted_at')->findOrFail($id);
        $insumo->delete();

        return response()->json(['message' => 'Insumo eliminado correctamente.']);
    }
}
