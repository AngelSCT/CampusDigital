<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        $query = Insumo::query();

        if ($request->filled('search')) {
            $query->where('nombre_insumo', 'ilike', '%' . $request->search . '%');
        }

        $insumos = $query->orderBy('nombre_insumo')->paginate(10)->withQueryString();

        // TODO: return Inertia::render('Admin/Insumos/Index', [...])
        return response()->json($insumos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_insumo' => ['required', 'string', 'max:255'],
            'stock_actual'  => ['required', 'integer', 'min:0'],
        ]);

        $insumo = Insumo::create($validated);

        // TODO: return redirect()->route('admin.insumos.index')->with('success', ...)
        return response()->json($insumo, 201);
    }

    public function show(Insumo $insumo)
    {
        // TODO: return Inertia::render('Admin/Insumos/Show', [...])
        return response()->json($insumo);
    }

    public function update(Request $request, Insumo $insumo)
    {
        $validated = $request->validate([
            'nombre_insumo' => ['required', 'string', 'max:255'],
            'stock_actual'  => ['required', 'integer', 'min:0'],
        ]);

        $insumo->update($validated);

        // TODO: return redirect()->route('admin.insumos.index')->with('success', ...)
        return response()->json($insumo->fresh());
    }

    public function destroy(Insumo $insumo)
    {
        $insumo->delete();

        // TODO: return redirect()->route('admin.insumos.index')->with('success', ...)
        return response()->json(['message' => 'Insumo eliminado correctamente.']);
    }
}
