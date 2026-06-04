<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaldoMonederoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'usuario_id'        => $this->usuario_id,
            'saldo_disponible'  => $this->saldo_disponible,
            'saldo_retenido'    => $this->saldo_retenido,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'usuario'           => new UsuarioResource($this->whenLoaded('usuario')),
        ];
    }
}
