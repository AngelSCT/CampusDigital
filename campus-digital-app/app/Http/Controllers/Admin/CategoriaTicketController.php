<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaTicket;
use App\Models\Area;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        return Inertia::render('Admin/CategoriasTicket/Index', [
            'categorias' => $categorias,
            'areas'      => Area::orderBy('name_area')->get(['id_area', 'name_area']),
            'filters'    => $request->only(['search', 'area']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_area'          => ['required', 'integer', 'exists:area,id_area'],
            'nombre_categoria' => ['required', 'string', 'max:120'],
            'tiempo_sla_horas' => ['required', 'integer', 'min:1'],
        ]);

        CategoriaTicket::create($validated);

        return redirect()->route('admin.categorias-ticket.index')->with('success', 'Categoría creada correctamente.');
    }

    public function show(CategoriaTicket $categoriaTicket)
    {
        return Inertia::render('Admin/CategoriasTicket/Show', [
            'categoria' => $categoriaTicket->load('area'),
            'areas'     => Area::orderBy('name_area')->get(['id_area', 'name_area']),
        ]);
    }

    public function update(Request $request, CategoriaTicket $categoriaTicket)
    {
        $validated = $request->validate([
            'id_area'          => ['required', 'integer', 'exists:area,id_area'],
            'nombre_categoria' => ['required', 'string', 'max:120'],
            'tiempo_sla_horas' => ['required', 'integer', 'min:1'],
        ]);

        $categoriaTicket->update($validated);

        return redirect()->route('admin.categorias-ticket.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(CategoriaTicket $categoriaTicket)
    {
        $categoriaTicket->delete();

        return redirect()->route('admin.categorias-ticket.index')->with('success', 'Categoría eliminada correctamente.');
    }
}
