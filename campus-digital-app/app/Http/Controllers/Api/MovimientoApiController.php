<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movimiento;

class MovimientoApiController extends Controller
{
    public function index()
    {
        return response()->json(Movimiento::all());
    }

    public function show($id)
    {
        return response()->json(Movimiento::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!empty($data['id'])) {
            $item = Movimiento::findOrFail($data['id']);
            $item->update($data);
            return response()->json($item);
        }
        $item = Movimiento::create($data);
        return response()->json($item, 201);
    }

    public function destroy($id)
    {
        $item = Movimiento::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }
}
