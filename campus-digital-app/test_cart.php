<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = app(App\Services\TicketCartClient::class);
$response = $client->crearCarrito('1', 2);
dump($response);
