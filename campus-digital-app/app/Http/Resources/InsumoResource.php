<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsumoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_insumo'     => $this->id_insumo,
            'nombre_insumo' => $this->nombre_insumo,
            'stock_actual'  => $this->stock_actual,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
