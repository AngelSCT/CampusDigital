<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Usuario;
use App\Models\AccesoBitacora;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $usuariosActivos = Usuario::whereNull('deleted_at')
            ->where('bloqueado', false)
            ->count();

        $usuariosPorRol = DB::table('usuario')
            ->join('usuario_rol', 'usuario.id', '=', 'usuario_rol.usuario_id')
            ->join('rol', 'usuario_rol.rol_id', '=', 'rol.id')
            ->whereNull('usuario.deleted_at')
            ->whereNull('usuario_rol.deleted_at')
            ->select('rol.nombre', DB::raw('count(*) as total'))
            ->groupBy('rol.nombre')
            ->get();

        $intentosAcceso = AccesoBitacora::select('exito', DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('exito')
            ->get();

        $actividadReciente = AccesoBitacora::with('usuario')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'usuarios_activos' => $usuariosActivos,
                'usuarios_por_rol' => $usuariosPorRol,
                'intentos_acceso' => $intentosAcceso,
                'actividad_reciente' => $actividadReciente,
            ],
        ]);
    }
}