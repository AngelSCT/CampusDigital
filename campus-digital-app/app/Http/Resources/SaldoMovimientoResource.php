<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaldoMovimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'usuario_id'          => $this->usuario_id,
            'saldo_monedero_id'   => $this->saldo_monedero_id,
            'tipo'                => $this->tipo,
            'monto'               => $this->monto,
            'saldo_anterior'      => $this->saldo_anterior,
            'saldo_nuevo'         => $this->saldo_nuevo,
            'modulo'              => $this->modulo,
            'concepto'            => $this->concepto,
            'referencia_tabla'    => $this->referencia_tabla,
            'referencia_id'       => $this->referencia_id,
            'operador_usuario_id' => $this->operador_usuario_id,
            'tarjeta_lectura_id'  => $this->tarjeta_lectura_id,
            'meta_json'           => $this->meta_json,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'usuario'             => new UsuarioResource($this->whenLoaded('usuario')),
            'operador'            => new UsuarioResource($this->whenLoaded('operador')),
        ];
    }
}
