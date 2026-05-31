<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\UsuarioPerfil;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Servicio de gestión del perfil de usuario.
 * Maneja actualización de datos personales y foto de perfil.
 */
class PerfilService
{
    /**
     * Actualiza los datos del perfil extendido de un usuario.
     */
    public function actualizarPerfil(Usuario $usuario, array $datos): UsuarioPerfil
    {
        $perfil = $usuario->perfil ?? UsuarioPerfil::create(['usuario_id' => $usuario->id]);

        $perfil->update(array_filter([
            'fecha_nacimiento'  => $datos['fecha_nacimiento'] ?? null,
            'genero'            => $datos['genero']           ?? null,
            'direccion'         => $datos['direccion']        ?? null,
            'preferencias_json' => $datos['preferencias']     ?? null,
        ]));

        return $perfil;
    }

    /**
     * Sube y asocia una foto de perfil al usuario.
     */
    public function actualizarFoto(Usuario $usuario, UploadedFile $foto): string
    {
        if ($usuario->foto_url) {
            Storage::disk('public')->delete($usuario->foto_url);
        }

        $ruta = $foto->store("fotos/{$usuario->id}", 'public');

        $usuario->update(['foto_url' => $ruta]);

        return $ruta;
    }

    /**
     * Elimina la foto de perfil del usuario.
     */
    public function eliminarFoto(Usuario $usuario): void
    {
        if ($usuario->foto_url) {
            Storage::disk('public')->delete($usuario->foto_url);
            $usuario->update(['foto_url' => null]);
        }
    }

    /**
     * Guarda preferencias del usuario en formato JSON.
     */
    public function guardarPreferencias(Usuario $usuario, array $preferencias): void
    {
        $perfil = $usuario->perfil ?? UsuarioPerfil::create(['usuario_id' => $usuario->id]);
        $perfil->update(['preferencias_json' => $preferencias]);
    }
}