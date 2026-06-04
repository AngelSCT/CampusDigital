<?php

namespace App\Models\Cart;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudModulo extends Model
{
    protected $table = 'cart_solicitudes_modulo';

    protected $fillable = [
        'folio',
        'nombre_modulo',
        'tipo_modulo',
        'categorias_solicitadas',
        'contacto_nombre',
        'contacto_email',
        'descripcion',
        'estado',
        'motivo_rechazo',
        'revisado_por',
        'revisado_at',
    ];

    protected $casts = [
        'categorias_solicitadas' => 'array',
        'revisado_at'            => 'datetime',
    ];

    const ESTADO_PENDIENTE  = 'pendiente';
    const ESTADO_APROBADA   = 'aprobada';
    const ESTADO_RECHAZADA  = 'rechazada';

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'revisado_por');
    }

    public function moduloCliente(): HasOne
    {
        return $this->hasOne(ModuloCliente::class, 'solicitud_id');
    }
}
