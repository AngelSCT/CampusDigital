<?php

namespace Database\Seeders;

use App\Models\Movimiento;
use App\Models\Recarga;
use App\Models\Saldo;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SimulacionDemoSeeder
 *
 * Genera datos de prueba realistas para el módulo 8:
 * - Saldo inicial para cada usuario
 * - Recargas históricas (exitosas y fallidas)
 * - Movimientos de consumo en módulos del campus (cafeteria, copias, souvenirs, etc.)
 *
 * Ejecutar: php artisan db:seed --class=SimulacionDemoSeeder
 */
class SimulacionDemoSeeder extends Seeder
{
    /**
     * Configuración de módulos con rangos de montos realistas.
     * Centralizado para consistencia con SimulacionService.
     */
    private const MODULOS = [
        'cafeteria'  => ['label' => 'Cafetería',            'min' => 50,  'max' => 150, 'cargo' => true],
        'copias'     => ['label' => 'Copias / Impresiones', 'min' => 10,  'max' => 50,  'cargo' => true],
        'souvenirs'  => ['label' => 'Souvenirs',            'min' => 30,  'max' => 100, 'cargo' => true],
        'biblioteca' => ['label' => 'Biblioteca',           'min' => 5,   'max' => 20,  'cargo' => true],
        'acceso'     => ['label' => 'Control de Acceso',    'min' => 0,   'max' => 0,   'cargo' => false],
    ];

    public function run(): void
    {
        $usuarios = Usuario::all();

        if ($usuarios->isEmpty()) {
            $this->command->warn('No hay usuarios. Ejecuta UsuariosPruebaSeeder primero.');
            return;
        }

        foreach ($usuarios as $usuario) {
            DB::transaction(function () use ($usuario) {
                $this->generarDatosUsuario($usuario);
            });
        }

        $this->command->info("Datos de simulación generados para {$usuarios->count()} usuario(s).");
    }

    /**
     * Genera saldo, recargas y movimientos de consumo para un usuario.
     */
    private function generarDatosUsuario(Usuario $usuario): void
    {
        // Saldo inicial realista (entre $200 y $800)
        $saldoInicial = round(rand(200, 800) + rand(0, 99) / 100, 2);

        $saldo = Saldo::firstOrCreate(
            ['usuario_id' => $usuario->id],
            ['saldo' => $saldoInicial]
        );

        // Si ya existía, solo actualizar si el saldo es 0
        if ((float) $saldo->saldo === 0.0) {
            $saldo->saldo = $saldoInicial;
            $saldo->save();
        }

        $saldoActual = (float) $saldo->saldo;

        // Generar entre 2 y 5 recargas históricas
        $numRecargas = rand(2, 5);
        for ($i = 0; $i < $numRecargas; $i++) {
            $saldoActual = $this->generarRecarga($usuario, $saldo, $saldoActual, $i);
        }

        // Generar entre 5 y 15 movimientos de consumo en módulos
        $numMovimientos = rand(5, 15);
        for ($i = 0; $i < $numMovimientos; $i++) {
            $saldoActual = $this->generarMovimientoConsumo($usuario, $saldo, $saldoActual, $i);
        }

        // Actualizar el saldo final
        $saldo->saldo = max(0, $saldoActual);
        $saldo->save();
    }

    /**
     * Genera una recarga histórica (exitosa o fallida).
     */
    private function generarRecarga(Usuario $usuario, Saldo $saldo, float $saldoActual, int $idx): float
    {
        $metodos    = ['tarjeta', 'efectivo', 'transferencia', 'billetera_digital'];
        $metodo     = $metodos[array_rand($metodos)];
        $monto      = round(rand(100, 500) + rand(0, 99) / 100, 2);
        $esExitosa  = rand(1, 100) <= 80; // 80% de éxito
        $diasAtras  = rand(1, 60);

        $recarga = Recarga::create([
            'usuario_id'  => $usuario->id,
            'monto'       => $monto,
            'metodo_pago' => $metodo,
            'estado'      => $esExitosa ? 'exitosa' : 'fallida',
            'referencia'  => 'WEB-' . strtoupper(uniqid()),
            'razon_fallo' => $esExitosa ? null : 'Pago rechazado por la entidad financiera',
            'created_at'  => now()->subDays($diasAtras)->subHours(rand(0, 23)),
            'updated_at'  => now()->subDays($diasAtras),
        ]);

        if ($esExitosa) {
            $saldoAnterior  = $saldoActual;
            $saldoActual   += $monto;

            $movimiento = Movimiento::create([
                'usuario_id'      => $usuario->id,
                'tipo'            => 'recarga',
                'monto'           => $monto,
                'estado'          => 'exitosa',
                'modulo'          => 'recarga',
                'concepto'        => "Recarga de saldo vía {$metodo}",
                'saldo_anterior'  => $saldoAnterior,
                'saldo_nuevo'     => $saldoActual,
                'referencia_type' => Recarga::class,
                'referencia_id'   => $recarga->id,
                'created_at'      => $recarga->created_at,
                'updated_at'      => $recarga->updated_at,
            ]);

            $recarga->update(['saldo_movimiento_id' => $movimiento->id]);
        }

        return $saldoActual;
    }

    /**
     * Genera un movimiento de consumo en un módulo aleatorio.
     */
    private function generarMovimientoConsumo(Usuario $usuario, Saldo $saldo, float $saldoActual, int $idx): float
    {
        $claves    = array_keys(self::MODULOS);
        $clave     = $claves[array_rand($claves)];
        $config    = self::MODULOS[$clave];
        $diasAtras = rand(0, 45);

        if (!$config['cargo']) {
            // Acceso: solo registra, sin cargo
            Movimiento::create([
                'usuario_id'      => $usuario->id,
                'tipo'            => 'pago',
                'monto'           => 0,
                'estado'          => 'exitosa',
                'modulo'          => 'acceso',
                'concepto'        => 'Consulta de acceso al campus',
                'saldo_anterior'  => $saldoActual,
                'saldo_nuevo'     => $saldoActual,
                'referencia_type' => null,
                'referencia_id'   => null,
                'created_at'      => now()->subDays($diasAtras)->subHours(rand(0, 23)),
                'updated_at'      => now()->subDays($diasAtras),
            ]);

            return $saldoActual;
        }

        $monto = round(rand($config['min'], $config['max'] - 1) + rand(0, 99) / 100, 2);

        // Solo generar cargo si hay saldo suficiente
        if ($saldoActual < $monto) {
            return $saldoActual;
        }

        $saldoAnterior  = $saldoActual;
        $saldoActual   -= $monto;

        Movimiento::create([
            'usuario_id'      => $usuario->id,
            'tipo'            => 'pago',
            'monto'           => $monto,
            'estado'          => 'exitosa',
            'modulo'          => $clave,
            'concepto'        => "Consumo en {$config['label']}",
            'saldo_anterior'  => $saldoAnterior,
            'saldo_nuevo'     => $saldoActual,
            'referencia_type' => null,
            'referencia_id'   => null,
            'created_at'      => now()->subDays($diasAtras)->subHours(rand(0, 23)),
            'updated_at'      => now()->subDays($diasAtras),
        ]);

        return $saldoActual;
    }
}
