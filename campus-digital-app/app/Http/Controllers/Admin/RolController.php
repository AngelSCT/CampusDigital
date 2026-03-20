<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolController extends Controller
{
    public function index(Request $request)
    {
        $query = Rol::withCount('usuarios');

        if ($request->filled('search')) {
            $query->where('nombre', 'ilike', "%{$request->search}%");
        }

        $roles = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Create', [
            'permisos' => Permiso::where('activo', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:rol,nombre'],
            'descripcion' => ['nullable', 'string'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['exists:permiso,id'],
        ]);

        $rol = Rol::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? '',
            'activo' => true,
        ]);

        if (!empty($validated['permisos'])) {
            $rol->permisos()->attach($validated['permisos']);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Rol $rol)
    {
        $rol->load('permisos');
        
        return Inertia::render('Admin/Roles/Edit', [
            'rol' => $rol,
            'permisos' => Permiso::where('activo', true)->get(),
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:rol,nombre,' . $rol->id],
            'descripcion' => ['nullable', 'string'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['exists:permiso,id'],
        ]);

        $rol->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? '',
        ]);

        $rol->permisos()->sync($validated['permisos'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Rol $rol)
    {
        if ($rol->usuarios()->count() > 0) {
            return back()->withErrors(['error' => 'No puedes eliminar un rol que tiene usuarios asignados.']);
        }

        $rol->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado exitosamente.');
    }


        public function show(Rol $rol)
    {
        $rol->load([
            'permisos' => fn($q) => $q->orderBy('clave'),
            'usuarios' => fn($q) => $q->select(
                    'usuario.id', 'usuario.nombre', 'usuario.apellido',
                    'usuario.email', 'usuario.foto_url', 'usuario.bloqueado',
                    'usuario.ultimo_login_at', 'usuario.created_at'
                )
                ->withPivot('asignado_at', 'asignado_por_usuario_id')
                ->orderBy('usuario.nombre'),
        ]);
    
        $totalUsuarios      = $rol->usuarios()->count();
        $usuariosActivos    = $rol->usuarios()->where('bloqueado', false)->count();
        $usuariosBloqueados = $rol->usuarios()->where('bloqueado', true)->count();
    
        $ultimaAsignacion = \DB::table('usuario_rol')
            ->where('rol_id', $rol->id)
            ->whereNull('deleted_at')
            ->max('asignado_at');
    
        $asignadoresPorId = \DB::table('usuario_rol as ur')
            ->join('usuario as u', 'u.id', '=', 'ur.asignado_por_usuario_id')
            ->where('ur.rol_id', $rol->id)
            ->whereNull('ur.deleted_at')
            ->select('u.id', 'u.nombre', 'u.apellido', \DB::raw('count(*) as veces'))
            ->groupBy('u.id', 'u.nombre', 'u.apellido')
            ->orderByDesc('veces')
            ->get();
    
        $permisosPorModulo = $rol->permisos->groupBy(function ($p) {
            return explode('.', $p->clave)[0];
        });
    
        $actividadReciente = \DB::table('actividad_bitacora as ab')
            ->join('usuario as u', 'u.id', '=', 'ab.usuario_id')
            ->join('usuario_rol as ur', function ($join) use ($rol) {
                $join->on('ur.usuario_id', '=', 'ab.usuario_id')
                    ->where('ur.rol_id', $rol->id)
                    ->whereNull('ur.deleted_at');
            })
            ->select(
                'u.nombre', 'u.apellido', 'u.email',
                'ab.accion', 'ab.modulo', 'ab.created_at'
            )
            ->orderByDesc('ab.created_at')
            ->limit(10)
            ->get();
    
        return Inertia::render('Admin/Roles/Show', [
            'rol'               => $rol,
            'stats' => [
                'total_usuarios'      => $totalUsuarios,
                'usuarios_activos'    => $usuariosActivos,
                'usuarios_bloqueados' => $usuariosBloqueados,
                'total_permisos'      => $rol->permisos->count(),
                'ultima_asignacion'   => $ultimaAsignacion,
            ],
            'permisosPorModulo'   => $permisosPorModulo,
            'asignadores'         => $asignadoresPorId,
            'actividadReciente'   => $actividadReciente,
        ]);
    }
}