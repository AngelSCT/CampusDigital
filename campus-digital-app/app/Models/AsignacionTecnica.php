<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignacionTecnica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'asignaciones_tecnicas';
    protected $primaryKey = 'id_asignacion';
    const DELETED_AT      = 'deleted_at';

    protected $fillable = [
        'id_ticket',
        'id_usuario_tecnico',
    ];

    protected $casts = [
        'id_ticket'          => 'integer',
        'id_usuario_tecnico' => 'integer',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_tecnico', 'id');
    }
}
