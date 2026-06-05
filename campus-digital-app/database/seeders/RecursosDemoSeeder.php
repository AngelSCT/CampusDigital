<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * PARA CORRER:
 *   php artisan db:seed --class=RecursosDemoSeeder
 *
 * Crea recursos reservables de ejemplo: salas, laboratorios y equipos.
 * Toma ubicaciones existentes o crea unas por defecto.
 */
class RecursosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando RecursosDemoSeeder...');

        // Asegurar al menos una ubicación
        $ubicacionesIds = DB::table('ubicaciones')->pluck('id_ubicacion')->toArray();
        if (empty($ubicacionesIds)) {
            $this->command->warn('No hay ubicaciones, creando una de ejemplo...');
            DB::table('ubicaciones')->insert([
                'edificio'         => 'Edificio A',
                'aula_departamento'=> 'Planta Baja',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            $ubicacionesIds = DB::table('ubicaciones')->pluck('id_ubicacion')->toArray();
        }

        $horarioDefecto = json_encode([
            'lunes'     => ['08:00-20:00'],
            'martes'    => ['08:00-20:00'],
            'miercoles' => ['08:00-20:00'],
            'jueves'    => ['08:00-20:00'],
            'viernes'   => ['08:00-18:00'],
            'sabado'    => ['09:00-14:00'],
        ]);

        $recursos = [
            [
                'nombre'         => 'Sala de Estudio 1',
                'descripcion'    => 'Sala silenciosa para estudio individual o en grupo pequeño, con pizarrón y proyector.',
                'tipo'           => 'sala',
                'capacidad'      => 6,
                'id_ubicacion'   => $ubicacionesIds[0],
                'estado'         => 'disponible',
                'costo_por_hora' => 0.00,
                'horarios'       => $horarioDefecto,
            ],
            [
                'nombre'         => 'Sala de Juntas Principal',
                'descripcion'    => 'Sala para reuniones ejecutivas con pantalla grande y videoconferencia.',
                'tipo'           => 'sala',
                'capacidad'      => 12,
                'id_ubicacion'   => $ubicacionesIds[0],
                'estado'         => 'disponible',
                'costo_por_hora' => 50.00,
                'horarios'       => $horarioDefecto,
            ],
            [
                'nombre'         => 'Sala de Conferencias',
                'descripcion'    => 'Sala grande con capacidad para eventos, conferencias y presentaciones.',
                'tipo'           => 'sala',
                'capacidad'      => 40,
                'id_ubicacion'   => $ubicacionesIds[0],
                'estado'         => 'disponible',
                'costo_por_hora' => 100.00,
                'horarios'       => $horarioDefecto,
            ],
            [
                'nombre'         => 'Laboratorio de Cómputo 1',
                'descripcion'    => 'Laboratorio con 20 equipos con software especializado.',
                'tipo'           => 'laboratorio',
                'capacidad'      => 20,
                'id_ubicacion'   => $ubicacionesIds[0],
                'estado'         => 'disponible',
                'costo_por_hora' => 30.00,
                'horarios'       => $horarioDefecto,
            ],
            [
                'nombre'         => 'Laboratorio de Electrónica',
                'descripcion'    => 'Laboratorio con osciloscopios, multímetros y fuentes de poder.',
                'tipo'           => 'laboratorio',
                'capacidad'      => 15,
                'id_ubicacion'   => $ubicacionesIds[0],
                'estado'         => 'disponible',
                'costo_por_hora' => 40.00,
                'horarios'       => $horarioDefecto,
            ],
            [
                'nombre'         => 'Cámara de Documentos',
                'descripcion'    => 'Equipo portátil para digitalización de documentos y proyección.',
                'tipo'           => 'equipo',
                'capacidad'      => 1,
                'id_ubicacion'   => $ubicacionesIds[0],
                'estado'         => 'disponible',
                'costo_por_hora' => 0.00,
                'horarios'       => $horarioDefecto,
            ],
            [
                'nombre'         => 'Proyector Móvil 4K',
                'descripcion'    => 'Proyector portátil resolución 4K con bocinas integradas.',
                'tipo'           => 'equipo',
                'capacidad'      => 1,
                'id_ubicacion'   => $ubicacionesIds[0],
                'estado'         => 'mantenimiento',
                'costo_por_hora' => 0.00,
                'horarios'       => $horarioDefecto,
            ],
        ];

        foreach ($recursos as $recurso) {
            DB::table('recursos')->updateOrInsert(
                ['nombre' => $recurso['nombre']],
                $recurso + ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('✓ ' . count($recursos) . ' recursos creados/actualizados.');
    }
}
