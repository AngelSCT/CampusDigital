<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;


//PARA CORRER ESTE SEEDER DE PRUEBA DE DATOS 
// php artisan db:seed --class=DashboardDemoSeeder

class DashboardDemoSeeder extends Seeder
{
    const MODULOS_LECTURA  = ['cafeteria', 'copias', 'souvenirs', 'biblioteca', 'acceso'];
    const MODULOS_ACTIVIDAD= ['usuarios', 'roles', 'permisos', 'tarjetas', 'bitacora', 'reportes', 'perfil', 'seguridad'];
    const TIPOS_LECTURA    = ['acceso', 'consumo', 'consulta_saldo', 'confirmacion_entrega'];
    const EVENTOS_ACCESO   = ['login', 'logout', 'login_failed', 'token_refresh', 'password_reset'];
    const ESTADOS_PEDIDO   = ['creado', 'aceptado', 'en_proceso', 'listo', 'entregado', 'cancelado'];
    const ACCIONES        = ['crear', 'editar', 'eliminar', 'ver', 'exportar', 'bloquear', 'desbloquear'];

    public function run(): void
    {
        if (DB::table('usuario')->where('email', 'admin@campus.edu.mx')->exists()) {
            $this->command->warn('DashboardDemoSeeder ya ejecutado anteriormente — omitiendo.');
            return;
        }

        $this->command->info('Iniciando DashboardDemoSeeder...');

        $this->command->info('Creando roles y permisos base...');
        $roles    = $this->seedRoles();

        $this->command->info('Creando usuarios de prueba...');
        $usuarios = $this->seedUsuarios($roles);

        $this->command->info('Creando tarjetas universitarias...');
        $tarjetas = $this->seedTarjetas($usuarios);

        $this->command->info('Creando sesiones...');
        $sesiones = $this->seedSesiones($usuarios);

        $this->command->info('Creando bitácora de accesos (30 días)...');
        $this->seedAccesosBitacora($usuarios, $sesiones);

        $this->command->info('Creando bitácora de actividad (30 días)...');
        $this->seedActividadBitacora($usuarios, $sesiones);

        $this->command->info('Creando lecturas de tarjeta (14 días)...');
        $this->seedLecturasTarjeta($tarjetas, $usuarios);

        $this->command->info('Creando monederos y movimientos...');
        $this->seedMonederos($usuarios);

        $this->command->info('Creando pedidos...');
        $this->seedPedidos($usuarios, $tarjetas);

        $this->command->info('DashboardDemoSeeder completado.');
        $this->command->table(
            ['Recurso', 'Cantidad'],
            [
                ['Usuarios creados',         count($usuarios)],
                ['Tarjetas creadas',          count($tarjetas)],
                ['Sesiones creadas',          count($sesiones)],
                ['Lecturas (14d)',            '~' . count($tarjetas) * 20],
                ['Registros de acceso (30d)', '~200'],
                ['Registros actividad (30d)', '~300'],
            ]
        );
    }

