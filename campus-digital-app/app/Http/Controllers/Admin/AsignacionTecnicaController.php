<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionTecnica;
use App\Models\Usuario;
use App\Models\Ticket;
use Illuminate\Http\Request;

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

        // TODO: return Inertia::render('Admin/AsignacionesTecnicas/Index', [...])
        return response()->json($asignaciones);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_ticket'          => ['required', 'integer', 'exists:tickets,id_ticket'],
            'id_usuario_tecnico' => ['required', 'integer', 'exists:usuario,id'],
        ]);

        $asignacion = AsignacionTecnica::create($validated);

        // TODO: return redirect()->route('admin.asignaciones-tecnicas.index')->with('success', ...)
        return response()->json($asignacion->load(['ticket', 'tecnico']), 201);
    }

    public function show(AsignacionTecnica $asignacionTecnica)
    {
        // TODO: return Inertia::render('Admin/AsignacionesTecnicas/Show', [...])
        return response()->json($asignacionTecnica->load(['ticket', 'tecnico']));
    }

    public function update(Request $request, AsignacionTecnica $asignacionTecnica)
    {
        $validated = $request->validate([
            'id_ticket'          => ['required', 'integer', 'exists:tickets,id_ticket'],
            'id_usuario_tecnico' => ['required', 'integer', 'exists:usuario,id'],
        ]);

        $asignacionTecnica->update($validated);

        // TODO: return redirect()->route('admin.asignaciones-tecnicas.index')->with('success', ...)
        return response()->json($asignacionTecnica->fresh()->load(['ticket', 'tecnico']));
    }

    public function destroy(AsignacionTecnica $asignacionTecnica)
    {
        $asignacionTecnica->delete();

        // TODO: return redirect()->route('admin.asignaciones-tecnicas.index')->with('success', ...)
        return response()->json(['message' => 'Asignación técnica eliminada correctamente.']);
    }
}
