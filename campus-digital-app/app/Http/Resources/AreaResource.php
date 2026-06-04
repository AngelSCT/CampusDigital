<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_area'    => $this->id_area,
            'name_area'  => $this->name_area,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
