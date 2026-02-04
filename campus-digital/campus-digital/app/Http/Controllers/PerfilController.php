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
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'genero' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $usuario->update([
            'telefono' => $validated['telefono'] ?? '',
        ]);

        if ($usuario->perfil) {
            $usuario->perfil->update([
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'genero' => $validated['genero'] ?? '',
                'direccion' => $validated['direccion'] ?? '',
            ]);
        }

        return back()->with('success', 'Perfil actualizado exitosamente.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'], // 2MB max
        ]);

        $usuario = auth()->user();

        // Eliminar foto anterior si existe
        if ($usuario->foto_url && Storage::disk('public')->exists($usuario->foto_url)) {
            Storage::disk('public')->delete($usuario->foto_url);
        }

        // Guardar nueva foto
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