<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Catalogo;

class CatalogoApiController extends Controller
{
    public function index()
    {
        return response()->json(Catalogo::all());
    }

    public function show($id)
    {
        return response()->json(Catalogo::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!empty($data['id'])) {
            $item = Catalogo::findOrFail($data['id']);
            $item->update($data);
            return response()->json($item);
        }
        $item = Catalogo::create($data);
        return response()->json($item, 201);
    }

    public function destroy($id)
    {
        $item = Catalogo::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }
}
