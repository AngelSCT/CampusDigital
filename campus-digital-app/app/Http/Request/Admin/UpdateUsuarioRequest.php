<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $usuarioId = $this->route('usuario');

        return [
            'nombre'   => ['required', 'string', 'min:2', 'max:100'],
            'apellido' => ['required', 'string', 'min:2', 'max:100'],
            'email'    => [
                'required',
                'email',
                'max:255',
                Rule::unique('usuario', 'email')->ignore($usuarioId),
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
            'roles'    => ['nullable', 'array'],
            'roles.*'  => ['integer', 'exists:rol,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre es obligatorio.',
            'nombre.min'        => 'El nombre debe tener al menos 2 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.min'      => 'El apellido debe tener al menos 2 caracteres.',
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El correo electrónico no tiene un formato válido.',
            'email.unique'      => 'Este correo electrónico ya está en uso por otro usuario.',
            'roles.array'       => 'Los roles deben enviarse como arreglo.',
            'roles.*.exists'    => 'Uno o más roles seleccionados no existen.',
        ];
    }
}