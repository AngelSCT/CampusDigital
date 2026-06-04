<?php

namespace App\Models\Cart;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenModulo extends Model
{
    protected $table = 'cart_tokens_modulo';

    protected $fillable = [
        'modulo_id',
        'jti',
        'tipo',
        'estado',
        'emitido_at',
        'expira_at',
        'entregado_at',
        'revocado_at',
        'revocado_por',
        'motivo_revocacion',
        'pair_jti',
        'replaces_jti',
    ];

    protected $casts = [
        'emitido_at'   => 'datetime',
        'expira_at'    => 'datetime',
        'entregado_at' => 'datetime',
        'revocado_at'  => 'datetime',
    ];

    const TIPO_ACCESS  = 'access';
    const TIPO_REFRESH = 'refresh';

    const ESTADO_ACTIVO   = 'activo';
    const ESTADO_REVOCADO = 'revocado';
    const ESTADO_EXPIRADO = 'expirado';

    const MOTIVO_MANUAL    = 'manual';
    const MOTIVO_ROTACION  = 'rotacion';
    const MOTIVO_COMPROMISO = 'compromiso_secreto';

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(ModuloCliente::class, 'modulo_id');
    }

    public function revocador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'revocado_por');
    }
}
