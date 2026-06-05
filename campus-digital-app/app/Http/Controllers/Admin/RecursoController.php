<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recurso;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecursoController extends Controller
{
    public function index(Request $request)
    {
        $query = Recurso::with('ubicacion');

        if ($request->filled('search')) {
            $query->where('nombre', 'ilike', "%{$request->search}%");
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $recursos = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Reservas/RecursosIndex', [
            'recursos' => $recursos,
            'filters'  => $request->only(['search', 'tipo', 'estado']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'         => ['required', 'string', 'max:150'],
            'descripcion'    => ['nullable', 'string'],
            'tipo'           => ['required', 'string', 'in:sala,laboratorio,equipo'],
            'capacidad'      => ['required', 'integer', 'min:1'],
            'id_ubicacion'   => ['nullable', 'integer', 'exists:ubicaciones,id_ubicacion'],
            'estado'         => ['required', 'string', 'in:disponible,mantenimiento,inactivo'],
            'costo_por_hora' => ['nullable', 'numeric', 'min:0'],
            'imagen_url'     => ['nullable', 'string', 'max:500'],
            'horarios'       => ['nullable'],
        ]);

        if (isset($validated['horarios']) && is_string($validated['horarios'])) {
            $decoded = json_decode($validated['horarios'], true);
            $validated['horarios'] = $decoded ?: null;
        }

        Recurso::create($validated);

        return redirect()->route('admin.reservas.recursos.index')->with('success', 'Recurso creado correctamente.');
    }

    public function show(Recurso $recurso)
    {
        $recurso->load('ubicacion');
        $recurso->loadCount(['reservas', 'turnos']);

        $reservasRecientes = $recurso->reservas()
            ->with('usuario')
            ->orderBy('fecha_inicio', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Reservas/RecursosShow', [
            'recurso'           => $recurso,
            'reservasRecientes' => $reservasRecientes,
        ]);
    }

    public function update(Request $request, Recurso $recurso)
    {
        $validated = $request->validate([
            'nombre'         => ['required', 'string', 'max:150'],
            'descripcion'    => ['nullable', 'string'],
            'tipo'           => ['required', 'string', 'in:sala,laboratorio,equipo'],
            'capacidad'      => ['required', 'integer', 'min:1'],
            'id_ubicacion'   => ['nullable', 'integer', 'exists:ubicaciones,id_ubicacion'],
            'estado'         => ['required', 'string', 'in:disponible,mantenimiento,inactivo'],
            'costo_por_hora' => ['nullable', 'numeric', 'min:0'],
            'imagen_url'     => ['nullable', 'string', 'max:500'],
            'horarios'       => ['nullable'],
        ]);

        if (isset($validated['horarios']) && is_string($validated['horarios'])) {
            $decoded = json_decode($validated['horarios'], true);
            $validated['horarios'] = $decoded ?: null;
        }

        $recurso->update($validated);

        return redirect()->route('admin.reservas.recursos.show', $recurso->id_recurso)->with('success', 'Recurso actualizado.');
    }

    public function destroy(Recurso $recurso)
    {
        $recurso->delete();
        return redirect()->route('admin.reservas.recursos.index')->with('success', 'Recurso eliminado.');
    }
}
