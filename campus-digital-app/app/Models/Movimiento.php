<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    public function user()
{
    return $this->belongsTo(User::class);
}

public function referencia()
{
    return $this->morphTo();
}
}
