<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\AccesoBitacora;
use App\Models\ActividadBitacora;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function usuarios(Request $request)
    {
        $query = Usuario::with('roles')->whereNull('deleted_at');

        if ($request->filled('rol')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('nombre', $request->rol);
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }

        $usuarios = $query->orderBy('created_at', 'desc')->get();

        if ($request->export === 'csv') {
            return $this->exportUsuariosCSV($usuarios);
        }

        return Inertia::render('Admin/Reportes/Usuarios', [
            'usuarios' => $usuarios,
            'filters' => $request->only(['rol', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function accesos(Request $request)
    {
        $query = AccesoBitacora::with('usuario');

        if ($request->filled('evento')) {
            $query->where('evento', $request->evento);
        }

        if ($request->filled('exito')) {
            $query->where('exito', $request->exito === 'true');
        }

        if ($request->filled('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }

        $accesos = $query->orderBy('created_at', 'desc')->paginate(50);

        if ($request->export === 'csv') {
            return $this->exportAccesosCSV($accesos->items());
        }

        return Inertia::render('Admin/Reportes/Accesos', [
            'accesos' => $accesos,
            'filters' => $request->only(['evento', 'exito', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function actividad(Request $request)
    {
        $query = ActividadBitacora::with('usuario');

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->filled('accion')) {
            $query->where('accion', 'ilike', "%{$request->accion}%");
        }

        if ($request->filled('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }

        $actividades = $query->orderBy('created_at', 'desc')->paginate(50);

        if ($request->export === 'csv') {
            return $this->exportActividadCSV($actividades->items());
        }

        return Inertia::render('Admin/Reportes/Actividad', [
            'actividades' => $actividades,
            'filters' => $request->only(['usuario_id', 'modulo', 'accion', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    private function exportUsuariosCSV($usuarios)
    {
        $filename = 'usuarios_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($usuarios) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nombre', 'Apellido', 'Email', 'Teléfono', 'Roles', 'Bloqueado', 'Email Verificado', 'Fecha Registro']);

            foreach ($usuarios as $usuario) {
                fputcsv($file, [
                    $usuario->id,
                    $usuario->nombre,
                    $usuario->apellido,
                    $usuario->email,
                    $usuario->telefono,
                    $usuario->roles->pluck('nombre')->join(', '),
                    $usuario->bloqueado ? 'Sí' : 'No',
                    $usuario->email_verificado ? 'Sí' : 'No',
                    $usuario->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportAccesosCSV($accesos)
    {
        $filename = 'accesos_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($accesos) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Usuario', 'Email Intentado', 'Evento', 'Éxito', 'IP', 'Fecha']);

            foreach ($accesos as $acceso) {
                fputcsv($file, [
                    $acceso->id,
                    $acceso->usuario ? $acceso->usuario->nombre_completo : 'N/A',
                    $acceso->email_intentado,
                    $acceso->evento,
                    $acceso->exito ? 'Sí' : 'No',
                    $acceso->ip,
                    $acceso->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportActividadCSV($actividades)
    {
        $filename = 'actividad_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($actividades) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Usuario', 'Acción', 'Módulo', 'Tabla', 'Target ID', 'Éxito', 'Detalle', 'Fecha']);

            foreach ($actividades as $actividad) {
                fputcsv($file, [
                    $actividad->id,
                    $actividad->usuario ? $actividad->usuario->nombre_completo : 'N/A',
                    $actividad->accion,
                    $actividad->modulo,
                    $actividad->target_tabla,
                    $actividad->target_id,
                    $actividad->exito ? 'Sí' : 'No',
                    $actividad->detalle,
                    $actividad->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}