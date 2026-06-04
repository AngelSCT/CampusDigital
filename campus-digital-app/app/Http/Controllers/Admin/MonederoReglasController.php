<?php

namespace App\Http\Controllers\Admin;

use App\Models\SaldoRegla;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class MonederoReglasController extends Controller
{
    /**
     * GET /admin/monedero/reglas
     * Listar todas las reglas
     */
    public function index(Request $request)
    {
        $reglas = SaldoRegla::with('usuario')
            ->when($request->activas, fn($q) => $q->activas())
            ->paginate(15);

        return Inertia::render('Admin/Monedero/Reglas', [
            'reglas' => $reglas,
        ]);
    }

    /**
     * GET /admin/monedero/reglas/create
     * Formulario crear regla
     */
    public function create()
    {
        return Inertia::render('Admin/Monedero/ReglaForm', [
            'usuarios' => Usuario::all(['id', 'nombre']),
            'tiposLimite' => ['diario', 'semanal', 'mensual'],
            'modulos' => ['cafeteria', 'copias', 'souvenirs', 'biblioteca', 'recarga', 'otro'],
        ]);
    }

    /**
     * POST /admin/monedero/reglas
     * Guardar regla
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|integer|exists:usuario,id',
            'tipo_limite' => 'required|in:diario,semanal,mensual',
            'monto_limite' => 'required|numeric|min:0.01',
            'modulo' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        SaldoRegla::create($validated);

        return redirect()->route('admin.monedero.reglas.index')
            ->with('success', 'Regla creada exitosamente');
    }

    /**
     * GET /admin/monedero/reglas/{regla}
     * Ver detalle de regla
     */
    public function show(SaldoRegla $regla)
    {
        return Inertia::render('Admin/Monedero/ReglaDetail', [
            'regla' => $regla->load('usuario'),
        ]);
    }

    /**
     * GET /admin/monedero/reglas/{regla}/edit
     * Formulario editar regla
     */
    public function edit(SaldoRegla $regla)
    {
        return Inertia::render('Admin/Monedero/ReglaForm', [
            'regla' => $regla,
            'usuarios' => Usuario::all(['id', 'nombre']),
            'tiposLimite' => ['diario', 'semanal', 'mensual'],
            'modulos' => ['cafeteria', 'copias', 'souvenirs', 'biblioteca', 'recarga', 'otro'],
        ]);
    }

    /**
     * PUT /admin/monedero/reglas/{regla}
     * Actualizar regla
     */
    public function update(Request $request, SaldoRegla $regla)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|integer|exists:usuario,id',
            'tipo_limite' => 'required|in:diario,semanal,mensual',
            'monto_limite' => 'required|numeric|min:0.01',
            'modulo' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $regla->update($validated);

        return redirect()->route('admin.monedero.reglas.index')
            ->with('success', 'Regla actualizada exitosamente');
    }

    /**
     * DELETE /admin/monedero/reglas/{regla}
     * Eliminar regla
     */
    public function destroy(SaldoRegla $regla)
    {
        $regla->delete();

        return redirect()->route('admin.monedero.reglas.index')
            ->with('success', 'Regla eliminada exitosamente');
    }
}
