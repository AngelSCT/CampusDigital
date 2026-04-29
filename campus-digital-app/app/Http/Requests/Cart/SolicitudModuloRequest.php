<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SolicitudModuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_modulo'            => ['required', 'string', 'max:120'],
            'tipo_modulo'              => ['required', 'string', 'max:60'],
            'categorias_solicitadas'   => ['required', 'array', 'min:1'],
            'categorias_solicitadas.*' => ['required', 'string', Rule::exists('cart_categorias', 'slug')],
            'contacto_nombre'          => ['required', 'string', 'max:120'],
            'contacto_email'           => ['required', 'email', 'max:180'],
            'descripcion'              => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'categorias_solicitadas.*.exists' => 'Uno o más slugs de categoría no existen.',
        ];
    }
}
