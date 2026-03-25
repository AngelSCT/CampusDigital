<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $fillable = [
    'usuario_id',
    'tipo',
    'monto',
    'estado'];

    public function user()
{
    return $this->belongsTo(User::class);
}

public function referencia()
{
    return $this->morphTo();
}
}
