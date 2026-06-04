<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistorialTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_historial' => $this->id_historial,
            'id_ticket'    => $this->id_ticket,
            'id_usuario'   => $this->id_usuario,
            'estado_nuevo' => $this->estado_nuevo,
            'ticket'       => new TicketResource($this->whenLoaded('ticket')),
            'usuario'      => new UsuarioResource($this->whenLoaded('usuario')),
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
