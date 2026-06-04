<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\CatalogoVendedor;
use App\Models\Categoria;
use App\Models\DisponibilidadVendedor;
use App\Models\PrecioVendedor;
use App\Models\ReglaVendedor;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CatalogoVendedorController extends Controller
{
    public function index()
    {
        $precioActualSubquery = PrecioVendedor::query()
            ->select('id_cv', DB::raw('MAX(id_precio_v) as id_precio_v'))
            ->whereNull('fecha_fin')
            ->groupBy('id_cv');

        $catalogo = CatalogoVendedor::query()
            ->with(['vendedor', 'categoria', 'catalogoBase'])
            ->leftJoinSub($precioActualSubquery, 'precio_v_actual_rel', function ($join) {
                $join->on('catalogo_vendedor.id_cv', '=', 'precio_v_actual_rel.id_cv');
            })
            ->leftJoin('precios_vendedor as precio_v_actual', 'precio_v_actual.id_precio_v', '=', 'precio_v_actual_rel.id_precio_v')
            ->select('catalogo_vendedor.*', 'precio_v_actual.precio as precio_actual')
            ->orderByDesc('catalogo_vendedor.id_cv')
            ->get();

        return Inertia::render('CatalogoVendedor/Index', [
            'catalogo' => $catalogo,
        ]);
    }

    public function create()
    {
        return Inertia::render('CatalogoVendedor/Create', [
            'vendedores' => Vendedor::select('id_vendedor', 'nombre')->where('activo', true)->orderBy('nombre')->get(),
            'categorias' => Categoria::select('id_categoria', 'nombre')->orderBy('nombre')->get(),
            'catalogoBase' => Catalogo::select('id_catalogo', 'nombre')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_vendedor' => 'required|exists:vendedores,id_vendedor',
            'id_catalogo_base' => 'nullable|exists:catalogo,id_catalogo',
            'nombre_personalizado' => 'required|string|max:150',
            'descripcion_personalizada' => 'nullable|string',
            'tipo' => 'required|in:producto,servicio',
            'id_categoria' => 'nullable|exists:categorias,id_categoria',
            'activo' => 'nullable|boolean',
            'precio' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'dia_semana' => 'nullable|string|required_with:hora_inicio,hora_fin',
            'hora_inicio' => 'nullable|date_format:H:i|required_with:dia_semana,hora_fin',
            'hora_fin' => 'nullable|date_format:H:i|required_with:dia_semana,hora_inicio',
            'regla_descripcion' => 'nullable|string',
            'tipo_regla' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated) {
            $catalogoVendedor = CatalogoVendedor::create([
                'id_vendedor' => $validated['id_vendedor'],
                'id_catalogo_base' => $validated['id_catalogo_base'] ?? null,
                'nombre_personalizado' => $validated['nombre_personalizado'],
                'descripcion_personalizada' => $validated['descripcion_personalizada'] ?? null,
                'tipo' => $validated['tipo'],
                'id_categoria' => $validated['id_categoria'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ]);

            if (isset($validated['precio']) && $validated['precio'] !== null && $validated['precio'] !== '') {
                PrecioVendedor::create([
                    'id_cv' => $catalogoVendedor->id_cv,
                    'precio' => $validated['precio'],
                    'fecha_inicio' => $validated['fecha_inicio'] ?? now()->toDateString(),
                    'fecha_fin' => null,
                ]);
            }

            if (!empty($validated['dia_semana']) && !empty($validated['hora_inicio']) && !empty($validated['hora_fin'])) {
                DisponibilidadVendedor::create([
                    'id_cv' => $catalogoVendedor->id_cv,
                    'dia_semana' => $validated['dia_semana'],
                    'hora_inicio' => $validated['hora_inicio'],
                    'hora_fin' => $validated['hora_fin'],
                    'disponible' => true,
                ]);
            }

            if (!empty($validated['regla_descripcion'])) {
                ReglaVendedor::create([
                    'id_cv' => $catalogoVendedor->id_cv,
                    'descripcion' => $validated['regla_descripcion'],
                    'tipo_regla' => $validated['tipo_regla'] ?? 'general',
                ]);
            }
        });

        return redirect()->route('catalogo-vendedor.index');
    }

    public function edit($id)
    {
        $catalogo = CatalogoVendedor::findOrFail($id);

        return Inertia::render('CatalogoVendedor/Edit', [
            'catalogo' => $catalogo,
            'vendedores' => Vendedor::select('id_vendedor', 'nombre')->where('activo', true)->orderBy('nombre')->get(),
            'categorias' => Categoria::select('id_categoria', 'nombre')->orderBy('nombre')->get(),
            'catalogoBase' => Catalogo::select('id_catalogo', 'nombre')->orderBy('nombre')->get(),
            'precioActual' => PrecioVendedor::where('id_cv', $id)->whereNull('fecha_fin')->orderByDesc('fecha_inicio')->first(),
            'disponibilidad' => DisponibilidadVendedor::where('id_cv', $id)->orderBy('id_disp_v')->first(),
            'regla' => ReglaVendedor::where('id_cv', $id)->orderBy('id_regla_v')->first(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_vendedor' => 'required|exists:vendedores,id_vendedor',
            'id_catalogo_base' => 'nullable|exists:catalogo,id_catalogo',
            'nombre_personalizado' => 'required|string|max:150',
            'descripcion_personalizada' => 'nullable|string',
            'tipo' => 'required|in:producto,servicio',
            'id_categoria' => 'nullable|exists:categorias,id_categoria',
            'activo' => 'nullable|boolean',
            'precio' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'dia_semana' => 'nullable|string|required_with:hora_inicio,hora_fin',
            'hora_inicio' => 'nullable|date_format:H:i|required_with:dia_semana,hora_fin',
            'hora_fin' => 'nullable|date_format:H:i|required_with:dia_semana,hora_inicio',
            'regla_descripcion' => 'nullable|string',
            'tipo_regla' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $catalogoVendedor = CatalogoVendedor::findOrFail($id);

            $catalogoVendedor->update([
                'id_vendedor' => $validated['id_vendedor'],
                'id_catalogo_base' => $validated['id_catalogo_base'] ?? null,
                'nombre_personalizado' => $validated['nombre_personalizado'],
                'descripcion_personalizada' => $validated['descripcion_personalizada'] ?? null,
                'tipo' => $validated['tipo'],
                'id_categoria' => $validated['id_categoria'] ?? null,
                'activo' => $validated['activo'] ?? false,
            ]);

            if (isset($validated['precio']) && $validated['precio'] !== null && $validated['precio'] !== '') {
                $precioActual = PrecioVendedor::where('id_cv', $id)->whereNull('fecha_fin')->first();

                $nuevoPrecio = (float) $validated['precio'];
                $precioSinCambio = $precioActual && (float) $precioActual->precio === $nuevoPrecio;

                if (!$precioSinCambio) {
                    if ($precioActual) {
                        $precioActual->update([
                            'fecha_fin' => now(),
                        ]);
                    }

                    PrecioVendedor::create([
                        'id_cv' => $id,
                        'precio' => $validated['precio'],
                        'fecha_inicio' => $validated['fecha_inicio'] ?? now()->toDateString(),
                        'fecha_fin' => null,
                    ]);
                }
            }

            if (!empty($validated['dia_semana']) && !empty($validated['hora_inicio']) && !empty($validated['hora_fin'])) {
                $disponibilidad = DisponibilidadVendedor::where('id_cv', $id)->orderBy('id_disp_v')->first();

                if ($disponibilidad) {
                    $disponibilidad->update([
                        'dia_semana' => $validated['dia_semana'],
                        'hora_inicio' => $validated['hora_inicio'],
                        'hora_fin' => $validated['hora_fin'],
                        'disponible' => true,
                    ]);
                } else {
                    DisponibilidadVendedor::create([
                        'id_cv' => $id,
                        'dia_semana' => $validated['dia_semana'],
                        'hora_inicio' => $validated['hora_inicio'],
                        'hora_fin' => $validated['hora_fin'],
                        'disponible' => true,
                    ]);
                }
            }

            if (!empty($validated['regla_descripcion'])) {
                $regla = ReglaVendedor::where('id_cv', $id)->orderBy('id_regla_v')->first();

                if ($regla) {
                    $regla->update([
                        'descripcion' => $validated['regla_descripcion'],
                        'tipo_regla' => $validated['tipo_regla'] ?? $regla->tipo_regla ?? 'general',
                    ]);
                } else {
                    ReglaVendedor::create([
                        'id_cv' => $id,
                        'descripcion' => $validated['regla_descripcion'],
                        'tipo_regla' => $validated['tipo_regla'] ?? 'general',
                    ]);
                }
            }
        });

        return redirect()->route('catalogo-vendedor.index');
    }

    public function destroy($id)
    {
        CatalogoVendedor::findOrFail($id)->delete();

        return redirect()->route('catalogo-vendedor.index');
    }
}
