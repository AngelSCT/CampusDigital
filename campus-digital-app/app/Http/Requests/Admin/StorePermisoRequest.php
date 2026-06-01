<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermisoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $permisoId = $this->route('permiso');

        return [
            'clave' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                $permisoId
                    ? Rule::unique('permiso', 'clave')->ignore($permisoId)->whereNull('deleted_at')
                    : Rule::unique('permiso', 'clave')->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo'      => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'clave.required' => 'La clave del permiso es obligatoria.',
            'clave.max'      => 'La clave no puede tener más de 100 caracteres.',
            'clave.regex'    => 'La clave solo puede contener letras minúsculas, números y guiones bajos.',
            'clave.unique'   => 'Ya existe un permiso con esta clave.',
        ];
    }
}