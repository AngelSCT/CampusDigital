<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\SaldoMonedero;

class DemoSaldoSeeder extends Seeder
{
    public function run(): void
    {
        $juan = Usuario::where('email', 'estudiante@campusdigital.com')->first();

        if (! $juan) {
            $this->command->warn('Usuario Juan (estudiante@campusdigital.com) no encontrado — omitiendo saldo demo.');
            return;
        }

        $monedero = SaldoMonedero::firstOrCreate(
            ['usuario_id' => $juan->id],
            ['saldo_disponible' => 0, 'saldo_retenido' => 0]
        );

        if ($monedero->saldo_disponible < 200) {
            $monedero->saldo_disponible = 500.00;
            $monedero->save();
            $this->command->info("Saldo demo asignado: \$500.00 a {$juan->email}");
        } else {
            $this->command->info("Juan ya tiene saldo: \${$monedero->saldo_disponible}");
        }
    }
}
