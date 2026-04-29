<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CrearCarritoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'usuario_ref'       => ['required', 'string', 'max:120'],
            'requiere_saldo'    => ['nullable', 'boolean'],
            'expira_en_minutos' => ['nullable', 'integer', 'min:1'],
            'metadata'          => ['nullable', 'array'],
        ];
    }
}
