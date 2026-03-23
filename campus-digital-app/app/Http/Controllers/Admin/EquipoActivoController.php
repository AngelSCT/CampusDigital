<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipoActivo;
use App\Models\CategoriaTicket;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class EquipoActivoController extends Controller
{
    public function index(Request $request)
    {
        $query = EquipoActivo::with(['categoria', 'ubicacion']);

        if ($request->filled('search')) {
            $query->where('nombre_equipo', 'ilike', "%{$request->search}%");
        }

        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        if ($request->filled('ubicacion')) {
            $query->where('id_ubicacion', $request->ubicacion);
        }

        if ($request->filled('estado')) {
            $query->where('estado_actual', $request->estado);
        }

        $equipos = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // TODO: return Inertia::render('Admin/EquiposActivos/Index', [...])
        return response()->json($equipos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_categoria'  => ['required', 'integer', 'exists:categorias_ticket,id_categoria'],
            'id_ubicacion'  => ['required', 'integer', 'exists:ubicaciones,id_ubicacion'],
            'nombre_equipo' => ['required', 'string', 'max:120'],
            'estado_actual' => ['required', 'string', 'max:50'],
        ]);

        $equipo = EquipoActivo::create($validated);

        // TODO: return redirect()->route('admin.equipos-activos.index')->with('success', ...)
        return response()->json($equipo->load(['categoria', 'ubicacion']), 201);
    }

    public function show(EquipoActivo $equipoActivo)
    {
        // TODO: return Inertia::render('Admin/EquiposActivos/Show', [...])
        return response()->json($equipoActivo->load(['categoria', 'ubicacion']));
    }

    public function update(Request $request, EquipoActivo $equipoActivo)
    {
        $validated = $request->validate([
            'id_categoria'  => ['required', 'integer', 'exists:categorias_ticket,id_categoria'],
            'id_ubicacion'  => ['required', 'integer', 'exists:ubicaciones,id_ubicacion'],
            'nombre_equipo' => ['required', 'string', 'max:120'],
            'estado_actual' => ['required', 'string', 'max:50'],
        ]);

        $equipoActivo->update($validated);

        // TODO: return redirect()->route('admin.equipos-activos.index')->with('success', ...)
        return response()->json($equipoActivo->fresh()->load(['categoria', 'ubicacion']));
    }

    public function destroy(EquipoActivo $equipoActivo)
    {
        $equipoActivo->delete();

        // TODO: return redirect()->route('admin.equipos-activos.index')->with('success', ...)
        return response()->json(['message' => 'Equipo eliminado correctamente.']);
    }
}
