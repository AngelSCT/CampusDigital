<?php

namespace App\Console\Commands\Cart;

use App\Models\Cart\ModuloCliente;
use App\Models\Cart\SolicitudModulo;
use App\Models\Cart\TokenModulo;
use App\Modules\Cart\Services\ModuleTokenService;
use Illuminate\Console\Command;

class TicketsSetupCommand extends Command
{
    protected $signature   = 'carrito:tickets-setup';
    protected $description = 'Configura la conexión del Módulo de Tickets: crea el módulo en DB, emite tokens y los escribe en .env';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   Carrito — Módulo Tickets               ║');
        $this->info('╚══════════════════════════════════════════╝');

        $this->info('1/3 Configurando módulo en base de datos...');

        $solicitud = SolicitudModulo::firstOrCreate(
            ['folio' => 'SYS-TICKETS'],
            [
                'nombre_modulo'          => 'Sistema de Tickets y Mantenimiento',
                'tipo_modulo'            => 'tickets',
                'categorias_solicitadas' => ['servicio', 'producto'],
                'contacto_nombre'        => 'Admin Tickets',
                'contacto_email'         => 'tickets@campus.test',
                'estado'                 => SolicitudModulo::ESTADO_APROBADA,
            ]
        );

        $modulo = ModuloCliente::firstOrCreate(
            ['solicitud_id' => $solicitud->id],
            [
                'nombre' => 'Sistema de Tickets y Mantenimiento',
                'slug' => 'tickets-mantenimiento',
                'tipo_modulo' => 'tickets',
                'categorias_autorizadas' => ['servicio', 'producto'],
                'estado' => 'activo',
                'webhooks' => [
                    'pago_exitoso' => config('tickets.api.base_url') . '/api/tickets/{ticket_id}/confirmar-pago'
                ],
                'permite_pago_diferido' => false,
            ]
        );

        $this->info('2/3 Emitiendo tokens...');
        $tokenService = app(ModuleTokenService::class);

        TokenModulo::where('modulo_id', $modulo->id)
            ->where('tipo', TokenModulo::TIPO_ACCESS)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->get()
            ->each(fn($t) => $tokenService->revoke($t->jti, 'rotacion_demo'));

        $pair = $tokenService->issuePair($modulo);

        $this->info('3/3 Escribiendo tokens en .env...');
        $this->writeEnvVars([
            'TICKETS_CART_MODULE_TOKEN'    => $pair['access_token'],
            'TICKETS_CART_REFRESH_TOKEN' => $pair['refresh_token'],
        ]);

        $this->call('config:clear');

        $this->info('✓ Setup completado exitosamente.');
        return self::SUCCESS;
    }

    private function writeEnvVars(array $vars): void
    {
        $path    = base_path('.env');
        $content = file_get_contents($path);

        foreach ($vars as $key => $value) {
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($path, $content);
    }
}
