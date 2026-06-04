<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Catalogo;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MovimientoController extends Controller
{
    public function index()
    {
        $movimientos = Movimiento::with('catalogo')->get();

        return Inertia::render('Movimientos/Index', [
            'movimientos' => $movimientos
        ]);
    }

    public function create()
    {
        $catalogo = Catalogo::all();

        return Inertia::render('Movimientos/Create', [
            'catalogo' => $catalogo
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_catalogo' => 'required|integer|exists:catalogo,id_catalogo',
            'cantidad' => 'required|integer|not_in:0',
        ]);

        DB::transaction(function () use ($validated) {
            Movimiento::create([
                'id_catalogo' => $validated['id_catalogo'],
                'cantidad' => $validated['cantidad'],
                'fecha' => now(),
            ]);

            $inventario = Inventario::firstOrCreate(
                ['id_catalogo' => $validated['id_catalogo']],
                [
                    'stock_actual' => 0,
                    'stock_minimo' => 0,
                    'fecha_actualizacion' => now(),
                ]
            );

            $inventario->update([
                'stock_actual' => $inventario->stock_actual + $validated['cantidad'],
                'fecha_actualizacion' => now(),
            ]);
        });

        return redirect('/movimientos');
    }
}