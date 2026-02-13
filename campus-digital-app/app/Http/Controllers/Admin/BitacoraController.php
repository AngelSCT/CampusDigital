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

public function exportAccesosPdf(Request $request)
{
    $accesos = AccesoBitacora::with('usuario')
        ->orderBy('created_at', 'desc')
        ->get();
    
    $estadisticas = [
        'total' => $accesos->count(),
        'exitosos' => $accesos->where('exito', true)->count(),
        'fallidos' => $accesos->where('exito', false)->count(),
    ];
    
    $eventoStats = $accesos->groupBy('evento')->map(function($items, $evento) {
        return [
            'evento' => $evento,
            'total' => $items->count(),
            'exitosos' => $items->where('exito', true)->count(),
            'fallidos' => $items->where('exito', false)->count(),
        ];
    })->values();

    $pdf = \PDF::loadView('pdf.accesos-bitacora', [
        'accesos' => $accesos,
        'estadisticas' => $estadisticas,
        'eventoStats' => $eventoStats,
        'titulo' => 'Bitácora de Accesos al Sistema',
        'fecha' => now()->format('d/m/Y H:i:s'),
        'generadoPor' => auth()->user()->nombre . ' ' . auth()->user()->apellido,
        'periodo' => 'Todos los registros'
    ]);

    $pdf->setPaper('A4', 'landscape');

    return $pdf->download('accesos_bitacora_' . now()->format('Y-m-d_His') . '.pdf');
}

