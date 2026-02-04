<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuario';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'foto_url',
        'password_hash',
        'email_verificado',
        'bloqueado',
        'bloqueado_hasta',
        'seguridad_json',
    ];

    protected $hidden = [
        'password_hash',
        'seguridad_json',
    ];

    protected $casts = [
        'email_verificado' => 'boolean',
        'bloqueado' => 'boolean',
        'bloqueado_hasta' => 'datetime',
        'ultimo_login_at' => 'datetime',
        'seguridad_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Override para Fortify
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Override para email verification
    public function hasVerifiedEmail()
    {
        return $this->email_verificado;
    }

    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verificado' => true,
        ])->save();
    }

    public function getEmailForVerification()
    {
        return $this->email;
    }



    public function perfil()
    {
        return $this->hasOne(UsuarioPerfil::class, 'usuario_id');
    }

    public function sesiones()
    {
        return $this->hasMany(UsuarioSesion::class, 'usuario_id');
    }

    public function accesos()
    {
        return $this->hasMany(AccesoBitacora::class, 'usuario_id');
    }

    public function actividades()
    {
        return $this->hasMany(ActividadBitacora::class, 'usuario_id');
    }

    // Métodos auxiliares
    public function hasRole($roleName)
    {
        return $this->roles()->where('nombre', $roleName)->exists();
    }

    public function hasAnyRole($roles)
    {
        return $this->roles()->whereIn('nombre', $roles)->exists();
    }

    public function hasPermission($permissionKey)
    {
        return $this->roles()
            ->whereHas('permisos', function ($query) use ($permissionKey) {
                $query->where('clave', $permissionKey);
            })
            ->exists();
    }

    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    public function estaBloqueado()
    {
        if (!$this->bloqueado) {
            return false;
        }

        if ($this->bloqueado_hasta && now()->greaterThan($this->bloqueado_hasta)) {
            $this->update(['bloqueado' => false, 'bloqueado_hasta' => null]);
            return false;
        }

        return true;
    }
    public function sendEmailVerificationNotification()
{
    $this->notify(new \App\Notifications\VerifyEmailNotification);
}

public function roles()
{
    return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id')
        ->withTimestamps()
        ->wherePivotNull('deleted_at');
}

}