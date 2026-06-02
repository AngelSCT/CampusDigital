<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecargaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'usuario_id'          => $this->usuario_id,
            'monto'               => $this->monto,
            'metodo_pago'         => $this->metodo_pago,
            'estado'              => $this->estado,
            'referencia_pago'     => $this->referencia_pago,
            'razon_fallo'         => $this->razon_fallo,
            'saldo_movimiento_id' => $this->saldo_movimiento_id,
            'meta_json'           => $this->meta_json,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'usuario'             => new UsuarioResource($this->whenLoaded('usuario')),
        ];
    }
}
