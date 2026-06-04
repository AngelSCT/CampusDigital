<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsignacionTecnicaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_asignacion'      => $this->id_asignacion,
            'id_ticket'          => $this->id_ticket,
            'id_usuario_tecnico' => $this->id_usuario_tecnico,
            'ticket'             => new TicketResource($this->whenLoaded('ticket')),
            'tecnico'            => new UsuarioResource($this->whenLoaded('tecnico')),
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
