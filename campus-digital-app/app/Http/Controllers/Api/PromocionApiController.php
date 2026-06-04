<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promocion;

class PromocionApiController extends Controller
{
    public function index()
    {
        return response()->json(Promocion::all());
    }

    public function show($id)
    {
        return response()->json(Promocion::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!empty($data['id'])) {
            $item = Promocion::findOrFail($data['id']);
            $item->update($data);
            return response()->json($item);
        }
        $item = Promocion::create($data);
        return response()->json($item, 201);
    }

    public function destroy($id)
    {
        $item = Promocion::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }
}
