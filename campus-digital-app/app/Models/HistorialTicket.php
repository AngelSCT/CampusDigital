<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistorialTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'historial_tickets';
    protected $primaryKey = 'id_historial';
    const DELETED_AT      = 'deleted_at';

    protected $fillable = [
        'id_ticket',
        'id_usuario',
        'estado_nuevo',
    ];

    protected $casts = [
        'id_ticket'   => 'integer',
        'id_usuario'  => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }
}
