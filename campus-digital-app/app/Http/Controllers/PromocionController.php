<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\CatalogoVendedor;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PromocionController extends Controller
{
    public function index()
    {
        return Inertia::render('Promociones/Index', [
            'promociones' => Promocion::with(['catalogo', 'catalogoVendedor'])->orderByDesc('id_promocion')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Promociones/Create', [
            'catalogo' => Catalogo::select('id_catalogo', 'nombre')->orderBy('nombre')->get(),
            'catalogoVendedor' => CatalogoVendedor::select('id_cv', 'nombre_personalizado')->orderBy('nombre_personalizado')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'tipo' => 'nullable|string|max:50',
            'valor' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activa' => 'nullable|boolean',
            'catalogo_ids' => 'nullable|array',
            'catalogo_ids.*' => 'integer|exists:catalogo,id_catalogo',
            'catalogo_vendedor_ids' => 'nullable|array',
            'catalogo_vendedor_ids.*' => 'integer|exists:catalogo_vendedor,id_cv',
        ]);

        DB::transaction(function () use ($validated) {
            $promocion = Promocion::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo' => $validated['tipo'] ?? null,
                'valor' => $validated['valor'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'] ?? null,
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'activa' => $validated['activa'] ?? true,
            ]);

            $promocion->catalogo()->sync($validated['catalogo_ids'] ?? []);
            $promocion->catalogoVendedor()->sync($validated['catalogo_vendedor_ids'] ?? []);
        });

        return redirect()->route('promociones.index');
    }

    public function edit($id)
    {
        $promocion = Promocion::with(['catalogo', 'catalogoVendedor'])->findOrFail($id);

        return Inertia::render('Promociones/Edit', [
            'promocion' => $promocion,
            'catalogo' => Catalogo::select('id_catalogo', 'nombre')->orderBy('nombre')->get(),
            'catalogoVendedor' => CatalogoVendedor::select('id_cv', 'nombre_personalizado')->orderBy('nombre_personalizado')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'tipo' => 'nullable|string|max:50',
            'valor' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activa' => 'nullable|boolean',
            'catalogo_ids' => 'nullable|array',
            'catalogo_ids.*' => 'integer|exists:catalogo,id_catalogo',
            'catalogo_vendedor_ids' => 'nullable|array',
            'catalogo_vendedor_ids.*' => 'integer|exists:catalogo_vendedor,id_cv',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $promocion = Promocion::findOrFail($id);

            $promocion->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo' => $validated['tipo'] ?? null,
                'valor' => $validated['valor'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'] ?? null,
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'activa' => $validated['activa'] ?? false,
            ]);

            $promocion->catalogo()->sync($validated['catalogo_ids'] ?? []);
            $promocion->catalogoVendedor()->sync($validated['catalogo_vendedor_ids'] ?? []);
        });

        return redirect()->route('promociones.index');
    }

    public function destroy($id)
    {
        Promocion::findOrFail($id)->delete();

        return redirect()->route('promociones.index');
    }
}
