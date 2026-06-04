<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecargasSeeder extends Seeder
{
    public function run(): void
    {
        $usuarioId = 1;
        $recargas = [];

        $metodos = ['tarjeta', 'efectivo'];
        $estados = ['exitosa', 'exitosa', 'exitosa', 'fallida', 'pendiente'];
        $razones = [
            'Fondos insuficientes',
            'Tarjeta vencida',
            'Error de conexión con el banco',
            'Límite diario excedido',
            null,
        ];

        // Generar 40 recargas en los últimos 90 días
        for ($i = 0; $i < 40; $i++) {
            $estado = $estados[array_rand($estados)];
            $metodo = $metodos[array_rand($metodos)];
            $fecha = Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23));

            $recargas[] = [
                'usuario_id'   => $usuarioId,
                'monto'        => rand(1, 20) * 50, // 50, 100, 150... hasta 1000
                'metodo_pago'  => $metodo,
                'estado'       => $estado,
                'referencia'   => 'REF-' . strtoupper(substr(md5(uniqid()), 0, 10)),
                'razon_fallo'  => $estado === 'fallida' ? $razones[array_rand($razones)] : null,
                'created_at'   => $fecha,
                'updated_at'   => $fecha,
            ];
        }

        DB::table('recargas')->insert($recargas);

        $this->command->info('✅ 40 recargas de prueba insertadas correctamente.');
    }
}
