<?php
$modulo = App\Models\Cart\ModuloCliente::where('slug', 'catalogo')->first();
if (!$modulo) { echo "NO EXISTE - re-registrar\n"; exit; }
echo "Módulo encontrado: ID=" . $modulo->id . "\n";
$service = app(App\Modules\Cart\Services\ModuleTokenService::class);
$tokens = $service->issuePair($modulo);
echo "CART_CLIENT_MODULE_TOKEN=" . $tokens['access_token'] . "\n";
echo "CART_CLIENT_REFRESH_TOKEN=" . $tokens['refresh_token'] . "\n";