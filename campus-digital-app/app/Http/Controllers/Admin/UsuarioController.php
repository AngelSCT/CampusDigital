<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::with(['roles']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('apellido', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('rol')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('rol.id', $request->rol);
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $query->where('bloqueado', false);
            } elseif ($request->estado === 'bloqueado') {
                $query->where('bloqueado', true);
            }
        }

        if ($request->filled('verificado')) {
            $query->where('email_verificado', $request->verificado === 'si');
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $usuarios = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => Rol::where('activo', true)->get(),
            'filters' => $request->only(['search', 'rol', 'estado', 'verificado', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Usuarios/Create', [
            'roles' => Rol::where('activo', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:usuario,email'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['required', Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:rol,id'],
        ]);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? '',
            'password_hash' => Hash::make($validated['password']),
            'email_verificado' => false,
            'bloqueado' => false,
        ]);

        $usuario->roles()->attach($validated['roles']);

        // Registrar en bitácora
        \App\Models\ActividadBitacora::create([
            'usuario_id' => auth()->id(),
            'accion' => 'crear_usuario',
            'modulo' => 'seguridad',
            'target_tabla' => 'usuario',
            'target_id' => $usuario->id,
            'detalle' => "Usuario creado: {$usuario->email}",
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(Usuario $usuario)
    {
        $usuario->load('roles');
        
        return Inertia::render('Admin/Usuarios/Edit', [
            'usuario' => $usuario,
            'roles' => Rol::where('activo', true)->get(),
        ]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:usuario,email,' . $usuario->id],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:rol,id'],
        ]);

        $updateData = [
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? '',
        ];

        if (!empty($validated['password'])) {
            $updateData['password_hash'] = Hash::make($validated['password']);
        }

        $usuario->update($updateData);
        $usuario->roles()->sync($validated['roles']);

        // Registrar en bitácora
        \App\Models\ActividadBitacora::create([
            'usuario_id' => auth()->id(),
            'accion' => 'actualizar_usuario',
            'modulo' => 'seguridad',
            'target_tabla' => 'usuario',
            'target_id' => $usuario->id,
            'detalle' => "Usuario actualizado: {$usuario->email}",
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(Request $request, Usuario $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta.']);
        }

        $email = $usuario->email;
        $usuario->delete();

        // Registrar en bitácora
        \App\Models\ActividadBitacora::create([
            'usuario_id' => auth()->id(),
            'accion' => 'eliminar_usuario',
            'modulo' => 'seguridad',
            'target_tabla' => 'usuario',
            'target_id' => $usuario->id,
            'detalle' => "Usuario eliminado: {$email}",
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
    }

    public function toggleBlock(Request $request, Usuario $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes bloquearte a ti mismo.']);
        }

        $usuario->update([
            'bloqueado' => !$usuario->bloqueado,
            'bloqueado_hasta' => !$usuario->bloqueado ? now()->addDays(30) : null,
        ]);

        $accion = $usuario->bloqueado ? 'bloqueado' : 'desbloqueado';

        // Registrar en bitácora
        \App\Models\ActividadBitacora::create([
            'usuario_id' => auth()->id(),
            'accion' => 'cambiar_estado_usuario',
            'modulo' => 'seguridad',
            'target_tabla' => 'usuario',
            'target_id' => $usuario->id,
            'detalle' => "Usuario {$accion}: {$usuario->email}",
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Usuario {$accion} exitosamente.");
    }

    public function resetPassword(Request $request, Usuario $usuario)
    {
        $newPassword = \Str::random(12);
        
        $usuario->update([
            'password_hash' => Hash::make($newPassword),
        ]);


        \App\Models\ActividadBitacora::create([
            'usuario_id' => auth()->id(),
            'accion' => 'resetear_password',
            'modulo' => 'seguridad',
            'target_tabla' => 'usuario',
            'target_id' => $usuario->id,
            'detalle' => "Password reseteado para: {$usuario->email}",
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Contraseña reseteada. Nueva contraseña: {$newPassword}");
    }

    public function export(Request $request)
    {
        $usuarios = Usuario::with('roles')->get();

        $csvData = "ID,Nombre,Apellido,Email,Teléfono,Roles,Estado,Email Verificado,Fecha Creación\n";
        
        foreach ($usuarios as $usuario) {
            $roles = $usuario->roles->pluck('nombre')->join(', ');
            $estado = $usuario->bloqueado ? 'Bloqueado' : 'Activo';
            $verificado = $usuario->email_verificado ? 'Sí' : 'No';
            
            $csvData .= "{$usuario->id},{$usuario->nombre},{$usuario->apellido},{$usuario->email},{$usuario->telefono},\"{$roles}\",{$estado},{$verificado},{$usuario->created_at}\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="usuarios_' . now()->format('Y-m-d') . '.csv"');
    }

public function exportByRole(Request $request)
{
    $filename = 'usuarios_por_rol_' . now()->format('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];

    $callback = function() {
        $file = fopen('php://output', 'w');
        
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Obtener todos los roles activos
        $roles = Rol::where('activo', true)->orderBy('nombre')->get();
        
        foreach ($roles as $rol) {
            // Encabezado del rol
            fputcsv($file, ["ROL: " . strtoupper($rol->nombre)]);
            fputcsv($file, ['']); // Línea vacía
            
            // Encabezados de columnas
            fputcsv($file, [
                'ID',
                'Nombre Completo',
                'Email',
                'Teléfono',
                'Estado',
                'Email Verificado',
                'Fecha de Registro',
                'Último Acceso'
            ]);
            
            // Obtener usuarios de este rol
            $usuarios = Usuario::whereHas('roles', function($query) use ($rol) {
                $query->where('rol.id', $rol->id);
            })
            ->orderBy('nombre')
            ->get();
            
            // Datos de usuarios
            foreach ($usuarios as $usuario) {
                fputcsv($file, [
                    $usuario->id,
                    $usuario->nombre . ' ' . $usuario->apellido,
                    $usuario->email,
                    $usuario->telefono ?? 'N/A',
                    $usuario->bloqueado ? 'Bloqueado' : 'Activo',
                    $usuario->email_verificado ? 'Sí' : 'No',
                    $usuario->created_at->format('d/m/Y H:i'),
                    $usuario->last_login_at ? $usuario->last_login_at->format('d/m/Y H:i') : 'Nunca'
                ]);
            }
            
            // Resumen del rol
            fputcsv($file, ['']); // Línea vacía
            fputcsv($file, ['Total de usuarios en este rol:', count($usuarios)]);
            fputcsv($file, ['']); // Línea vacía
            fputcsv($file, ['']); // Separador entre roles
        }
        
        // Resumen general
        fputcsv($file, ['=== RESUMEN GENERAL ===']);
        fputcsv($file, ['']); // Línea vacía
        fputcsv($file, ['Rol', 'Cantidad de Usuarios', 'Activos', 'Bloqueados', 'Email Verificado']);
        
        foreach ($roles as $rol) {
            $totalUsuarios = Usuario::whereHas('roles', function($query) use ($rol) {
                $query->where('rol.id', $rol->id);
            })->count();
            
            $activos = Usuario::whereHas('roles', function($query) use ($rol) {
                $query->where('rol.id', $rol->id);
            })->where('bloqueado', false)->count();
            
            $bloqueados = Usuario::whereHas('roles', function($query) use ($rol) {
                $query->where('rol.id', $rol->id);
            })->where('bloqueado', true)->count();
            
            $verificados = Usuario::whereHas('roles', function($query) use ($rol) {
                $query->where('rol.id', $rol->id);
            })->where('email_verificado', true)->count();
            
            fputcsv($file, [
                $rol->nombre,
                $totalUsuarios,
                $activos,
                $bloqueados,
                $verificados
            ]);
        }
        
        fputcsv($file, ['']); // Línea vacía
        fputcsv($file, ['Total general de usuarios:', Usuario::count()]);
        fputcsv($file, ['Usuarios activos:', Usuario::where('bloqueado', false)->count()]);
        fputcsv($file, ['Usuarios bloqueados:', Usuario::where('bloqueado', true)->count()]);
        fputcsv($file, ['Emails verificados:', Usuario::where('email_verificado', true)->count()]);
        fputcsv($file, ['']); // Línea vacía
        fputcsv($file, ['Reporte generado el:', now()->format('d/m/Y H:i:s')]);
        fputcsv($file, ['Generado por:', auth()->user()->nombre . ' ' . auth()->user()->apellido]);
        
        fclose($file);
    };

    // Registrar en bitácora
    \App\Models\ActividadBitacora::create([
        'usuario_id' => auth()->id(),
        'accion' => 'exportar_usuarios_por_rol',
        'modulo' => 'seguridad',
        'target_tabla' => 'usuario',
        'detalle' => 'Exportación de reporte de usuarios agrupados por rol',
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    return response()->stream($callback, 200, $headers);
}

public function exportPdf(Request $request)
{
    $usuarios = Usuario::with('roles')->orderBy('nombre')->get();
    
    $pdf = \PDF::loadView('pdf.usuarios', [
        'usuarios' => $usuarios,
        'titulo' => 'Lista de Usuarios',
        'fecha' => now()->format('d/m/Y H:i:s'),
        'generadoPor' => auth()->user()->nombre . ' ' . auth()->user()->apellido
    ]);

    \App\Models\ActividadBitacora::create([
        'usuario_id' => auth()->id(),
        'accion' => 'exportar_usuarios_pdf',
        'modulo' => 'seguridad',
        'target_tabla' => 'usuario',
        'detalle' => 'Exportación de usuarios en formato PDF',
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    return $pdf->download('usuarios_' . now()->format('Y-m-d_His') . '.pdf');
}

public function exportByRolePdf(Request $request)
{
    $roles = Rol::where('activo', true)->orderBy('nombre')->get();
    
    $datosRoles = [];
    $estadisticas = [
        'total' => Usuario::count(),
        'activos' => Usuario::where('bloqueado', false)->count(),
        'bloqueados' => Usuario::where('bloqueado', true)->count(),
        'verificados' => Usuario::where('email_verificado', true)->count(),
    ];
    
    foreach ($roles as $rol) {
        $usuarios = Usuario::whereHas('roles', function($query) use ($rol) {
            $query->where('rol.id', $rol->id);
        })->orderBy('nombre')->get();
        
        $datosRoles[] = [
            'rol' => $rol,
            'usuarios' => $usuarios,
            'total' => $usuarios->count(),
            'activos' => $usuarios->where('bloqueado', false)->count(),
            'bloqueados' => $usuarios->where('bloqueado', true)->count(),
            'verificados' => $usuarios->where('email_verificado', true)->count(),
        ];
    }
    
    $pdf = \PDF::loadView('pdf.usuarios-por-rol', [
        'datosRoles' => $datosRoles,
        'estadisticas' => $estadisticas,
        'titulo' => 'Reporte de Usuarios por Rol',
        'fecha' => now()->format('d/m/Y H:i:s'),
        'generadoPor' => auth()->user()->nombre . ' ' . auth()->user()->apellido
    ]);

    $pdf->setPaper('A4', 'portrait');

    // Registrar en bitácora
    \App\Models\ActividadBitacora::create([
        'usuario_id' => auth()->id(),
        'accion' => 'exportar_usuarios_por_rol_pdf',
        'modulo' => 'seguridad',
        'target_tabla' => 'usuario',
        'detalle' => 'Exportación de reporte de usuarios por rol en formato PDF',
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    return $pdf->download('usuarios_por_rol_' . now()->format('Y-m-d_His') . '.pdf');
}
}