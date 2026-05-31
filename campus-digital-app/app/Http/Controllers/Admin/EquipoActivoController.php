<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipoActivo;
use App\Models\CategoriaTicket;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        $categorias = CategoriaTicket::orderBy('nombre_categoria')->get(['id_categoria', 'nombre_categoria']);
        $ubicaciones = Ubicacion::orderBy('edificio')->get(['id_ubicacion', 'edificio', 'aula_departamento']);

        return Inertia::render('Admin/EquiposActivos/Index', [
            'equipos'     => $equipos,
            'categorias'  => $categorias,
            'ubicaciones' => $ubicaciones,
            'filters'     => $request->only(['search', 'categoria', 'ubicacion', 'estado']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_categoria'  => ['required', 'integer', 'exists:categorias_ticket,id_categoria'],
            'id_ubicacion'  => ['required', 'integer', 'exists:ubicaciones,id_ubicacion'],
            'nombre_equipo' => ['required', 'string', 'max:120'],
            'estado_actual' => ['required', 'string', 'max:50'],
        ]);

        EquipoActivo::create($validated);

        return redirect()->route('admin.equipos-activos.index')->with('success', 'Equipo activo creado correctamente.');
    }

    public function show(EquipoActivo $equipoActivo)
    {
        $equipoActivo->load(['categoria', 'ubicacion']);

        $categorias  = CategoriaTicket::orderBy('nombre_categoria')->get(['id_categoria', 'nombre_categoria']);
        $ubicaciones = Ubicacion::orderBy('edificio')->get(['id_ubicacion', 'edificio', 'aula_departamento']);

        return Inertia::render('Admin/EquiposActivos/Show', [
            'equipo'      => $equipoActivo,
            'categorias'  => $categorias,
            'ubicaciones' => $ubicaciones,
        ]);
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

        return redirect()->route('admin.equipos-activos.show', $equipoActivo->id_equipo)->with('success', 'Equipo activo actualizado correctamente.');
    }

    public function destroy(EquipoActivo $equipoActivo)
    {
        $equipoActivo->delete();

        return redirect()->route('admin.equipos-activos.index')->with('success', 'Equipo activo eliminado correctamente.');
    }
}