public function exportAccesosPeriodo(Request $request)
{
    $request->validate([
        'fecha_desde' => 'required|date',
        'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
    ]);

    $query = AccesoBitacora::with('usuario')
        ->whereDate('created_at', '>=', $request->fecha_desde)
        ->whereDate('created_at', '<=', $request->fecha_hasta);

    if ($request->filled('evento')) {
        $query->where('evento', $request->evento);
    }

    if ($request->filled('exito')) {
        $query->where('exito', $request->exito === 'si');
    }

    $accesos = $query->orderBy('created_at', 'desc')->get();

    $filename = 'accesos_periodo_' . $request->fecha_desde . '_' . $request->fecha_hasta . '_' . now()->format('His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];

    $callback = function() use ($accesos, $request) {
        $file = fopen('php://output', 'w');
        
        
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Información del reporte
        fputcsv($file, ['REPORTE DE ACCESOS POR PERÍODO']);
        fputcsv($file, ['']);
        fputcsv($file, ['Período:', $request->fecha_desde . ' al ' . $request->fecha_hasta]);
        if ($request->filled('evento')) {
            fputcsv($file, ['Evento filtrado:', $request->evento]);
        }
        if ($request->filled('exito')) {
            fputcsv($file, ['Estado filtrado:', $request->exito === 'si' ? 'Exitosos' : 'Fallidos']);
        }
        fputcsv($file, ['Total de registros:', count($accesos)]);
        fputcsv($file, ['Fecha de generación:', now()->format('d/m/Y H:i:s')]);
        fputcsv($file, ['Generado por:', auth()->user()->nombre . ' ' . auth()->user()->apellido]);
        fputcsv($file, ['']);
        fputcsv($file, ['']);
        
        // Encabezados
        fputcsv($file, [
            'ID',
            'Usuario',
            'Email Intentado',
            'Evento',
            'Estado',
            'IP',
            'User Agent',
            'Detalle',
            'Fecha y Hora'
        ]);
        
        // Datos
        foreach ($accesos as $acceso) {
            fputcsv($file, [
                $acceso->id,
                $acceso->usuario ? $acceso->usuario->email : 'N/A',
                $acceso->email_intentado ?? 'N/A',
                $acceso->evento,
                $acceso->exito ? 'Exitoso' : 'Fallido',
                $acceso->ip ?? 'N/A',
                $acceso->user_agent ?? 'N/A',
                $acceso->detalle ?? 'Sin detalles',
                $acceso->created_at->format('d/m/Y H:i:s')
            ]);
        }
        
        
        fputcsv($file, ['']);
        fputcsv($file, ['']);
        fputcsv($file, ['ESTADÍSTICAS DEL PERÍODO']);
        fputcsv($file, ['']);
        fputcsv($file, ['Total de accesos:', count($accesos)]);
        fputcsv($file, ['Accesos exitosos:', $accesos->where('exito', true)->count()]);
        fputcsv($file, ['Accesos fallidos:', $accesos->where('exito', false)->count()]);
        fputcsv($file, ['']);
        
        // Estadísticas por evento
        $eventoStats = $accesos->groupBy('evento');
        fputcsv($file, ['ACCESOS POR EVENTO']);
        fputcsv($file, ['Evento', 'Total', 'Exitosos', 'Fallidos']);
        foreach ($eventoStats as $evento => $items) {
            fputcsv($file, [
                $evento,
                $items->count(),
                $items->where('exito', true)->count(),
                $items->where('exito', false)->count()
            ]);
        }
        
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportAccesosPeriodoPdf(Request $request)
{
    $request->validate([
        'fecha_desde' => 'required|date',
        'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
    ]);

    $query = AccesoBitacora::with('usuario')
        ->whereDate('created_at', '>=', $request->fecha_desde)
        ->whereDate('created_at', '<=', $request->fecha_hasta);

    if ($request->filled('evento')) {
        $query->where('evento', $request->evento);
    }

    if ($request->filled('exito')) {
        $query->where('exito', $request->exito === 'si');
    }

    $accesos = $query->orderBy('created_at', 'desc')->get();
    
    $estadisticas = [
        'total' => $accesos->count(),
        'exitosos' => $accesos->where('exito', true)->count(),
        'fallidos' => $accesos->where('exito', false)->count(),
    ];
    
    $eventoStats = $accesos->groupBy('evento')->map(function($items, $evento) {
        return [
            'evento' => $evento,
            'total' => $items->count(),
            'exitosos' => $items->where('exito', true)->count(),
            'fallidos' => $items->where('exito', false)->count(),
        ];
    })->values();

    $filtrosAplicados = [];
    if ($request->filled('evento')) {
        $filtrosAplicados[] = 'Evento: ' . $request->evento;
    }
    if ($request->filled('exito')) {
        $filtrosAplicados[] = 'Estado: ' . ($request->exito === 'si' ? 'Exitosos' : 'Fallidos');
    }

    $pdf = \PDF::loadView('pdf.accesos-bitacora', [
        'accesos' => $accesos,
        'estadisticas' => $estadisticas,
        'eventoStats' => $eventoStats,
        'titulo' => 'Bitácora de Accesos por Período',
        'fecha' => now()->format('d/m/Y H:i:s'),
        'generadoPor' => auth()->user()->nombre . ' ' . auth()->user()->apellido,
        'periodo' => \Carbon\Carbon::parse($request->fecha_desde)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($request->fecha_hasta)->format('d/m/Y'),
        'filtrosAplicados' => $filtrosAplicados
    ]);

    $pdf->setPaper('A4', 'landscape');

    return $pdf->download('accesos_periodo_' . $request->fecha_desde . '_' . $request->fecha_hasta . '.pdf');
}

public function exportActividadPdf(Request $request)
{
    $actividades = ActividadBitacora::with('usuario')
        ->orderBy('created_at', 'desc')
        ->get();
    
    $estadisticas = [
        'total' => $actividades->count(),
        'exitosos' => $actividades->where('exito', true)->count(),
        'fallidos' => $actividades->where('exito', false)->count(),
    ];
    
    $moduloStats = $actividades->groupBy('modulo')->map(function($items, $modulo) {
        return [
            'modulo' => $modulo,
            'total' => $items->count(),
            'exitosos' => $items->where('exito', true)->count(),
            'fallidos' => $items->where('exito', false)->count(),
        ];
    })->values();

    $accionStats = $actividades->groupBy('accion')->map(function($items, $accion) {
        return [
            'accion' => $accion,
            'total' => $items->count(),
        ];
    })->values()->take(10);

    $pdf = \PDF::loadView('pdf.actividad-bitacora', [
        'actividades' => $actividades,
        'estadisticas' => $estadisticas,
        'moduloStats' => $moduloStats,
        'accionStats' => $accionStats,
        'titulo' => 'Bitácora de Actividad del Sistema',
        'fecha' => now()->format('d/m/Y H:i:s'),
        'generadoPor' => auth()->user()->nombre . ' ' . auth()->user()->apellido,
        'periodo' => 'Todos los registros',
        'filtrosAplicados' => []
    ]);

    $pdf->setPaper('A4', 'landscape');

    return $pdf->download('actividad_bitacora_' . now()->format('Y-m-d_His') . '.pdf');
}

public function exportActividadPeriodo(Request $request)
{
    $request->validate([
        'fecha_desde' => 'required|date',
        'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
    ]);

    $query = ActividadBitacora::with('usuario')
        ->whereDate('created_at', '>=', $request->fecha_desde)
        ->whereDate('created_at', '<=', $request->fecha_hasta);

    if ($request->filled('modulo')) {
        $query->where('modulo', $request->modulo);
    }

    if ($request->filled('accion')) {
        $query->where('accion', $request->accion);
    }

    $actividades = $query->orderBy('created_at', 'desc')->get();

    $filename = 'actividad_periodo_' . $request->fecha_desde . '_' . $request->fecha_hasta . '_' . now()->format('His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];

    $callback = function() use ($actividades, $request) {
        $file = fopen('php://output', 'w');
        
        
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Información del reporte
        fputcsv($file, ['REPORTE DE ACTIVIDAD POR PERÍODO']);
        fputcsv($file, ['']);
        fputcsv($file, ['Período:', $request->fecha_desde . ' al ' . $request->fecha_hasta]);
        if ($request->filled('modulo')) {
            fputcsv($file, ['Módulo filtrado:', $request->modulo]);
        }
        if ($request->filled('accion')) {
            fputcsv($file, ['Acción filtrada:', $request->accion]);
        }
        fputcsv($file, ['Total de registros:', count($actividades)]);
        fputcsv($file, ['Fecha de generación:', now()->format('d/m/Y H:i:s')]);
        fputcsv($file, ['Generado por:', auth()->user()->nombre . ' ' . auth()->user()->apellido]);
        fputcsv($file, ['']);
        fputcsv($file, ['']);
        
        // Encabezados
        fputcsv($file, [
            'ID',
            'Usuario',
            'Módulo',
            'Acción',
            'Estado',
            'IP',
            'Tabla Afectada',
            'ID Afectado',
            'Detalle',
            'User Agent',
            'Fecha y Hora'
        ]);
        
        // Datos
        foreach ($actividades as $actividad) {
            fputcsv($file, [
                $actividad->id,
                $actividad->usuario ? $actividad->usuario->email : 'N/A',
                $actividad->modulo,
                $actividad->accion,
                $actividad->exito ? 'Exitoso' : 'Fallido',
                $actividad->ip ?? 'N/A',
                $actividad->target_tabla ?? 'N/A',
                $actividad->target_id ?? 'N/A',
                $actividad->detalle ?? 'Sin detalles',
                $actividad->user_agent ?? 'N/A',
                $actividad->created_at->format('d/m/Y H:i:s')
            ]);
        }
        
        
        fputcsv($file, ['']);
        fputcsv($file, ['']);
        fputcsv($file, ['ESTADÍSTICAS DEL PERÍODO']);
        fputcsv($file, ['']);
        fputcsv($file, ['Total de actividades:', count($actividades)]);
        fputcsv($file, ['Actividades exitosas:', $actividades->where('exito', true)->count()]);
        fputcsv($file, ['Actividades fallidas:', $actividades->where('exito', false)->count()]);
        fputcsv($file, ['']);
        
        // Estadísticas por módulo
        $moduloStats = $actividades->groupBy('modulo');
        fputcsv($file, ['ACTIVIDADES POR MÓDULO']);
        fputcsv($file, ['Módulo', 'Total', 'Exitosas', 'Fallidas']);
        foreach ($moduloStats as $modulo => $items) {
            fputcsv($file, [
                $modulo,
                $items->count(),
                $items->where('exito', true)->count(),
                $items->where('exito', false)->count()
            ]);
        }
        
        fputcsv($file, ['']);
        
        
        $accionStats = $actividades->groupBy('accion');
        fputcsv($file, ['ACTIVIDADES POR ACCIÓN (Top 10)']);
        fputcsv($file, ['Acción', 'Total']);
        $counter = 0;
        foreach ($accionStats->sortByDesc(function($items) { return $items->count(); }) as $accion => $items) {
            if ($counter++ >= 10) break;
            fputcsv($file, [$accion, $items->count()]);
        }
        
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportActividadPeriodoPdf(Request $request)
{
    $request->validate([
        'fecha_desde' => 'required|date',
        'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
    ]);

    $query = ActividadBitacora::with('usuario')
        ->whereDate('created_at', '>=', $request->fecha_desde)
        ->whereDate('created_at', '<=', $request->fecha_hasta);

    if ($request->filled('modulo')) {
        $query->where('modulo', $request->modulo);
    }

    if ($request->filled('accion')) {
        $query->where('accion', $request->accion);
    }

    $actividades = $query->orderBy('created_at', 'desc')->get();
    
    $estadisticas = [
        'total' => $actividades->count(),
        'exitosos' => $actividades->where('exito', true)->count(),
        'fallidos' => $actividades->where('exito', false)->count(),
    ];
    
    $moduloStats = $actividades->groupBy('modulo')->map(function($items, $modulo) {
        return [
            'modulo' => $modulo,
            'total' => $items->count(),
            'exitosos' => $items->where('exito', true)->count(),
            'fallidos' => $items->where('exito', false)->count(),
        ];
    })->values();

    $accionStats = $actividades->groupBy('accion')->map(function($items, $accion) {
        return [
            'accion' => $accion,
            'total' => $items->count(),
        ];
    })->values()->sortByDesc('total')->take(10)->values();

    $filtrosAplicados = [];
    if ($request->filled('modulo')) {
        $filtrosAplicados[] = 'Módulo: ' . $request->modulo;
    }
    if ($request->filled('accion')) {
        $filtrosAplicados[] = 'Acción: ' . $request->accion;
    }

    $pdf = \PDF::loadView('pdf.actividad-bitacora', [
        'actividades' => $actividades,
        'estadisticas' => $estadisticas,
        'moduloStats' => $moduloStats,
        'accionStats' => $accionStats,
        'titulo' => 'Bitácora de Actividad por Período',
        'fecha' => now()->format('d/m/Y H:i:s'),
        'generadoPor' => auth()->user()->nombre . ' ' . auth()->user()->apellido,
        'periodo' => \Carbon\Carbon::parse($request->fecha_desde)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($request->fecha_hasta)->format('d/m/Y'),
        'filtrosAplicados' => $filtrosAplicados
    ]);

    $pdf->setPaper('A4', 'landscape');

    return $pdf->download('actividad_periodo_' . $request->fecha_desde . '_' . $request->fecha_hasta . '.pdf');
}

public function exportActividadModulo(Request $request)
{
    $query = ActividadBitacora::with('usuario');

    if ($request->filled('fecha_desde')) {
        $query->whereDate('created_at', '>=', $request->fecha_desde);
    }

    if ($request->filled('fecha_hasta')) {
        $query->whereDate('created_at', '<=', $request->fecha_hasta);
    }

    $actividades = $query->orderBy('modulo')->orderBy('created_at', 'desc')->get();
    $modulos = $actividades->groupBy('modulo');

    $filename = 'actividad_por_modulo_' . now()->format('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];

    $callback = function() use ($modulos, $request, $actividades) {
        $file = fopen('php://output', 'w');
        
        
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        
        fputcsv($file, ['REPORTE DE ACTIVIDAD AGRUPADO POR MÓDULO']);
        fputcsv($file, ['']);
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            fputcsv($file, ['Período:', $request->fecha_desde . ' al ' . $request->fecha_hasta]);
        } else {
            fputcsv($file, ['Período:', 'Todos los registros']);
        }
        fputcsv($file, ['Total de módulos:', $modulos->count()]);
        fputcsv($file, ['Total de actividades:', $actividades->count()]);
        fputcsv($file, ['Fecha de generación:', now()->format('d/m/Y H:i:s')]);
        fputcsv($file, ['Generado por:', auth()->user()->nombre . ' ' . auth()->user()->apellido]);
        fputcsv($file, ['']);
        fputcsv($file, ['']);
        
        foreach ($modulos as $modulo => $items) {
            fputcsv($file, ['MÓDULO: ' . strtoupper($modulo)]);
            fputcsv($file, ['Total de actividades:', $items->count()]);
            fputcsv($file, ['Exitosas:', $items->where('exito', true)->count()]);
            fputcsv($file, ['Fallidas:', $items->where('exito', false)->count()]);
            fputcsv($file, ['']);
            
            // Encabezados de columnas
            fputcsv($file, [
                'ID',
                'Usuario',
                'Acción',
                'Estado',
                'IP',
                'Detalle',
                'Fecha y Hora'
            ]);
            
            // Datos
            foreach ($items as $actividad) {
                fputcsv($file, [
                    $actividad->id,
                    $actividad->usuario ? $actividad->usuario->email : 'N/A',
                    $actividad->accion,
                    $actividad->exito ? 'Exitoso' : 'Fallido',
                    $actividad->ip ?? 'N/A',
                    $actividad->detalle ?? 'Sin detalles',
                    $actividad->created_at->format('d/m/Y H:i:s')
                ]);
            }
            
            fputcsv($file, ['']);
            fputcsv($file, ['']);
        }
        
        // Resumen general
        fputcsv($file, ['RESUMEN GENERAL']);
        fputcsv($file, ['']);
        fputcsv($file, ['Módulo', 'Total Actividades', 'Exitosas', 'Fallidas']);
        foreach ($modulos as $modulo => $items) {
            fputcsv($file, [
                $modulo,
                $items->count(),
                $items->where('exito', true)->count(),
                $items->where('exito', false)->count()
            ]);
        }
        
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportActividadModuloPdf(Request $request)
{
    $query = ActividadBitacora::with('usuario');

    if ($request->filled('fecha_desde')) {
        $query->whereDate('created_at', '>=', $request->fecha_desde);
    }

    if ($request->filled('fecha_hasta')) {
        $query->whereDate('created_at', '<=', $request->fecha_hasta);
    }

    $actividades = $query->orderBy('modulo')->orderBy('created_at', 'desc')->get();
    
    $datosModulos = [];
    $estadisticas = [
        'total' => $actividades->count(),
        'exitosos' => $actividades->where('exito', true)->count(),
        'fallidos' => $actividades->where('exito', false)->count(),
    ];
    
    foreach ($actividades->groupBy('modulo') as $modulo => $items) {
        $datosModulos[] = [
            'modulo' => $modulo,
            'actividades' => $items,
            'total' => $items->count(),
            'exitosos' => $items->where('exito', true)->count(),
            'fallidos' => $items->where('exito', false)->count(),
        ];
    }

    $periodo = 'Todos los registros';
    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
        $periodo = \Carbon\Carbon::parse($request->fecha_desde)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($request->fecha_hasta)->format('d/m/Y');
    }

    $pdf = \PDF::loadView('pdf.actividad-por-modulo', [
        'datosModulos' => $datosModulos,
        'estadisticas' => $estadisticas,
        'titulo' => 'Reporte de Actividad por Módulo',
        'fecha' => now()->format('d/m/Y H:i:s'),
        'generadoPor' => auth()->user()->nombre . ' ' . auth()->user()->apellido,
        'periodo' => $periodo
    ]);

    $pdf->setPaper('A4', 'landscape');

    return $pdf->download('actividad_por_modulo_' . now()->format('Y-m-d_His') . '.pdf');
}
}