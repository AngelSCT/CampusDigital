<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'monto',
        'estado',
        'referencia_type',
        'referencia_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function referenciable()
    {
        return $this->morphTo();
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_catalogo',
        'cantidad',
        'fecha'
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'id_catalogo');
    }
}