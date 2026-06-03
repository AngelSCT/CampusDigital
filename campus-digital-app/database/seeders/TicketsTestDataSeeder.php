<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketsTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tablas para evitar duplicados en caso de re-ejecución
        DB::table('equipos_activos')->delete();
        DB::table('ubicaciones')->delete();
        DB::table('categorias_ticket')->delete();
        DB::table('area')->delete();

        // 1. Insertar áreas de soporte (Módulo 4.9)
        $areaSistemas = DB::table('area')->insertGetId([
            'name_area' => 'Sistemas / TI',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $areaInfra = DB::table('area')->insertGetId([
            'name_area' => 'Infraestructura',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $areaServicios = DB::table('area')->insertGetId([
            'name_area' => 'Servicios Generales',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Insertar categorías mapeadas a las áreas
        $catRed = DB::table('categorias_ticket')->insertGetId([
            'id_area' => $areaSistemas,
            'nombre_categoria' => 'Falla de Red/Internet',
            'tiempo_sla_horas' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catEquipo = DB::table('categorias_ticket')->insertGetId([
            'id_area' => $areaSistemas,
            'nombre_categoria' => 'Falla de Equipo',
            'tiempo_sla_horas' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catIlum = DB::table('categorias_ticket')->insertGetId([
            'id_area' => $areaInfra,
            'nombre_categoria' => 'Falla de Iluminación',
            'tiempo_sla_horas' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catClima = DB::table('categorias_ticket')->insertGetId([
            'id_area' => $areaInfra,
            'nombre_categoria' => 'Falla de Clima',
            'tiempo_sla_horas' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catLimp = DB::table('categorias_ticket')->insertGetId([
            'id_area' => $areaServicios,
            'nombre_categoria' => 'Limpieza',
            'tiempo_sla_horas' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catJard = DB::table('categorias_ticket')->insertGetId([
            'id_area' => $areaServicios,
            'nombre_categoria' => 'Jardinería',
            'tiempo_sla_horas' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Insertar ubicaciones
        $locA = DB::table('ubicaciones')->insertGetId([
            'edificio' => 'Edificio A',
            'aula_departamento' => 'Aula 101',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $locB = DB::table('ubicaciones')->insertGetId([
            'edificio' => 'Edificio B',
            'aula_departamento' => 'Biblioteca',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $locC = DB::table('ubicaciones')->insertGetId([
            'edificio' => 'Edificio C',
            'aula_departamento' => 'Laboratorio de Cómputo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Insertar equipos activos vinculados a ubicación y categoría
        DB::table('equipos_activos')->insert([
            [
                'id_categoria' => $catEquipo,
                'id_ubicacion' => $locC,
                'nombre_equipo' => 'Computadora Dell OptiPlex',
                'estado_actual' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_categoria' => $catRed,
                'id_ubicacion' => $locA,
                'nombre_equipo' => 'Proyector Epson',
                'estado_actual' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_categoria' => $catEquipo,
                'id_ubicacion' => $locB,
                'nombre_equipo' => 'Impresora HP LaserJet',
                'estado_actual' => 'Mantenimiento',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
