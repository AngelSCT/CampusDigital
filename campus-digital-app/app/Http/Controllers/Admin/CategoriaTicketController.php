<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaTicket;
use App\Models\Area;
use Illuminate\Http\Request;

class CategoriaTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = CategoriaTicket::with('area');

        if ($request->filled('search')) {
            $query->where('nombre_categoria', 'ilike', "%{$request->search}%");
        }

        if ($request->filled('area')) {
            $query->where('id_area', $request->area);
        }

        $categorias = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // TODO: return Inertia::render('Admin/CategoriasTicket/Index', [...])
        return response()->json($categorias);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_area'          => ['required', 'integer', 'exists:area,id_area'],
            'nombre_categoria' => ['required', 'string', 'max:120'],
            'tiempo_sla_horas' => ['required', 'integer', 'min:1'],
        ]);

        $categoria = CategoriaTicket::create($validated);

        // TODO: return redirect()->route('admin.categorias-ticket.index')->with('success', ...)
        return response()->json($categoria->load('area'), 201);
    }

    public function show(CategoriaTicket $categoriaTicket)
    {
        // TODO: return Inertia::render('Admin/CategoriasTicket/Show', [...])
        return response()->json($categoriaTicket->load('area'));
    }

    public function update(Request $request, CategoriaTicket $categoriaTicket)
    {
        $validated = $request->validate([
            'id_area'          => ['required', 'integer', 'exists:area,id_area'],
            'nombre_categoria' => ['required', 'string', 'max:120'],
            'tiempo_sla_horas' => ['required', 'integer', 'min:1'],
        ]);

        $categoriaTicket->update($validated);

        // TODO: return redirect()->route('admin.categorias-ticket.index')->with('success', ...)
        return response()->json($categoriaTicket->fresh()->load('area'));
    }

    public function destroy(CategoriaTicket $categoriaTicket)
    {
        $categoriaTicket->delete();

        // TODO: return redirect()->route('admin.categorias-ticket.index')->with('success', ...)
        return response()->json(['message' => 'Categoría eliminada correctamente.']);
    }
}
