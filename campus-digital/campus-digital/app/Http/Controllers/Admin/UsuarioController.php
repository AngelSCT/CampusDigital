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

        // Aquí podrías enviar un email al usuario con la nueva contraseña
        // Mail::to($usuario->email)->send(new PasswordResetMail($newPassword));

        // Registrar en bitácora
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
}