<?php

namespace App\Http\Controllers;

use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VendedorController extends Controller
{
    public function index()
    {
        return Inertia::render('Vendedores/Index', [
            'vendedores' => Vendedor::orderByDesc('id_vendedor')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Vendedores/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|max:255|unique:vendedores,email',
            'telefono' => 'nullable|string|max:20',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);

        Vendedor::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $validated['activo'] ?? true,
            'fecha_registro' => now(),
        ]);

        return redirect()->route('vendedores.index');
    }

    public function edit($id)
    {
        return Inertia::render('Vendedores/Edit', [
            'vendedor' => Vendedor::findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $vendedor = Vendedor::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('vendedores', 'email')->ignore($vendedor->id_vendedor, 'id_vendedor'),
            ],
            'telefono' => 'nullable|string|max:20',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);

        $vendedor->update([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $validated['activo'] ?? false,
        ]);

        return redirect()->route('vendedores.index');
    }

    public function destroy($id)
    {
        Vendedor::findOrFail($id)->delete();

        return redirect()->route('vendedores.index');
    }
}
