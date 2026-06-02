<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UbicacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Ubicacion::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('edificio',           'ilike', "%{$request->search}%")
                  ->orWhere('aula_departamento', 'ilike', "%{$request->search}%");
            });
        }

        $ubicaciones = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Ubicaciones/Index', [
            'ubicaciones' => $ubicaciones,
            'filters'     => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'edificio'          => ['required', 'string', 'max:120'],
            'aula_departamento' => ['required', 'string', 'max:120'],
        ]);

        Ubicacion::create($validated);

        return redirect()->route('admin.ubicaciones.index')->with('success', 'Ubicación creada correctamente.');
    }

    public function show(Ubicacion $ubicacion)
    {
        return Inertia::render('Admin/Ubicaciones/Show', [
            'ubicacion' => $ubicacion,
        ]);
    }

    public function update(Request $request, Ubicacion $ubicacion)
    {
        $validated = $request->validate([
            'edificio'          => ['required', 'string', 'max:120'],
            'aula_departamento' => ['required', 'string', 'max:120'],
        ]);

        $ubicacion->update($validated);

        return redirect()->route('admin.ubicaciones.index')->with('success', 'Ubicación actualizada correctamente.');
    }

    public function destroy(Ubicacion $ubicacion)
    {
        $ubicacion->delete();

        return redirect()->route('admin.ubicaciones.index')->with('success', 'Ubicación eliminada correctamente.');
    }
}
