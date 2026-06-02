<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MantenimientoPreventivoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_preventivo'            => $this->id_preventivo,
            'id_equipo'                => $this->id_equipo,
            'proxima_fecha_programada' => $this->proxima_fecha_programada,
            'equipo'                   => new EquipoActivoResource($this->whenLoaded('equipo')),
            'created_at'               => $this->created_at,
            'updated_at'               => $this->updated_at,
        ];
    }
}
