<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipoActivoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_equipo'     => $this->id_equipo,
            'id_categoria'  => $this->id_categoria,
            'id_ubicacion'  => $this->id_ubicacion,
            'nombre_equipo' => $this->nombre_equipo,
            'estado_actual' => $this->estado_actual,
            'categoria'     => new CategoriaTicketResource($this->whenLoaded('categoria')),
            'ubicacion'     => new UbicacionResource($this->whenLoaded('ubicacion')),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
