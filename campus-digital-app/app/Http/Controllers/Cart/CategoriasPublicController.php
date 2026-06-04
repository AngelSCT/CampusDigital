<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart\Categoria;

class CategoriasPublicController extends Controller
{
    public function index()
    {
        $data = Categoria::with('reglas')
            ->where('activa', true)
            ->orderBy('slug')
            ->get()
            ->map(fn(Categoria $cat) => [
                'slug'        => $cat->slug,
                'nombre'      => $cat->nombre,
                'descripcion' => $cat->descripcion,
                'reglas_base' => $cat->reglas
                    ->mapWithKeys(fn($r) => [$r->clave => $r->valorCasteado()])
                    ->toArray(),
            ]);

        return response()->json(['data' => $data]);
    }
}
