<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'nombre'    => ['required', 'string', 'min:2', 'max:100'],
            'apellido'  => ['required', 'string', 'min:2', 'max:100'],
            'email'     => ['required', 'email', 'max:255', 'unique:usuario,email'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'roles'     => ['nullable', 'array'],
            'roles.*'   => ['integer', 'exists:rol,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre es obligatorio.',
            'nombre.min'         => 'El nombre debe tener al menos 2 caracteres.',
            'apellido.required'  => 'El apellido es obligatorio.',
            'apellido.min'       => 'El apellido debe tener al menos 2 caracteres.',
            'email.required'     => 'El correo electrónico es obligatorio.',
            'email.email'        => 'El correo electrónico no tiene un formato válido.',
            'email.unique'       => 'Este correo electrónico ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'roles.array'        => 'Los roles deben enviarse como arreglo.',
            'roles.*.exists'     => 'Uno o más roles seleccionados no existen.',
        ];
    }
}