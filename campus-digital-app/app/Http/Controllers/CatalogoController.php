<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Catalogo;
use App\Models\Categoria;
use App\Models\Area;
use App\Models\Precio;
use App\Models\Disponibilidad;
use App\Models\Regla;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CatalogoController extends Controller
{
    public function index()
    {
        $precioActualSubquery = Precio::query()
            ->select('id_catalogo', DB::raw('MAX(id_precio) as id_precio'))
            ->whereNull('fecha_fin')
            ->groupBy('id_catalogo');

        $catalogo = Catalogo::query()
            ->with('categoria')
            ->leftJoinSub($precioActualSubquery, 'precio_actual_rel', function ($join) {
                $join->on('catalogo.id_catalogo', '=', 'precio_actual_rel.id_catalogo');
            })
            ->leftJoin('precios as precio_actual', 'precio_actual.id_precio', '=', 'precio_actual_rel.id_precio')
            ->select('catalogo.*', 'precio_actual.precio as precio_actual')
            ->get();

        $categorias = Categoria::select('id_categoria', 'nombre')->get();

        return Inertia::render('Catalogo/Index', [
            'catalogo' => $catalogo,
            'categorias' => $categorias,
        ]);
    }

    public function create()
    {
        $categorias = Categoria::all();
        $areas = Area::all();

        return Inertia::render('Catalogo/Create', [
            'categorias' => $categorias,
            'areas' => $areas
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required',
            'tipo' => 'required',
            'id_categoria' => 'required',
            'areas' => 'array',
            'precio' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'dia_semana' => 'nullable|string|required_with:hora_inicio,hora_fin',
            'hora_inicio' => 'nullable|date_format:H:i|required_with:dia_semana,hora_fin',
            'hora_fin' => 'nullable|date_format:H:i|required_with:dia_semana,hora_inicio',
            'regla_descripcion' => 'nullable|string',
            'tipo_regla' => 'nullable|string|max:80'
        ]);

        DB::transaction(function () use ($request, $validated) {
            $catalogo = Catalogo::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'tipo' => $request->tipo,
                'id_categoria' => $request->id_categoria,
                'activo' => true
            ]);

            if ($request->has('areas')) {
                $catalogo->areas()->sync($request->areas);
            }

            if (isset($validated['precio']) && $validated['precio'] !== null && $validated['precio'] !== '') {
                Precio::create([
                    'id_catalogo' => $catalogo->id_catalogo,
                    'precio' => $validated['precio'],
                    'fecha_inicio' => $validated['fecha_inicio'] ?? now()->toDateString(),
                    'fecha_fin' => null,
                ]);
            }

            if (!empty($validated['dia_semana']) && !empty($validated['hora_inicio']) && !empty($validated['hora_fin'])) {
                Disponibilidad::create([
                    'id_catalogo' => $catalogo->id_catalogo,
                    'dia_semana' => $validated['dia_semana'],
                    'hora_inicio' => $validated['hora_inicio'],
                    'hora_fin' => $validated['hora_fin'],
                    'disponible' => true,
                ]);
            }

            if (!empty($validated['regla_descripcion'])) {
                Regla::create([
                    'id_catalogo' => $catalogo->id_catalogo,
                    'descripcion' => $validated['regla_descripcion'],
                    'tipo_regla' => $validated['tipo_regla'] ?? 'general',
                ]);
            }
        });

        return redirect()->route('catalogo.index');
    }

    public function edit($id)
    {
        $catalogo = Catalogo::with('areas')->findOrFail($id);
        $categorias = Categoria::all();
        $areas = Area::all();
        $precioActual = Precio::where('id_catalogo', $id)
            ->whereNull('fecha_fin')
            ->orderByDesc('fecha_inicio')
            ->first();
        $disponibilidad = Disponibilidad::where('id_catalogo', $id)
            ->orderBy('id_disponibilidad')
            ->first();
        $regla = Regla::where('id_catalogo', $id)
            ->orderBy('id_regla')
            ->first();

        return Inertia::render('Catalogo/Edit', [
            'catalogo' => $catalogo,
            'categorias' => $categorias,
            'areas' => $areas,
            'precioActual' => $precioActual,
            'disponibilidad' => $disponibilidad,
            'regla' => $regla,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required',
            'tipo' => 'required',
            'id_categoria' => 'required',
            'areas' => 'array',
            'precio' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'dia_semana' => 'nullable|string|required_with:hora_inicio,hora_fin',
            'hora_inicio' => 'nullable|date_format:H:i|required_with:dia_semana,hora_fin',
            'hora_fin' => 'nullable|date_format:H:i|required_with:dia_semana,hora_inicio',
            'regla_descripcion' => 'nullable|string',
            'tipo_regla' => 'nullable|string|max:80'
        ]);

        DB::transaction(function () use ($request, $validated, $id) {
            $catalogo = Catalogo::findOrFail($id);

            $catalogo->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'tipo' => $request->tipo,
                'id_categoria' => $request->id_categoria
            ]);

            if ($request->has('areas')) {
                $catalogo->areas()->sync($request->areas);
            } else {
                $catalogo->areas()->sync([]);
            }

            if (isset($validated['precio']) && $validated['precio'] !== null && $validated['precio'] !== '') {
                $precioActual = Precio::where('id_catalogo', $id)
                    ->whereNull('fecha_fin')
                    ->first();

                $nuevoPrecio = (float) $validated['precio'];
                $precioSinCambio = $precioActual && (float) $precioActual->precio === $nuevoPrecio;

                if (!$precioSinCambio) {
                    if ($precioActual) {
                        $precioActual->update([
                            'fecha_fin' => now()
                        ]);
                    }

                    Precio::create([
                        'id_catalogo' => $id,
                        'precio' => $validated['precio'],
                        'fecha_inicio' => $validated['fecha_inicio'] ?? now()->toDateString(),
                        'fecha_fin' => null,
                    ]);
                }
            }

            if (!empty($validated['dia_semana']) && !empty($validated['hora_inicio']) && !empty($validated['hora_fin'])) {
                $disponibilidad = Disponibilidad::where('id_catalogo', $id)
                    ->orderBy('id_disponibilidad')
                    ->first();

                if ($disponibilidad) {
                    $disponibilidad->update([
                        'dia_semana' => $validated['dia_semana'],
                        'hora_inicio' => $validated['hora_inicio'],
                        'hora_fin' => $validated['hora_fin'],
                        'disponible' => true,
                    ]);
                } else {
                    Disponibilidad::create([
                        'id_catalogo' => $id,
                        'dia_semana' => $validated['dia_semana'],
                        'hora_inicio' => $validated['hora_inicio'],
                        'hora_fin' => $validated['hora_fin'],
                        'disponible' => true,
                    ]);
                }
            }

            if (!empty($validated['regla_descripcion'])) {
                $regla = Regla::where('id_catalogo', $id)
                    ->orderBy('id_regla')
                    ->first();

                if ($regla) {
                    $regla->update([
                        'descripcion' => $validated['regla_descripcion'],
                        'tipo_regla' => $validated['tipo_regla'] ?? $regla->tipo_regla ?? 'general',
                    ]);
                } else {
                    Regla::create([
                        'id_catalogo' => $id,
                        'descripcion' => $validated['regla_descripcion'],
                        'tipo_regla' => $validated['tipo_regla'] ?? 'general',
                    ]);
                }
            }
        });

        return redirect()->route('catalogo.index');
    }

    public function destroy($id)
    {
        $catalogo = Catalogo::findOrFail($id);
        $catalogo->delete();

        return redirect()->route('catalogo.index');
    }

    public function quickUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'activo' => 'nullable|boolean',
            'precio' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $catalogo = Catalogo::findOrFail($id);

            if (array_key_exists('activo', $validated)) {
                $catalogo->update([
                    'activo' => (bool) $validated['activo'],
                ]);
            }

            if (array_key_exists('precio', $validated) && $validated['precio'] !== null && $validated['precio'] !== '') {
                $precioActual = Precio::where('id_catalogo', $id)
                    ->whereNull('fecha_fin')
                    ->first();

                $nuevoPrecio = (float) $validated['precio'];
                $precioSinCambio = $precioActual && (float) $precioActual->precio === $nuevoPrecio;

                if (!$precioSinCambio) {
                    if ($precioActual) {
                        $precioActual->update([
                            'fecha_fin' => now(),
                        ]);
                    }

                    Precio::create([
                        'id_catalogo' => $id,
                        'precio' => $validated['precio'],
                        'fecha_inicio' => now()->toDateString(),
                        'fecha_fin' => null,
                    ]);
                }
            }
        });

        return redirect()->route('catalogo.index');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids'          => 'required|array|min:1',
            'ids.*'        => 'integer|exists:catalogo,id_catalogo',
            'action'       => 'required|in:activar,desactivar,cambiar_categoria,eliminar',
            'id_categoria' => 'nullable|required_if:action,cambiar_categoria|exists:categorias,id_categoria',
        ]);

        $ids = $validated['ids'];

        DB::transaction(function () use ($validated, $ids) {
            if ($validated['action'] === 'activar') {
                Catalogo::whereIn('id_catalogo', $ids)->update(['activo' => true]);
                return;
            }

            if ($validated['action'] === 'desactivar') {
                Catalogo::whereIn('id_catalogo', $ids)->update(['activo' => false]);
                return;
            }

            if ($validated['action'] === 'cambiar_categoria') {
                Catalogo::whereIn('id_catalogo', $ids)->update(['id_categoria' => $validated['id_categoria']]);
                return;
            }

            if ($validated['action'] === 'eliminar') {
                Catalogo::whereIn('id_catalogo', $ids)->delete();
            }
        });

        return redirect()->route('catalogo.index');
    }

    public function show($id)
    {
        $catalogo = \App\Models\Catalogo\Catalogo::with(['categoria', 'inventario', 'precios'])
            ->where('id_catalogo', $id)
            ->where('activo', true)
            ->firstOrFail();

        $resolver      = app(\App\Services\Catalogo\CatalogoCartResolver::class);
        $estadoCarrito = $resolver->estadoCarrito($catalogo);

        $saldo = 0;
        try {
            $monedero = \App\Models\SaldoMonedero::where('usuario_id', auth()->id())->first();
            $saldo    = $monedero ? (float) $monedero->saldo_disponible : 0;
        } catch (\Throwable) {}

        return Inertia::render('Catalogo/Show', [
            'producto' => array_merge($catalogo->only([
                'id_catalogo', 'nombre', 'descripcion', 'tipo', 'aplica_iva',
            ]), $estadoCarrito),
            'categoria'          => $catalogo->categoria?->nombre,
            'precio_vigente'     => $catalogo->precioVigenteValor()
                ? number_format((float) $catalogo->precioVigenteValor(), 2, '.', '')
                : null,
            'saldo_disponible'   => $saldo,
        ]);
    }
}