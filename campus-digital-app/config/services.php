<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

<<<<<<< HEAD
    // Módulo Carrito — URL base del servidor que expone la Cart API.
    // En local apunta al mismo Laravel. En producción/staging ajustar CART_API_URL.
    'cart' => [
        'url'                      => env('CART_API_URL', env('APP_URL', 'http://localhost')),
        // Tokens del módulo Demo Biblioteca — generados por: php artisan carrito:demo-biblioteca-setup
        'biblioteca_token'         => env('BIBLIOTECA_CART_TOKEN'),
        'biblioteca_refresh_token' => env('BIBLIOTECA_REFRESH_TOKEN'),
=======
    /*
    |--------------------------------------------------------------------------
    | Módulo 2 — API REST (Recargas, Saldo, Pedidos)
    |--------------------------------------------------------------------------
    |
    | MODULO2_API_URL  → URL base del servidor del Módulo 2
    | MODULO2_API_KEY  → Clave para el header X-API-KEY
    |
    */
    'modulo2_api' => [
        'base_url' => env('MODULO2_API_URL', 'http://localhost:8001'),
        'api_key'  => env('MODULO2_API_KEY', ''),
>>>>>>> origin/4.8_Recargas_final
    ],

];
