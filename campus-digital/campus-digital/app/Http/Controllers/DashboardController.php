<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\AccesoBitacora;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('roles');
        
        // Dashboard diferenciado por rol
        if ($user->hasRole('administrador')) {
            return $this->dashboardAdministrador($user);
        } elseif ($user->hasRole('proveedor_area')) {
            return $this->dashboardProveedor($user);
        } else {
            return $this->dashboardEstudiante($user);
        }
    }

    private function dashboardAdministrador($user)
    {
        $stats = [
            'usuarios_activos' => Usuario::where('bloqueado', false)->count(),
            'usuarios_por_rol' => DB::table('usuario')
                ->join('usuario_rol', 'usuario.id', '=', 'usuario_rol.usuario_id')
                ->join('rol', 'usuario_rol.rol_id', '=', 'rol.id')
                ->select('rol.nombre', DB::raw('count(*) as total'))
                ->whereNull('usuario.deleted_at')
                ->groupBy('rol.nombre')
                ->get(),
            'actividad_reciente' => AccesoBitacora::orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        $accesos = AccesoBitacora::where('created_at', '>=', now()->subDays(7))->get();
        $stats['accesos_exitosos'] = $accesos->where('exito', true)->count();
        $stats['accesos_fallidos'] = $accesos->where('exito', false)->count();

        return Inertia::render('Dashboard/Admin', [
            'stats' => $stats,
        ]);
    }

    private function dashboardProveedor($user)
    {
        return Inertia::render('Dashboard/Proveedor', [
            // Aquí puedes agregar stats del proveedor cuando tengas los módulos
        ]);
    }

    private function dashboardEstudiante($user)
    {
        return Inertia::render('Dashboard/Estudiante', [
            // Aquí puedes agregar stats del estudiante cuando tengas los módulos
        ]);
    }
}