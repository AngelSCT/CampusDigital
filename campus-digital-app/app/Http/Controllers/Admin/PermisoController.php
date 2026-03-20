<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermisoController extends Controller
{
    public function index(Request $request)
    {
        $query = Permiso::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('clave', 'ilike', "%{$request->search}%")
                  ->orWhere('descripcion', 'ilike', "%{$request->search}%");
            });
        }

        $permisos = $query->orderBy('clave')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Permisos/Index', [
            'permisos' => $permisos,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Permisos/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clave' => ['required', 'string', 'max:100', 'unique:permiso,clave'],
            'descripcion' => ['nullable', 'string'],
        ]);

        Permiso::create([
            'clave' => $validated['clave'],
            'descripcion' => $validated['descripcion'] ?? '',
            'activo' => true,
        ]);

        return redirect()->route('admin.permisos.index')->with('success', 'Permiso creado exitosamente.');
    }

    public function edit(Permiso $permiso)
    {
        return Inertia::render('Admin/Permisos/Edit', [
            'permiso' => $permiso,
        ]);
    }

    public function update(Request $request, Permiso $permiso)
    {
        $validated = $request->validate([
            'clave' => ['required', 'string', 'max:100', 'unique:permiso,clave,' . $permiso->id],
            'descripcion' => ['nullable', 'string'],
        ]);

        $permiso->update([
            'clave' => $validated['clave'],
            'descripcion' => $validated['descripcion'] ?? '',
        ]);

        return redirect()->route('admin.permisos.index')->with('success', 'Permiso actualizado exitosamente.');
    }

    public function destroy(Permiso $permiso)
    {
        $permiso->delete();

        return redirect()->route('admin.permisos.index')->with('success', 'Permiso eliminado exitosamente.');
    }


    public function show(Permiso $permiso)
    {
        $permiso->load([
            'roles' => fn($q) => $q
                ->withCount('usuarios')
                ->orderBy('nombre'),
        ]);
    
        $totalRoles         = $permiso->roles->count();
        $rolesActivos       = $permiso->roles->where('activo', true)->count();
        $totalUsuariosAfect = $permiso->roles->sum('usuarios_count');
    
        $modulo = explode('.', $permiso->clave)[0] ?? $permiso->clave;
    
        $permisosHermanos = Permiso::where('clave', 'like', $modulo . '.%')
            ->where('id', '!=', $permiso->id)
            ->where('activo', true)
            ->orderBy('clave')
            ->get(['id', 'clave', 'descripcion']);
    
        $actividadReciente = DB::table('actividad_bitacora as ab')
            ->join('usuario as u', 'u.id', '=', 'ab.usuario_id')
            ->where('ab.accion', 'like', '%' . $permiso->clave . '%')
            ->select(
                'u.nombre', 'u.apellido', 'u.email',
                'ab.accion', 'ab.modulo', 'ab.exito', 'ab.ip', 'ab.created_at'
            )
            ->orderByDesc('ab.created_at')
            ->limit(10)
            ->get();
    
        $usuariosConAcceso = DB::table('usuario_rol as ur')
            ->join('rol_permiso as rp', 'rp.rol_id', '=', 'ur.rol_id')
            ->where('rp.permiso_id', $permiso->id)
            ->whereNull('ur.deleted_at')
            ->whereNull('rp.deleted_at')
            ->distinct('ur.usuario_id')
            ->count('ur.usuario_id');
    
        return Inertia::render('Admin/Permisos/Show', [
            'permiso'           => $permiso,
            'stats' => [
                'total_roles'           => $totalRoles,
                'roles_activos'         => $rolesActivos,
                'total_usuarios_afect'  => $totalUsuariosAfect,
                'usuarios_con_acceso'   => $usuariosConAcceso,
                'modulo'                => $modulo,
            ],
            'permisosHermanos'  => $permisosHermanos,
            'actividadReciente' => $actividadReciente,
        ]);
    }
}