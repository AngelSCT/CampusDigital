<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TarjetaLectura extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tarjeta_lectura';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'tarjeta_id',
        'uid_leido',
        'modulo',
        'tipo_lectura',
        'exito',
        'detalle',
        'ip',
        'user_agent',
        'operador_usuario_id',
        'pedido_id',        
        'meta_json',
    ];

    protected $casts = [
        'exito'      => 'boolean',
        'meta_json'  => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const TIPOS = [
        'acceso',
        'consumo',
        'consulta_saldo',
        'confirmacion_entrega',
    ];

    const MODULOS = [
        'cafeteria',
        'copias',
        'souvenirs',
        'biblioteca',
        'acceso',
        'otro',
    ];


    public function tarjeta()
    {
        return $this->belongsTo(TarjetaUniversitaria::class, 'tarjeta_id');
    }

    public function operador()
    {
        return $this->belongsTo(Usuario::class, 'operador_usuario_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}