<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Ubicacion::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('edificio',         'ilike', "%{$request->search}%")
                  ->orWhere('aula_departamento', 'ilike', "%{$request->search}%");
            });
        }

        $ubicaciones = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // TODO: return Inertia::render('Admin/Ubicaciones/Index', [...])
        return response()->json($ubicaciones);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'edificio'          => ['required', 'string', 'max:120'],
            'aula_departamento' => ['required', 'string', 'max:120'],
        ]);

        $ubicacion = Ubicacion::create($validated);

        // TODO: return redirect()->route('admin.ubicaciones.index')->with('success', ...)
        return response()->json($ubicacion, 201);
    }

    public function show(Ubicacion $ubicacion)
    {
        // TODO: return Inertia::render('Admin/Ubicaciones/Show', ['ubicacion' => $ubicacion])
        return response()->json($ubicacion);
    }

    public function update(Request $request, Ubicacion $ubicacion)
    {
        $validated = $request->validate([
            'edificio'          => ['required', 'string', 'max:120'],
            'aula_departamento' => ['required', 'string', 'max:120'],
        ]);

        $ubicacion->update($validated);

        // TODO: return redirect()->route('admin.ubicaciones.index')->with('success', ...)
        return response()->json($ubicacion->fresh());
    }

    public function destroy(Ubicacion $ubicacion)
    {
        $ubicacion->delete();

        // TODO: return redirect()->route('admin.ubicaciones.index')->with('success', ...)
        return response()->json(['message' => 'Ubicación eliminada correctamente.']);
    }
}
