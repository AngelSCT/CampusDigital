<?php

namespace App\Models\Cart;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    protected $table = 'cart_bitacora';

    // Tabla de solo inserción: no tiene columna updated_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'accion',
        'modulo_id',
        'user_id',
        'jti',
        'carrito_uuid',
        'ip_address',
        'payload',
    ];

    protected $casts = [
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];

    const ACCION_MODULO_SOLICITUD      = 'modulo.solicitud';
    const ACCION_TOKEN_EMISION         = 'token.emision';
    const ACCION_TOKEN_VISUALIZADO     = 'token.visualizado';
    const ACCION_TOKEN_REVOCACION      = 'token.revocacion';
    const ACCION_TOKEN_REUSO_DETECTADO = 'token.reuso_detectado';
    const ACCION_CARRITO_CREADO        = 'carrito.creado';
    const ACCION_CARRITO_CHECKOUT      = 'carrito.checkout';
    const ACCION_CARRITO_CANCELADO     = 'carrito.cancelado';
    const ACCION_ITEM_AGREGADO         = 'item.agregado';
    const ACCION_ITEM_REMOVIDO         = 'item.removido';
    const ACCION_ITEM_DEVUELTO         = 'item.devuelto';
    const ACCION_AUTH_FALLO            = 'auth.fallo';

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(ModuloCliente::class, 'modulo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
