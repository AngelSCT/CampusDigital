<?php

return [

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

    'modulo2_api' => [
        'base_url' => env('MODULO2_API_URL', 'http://localhost:8001'),
        'api_key'  => env('MODULO2_API_KEY', ''),
    ],

    'modulo4_cart' => [
        'base_url'      => env('MODULO4_CART_URL', 'http://localhost:8002'),
        'access_token'  => env('MODULO4_CART_TOKEN', ''),
        'refresh_token' => env('MODULO4_CART_REFRESH_TOKEN', ''),
    ],

];
