<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Regla;

class ReglaApiController extends Controller
{
    public function index()
    {
        return response()->json(Regla::all());
    }

    public function show($id)
    {
        return response()->json(Regla::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!empty($data['id'])) {
            $item = Regla::findOrFail($data['id']);
            $item->update($data);
            return response()->json($item);
        }
        $item = Regla::create($data);
        return response()->json($item, 201);
    }

    public function destroy($id)
    {
        $item = Regla::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }
}
