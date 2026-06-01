<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'nombre'           => ['required', 'string', 'min:2', 'max:100'],
            'apellido'         => ['required', 'string', 'min:2', 'max:100'],
            'email'            => [
                'required',
                'email',
                'max:255',
                Rule::unique('usuario', 'email')->ignore($userId)->whereNull('deleted_at'),
            ],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'genero'           => ['nullable', 'string', 'in:masculino,femenino,otro,prefiero_no_decir'],
            'direccion'        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'         => 'El nombre es obligatorio.',
            'nombre.min'              => 'El nombre debe tener al menos 2 caracteres.',
            'apellido.required'       => 'El apellido es obligatorio.',
            'email.required'          => 'El correo electrónico es obligatorio.',
            'email.email'             => 'El formato del correo electrónico no es válido.',
            'email.unique'            => 'Este correo electrónico ya está en uso.',
            'fecha_nacimiento.date'   => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'genero.in'               => 'El género seleccionado no es válido.',
            'direccion.max'           => 'La dirección no puede exceder 500 caracteres.',
        ];
    }
}