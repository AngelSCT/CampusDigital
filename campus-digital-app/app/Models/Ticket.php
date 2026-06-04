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
        'costo_total',
        'carrito_uuid',
        'estado_pago',
        'fecha_pago',
    ];

    protected $casts = [
        'id_usuario_solicitante' => 'integer',
        'id_categoria'           => 'integer',
        'id_equipo'              => 'integer',
        'fecha_creacion'         => 'datetime',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
        'deleted_at'             => 'datetime',
        'costo_total'            => 'decimal:2',
        'fecha_pago'             => 'datetime',
    ];

    const PAGO_SIN_COBRO  = 'sin_cobro';
    const PAGO_PENDIENTE  = 'pendiente_pago';
    const PAGO_PAGADO     = 'pagado';
    const PAGO_CANCELADO  = 'cancelado';

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

    public function gastos()
    {
        return $this->hasMany(GastoTicket::class, 'id_ticket', 'id_ticket');
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionTecnica::class, 'id_ticket', 'id_ticket');
    }

    public function historial()
    {
        return $this->hasMany(HistorialTicket::class, 'id_ticket', 'id_ticket');
    }

    public function calcularCostoTotal()
    {
        $this->load('gastos.insumo');
        $total = 0;
        foreach ($this->gastos as $gasto) {
            $total += $gasto->cantidad * ($gasto->insumo->precio_unitario ?? 0);
        }
        $this->costo_total = $total;
        $this->save();
        return $total;
    }
}
