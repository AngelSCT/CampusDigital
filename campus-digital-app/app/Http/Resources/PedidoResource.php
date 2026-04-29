<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'usuario_id'             => $this->usuario_id,
            'numero_folio'           => $this->numero_folio,
            'estado'                 => $this->estado,
            'modulo'                 => $this->modulo,
            'total'                  => $this->total,
            'descripcion'            => $this->descripcion,
            'notas'                  => $this->notas,
            'operador_usuario_id'    => $this->operador_usuario_id,
            'confirmado_con_tarjeta' => $this->confirmado_con_tarjeta,
            'confirmado_at'          => $this->confirmado_at,
            'tarjeta_lectura_id'     => $this->tarjeta_lectura_id,
            'cobrado_de_saldo'       => $this->cobrado_de_saldo,
            'saldo_movimiento_id'    => $this->saldo_movimiento_id,
            'meta_json'              => $this->meta_json,
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
            'usuario'                => new UsuarioResource($this->whenLoaded('usuario')),
            'operador'               => new UsuarioResource($this->whenLoaded('operador')),
        ];
    }
}
