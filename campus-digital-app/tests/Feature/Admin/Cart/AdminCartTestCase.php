<?php

namespace Tests\Feature\Admin\Cart;

use App\Models\Cart\SolicitudModulo;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Cart\CartTestCase;

/**
 * Base para tests del panel admin del módulo Carrito.
 *
 * Extiende CartTestCase (SQLite + migraciones cart_*) y además crea las
 * tablas mínimas de autenticación (usuario, rol, usuario_rol) para que
 * EnsureCartAdminRole pueda verificar roles sin depender de PostgreSQL.
 */
abstract class AdminCartTestCase extends CartTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->createAuthTables();
        $this->seedCategorias();
    }

    // ─── Auth tables ─────────────────────────────────────────────────────────

    private function createAuthTables(): void
    {
        // Tabla mínima de usuario (solo columnas que usa el código de auth y roles)
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido')->default('');
            $table->string('email')->unique();
            $table->string('password_hash')->nullable();
            $table->boolean('email_verificado')->default(true);
            $table->boolean('bloqueado')->default(false);
            $table->rememberToken();
            $table->softDeletes();   // SoftDeletes en Usuario usa deleted_at
            $table->timestamps();
        });

        // Tabla rol (replicada mínimamente; SoftDeletes)
        Schema::create('rol', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Pivot usuario_rol con timestamps y deleted_at
        // (belongsToMany->withTimestamps() + wherePivotNull('deleted_at'))
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('rol_id');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Crea un usuario con el rol 'admin_carrito'. */
    protected function createAdminUser(): Usuario
    {
        $user = Usuario::create([
            'nombre'           => 'Admin Cart',
            'apellido'         => 'Test',
            'email'            => 'admin-cart@test.com',
            'email_verificado' => true,
            'bloqueado'        => false,
        ]);

        $rol = Rol::create(['nombre' => 'admin_carrito', 'activo' => true]);
        $user->roles()->attach($rol->id);

        return $user;
    }

    /** Crea un usuario sin rol especial. */
    protected function createRegularUser(): Usuario
    {
        return Usuario::create([
            'nombre'           => 'Usuario Normal',
            'apellido'         => 'Test',
            'email'            => 'normal@test.com',
            'email_verificado' => true,
            'bloqueado'        => false,
        ]);
    }

    /** Crea una SolicitudModulo en estado pendiente. */
    protected function crearSolicitudPendiente(string $tipo = 'biblioteca'): SolicitudModulo
    {
        return SolicitudModulo::create([
            'folio'                  => strtoupper(substr($tipo, 0, 3)) . '-' . strtoupper(bin2hex(random_bytes(3))),
            'nombre_modulo'          => ucfirst($tipo) . ' Test',
            'tipo_modulo'            => $tipo,
            'categorias_solicitadas' => ['prestamo'],
            'contacto_nombre'        => 'Test',
            'contacto_email'         => 'test@uni.edu',
            'estado'                 => SolicitudModulo::ESTADO_PENDIENTE,
        ]);
    }
}
