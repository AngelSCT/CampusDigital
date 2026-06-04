<?php

namespace App\Console\Commands\Cart;

use Illuminate\Console\Command;

class GenSecretCommand extends Command
{
    protected $signature   = 'carrito:gen-secret';
    protected $description = 'Genera un secreto HS256 de 256 bits para MODULE_JWT_SECRET. Solo lo imprime; no modifica el .env.';

    public function handle(): int
    {
        $secret = bin2hex(random_bytes(32)); // 256 bits → 64 chars hex

        $this->newLine();
        $this->line('  <comment>Copia la siguiente línea en tu archivo .env de forma manual.</comment>');
        $this->line('  <comment>NO compartas este secreto ni lo subas al repositorio.</comment>');
        $this->newLine();
        $this->line("  MODULE_JWT_SECRET={$secret}");
        $this->newLine();

        return self::SUCCESS;
    }
}
