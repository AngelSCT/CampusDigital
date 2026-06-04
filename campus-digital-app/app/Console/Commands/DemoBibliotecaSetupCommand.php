<?php

namespace App\Console\Commands;

use App\Models\Cart\ModuloCliente;
use App\Models\Cart\TokenModulo;
use App\Modules\Cart\Services\ModuleTokenService;
use Database\Seeders\DemoBibliotecaSeeder;
use Illuminate\Console\Command;

class DemoBibliotecaSetupCommand extends Command
{
    protected $signature   = 'carrito:demo-biblioteca-setup';
    protected $description = 'Configura el demo del Módulo Biblioteca: crea el módulo en DB, emite tokens y los escribe en .env';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   Carrito — Demo Módulo Biblioteca       ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('');

        // ── 1. Setup de DB (idempotente) ─────────────────────────────────────
        $this->info('1/3 Configurando módulo en base de datos...');
        $this->call(DemoBibliotecaSeeder::class);

        // ── 2. Emitir par de tokens fresco ───────────────────────────────────
        $this->info('2/3 Emitiendo tokens...');

        $modulo = ModuloCliente::where('slug', 'biblioteca-demo')->firstOrFail();
        $tokenService = app(ModuleTokenService::class);

        // Revocar tokens de acceso activos anteriores
        TokenModulo::where('modulo_id', $modulo->id)
            ->where('tipo', TokenModulo::TIPO_ACCESS)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->get()
            ->each(fn($t) => $tokenService->revoke($t->jti, 'rotacion_demo'));

        $pair = $tokenService->issuePair($modulo);

        // ── 3. Escribir tokens directamente al .env ───────────────────────────
        $this->info('3/3 Escribiendo tokens en .env...');

        $this->writeEnvVars([
            'BIBLIOTECA_CART_TOKEN'    => $pair['access_token'],
            'BIBLIOTECA_REFRESH_TOKEN' => $pair['refresh_token'],
        ]);

        // ── Limpiar caché de config ───────────────────────────────────────────
        $this->call('config:clear');

        // ── Resumen ───────────────────────────────────────────────────────────
        $this->info('');
        $this->info('✓ Setup completado. No necesitas copiar nada manualmente.');
        $this->info('');
        $this->line('  Módulo : ' . $modulo->nombre . ' (ID ' . $modulo->id . ')');
        $this->line('  Token  : escrito en BIBLIOTECA_CART_TOKEN (.env)');
        $this->line('  Refresh: escrito en BIBLIOTECA_REFRESH_TOKEN (.env)');
        $this->info('');
        $this->info('  Visita: ' . config('app.url') . '/demo/biblioteca/prestamo');
        $this->info('');
        $this->info('  Para regenerar tokens (ej. si se revocan desde /admin/cart/modulos):');
        $this->line('    php artisan carrito:demo-biblioteca-setup');
        $this->info('');
        $this->info('El JWT del módulo NUNCA sale del servidor.');

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
