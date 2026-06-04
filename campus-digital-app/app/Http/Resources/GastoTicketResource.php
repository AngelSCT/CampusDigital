<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GastoTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_gasto'   => $this->id_gasto,
            'id_ticket'  => $this->id_ticket,
            'id_insumo'  => $this->id_insumo,
            'cantidad'   => $this->cantidad,
            'ticket'     => new TicketResource($this->whenLoaded('ticket')),
            'insumo'     => new InsumoResource($this->whenLoaded('insumo')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
