<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function show()
    {
        $usuario = auth()->user();
        $usuario->load(['roles', 'perfil']);

        return Inertia::render('Perfil/Show', [
            'usuario' => $usuario,
        ]);
    }

    public function updateProfile(Request $request)
    {
        
        $usuario = auth()->user();

        $validated = $request->validate([
            'nombre'           => ['nullable', 'string', 'max:120'],
            'apellido'         => ['nullable', 'string', 'max:120'],
            'email'            => ['nullable', 'email', 'unique:usuario,email,' . $usuario->id],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'genero'           => ['nullable', 'string', 'max:30'],
            'direccion'        => ['nullable', 'string', 'max:500'],
            'telefono'         => ['nullable', 'string', 'max:30'],
        ]);

        $camposUsuario = ['telefono' => $validated['telefono'] ?? ''];

        if ($usuario->hasRole('administrador')) {
            $camposUsuario['nombre']   = $validated['nombre']   ?? $usuario->nombre;
            $camposUsuario['apellido'] = $validated['apellido'] ?? $usuario->apellido;
            $camposUsuario['email']    = $validated['email']    ?? $usuario->email;
        }

        $usuario->update($camposUsuario);

        $usuario->perfil()->updateOrCreate(
            ['usuario_id' => $usuario->id],
            [
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'genero'           => $validated['genero']           ?? '',
                'direccion'        => $validated['direccion']        ?? '',
            ]
        );

        return back()->with('success', 'Perfil actualizado exitosamente.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'], 
        ]);

        $usuario = auth()->user();

        if ($usuario->foto_url && Storage::disk('public')->exists($usuario->foto_url)) {
            Storage::disk('public')->delete($usuario->foto_url);
        }

        $path = $request->file('photo')->store('fotos-perfil', 'public');

        $usuario->update([
            'foto_url' => $path,
        ]);

        return back()->with('success', 'Foto de perfil actualizada exitosamente.');
    }

    public function deletePhoto()
    {
        $usuario = auth()->user();

        if ($usuario->foto_url && Storage::disk('public')->exists($usuario->foto_url)) {
            Storage::disk('public')->delete($usuario->foto_url);
        }

        $usuario->update([
            'foto_url' => '',
        ]);

        return back()->with('success', 'Foto de perfil eliminada exitosamente.');
    }
}