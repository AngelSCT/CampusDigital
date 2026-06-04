<?php

namespace App\Http\Controllers;

use App\Models\Catalogo\Catalogo;
use App\Models\Catalogo\Categoria;
use App\Services\Catalogo\CatalogoCartResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogoEstudianteController extends Controller
{
    public function __construct(private readonly CatalogoCartResolver $resolver) {}

    public function index(Request $request)
    {
        $query = Catalogo::with(['categoria', 'inventario', 'precios'])
            ->where('activo', true)
            ->orderBy('nombre');

        $categoriaActiva = $request->input('categoria_id') ? (int) $request->input('categoria_id') : null;

        if ($categoriaActiva) {
            $query->where('id_categoria', $categoriaActiva);
        }

        $productos = $query->get()->map(function (Catalogo $catalogo) {
            $estado        = $this->resolver->estadoCarrito($catalogo);
            $precioValor   = $catalogo->precioVigenteValor();

            return [
                'id_catalogo'          => $catalogo->id_catalogo,
                'nombre'               => $catalogo->nombre,
                'descripcion'          => $catalogo->descripcion,
                'tipo'                 => $catalogo->tipo,
                'precio_vigente'       => $precioValor !== null
                    ? number_format((float) $precioValor, 2, '.', '')
                    : null,
                'categoria_nombre'     => $catalogo->categoria?->nombre,
                'cart_disponible'      => $estado['cart_disponible'],
                'motivo_no_disponible' => $estado['motivo_no_disponible'],
            ];
        });

        $categorias = Categoria::orderBy('nombre')->get(['id_categoria', 'nombre']);

        return Inertia::render('Catalogo/Tienda', [
            'productos'        => $productos,
            'categorias'       => $categorias,
            'categoria_activa' => $categoriaActiva,
            'cart_web_url'     => rtrim(env('CART_WEB_URL', 'http://campusdigital.test'), '/') . '/carrito',
        ]);
    }
}
