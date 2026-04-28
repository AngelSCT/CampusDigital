<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    protected $fillable = [
        'usuario_id',
        'saldo'
    ];
}
