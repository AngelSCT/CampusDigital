<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TarjetaUniversitariaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'usuario_id'                => $this->usuario_id,
            'uid'                       => $this->uid,
            'estado'                    => $this->estado,
            'motivo_bloqueo'            => $this->motivo_bloqueo,
            'registrado_por_usuario_id' => $this->registrado_por_usuario_id,
            'bloqueado_por_usuario_id'  => $this->bloqueado_por_usuario_id,
            'bloqueado_at'              => $this->bloqueado_at,
            'meta_json'                 => $this->meta_json,
            'created_at'               => $this->created_at,
            'updated_at'               => $this->updated_at,
            'usuario'                   => new UsuarioResource($this->whenLoaded('usuario')),
            'registrado_por'            => new UsuarioResource($this->whenLoaded('registradoPor')),
            'bloqueado_por'             => new UsuarioResource($this->whenLoaded('bloqueadoPor')),
        ];
    }
}
