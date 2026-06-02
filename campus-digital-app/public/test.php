<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = config('cart.client.module_token');
$refreshToken = config('cart.client.refresh_token');
$baseUrl = rtrim(config('cart.api.base_url'), '/');
$uuid = '12c20ced-6304-401e-b9d7-7ff2669f7642';
$url = $baseUrl . '/carritos/' . $uuid . '/checkout';

$refreshResp = \Illuminate\Support\Facades\Http::withToken($refreshToken)
    ->acceptJson()
    ->post($baseUrl . '/tokens/refresh', ['refresh_token' => $refreshToken]);

if (!$refreshResp->successful()) {
    echo "REFRESH FAILED: " . $refreshResp->status() . "\n";
    echo $refreshResp->body() . "\n";
    exit;
}

$newToken = $refreshResp->json('access_token');

$resp = \Illuminate\Support\Facades\Http::withToken($newToken)
    ->acceptJson()
    ->post($url);

echo "STATUS: " . $resp->status() . "\n";
echo "BODY: " . $resp->body() . "\n";
