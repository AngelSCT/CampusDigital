<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::query();

        if ($request->filled('search')) {
            $query->where('name_area', 'ilike', "%{$request->search}%");
        }

        $areas = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // TODO: return Inertia::render('Admin/Areas/Index', [...])
        return response()->json($areas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_area' => ['required', 'string', 'max:120', 'unique:area,name_area'],
        ]);

        $area = Area::create($validated);

        // TODO: return redirect()->route('admin.areas.index')->with('success', ...)
        return response()->json($area, 201);
    }

    public function show(Area $area)
    {
        // TODO: return Inertia::render('Admin/Areas/Show', ['area' => $area])
        return response()->json($area);
    }

    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'name_area' => ['required', 'string', 'max:120', 'unique:area,name_area,' . $area->id_area . ',id_area'],
        ]);

        $area->update($validated);

        // TODO: return redirect()->route('admin.areas.index')->with('success', ...)
        return response()->json($area);
    }

    public function destroy(Area $area)
    {
        $area->delete();

        // TODO: return redirect()->route('admin.areas.index')->with('success', ...)
        return response()->json(['message' => 'Área eliminada correctamente.']);
    }
}
