<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionTecnica;
use App\Models\Usuario;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AsignacionTecnicaController extends Controller
{
    public function index(Request $request)
    {
        $query = AsignacionTecnica::with(['ticket', 'tecnico']);

        if ($request->filled('ticket')) {
            $query->where('id_ticket', $request->ticket);
        }

        if ($request->filled('tecnico')) {
            $query->where('id_usuario_tecnico', $request->tecnico);
        }

        $asignaciones = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $tickets  = Ticket::orderBy('id_ticket', 'desc')->get(['id_ticket', 'estado', 'prioridad']);
        $tecnicos = Usuario::orderBy('nombre')->get(['id', 'nombre', 'apellido', 'email']);

        return Inertia::render('Admin/AsignacionesTecnicas/Index', [
            'asignaciones' => $asignaciones,
            'tickets'      => $tickets,
            'tecnicos'     => $tecnicos,
            'filters'      => $request->only(['ticket', 'tecnico']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_ticket'          => ['required', 'integer', 'exists:tickets,id_ticket'],
            'id_usuario_tecnico' => ['required', 'integer', 'exists:usuario,id'],
        ]);

        $asignacion = AsignacionTecnica::create($validated);

        return redirect()->route('admin.asignaciones-tecnicas.index')
            ->with('success', 'Asignación técnica creada correctamente.');
    }

    public function show(AsignacionTecnica $asignacionTecnica)
    {
        $asignacionTecnica->load(['ticket', 'tecnico']);

        $tickets  = Ticket::orderBy('id_ticket', 'desc')->get(['id_ticket', 'estado', 'prioridad']);
        $tecnicos = Usuario::orderBy('nombre')->get(['id', 'nombre', 'apellido', 'email']);

        return Inertia::render('Admin/AsignacionesTecnicas/Show', [
            'asignacion' => $asignacionTecnica,
            'tickets'    => $tickets,
            'tecnicos'   => $tecnicos,
        ]);
    }

    public function update(Request $request, AsignacionTecnica $asignacionTecnica)
    {
        $validated = $request->validate([
            'id_ticket'          => ['required', 'integer', 'exists:tickets,id_ticket'],
            'id_usuario_tecnico' => ['required', 'integer', 'exists:usuario,id'],
        ]);

        $asignacionTecnica->update($validated);

        return redirect()->route('admin.asignaciones-tecnicas.show', $asignacionTecnica->id_asignacion)
            ->with('success', 'Asignación técnica actualizada correctamente.');
    }

    public function destroy(AsignacionTecnica $asignacionTecnica)
    {
        $asignacionTecnica->delete();

        return redirect()->route('admin.asignaciones-tecnicas.index')
            ->with('success', 'Asignación técnica eliminada correctamente.');
    }
}
