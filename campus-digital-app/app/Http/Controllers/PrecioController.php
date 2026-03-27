<?php

namespace App\Http\Controllers;

use App\Models\Precio;
use App\Models\Catalogo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrecioController extends Controller
{
    public function index()
    {
        $precios = Precio::with('catalogo')->get();

        return Inertia::render('Precios/Index', [
            'precios' => $precios
        ]);
    }

    public function create()
    {
        $catalogo = Catalogo::all();

        return Inertia::render('Precios/Create', [
            'catalogo' => $catalogo
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'id_catalogo' => 'required',
        'precio' => 'required|numeric',
        'fecha_inicio' => 'required|date'
    ]);

    $precioActual = Precio::where('id_catalogo', $request->id_catalogo)
        ->whereNull('fecha_fin')
        ->first();

    if ($precioActual) {
        $precioActual->update([
            'fecha_fin' => now()
        ]);
    }

    Precio::create($request->all());

    return redirect('/precios');
}
}