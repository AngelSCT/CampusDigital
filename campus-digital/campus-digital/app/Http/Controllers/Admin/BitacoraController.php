<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccesoBitacora;
use App\Models\ActividadBitacora;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BitacoraController extends Controller
{
    public function accesos(Request $request)
    {
        $query = AccesoBitacora::with('usuario');

        // Filtros
        if ($request->filled('search')) {
            $query->where('email_intentado', 'ilike', "%{$request->search}%");
        }

        if ($request->filled('evento')) {
            $query->where('evento', $request->evento);
        }

        if ($request->filled('exito')) {
            $query->where('exito', $request->exito === 'si');
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $accesos = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $eventos = AccesoBitacora::select('evento')->distinct()->pluck('evento');

        return Inertia::render('Admin/Bitacora/Accesos', [
            'accesos' => $accesos,
            'eventos' => $eventos,
            'filters' => $request->only(['search', 'evento', 'exito', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function actividad(Request $request)
    {
        $query = ActividadBitacora::with('usuario');

        // Filtros
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('accion', 'ilike', "%{$request->search}%")
                  ->orWhere('detalle', 'ilike', "%{$request->search}%");
            });
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $actividades = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $modulos = ActividadBitacora::select('modulo')->distinct()->pluck('modulo');
        $acciones = ActividadBitacora::select('accion')->distinct()->pluck('accion');

        return Inertia::render('Admin/Bitacora/Actividad', [
            'actividades' => $actividades,
            'modulos' => $modulos,
            'acciones' => $acciones,
            'filters' => $request->only(['search', 'modulo', 'accion', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function exportAccesos(Request $request)
    {
        $accesos = AccesoBitacora::with('usuario')->get();

        $csvData = "ID,Usuario,Email Intentado,Evento,Éxito,IP,Fecha\n";
        
        foreach ($accesos as $acceso) {
            $usuario = $acceso->usuario ? $acceso->usuario->email : 'N/A';
            $exito = $acceso->exito ? 'Sí' : 'No';
            
            $csvData .= "{$acceso->id},{$usuario},{$acceso->email_intentado},{$acceso->evento},{$exito},{$acceso->ip},{$acceso->created_at}\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="accesos_' . now()->format('Y-m-d') . '.csv"');
    }

    public function exportActividad(Request $request)
    {
        $actividades = ActividadBitacora::with('usuario')->get();

        $csvData = "ID,Usuario,Módulo,Acción,Detalle,Éxito,IP,Fecha\n";
        
        foreach ($actividades as $actividad) {
            $usuario = $actividad->usuario ? $actividad->usuario->email : 'N/A';
            $exito = $actividad->exito ? 'Sí' : 'No';
            
            $csvData .= "{$actividad->id},{$usuario},{$actividad->modulo},{$actividad->accion},\"{$actividad->detalle}\",{$exito},{$actividad->ip},{$actividad->created_at}\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="actividad_' . now()->format('Y-m-d') . '.csv"');
    }
}