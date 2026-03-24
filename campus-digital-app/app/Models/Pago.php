<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    public function user()
{
    return $this->belongsTo(User::class);
}

public function comprobante()
{
    return $this->morphOne(Comprobante::class, 'referencia');
}
}
