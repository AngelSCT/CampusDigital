<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::query();

        if ($request->filled('search')) {
            $query->where('name_area', 'ilike', "%{$request->search}%");
        }

        $areas = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Areas/Index', [
            'areas'   => $areas,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_area' => ['required', 'string', 'max:120', 'unique:area,name_area'],
        ]);

        Area::create($validated);

        return redirect()->route('admin.areas.index')->with('success', 'Área creada correctamente.');
    }

    public function show(Area $area)
    {
        return Inertia::render('Admin/Areas/Show', [
            'area' => $area,
        ]);
    }

    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'name_area' => ['required', 'string', 'max:120', 'unique:area,name_area,' . $area->id_area . ',id_area'],
        ]);

        $area->update($validated);

        return redirect()->route('admin.areas.index')->with('success', 'Área actualizada correctamente.');
    }

    public function destroy(Area $area)
    {
        $area->delete();

        return redirect()->route('admin.areas.index')->with('success', 'Área eliminada correctamente.');
    }
}
