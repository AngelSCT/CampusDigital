<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Precio;

class PrecioApiController extends Controller
{
    public function index()
    {
        return response()->json(Precio::all());
    }

    public function show($id)
    {
        return response()->json(Precio::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!empty($data['id'])) {
            $item = Precio::findOrFail($data['id']);
            $item->update($data);
            return response()->json($item);
        }
        $item = Precio::create($data);
        return response()->json($item, 201);
    }

    public function destroy($id)
    {
        $item = Precio::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }
}
