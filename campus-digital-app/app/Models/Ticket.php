<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'tickets';
    protected $primaryKey = 'id_ticket';
    const DELETED_AT      = 'deleted_at';

    protected $fillable = [
        'id_usuario_solicitante',
        'id_categoria',
        'id_equipo',
        'estado',
        'prioridad',
        'fecha_creacion',
    ];

    protected $casts = [
        'id_usuario_solicitante' => 'integer',
        'id_categoria'           => 'integer',
        'id_equipo'              => 'integer',
        'fecha_creacion'         => 'datetime',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
        'deleted_at'             => 'datetime',
    ];

    public function usuarioSolicitante()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_solicitante', 'id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaTicket::class, 'id_categoria', 'id_categoria');
    }

    public function equipo()
    {
        return $this->belongsTo(EquipoActivo::class, 'id_equipo', 'id_equipo');
    }
}
