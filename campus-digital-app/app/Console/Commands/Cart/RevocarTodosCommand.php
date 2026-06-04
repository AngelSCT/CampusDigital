<?php

namespace App\Console\Commands\Cart;

use App\Models\Cart\TokenModulo;
use Illuminate\Console\Command;

class RevocarTodosCommand extends Command
{
    protected $signature   = 'carrito:revocar-todos';
    protected $description = 'Revoca TODOS los tokens activos del módulo Carrito (usar tras rotación del secreto JWT).';

    public function handle(): int
    {
        $count = TokenModulo::where('estado', TokenModulo::ESTADO_ACTIVO)->count();

        if ($count === 0) {
            $this->info('No hay tokens activos. Nada que revocar.');
            return self::SUCCESS;
        }

        $this->warn("Se revocarán {$count} token(s) activo(s).");
        $this->warn('Todos los módulos deberán recibir tokens nuevos desde el panel de administración.');

        if (!$this->confirm('¿Confirmas la revocación masiva? Esta acción es irreversible.')) {
            $this->info('Cancelado. No se realizaron cambios.');
            return self::SUCCESS;
        }

        TokenModulo::where('estado', TokenModulo::ESTADO_ACTIVO)
            ->update([
                'estado'            => TokenModulo::ESTADO_REVOCADO,
                'revocado_at'       => now(),
                'motivo_revocacion' => 'rotacion_secreto',
            ]);

        $this->info("Se revocaron {$count} token(s). Emite nuevos tokens desde el panel.");

        return self::SUCCESS;
    }
}
