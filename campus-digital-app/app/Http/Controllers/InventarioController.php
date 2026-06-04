<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Inventario;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventarioController extends Controller
{
    public function index()
    {
        $inventario = Inventario::query()
            ->with('catalogo')
            ->orderByDesc('id_inventario')
            ->get();

        return Inertia::render('Inventario/Index', [
            'inventario' => $inventario,
        ]);
    }

    public function createAddStock()
    {
        return Inertia::render('Inventario/AddStock', [
            'catalogo' => Catalogo::select('id_catalogo', 'nombre')->orderBy('nombre')->get(),
            'inventarioActual' => Inventario::select('id_catalogo', 'stock_actual', 'stock_minimo')->get(),
        ]);
    }

    public function storeAddStock(Request $request)
    {
        $validated = $request->validate([
            'id_catalogo' => 'required|integer|exists:catalogo,id_catalogo',
            'cantidad' => 'required|integer|min:1',
            'stock_minimo' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $inventario = Inventario::firstOrCreate(
                ['id_catalogo' => $validated['id_catalogo']],
                [
                    'stock_actual' => 0,
                    'stock_minimo' => 0,
                    'fecha_actualizacion' => now(),
                ]
            );

            $dataToUpdate = [
                'stock_actual' => $inventario->stock_actual + $validated['cantidad'],
                'fecha_actualizacion' => now(),
            ];

            if (array_key_exists('stock_minimo', $validated) && $validated['stock_minimo'] !== null) {
                $dataToUpdate['stock_minimo'] = $validated['stock_minimo'];
            }

            $inventario->update($dataToUpdate);

            Movimiento::create([
                'id_catalogo' => $validated['id_catalogo'],
                'cantidad' => $validated['cantidad'],
                'fecha' => now(),
            ]);
        });

        return redirect()->route('inventario.index');
    }
}
