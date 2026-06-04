<?php

namespace App\Console\Commands;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Console\Command;

class DemoAdminSetupCommand extends Command
{
    protected $signature   = 'carrito:demo-admin-setup {email : Email del usuario que recibirá el rol admin_carrito}';
    protected $description = 'Asigna el rol admin_carrito a un usuario existente (idempotente)';

    public function handle(): int
    {
        $email = $this->argument('email');

        // ── 1. Buscar usuario ────────────────────────────────────────────────
        $usuario = Usuario::where('email', $email)->first();

        if (!$usuario) {
            $this->error("Usuario con email '{$email}' no encontrado.");
            $this->line('  Verifica el email con: php artisan tinker --execute="echo App\Models\Usuario::pluck(\'email\')->implode(\', \');"');
            return self::FAILURE;
        }

        // ── 2. Crear rol si no existe ────────────────────────────────────────
        $rol = Rol::firstOrCreate(
            ['nombre' => 'admin_carrito'],
            ['activo' => true]
        );

        // ── 3. Asignar rol (idempotente, respeta soft-delete del pivot) ──────
        if ($usuario->hasRole('admin_carrito')) {
            $this->info("OK: '{$email}' ya tiene el rol admin_carrito. Nada que hacer.");
            return self::SUCCESS;
        }

        // Verificar si existe un pivot soft-deleted y restaurarlo
        $pivotExistente = \DB::table('usuario_rol')
            ->where('usuario_id', $usuario->id)
            ->where('rol_id', $rol->id)
            ->whereNotNull('deleted_at')
            ->first();

        if ($pivotExistente) {
            \DB::table('usuario_rol')
                ->where('id', $pivotExistente->id)
                ->update(['deleted_at' => null, 'updated_at' => now()]);
        } else {
            $usuario->roles()->attach($rol->id);
        }

        $this->info("OK: '{$email}' ahora tiene el rol admin_carrito.");
        $this->line("  Usuario ID : {$usuario->id}");
        $this->line("  Rol ID     : {$rol->id}");
        $this->line('');
        $this->line('  Rutas ahora accesibles:');
        $this->line('    /admin/cart/solicitudes');
        $this->line('    /admin/cart/modulos');
        $this->line('    /admin/cart/bitacora');

        return self::SUCCESS;
    }
}
