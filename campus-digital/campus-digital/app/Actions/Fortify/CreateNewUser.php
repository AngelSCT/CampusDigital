<?php

namespace App\Actions\Fortify;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): Usuario
    {
        Validator::make($input, [
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuario', 'email'),
            ],
            'password' => $this->passwordRules(),
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe proporcionar un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
        ])->validate();

        $usuario = Usuario::create([
            'nombre' => $input['nombre'],
            'apellido' => $input['apellido'],
            'email' => $input['email'],
            'password_hash' => Hash::make($input['password']),
        ]);

        // Asignar rol de estudiante por defecto
        $rolEstudiante = \App\Models\Rol::where('nombre', 'estudiante')->first();
        if ($rolEstudiante) {
            $usuario->roles()->attach($rolEstudiante->id);
        }

        // Crear perfil básico
        $usuario->perfil()->create([]);

        return $usuario;
    }
}