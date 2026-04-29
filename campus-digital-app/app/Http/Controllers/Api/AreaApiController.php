<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaApiController extends Controller
{
    // GET /api/areas
    public function index()
    {
        return response()->json(
            Area::whereNull('deleted_at')->get()
        );
    }

    // GET /api/areas/{id}
    public function show($id)
    {
        $area = Area::whereNull('deleted_at')->findOrFail($id);

        return response()->json($area);
    }

    // POST /api/areas
    public function store(Request $request)
    {
        $request->validate([
            'name_area' => 'required|string|max:120|unique:area,name_area',
        ]);

        $area = Area::create($request->only(['name_area']));

        return response()->json($area, 201);
    }

    // PUT /api/areas/{id}
    public function update(Request $request, $id)
    {
        $area = Area::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'name_area' => "required|string|max:120|unique:area,name_area,{$id},id_area",
        ]);

        $area->update($request->only(['name_area']));

        return response()->json($area->fresh());
    }

    // DELETE /api/areas/{id}
    public function destroy($id)
    {
        $area = Area::whereNull('deleted_at')->findOrFail($id);
        $area->delete();

        return response()->json(['message' => 'Área eliminada correctamente.']);
    }
}
