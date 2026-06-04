<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AreaController extends Controller
{
    public function index()
    {
        return Inertia::render('Areas/Index', [
            'areas' => Area::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Areas/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required'
        ]);

        Area::create($request->all());

        return redirect('/areas');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
        ]);

        $area = Area::create([
            'nombre' => $validated['nombre'],
        ]);

        return response()->json([
            'message' => 'Area creada correctamente.',
            'area' => $area,
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Areas/Edit', [
            'area' => Area::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);
        $area->update($request->all());

        return redirect('/areas');
    }

    public function destroy($id)
    {
        Area::findOrFail($id)->delete();

        return redirect('/areas');
    }
}