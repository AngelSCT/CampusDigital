<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Http\Request;

class InventarioApiController extends Controller
{
    // GET /api/inventario - Obtener ID y cantidad de todos los productos
    public function index()
    {
        $inventario = Inventario::select('id_inventario', 'stock_actual', 'id_catalogo')->get();
        
        return response()->json([
            'success' => true,
            'data' => $inventario,
            'count' => $inventario->count()
        ]);
    }

    // GET /api/inventario/{id} - Obtener inventario de un producto específico
    public function show($id)
    {
        $inventario = Inventario::select('id_inventario', 'stock_actual', 'id_catalogo')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $inventario
        ]);
    }

    // GET /api/inventario/stock/bajo - Obtener productos con stock bajo
    public function stockBajo()
    {
        $inventario = Inventario::whereRaw('stock_actual <= stock_minimo')
            ->select('id_inventario', 'stock_actual', 'stock_minimo', 'id_catalogo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $inventario,
            'count' => $inventario->count()
        ]);
    }
}