    private function seedRoles(): array
    {
        $rolesData = [
            ['nombre' => 'administrador', 'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'estudiante',    'descripcion' => 'Acceso básico de estudiante'],
            ['nombre' => 'proveedor_area','descripcion' => 'Proveedor de área de servicio'],
            ['nombre' => 'docente',       'descripcion' => 'Personal docente'],
        ];

        $permisos = [
            'user.read','user.write','user.delete','user.block','user.show',
            'role.read','role.write','role.delete','role.show',
            'permission.read','permission.write','permission.delete','permission.show',
            'card.read','card.read.any','card.write','card.block','card.auth',
            'audit.read','report.users','report.cards',
        ];

        foreach ($permisos as $clave) {
            DB::table('permiso')->updateOrInsert(
                ['clave' => $clave],
                ['descripcion' => 'Permiso ' . $clave, 'activo' => true,
                 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $roles = [];
        foreach ($rolesData as $data) {
            $id = DB::table('rol')->updateOrInsert(
                ['nombre' => $data['nombre']],
                ['descripcion' => $data['descripcion'], 'activo' => true,
                 'created_at' => now(), 'updated_at' => now()]
            );
            $roles[$data['nombre']] = DB::table('rol')->where('nombre', $data['nombre'])->value('id');
        }

        $adminRolId   = $roles['administrador'];
        $todosPermisos = DB::table('permiso')->pluck('id');
        foreach ($todosPermisos as $permisoId) {
            DB::table('rol_permiso')->updateOrInsert(
                ['rol_id' => $adminRolId, 'permiso_id' => $permisoId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $estudianteRolId = $roles['estudiante'];
        $permisosEst     = DB::table('permiso')->whereIn('clave', ['card.read', 'card.auth'])->pluck('id');
        foreach ($permisosEst as $permisoId) {
            DB::table('rol_permiso')->updateOrInsert(
                ['rol_id' => $estudianteRolId, 'permiso_id' => $permisoId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        return $roles;
    }

    private function seedUsuarios(array $roles): array
    {
        $nombresM  = ['Carlos','Miguel','Luis','Jorge','Fernando','Andrés','Ricardo','David','Eduardo','Antonio'];
        $nombresF  = ['María','Ana','Laura','Sofia','Valentina','Gabriela','Isabella','Daniela','Camila','Lucia'];
        $apellidos = ['García','Martínez','López','González','Hernández','Pérez','Ramírez','Torres','Flores','Cruz'];

        $usuarios = [];

        $adminEmail = 'admin@campus.edu.mx';
        if (!DB::table('usuario')->where('email', $adminEmail)->exists()) {
            $adminId = DB::table('usuario')->insertGetId([
                'nombre'           => 'Admin',
                'apellido'         => 'Sistema',
                'email'            => $adminEmail,
                'password_hash'    => Hash::make('password'),
                'email_verificado' => true,
                'bloqueado'        => false,
                'ultimo_login_at'  => now(),
                'created_at'       => now()->subDays(60),
                'updated_at'       => now(),
            ]);
            DB::table('usuario_rol')->insertOrIgnore([
                'usuario_id' => $adminId,
                'rol_id'     => $roles['administrador'],
                'asignado_at'=> now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $usuarios[] = $adminId;
        } else {
            $usuarios[] = DB::table('usuario')->where('email', $adminEmail)->value('id');
        }

        $distribucion = [
            ['rol' => 'estudiante',     'cantidad' => 30],
            ['rol' => 'proveedor_area', 'cantidad' => 5],
            ['rol' => 'docente',        'cantidad' => 3],
        ];

        foreach ($distribucion as $grupo) {
            for ($i = 0; $i < $grupo['cantidad']; $i++) {
                $esHombre = rand(0, 1);
                $nombre   = $esHombre
                    ? $nombresM[array_rand($nombresM)]
                    : $nombresF[array_rand($nombresF)];
                $apellido = $apellidos[array_rand($apellidos)];
                $email    = strtolower($nombre) . '.' . strtolower($apellido) . rand(10, 99) . '@campus.edu.mx';

                if (DB::table('usuario')->where('email', $email)->exists()) {
                    $email = strtolower($nombre) . rand(100, 999) . '@campus.edu.mx';
                }

                $bloqueado   = rand(1, 10) === 1; 
                $verificado  = rand(1, 5) !== 1; 
                $createdAt   = now()->subDays(rand(1, 90));
                $ultimoLogin = $bloqueado ? null : now()->subMinutes(rand(1, 10080));

                $usuarioId = DB::table('usuario')->insertGetId([
                    'nombre'           => $nombre,
                    'apellido'         => $apellido,
                    'email'            => $email,
                    'password_hash'    => Hash::make('password'),
                    'email_verificado' => $verificado,
                    'bloqueado'        => $bloqueado,
                    'ultimo_login_at'  => $ultimoLogin,
                    'created_at'       => $createdAt,
                    'updated_at'       => now(),
                ]);

                DB::table('usuario_rol')->insertOrIgnore([
                    'usuario_id'               => $usuarioId,
                    'rol_id'                   => $roles[$grupo['rol']],
                    'asignado_por_usuario_id'  => $usuarios[0], // admin
                    'asignado_at'              => $createdAt,
                    'created_at'               => $createdAt,
                    'updated_at'               => $createdAt,
                ]);

                $usuarios[] = $usuarioId;
            }
        }

        return $usuarios;
    }

    private function seedTarjetas(array $usuarios): array
    {
        $tarjetas = [];
        $estados  = ['activa', 'activa', 'activa', 'activa', 'bloqueada', 'perdida'];

        foreach ($usuarios as $index => $usuarioId) {
            if (rand(1, 10) === 1) continue;

            $estado = $estados[array_rand($estados)];
            $uid    = strtoupper(Str::random(8));

            while (DB::table('tarjeta_universitaria')->where('uid', $uid)->exists()) {
                $uid = strtoupper(Str::random(8));
            }

            $tarjetaId = DB::table('tarjeta_universitaria')->insertGetId([
                'usuario_id'                  => $usuarioId,
                'uid'                         => $uid,
                'estado'                      => $estado,
                'motivo_bloqueo'              => $estado === 'bloqueada' ? 'Bloqueo preventivo por seguridad' : null,
                'registrado_por_usuario_id'   => $usuarios[0],
                'bloqueado_por_usuario_id'    => $estado === 'bloqueada' ? $usuarios[0] : null,
                'bloqueado_at'                => $estado === 'bloqueada' ? now()->subDays(rand(1, 30)) : null,
                'created_at'                  => now()->subDays(rand(5, 80)),
                'updated_at'                  => now(),
            ]);

            $tarjetas[] = ['id' => $tarjetaId, 'uid' => $uid, 'usuario_id' => $usuarioId, 'estado' => $estado];
        }

        return $tarjetas;
    }

    private function seedSesiones(array $usuarios): array
    {
        $sesiones = [];

        foreach ($usuarios as $usuarioId) {
            $numSesiones = rand(2, 5);
            for ($i = 0; $i < $numSesiones; $i++) {
                $iniciada = now()->subDays(rand(0, 30))->subHours(rand(0, 23));
                $activa   = $i === 0 && rand(1, 3) === 1; 

                $sesionId = DB::table('usuario_sesion')->insertGetId([
                    'usuario_id'  => $usuarioId,
                    'session_id'  => Str::random(40),
                    'ip'          => fake()->ipv4(),
                    'user_agent'  => fake()->userAgent(),
                    'inicia_at'   => $iniciada,
                    'expira_at'   => $iniciada->copy()->addHours(8),
                    'termina_at'  => $activa ? null : $iniciada->copy()->addHours(rand(1, 4)),
                    'activa'      => $activa,
                    'created_at'  => $iniciada,
                    'updated_at'  => $iniciada,
                ]);

                $sesiones[] = ['id' => $sesionId, 'usuario_id' => $usuarioId];
            }
        }

        return $sesiones;
    }

    private function seedAccesosBitacora(array $usuarios, array $sesiones): void
    {
        $ips = array_map(fn() => fake()->ipv4(), range(1, 20));

        for ($i = 0; $i < 220; $i++) {
            $usuarioId = $usuarios[array_rand($usuarios)];
            $sesion    = $sesiones[array_rand($sesiones)];
            $evento    = self::EVENTOS_ACCESO[array_rand(self::EVENTOS_ACCESO)];
            $exito     = $evento === 'login_failed' ? false : (rand(1, 10) > 1);

            $diasAtras = rand(1, 3) <= 2 ? rand(0, 7) : rand(7, 30);
            $fecha     = now()->subDays($diasAtras)->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            DB::table('acceso_bitacora')->insert([
                'usuario_id'      => $exito ? $usuarioId : null,
                'sesion_id'       => $exito ? $sesion['id'] : null,
                'email_intentado' => DB::table('usuario')->where('id', $usuarioId)->value('email') ?? 'desconocido@campus.edu.mx',
                'evento'          => $evento,
                'exito'           => $exito,
                'detalle'         => $exito ? 'Acceso registrado correctamente' : 'Credenciales incorrectas o cuenta bloqueada',
                'ip'              => $ips[array_rand($ips)],
                'user_agent'      => fake()->userAgent(),
                'created_at'      => $fecha,
                'updated_at'      => $fecha,
            ]);
        }
    }

    private function seedActividadBitacora(array $usuarios, array $sesiones): void
    {
        $tablas = ['usuario', 'rol', 'permiso', 'tarjeta_universitaria', 'pedido'];

        for ($i = 0; $i < 300; $i++) {
            $usuarioId = $usuarios[array_rand($usuarios)];
            $sesion    = $sesiones[array_rand($sesiones)];
            $modulo    = self::MODULOS_ACTIVIDAD[array_rand(self::MODULOS_ACTIVIDAD)];
            $accion    = self::ACCIONES[array_rand(self::ACCIONES)];

            $diasAtras = rand(1, 3) <= 2 ? rand(0, 7) : rand(7, 30);
            $fecha     = now()->subDays($diasAtras)->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            DB::table('actividad_bitacora')->insert([
                'usuario_id'   => $usuarioId,
                'sesion_id'    => $sesion['id'],
                'accion'       => $accion,
                'modulo'       => $modulo,
                'target_tabla' => $tablas[array_rand($tablas)],
                'target_id'    => rand(1, 50),
                'exito'        => rand(1, 10) > 1,
                'detalle'      => ucfirst($accion) . ' en módulo ' . $modulo,
                'ip'           => fake()->ipv4(),
                'user_agent'   => fake()->userAgent(),
                'created_at'   => $fecha,
                'updated_at'   => $fecha,
            ]);
        }
    }

    private function seedLecturasTarjeta(array $tarjetas, array $usuarios): void
    {
        if (empty($tarjetas)) return;

        $operadores = array_slice($usuarios, 0, 5); 

        foreach ($tarjetas as $tarjeta) {
            $numLecturas = rand(10, 30);

            for ($i = 0; $i < $numLecturas; $i++) {
                $modulo      = self::MODULOS_LECTURA[array_rand(self::MODULOS_LECTURA)];
                $tipoLectura = self::TIPOS_LECTURA[array_rand(self::TIPOS_LECTURA)];
                $exito       = $tarjeta['estado'] === 'activa' ? (rand(1, 10) > 1) : false;

                $diasAtras = rand(1, 2);
                $fecha     = now()->subDays($diasAtras)->subHours(rand(6, 22))->subMinutes(rand(0, 59));

                DB::table('tarjeta_lectura')->insert([
                    'tarjeta_id'          => $tarjeta['id'],
                    'uid_leido'           => $tarjeta['uid'],
                    'modulo'              => $modulo,
                    'tipo_lectura'        => $tipoLectura,
                    'exito'               => $exito,
                    'detalle'             => $exito ? 'Lectura exitosa' : 'Tarjeta ' . $tarjeta['estado'],
                    'ip'                  => fake()->localIpv4(),
                    'operador_usuario_id' => $operadores[array_rand($operadores)],
                    'created_at'          => $fecha,
                    'updated_at'          => $fecha,
                ]);
            }
        }
    }

    private function seedMonederos(array $usuarios): void
    {
        foreach ($usuarios as $usuarioId) {
            if (DB::table('saldo_monedero')->where('usuario_id', $usuarioId)->exists()) continue;

            $saldo = rand(0, 500) + (rand(0, 99) / 100);

            $monederoId = DB::table('saldo_monedero')->insertGetId([
                'usuario_id'        => $usuarioId,
                'saldo_disponible'  => $saldo,
                'saldo_retenido'    => 0,
                'created_at'        => now()->subDays(rand(5, 90)),
                'updated_at'        => now(),
            ]);

            $saldoActual = 0;
            $numMov = rand(3, 8);
            for ($i = 0; $i < $numMov; $i++) {
                $tipo  = rand(0, 1) ? 'abono' : 'cargo';
                $monto = round(rand(10, 200) + rand(0, 99) / 100, 2);
                if ($tipo === 'cargo' && $saldoActual < $monto) {
                    $tipo = 'abono';
                }
                $saldoAnterior = $saldoActual;
                $saldoActual  += $tipo === 'abono' ? $monto : -$monto;
                $fecha = now()->subDays(rand(0, 30));

                DB::table('saldo_movimiento')->insert([
                    'usuario_id'        => $usuarioId,
                    'saldo_monedero_id' => $monederoId,
                    'tipo'              => $tipo,
                    'monto'             => $monto,
                    'saldo_anterior'    => round($saldoAnterior, 2),
                    'saldo_nuevo'       => round($saldoActual, 2),
                    'modulo'            => self::MODULOS_LECTURA[array_rand(self::MODULOS_LECTURA)],
                    'concepto'          => $tipo === 'abono' ? 'Recarga de saldo' : 'Consumo en ' . self::MODULOS_LECTURA[array_rand(self::MODULOS_LECTURA)],
                    'operador_usuario_id' => null,
                    'created_at'        => $fecha,
                    'updated_at'        => $fecha,
                ]);
            }

            DB::table('saldo_monedero')->where('id', $monederoId)->update([
                'saldo_disponible' => max(0, round($saldoActual, 2)),
                'updated_at'       => now(),
            ]);
        }
    }

    private function seedPedidos(array $usuarios, array $tarjetas): void
    {
        $operadores = array_slice($usuarios, 0, 5);

        for ($i = 0; $i < 60; $i++) {
            $usuarioId = $usuarios[array_rand($usuarios)];
            $estado    = self::ESTADOS_PEDIDO[array_rand(self::ESTADOS_PEDIDO)];
            $modulo    = self::MODULOS_LECTURA[array_rand(self::MODULOS_LECTURA)];
            $total     = round(rand(20, 300) + rand(0, 99) / 100, 2);
            $fecha     = now()->subDays(rand(0, 30))->subHours(rand(0, 12));
            $folio     = 'PED-' . strtoupper(Str::random(8));

            while (DB::table('pedido')->where('numero_folio', $folio)->exists()) {
                $folio = 'PED-' . strtoupper(Str::random(8));
            }

            DB::table('pedido')->insert([
                'usuario_id'             => $usuarioId,
                'numero_folio'           => $folio,
                'estado'                 => $estado,
                'modulo'                 => $modulo,
                'total'                  => $total,
                'descripcion'            => 'Pedido en ' . $modulo,
                'notas'                  => '',
                'operador_usuario_id'    => $operadores[array_rand($operadores)],
                'confirmado_con_tarjeta' => in_array($estado, ['entregado', 'listo']),
                'confirmado_at'          => in_array($estado, ['entregado', 'listo']) ? $fecha->copy()->addMinutes(rand(5, 30)) : null,
                'cobrado_de_saldo'       => rand(0, 1),
                'created_at'             => $fecha,
                'updated_at'             => $fecha,
            ]);
        }
    }
}