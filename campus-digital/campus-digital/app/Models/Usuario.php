<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuario';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'email',
        'password_hash',
        'nombre',
        'apellido',
        'telefono',
        'foto_url',
        'email_verificado',
        'ultimo_login_at',
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
        'ultimo_login_at' => 'datetime',
        'bloqueado_hasta' => 'datetime',
        'seguridad_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Para que Fortify funcione correctamente
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

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

    // Relaciones
    public function perfil()
    {
        return $this->hasOne(UsuarioPerfil::class, 'usuario_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id')
            ->whereNull('usuario_rol.deleted_at')
            ->withTimestamps();
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

    // Métodos de utilidad
    public function tieneRol(string $nombreRol): bool
    {
        return $this->roles()->where('nombre', $nombreRol)->exists();
    }

    public function tienePermiso(string $clavePermiso): bool
    {
        return $this->roles()
            ->whereHas('permisos', function ($query) use ($clavePermiso) {
                $query->where('clave', $clavePermiso);
            })->exists();
    }

    public function estaActivo(): bool
    {
        return !$this->bloqueado && ($this->bloqueado_hasta === null || $this->bloqueado_hasta->isPast());
    }
}