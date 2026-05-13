<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaApiController extends Controller
{
    public function index()
    {
        return response()->json(Categoria::all());
    }

    public function show($id)
    {
        return response()->json(Categoria::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!empty($data['id'])) {
            $item = Categoria::findOrFail($data['id']);
            $item->update($data);
            return response()->json($item);
        }
        $item = Categoria::create($data);
        return response()->json($item, 201);
    }

    public function destroy($id)
    {
        $item = Categoria::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }
}
