<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AgregarItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'categoria_slug'     => ['required', 'string'],
            'referencia_externa' => ['required', 'string', 'max:180'],
            'nombre'             => ['required', 'string', 'max:255'],
            'precio_unitario'    => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'cantidad'           => ['required', 'integer', 'min:1'],
            'duracion_horas'     => ['nullable', 'integer', 'min:1'],
            'metadata'           => ['nullable', 'array'],
        ];
    }
}
