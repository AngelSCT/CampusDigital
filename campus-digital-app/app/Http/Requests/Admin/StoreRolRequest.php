<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        $rolId = $this->route('rol');

        return [
            'nombre'      => [
                'required',
                'string',
                'min:2',
                'max:100',
                $rolId
                    ? Rule::unique('rol', 'nombre')->ignore($rolId)->whereNull('deleted_at')
                    : Rule::unique('rol', 'nombre')->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo'      => ['boolean'],
            'permisos'    => ['nullable', 'array'],
            'permisos.*'  => ['integer', 'exists:permiso,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre del rol es obligatorio.',
            'nombre.min'        => 'El nombre del rol debe tener al menos 2 caracteres.',
            'nombre.unique'     => 'Ya existe un rol con este nombre.',
            'permisos.array'    => 'Los permisos deben enviarse como arreglo.',
            'permisos.*.exists' => 'Uno o más permisos seleccionados no existen.',
        ];
    }
}