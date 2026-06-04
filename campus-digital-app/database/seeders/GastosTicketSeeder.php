<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Insumo;
use App\Models\Ticket;
use App\Models\Usuario;
use App\Models\CategoriaTicket;
use App\Models\GastoTicket;

class GastosTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear insumos con precio
        $insumosData = [
            ['nombre_insumo' => 'Cable de Red CAT6 (Metro)', 'stock_actual' => 100, 'precio_unitario' => 25.50],
            ['nombre_insumo' => 'Conector RJ45', 'stock_actual' => 500, 'precio_unitario' => 5.00],
            ['nombre_insumo' => 'Mantenimiento de Equipo (Hora)', 'stock_actual' => 999, 'precio_unitario' => 150.00],
        ];

        $insumos = [];
        foreach ($insumosData as $data) {
            $insumos[] = Insumo::firstOrCreate(
                ['nombre_insumo' => $data['nombre_insumo']],
                $data
            );
        }

        // 2. Obtener un usuario y una categoría para el ticket de prueba
        $usuario = Usuario::first();
        $categoria = CategoriaTicket::first();

        if (!$usuario || !$categoria) {
            $this->command->error('No hay usuarios o categorías para crear un ticket de prueba.');
            return;
        }

        // 3. Obtener o crear un ticket de prueba
        $ticket = Ticket::firstOrCreate(
            [
                'id_usuario_solicitante' => $usuario->id,
                'id_categoria' => $categoria->id_categoria,
            ],
            [
                'estado' => 'Abierto',
                'prioridad' => 'Media',
            ]
        );

        // 4. Agregar gastos al ticket
        foreach ($insumos as $insumo) {
            GastoTicket::firstOrCreate(
                [
                    'id_ticket' => $ticket->id_ticket,
                    'id_insumo' => $insumo->id_insumo,
                ],
                [
                    'cantidad' => rand(1, 5),
                ]
            );
        }

        // 5. Calcular costo total del ticket
        $costoTotal = $ticket->calcularCostoTotal();
        $this->command->info("Gastos generados para el Ticket #{$ticket->id_ticket}. Costo Total: $costoTotal");
    }
}
