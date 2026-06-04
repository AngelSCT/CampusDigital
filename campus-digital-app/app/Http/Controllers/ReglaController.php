<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Regla;
use App\Models\Catalogo;
use Inertia\Inertia;

class ReglaController extends Controller
{
    public function index()
    {
        $reglas = Regla::with('catalogo')->get();

        return Inertia::render('Reglas/Index', [
            'reglas' => $reglas
        ]);
    }

    public function create()
    {
        return Inertia::render('Reglas/Create', [
            'catalogo' => Catalogo::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_catalogo' => 'required',
            'descripcion' => 'required'
        ]);

        Regla::create($request->all());

        return redirect('/reglas');
    }

    public function edit($id)
    {
        return Inertia::render('Reglas/Edit', [
            'regla' => Regla::findOrFail($id),
            'catalogo' => Catalogo::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        $regla = Regla::findOrFail($id);

        $regla->update($request->all());

        return redirect('/reglas');
    }

    public function destroy($id)
    {
        Regla::findOrFail($id)->delete();

        return redirect('/reglas');
    }
}