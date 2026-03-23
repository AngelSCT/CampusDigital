<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MantenimientoPreventivo;
use Illuminate\Http\Request;

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

        // TODO: return Inertia::render('Admin/MantenimientosPreventivos/Index', [...])
        return response()->json($preventivos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_equipo'               => ['required', 'integer', 'exists:equipos_activos,id_equipo'],
            'proxima_fecha_programada' => ['required', 'date'],
        ]);

        $preventivo = MantenimientoPreventivo::create($validated);

        // TODO: return redirect()->route('admin.mantenimientos-preventivos.index')->with('success', ...)
        return response()->json($preventivo->load(['equipo.categoria', 'equipo.ubicacion']), 201);
    }

    public function show(MantenimientoPreventivo $mantenimientoPreventivo)
    {
        // TODO: return Inertia::render('Admin/MantenimientosPreventivos/Show', [...])
        return response()->json($mantenimientoPreventivo->load(['equipo.categoria', 'equipo.ubicacion']));
    }

    public function update(Request $request, MantenimientoPreventivo $mantenimientoPreventivo)
    {
        $validated = $request->validate([
            'id_equipo'               => ['required', 'integer', 'exists:equipos_activos,id_equipo'],
            'proxima_fecha_programada' => ['required', 'date'],
        ]);

        $mantenimientoPreventivo->update($validated);

        // TODO: return redirect()->route('admin.mantenimientos-preventivos.index')->with('success', ...)
        return response()->json($mantenimientoPreventivo->fresh()->load(['equipo.categoria', 'equipo.ubicacion']));
    }

    public function destroy(MantenimientoPreventivo $mantenimientoPreventivo)
    {
        $mantenimientoPreventivo->delete();

        // TODO: return redirect()->route('admin.mantenimientos-preventivos.index')->with('success', ...)
        return response()->json(['message' => 'Mantenimiento preventivo eliminado correctamente.']);
    }
}
