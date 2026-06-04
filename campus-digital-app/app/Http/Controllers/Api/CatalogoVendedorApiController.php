<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CatalogoVendedor;

class CatalogoVendedorApiController extends Controller
{
    public function index()
    {
        return response()->json(CatalogoVendedor::all());
    }

    public function show($id)
    {
        return response()->json(CatalogoVendedor::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!empty($data['id'])) {
            $item = CatalogoVendedor::findOrFail($data['id']);
            $item->update($data);
            return response()->json($item);
        }
        $item = CatalogoVendedor::create($data);
        return response()->json($item, 201);
    }

    public function destroy($id)
    {
        $item = CatalogoVendedor::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }
}
