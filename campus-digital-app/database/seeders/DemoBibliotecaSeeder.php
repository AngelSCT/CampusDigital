<?php

namespace Database\Seeders;

use App\Models\Cart\ModuloCliente;
use App\Models\Cart\SolicitudModulo;
use Illuminate\Database\Seeder;

/**
 * Configura los registros de DB para el módulo demo Biblioteca.
 * Idempotente: puede ejecutarse varias veces sin duplicar datos.
 *
 * La generación de tokens (JWT) la hace DemoBibliotecaSetupCommand,
 * que también escribe las variables directamente al .env.
 */
class DemoBibliotecaSeeder extends Seeder
{
    public function run(): void
    {
        // Categorías base deben existir antes.
        $this->call(CategoriasSeeder::class);

        // ── Solicitud (idempotente) ──────────────────────────────────────────
        $solicitud = SolicitudModulo::firstOrCreate(
            ['folio' => 'DEMO-BIB-001'],
            [
                'nombre_modulo'          => 'Biblioteca Demo',
                'tipo_modulo'            => 'biblioteca',
                'categorias_solicitadas' => ['prestamo'],
                'contacto_nombre'        => 'Admin Demo',
                'contacto_email'         => 'demo@campus.test',
                'estado'                 => SolicitudModulo::ESTADO_APROBADA,
            ]
        );

        if ($solicitud->estado !== SolicitudModulo::ESTADO_APROBADA) {
            $solicitud->update(['estado' => SolicitudModulo::ESTADO_APROBADA]);
        }

        // ── ModuloCliente (idempotente) ──────────────────────────────────────
        $modulo = ModuloCliente::firstOrCreate(
            ['solicitud_id' => $solicitud->id],
            [
                'nombre'                 => 'Biblioteca Demo',
                'slug'                   => 'biblioteca-demo',
                'tipo_modulo'            => 'biblioteca',
                'categorias_autorizadas' => ['prestamo'],
            ]
        );

        $this->command?->info("  DB: ModuloCliente id={$modulo->id} slug={$modulo->slug}");
    }
}
