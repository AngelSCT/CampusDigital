<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GastoTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'gastos_ticket';
    protected $primaryKey = 'id_gasto';
    const DELETED_AT      = 'deleted_at';

    protected $fillable = [
        'id_ticket',
        'id_insumo',
        'cantidad',
    ];

    protected $casts = [
        'id_ticket'  => 'integer',
        'id_insumo'  => 'integer',
        'cantidad'   => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }
}
