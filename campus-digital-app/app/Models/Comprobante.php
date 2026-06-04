<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $table = 'comprobantes';
    public $timestamps = true;

    protected $fillable = [
        'usuario_id',
        'referencia_id',
        'referencia_type',
        'total',
        'fecha'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha' => 'datetime'
    ];

    public function referencia()
    {
        return $this->morphTo();
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}