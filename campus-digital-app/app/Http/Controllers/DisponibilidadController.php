<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Disponibilidad;
use App\Models\Catalogo;
use Inertia\Inertia;

class DisponibilidadController extends Controller
{
    public function index()
    {
        $disponibilidad = Disponibilidad::with('catalogo')->get();

        return Inertia::render('Disponibilidad/Index', [
            'disponibilidad' => $disponibilidad
        ]);
    }

    public function create()
    {
        return Inertia::render('Disponibilidad/Create', [
            'catalogo' => Catalogo::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_catalogo' => 'required',
            'dia_semana' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required'
        ]);

        Disponibilidad::create([
            'id_catalogo' => $request->id_catalogo,
            'dia_semana' => $request->dia_semana,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'disponible' => true
        ]);

        return redirect('/disponibilidad');
    }

    public function edit($id)
    {
        return Inertia::render('Disponibilidad/Edit', [
            'item' => Disponibilidad::findOrFail($id),
            'catalogo' => Catalogo::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = Disponibilidad::findOrFail($id);

        $item->update($request->all());

        return redirect('/disponibilidad');
    }

    public function destroy($id)
    {
        Disponibilidad::findOrFail($id)->delete();

        return redirect('/disponibilidad');
    }
}