<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pedido;
use App\Models\PedidoHistorial;
use App\Models\Usuario;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = Usuario::take(5)->get();
        if ($usuarios->isEmpty()) {
            $this->command->warn('No hay usuarios. Corre primero el seeder principal.');
            return;
        }

        $modulos  = Pedido::MODULOS;
        $estados  = Pedido::ESTADOS;

        foreach (range(1, 30) as $i) {
            $usuario  = $usuarios->random();
            $estado   = $estados[array_rand($estados)];
            $modulo   = $modulos[array_rand($modulos)];

            $pedido = Pedido::create([
                'usuario_id'   => $usuario->id,
                'numero_folio' => 'PED-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'estado'       => $estado,
                'modulo'       => $modulo,
                'total'        => rand(20, 350) + (rand(0, 99) / 100),
                'descripcion'  => "Pedido de prueba #$i - $modulo",
                'notas'        => '',
                'cobrado_de_saldo' => true,
            ]);

            // Historial básico
            PedidoHistorial::create([
                'pedido_id'      => $pedido->id,
                'estado_anterior' => null,
                'estado_nuevo'   => 'creado',
                'usuario_id'     => $usuario->id,
                'notas'          => 'Pedido creado',
            ]);

            if ($estado !== 'creado') {
                PedidoHistorial::create([
                    'pedido_id'       => $pedido->id,
                    'estado_anterior' => 'creado',
                    'estado_nuevo'    => $estado,
                    'usuario_id'      => $usuario->id,
                    'notas'           => 'Cambio automático de prueba',
                ]);
            }
        }

        $this->command->info('✅ 30 pedidos de prueba creados.');
    }
}