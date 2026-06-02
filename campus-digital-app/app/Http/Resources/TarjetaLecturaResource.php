<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TarjetaLecturaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'tarjeta_id'          => $this->tarjeta_id,
            'uid_leido'           => $this->uid_leido,
            'modulo'              => $this->modulo,
            'tipo_lectura'        => $this->tipo_lectura,
            'exito'               => $this->exito,
            'detalle'             => $this->detalle,
            'ip'                  => $this->ip,
            'user_agent'          => $this->user_agent,
            'operador_usuario_id' => $this->operador_usuario_id,
            'pedido_id'           => $this->pedido_id,
            'meta_json'           => $this->meta_json,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'tarjeta'             => new TarjetaUniversitariaResource($this->whenLoaded('tarjeta')),
            'operador'            => new UsuarioResource($this->whenLoaded('operador')),
        ];
    }
}
