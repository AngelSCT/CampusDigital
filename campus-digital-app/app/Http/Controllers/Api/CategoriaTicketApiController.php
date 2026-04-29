<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaTicketResource;
use App\Models\CategoriaTicket;
use Illuminate\Http\Request;

class CategoriaTicketApiController extends Controller
{
    // GET /api/categorias-ticket
    public function index()
    {
        return CategoriaTicketResource::collection(
            CategoriaTicket::with('area')->whereNull('deleted_at')->get()
        );
    }

    // GET /api/categorias-ticket/{id}
    public function show($id)
    {
        return new CategoriaTicketResource(
            CategoriaTicket::with('area')->whereNull('deleted_at')->findOrFail($id)
        );
    }

    // POST /api/categorias-ticket
    public function store(Request $request)
    {
        $request->validate([
            'id_area'          => 'required|integer|exists:area,id_area',
            'nombre_categoria' => 'required|string|max:120',
            'tiempo_sla_horas' => 'required|integer|min:1',
        ]);

        $categoria = CategoriaTicket::create($request->only(['id_area', 'nombre_categoria', 'tiempo_sla_horas']));

        return (new CategoriaTicketResource($categoria->load('area')))->response()->setStatusCode(201);
    }

    // PUT /api/categorias-ticket/{id}
    public function update(Request $request, $id)
    {
        $categoria = CategoriaTicket::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'id_area'          => 'required|integer|exists:area,id_area',
            'nombre_categoria' => 'required|string|max:120',
            'tiempo_sla_horas' => 'required|integer|min:1',
        ]);

        $categoria->update($request->only(['id_area', 'nombre_categoria', 'tiempo_sla_horas']));

        return new CategoriaTicketResource($categoria->fresh()->load('area'));
    }

    // DELETE /api/categorias-ticket/{id}
    public function destroy($id)
    {
        $categoria = CategoriaTicket::whereNull('deleted_at')->findOrFail($id);
        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente.']);
    }
}
