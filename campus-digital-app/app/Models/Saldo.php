<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $fillable = [
        'usuario_id',
        'saldo'
    ];
}
