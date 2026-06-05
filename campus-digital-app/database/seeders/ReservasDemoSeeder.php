<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * PARA CORRER:
 *   php artisan db:seed --class=ReservasDemoSeeder
 *
 * Crea reservas y turnos de ejemplo para los últimos 30 días,
 * distribuyendo distintos estados para poblar dashboard y reportes.
 *
 * REQUIERE: RecursosDemoSeeder ejecutado antes, y usuarios existentes.
 */
class ReservasDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando ReservasDemoSeeder...');

        $recursos = DB::table('recursos')->get();
        if ($recursos->isEmpty()) {
            $this->command->error('No hay recursos. Ejecuta primero RecursosDemoSeeder.');
            return;
        }

        $usuarios = DB::table('usuario')->limit(20)->pluck('id')->toArray();
        if (empty($usuarios)) {
            $this->command->error('No hay usuarios. Ejecuta primero los seeders base.');
            return;
        }

        $estados = [
            'confirmada' => 0.55,
            'completada' => 0.25,
            'cancelada'  => 0.10,
            'no_show'    => 0.05,
            'pendiente'  => 0.05,
        ];

        $tiposTurno = ['atencion', 'recoleccion', 'cafeteria', 'biblioteca', 'general'];

        // ── Reservas (últimos 30 días) ────────────────────────────────────
        $this->command->info('Creando reservas...');
        $numReservas = 60;
        for ($i = 0; $i < $numReservas; $i++) {
            $recurso     = $recursos->random();
            $usuarioId   = $usuarios[array_rand($usuarios)];
            $estado      = $this->weightedRandom($estados);
            $inicio      = Carbon::now()->subDays(rand(0, 30))->setTime(rand(8, 18), 0);
            $duracion    = rand(1, 3);
            $fin         = $inicio->copy()->addHours($duracion);
            $cobra       = $recurso->costo_por_hora > 0 && $estado !== 'cancelada';
            $montoCobrado = $cobra ? round($duracion * $recurso->costo_por_hora, 2) : null;

            DB::table('reservas')->insert([
                'id_recurso'    => $recurso->id_recurso,
                'id_usuario'    => $usuarioId,
                'fecha_inicio'  => $inicio,
                'fecha_fin'     => $fin,
                'estado'        => $estado,
                'proposito'     => 'Reserva de prueba ' . Str::random(6),
                'cobro_saldo'   => $cobra,
                'monto_cobrado' => $montoCobrado,
                'cancelada_at'  => $estado === 'cancelada' ? $inicio->copy()->subHours(rand(1, 5)) : null,
                'created_at'    => $inicio->copy()->subDays(rand(1, 5)),
                'updated_at'    => $inicio,
            ]);
        }
        $this->command->info("✓ {$numReservas} reservas creadas.");

        // ── Turnos de hoy (en cola) ───────────────────────────────────────
        $this->command->info('Creando turnos de hoy...');
        $numTurnos = 15;
        $posicionPorTipo = [];
        foreach (array_slice($usuarios, 0, 5) as $idx => $usuarioId) {
            $tipo     = $tiposTurno[array_rand($tiposTurno)];
            $posicionPorTipo[$tipo] = ($posicionPorTipo[$tipo] ?? 0) + 1;
            $prefijo  = substr($tipo, 0, 1);
            $numero   = $prefijo . now()->format('ymd') . '-' . str_pad($posicionPorTipo[$tipo] + 200, 3, '0', STR_PAD_LEFT);
            $estado   = $idx === 0 ? 'llamado' : 'esperando';

            DB::table('turnos')->insert([
                'id_usuario'   => $usuarioId,
                'tipo_turno'   => $tipo,
                'numero_turno' => $numero,
                'estado'       => $estado,
                'posicion'     => $posicionPorTipo[$tipo],
                'llamado_at'   => $estado === 'llamado' ? now()->subMinutes(2) : null,
                'created_at'   => now()->subMinutes(rand(5, 60)),
                'updated_at'   => now(),
            ]);
        }
        $this->command->info("✓ Turnos de hoy creados.");

        // ── Turnos históricos de ayer/atras (mezcla estados) ─────────────
        $this->command->info('Creando turnos históricos...');
        for ($i = 0; $i < $numTurnos; $i++) {
            $tipo   = $tiposTurno[array_rand($tiposTurno)];
            $estado = collect(['atendido', 'atendido', 'atendido', 'no_show', 'cancelado'])->random();
            $prefijo = substr($tipo, 0, 1);
            $diasAtras = rand(1, 7);
            $secuenciaUnica = str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $numero  = $prefijo . now()->subDays($diasAtras)->format('ymd') . '-' . $secuenciaUnica;
            $fecha   = now()->subDays($diasAtras)->setTime(rand(8, 17), rand(0, 59));

            DB::table('turnos')->insert([
                'id_usuario'   => $usuarios[array_rand($usuarios)],
                'tipo_turno'   => $tipo,
                'numero_turno' => $numero,
                'estado'       => $estado,
                'posicion'     => rand(1, 10),
                'llamado_at'   => $estado !== 'cancelado' ? $fecha->copy()->addMinutes(rand(5, 30)) : null,
                'atendido_at'  => $estado === 'atendido' ? $fecha->copy()->addMinutes(rand(10, 40)) : null,
                'cancelado_at' => $estado === 'cancelado' ? $fecha : null,
                'created_at'   => $fecha,
                'updated_at'   => $fecha,
            ]);
        }

        $this->command->info('ReservasDemoSeeder completado.');
    }

    private function weightedRandom(array $weights): string
    {
        $rand = mt_rand(0, 100) / 100;
        $acc  = 0;
        foreach ($weights as $key => $weight) {
            $acc += $weight;
            if ($rand <= $acc) {
                return $key;
            }
        }
        return array_key_first($weights);
    }
}
