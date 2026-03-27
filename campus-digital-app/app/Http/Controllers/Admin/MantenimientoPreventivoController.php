<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MantenimientoPreventivo;
use App\Models\EquipoActivo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MantenimientoPreventivoController extends Controller
{
    public function index(Request $request)
    {
        $query = MantenimientoPreventivo::with(['equipo.categoria', 'equipo.ubicacion']);

        if ($request->filled('equipo')) {
            $query->where('id_equipo', $request->equipo);
        }

        if ($request->filled('desde')) {
            $query->whereDate('proxima_fecha_programada', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('proxima_fecha_programada', '<=', $request->hasta);
        }

        $preventivos = $query->orderBy('proxima_fecha_programada', 'asc')->paginate(10)->withQueryString();

        $equipos = EquipoActivo::orderBy('nombre_equipo')->get(['id_equipo', 'nombre_equipo']);

        return Inertia::render('Admin/MantenimientosPreventivos/Index', [
            'preventivos' => $preventivos,
            'equipos'     => $equipos,
            'filters'     => $request->only(['equipo', 'desde', 'hasta']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_equipo'               => ['required', 'integer', 'exists:equipos_activos,id_equipo'],
            'proxima_fecha_programada' => ['required', 'date'],
        ]);

        MantenimientoPreventivo::create($validated);

        return redirect()->route('admin.mantenimientos-preventivos.index')->with('success', 'Mantenimiento preventivo creado correctamente.');
    }

    public function show(MantenimientoPreventivo $mantenimientoPreventivo)
    {
        $mantenimientoPreventivo->load(['equipo.categoria', 'equipo.ubicacion']);

        $equipos = EquipoActivo::orderBy('nombre_equipo')->get(['id_equipo', 'nombre_equipo']);

        return Inertia::render('Admin/MantenimientosPreventivos/Show', [
            'preventivo' => $mantenimientoPreventivo,
            'equipos'    => $equipos,
        ]);
    }

    public function update(Request $request, MantenimientoPreventivo $mantenimientoPreventivo)
    {
        $validated = $request->validate([
            'id_equipo'               => ['required', 'integer', 'exists:equipos_activos,id_equipo'],
            'proxima_fecha_programada' => ['required', 'date'],
        ]);

        $mantenimientoPreventivo->update($validated);

        return redirect()->route('admin.mantenimientos-preventivos.show', $mantenimientoPreventivo->id_preventivo)->with('success', 'Mantenimiento preventivo actualizado correctamente.');
    }

    public function destroy(MantenimientoPreventivo $mantenimientoPreventivo)
    {
        $mantenimientoPreventivo->delete();

        return redirect()->route('admin.mantenimientos-preventivos.index')->with('success', 'Mantenimiento preventivo eliminado correctamente.');
    }
}
