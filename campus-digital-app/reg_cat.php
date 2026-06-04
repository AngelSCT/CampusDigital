<?php
$solicitud = App\Models\Cart\SolicitudModulo::create([
    'tipo_modulo' => 'catalogo',
    'nombre_modulo' => 'Catalogo',
    'contacto_nombre' => 'Admin Catalogo',
    'contacto_email' => 'catalogo@campusdigital.com',
    'categorias_solicitadas' => ['cafeteria','copias','tramites','souvenirs','servicios'],
    'estado' => 'aprobada',
    'folio' => 'CAT-002',
]);
$modulo = App\Models\Cart\ModuloCliente::create([
    'nombre' => 'Catalogo',
    'slug' => 'catalogo',
    'tipo_modulo' => 'catalogo',
    'categorias_autorizadas' => ['cafeteria','copias','tramites','souvenirs','servicios'],
    'activo' => true,
    'solicitud_id' => $solicitud->id,
]);
$service = app(App\Modules\Cart\Services\ModuleTokenService::class);
$tokens = $service->issuePair($modulo);
echo "CART_CLIENT_MODULE_TOKEN=" . $tokens['access_token'] . "\n";
echo "CART_CLIENT_REFRESH_TOKEN=" . $tokens['refresh_token'] . "\n";