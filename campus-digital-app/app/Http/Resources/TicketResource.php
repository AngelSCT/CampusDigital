<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_ticket'               => $this->id_ticket,
            'id_usuario_solicitante'  => $this->id_usuario_solicitante,
            'id_categoria'            => $this->id_categoria,
            'id_equipo'               => $this->id_equipo,
            'estado'                  => $this->estado,
            'prioridad'               => $this->prioridad,
            'fecha_creacion'          => $this->fecha_creacion,
            'costo_total'             => $this->costo_total,
            'carrito_uuid'            => $this->carrito_uuid,
            'estado_pago'             => $this->estado_pago,
            'fecha_pago'              => $this->fecha_pago,
            'usuario_solicitante'     => new UsuarioResource($this->whenLoaded('usuarioSolicitante')),
            'categoria'               => new CategoriaTicketResource($this->whenLoaded('categoria')),
            'equipo'                  => new EquipoActivoResource($this->whenLoaded('equipo')),
            'gastos'                  => GastoTicketResource::collection($this->whenLoaded('gastos')),
            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,
        ];
    }
}
