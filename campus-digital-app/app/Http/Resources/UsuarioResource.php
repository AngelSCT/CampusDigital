<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'nombre'           => $this->nombre,
            'apellido'         => $this->apellido,
            'nombre_completo'  => $this->nombre_completo,
            'email'            => $this->email,
            'telefono'         => $this->telefono,
            'foto_url'         => $this->foto_url,
            'email_verificado' => $this->email_verificado,
            'bloqueado'        => $this->bloqueado,
            'bloqueado_hasta'  => $this->bloqueado_hasta,
            'tienda'           => $this->tienda,
            'matricula'        => $this->matricula,
            'ultimo_login_at'  => $this->ultimo_login_at,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'perfil'           => $this->whenLoaded('perfil'),
            'roles'            => RolResource::collection($this->whenLoaded('roles')),
        ];
    }
}
