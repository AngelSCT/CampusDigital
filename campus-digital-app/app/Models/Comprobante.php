<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{

    protected $fillable = [
        'usuario_id',
        'total'
    ];

    public function user()
{
    return $this->belongsTo(User::class);
}

public function referencia()
{
    return $this->morphTo();
}
}
