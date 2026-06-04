<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_categoria'     => $this->id_categoria,
            'id_area'          => $this->id_area,
            'nombre_categoria' => $this->nombre_categoria,
            'tiempo_sla_horas' => $this->tiempo_sla_horas,
            'area'             => new AreaResource($this->whenLoaded('area')),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
