<?php
$juan = App\Models\Usuario::where('email', 'estudiante@campusdigital.com')->first();
echo "Juan ID: " . $juan->id . "\n";
$m = App\Models\SaldoMonedero::firstOrCreate(
    ['usuario_id' => $juan->id],
    ['saldo_disponible' => 0, 'saldo_retenido' => 0]
);
$m->saldo_disponible = 500.00;
$m->save();
echo "Saldo asignado: $500.00\n";