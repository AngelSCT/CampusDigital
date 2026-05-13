<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Disponibilidad;

class DisponibilidadApiController extends Controller
{
    public function index()
    {
        return response()->json(Disponibilidad::all());
    }

    public function show($id)
    {
        return response()->json(Disponibilidad::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!empty($data['id'])) {
            $item = Disponibilidad::findOrFail($data['id']);
            $item->update($data);
            return response()->json($item);
        }
        $item = Disponibilidad::create($data);
        return response()->json($item, 201);
    }

    public function destroy($id)
    {
        $item = Disponibilidad::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }
}
