<?php

return [
    'api' => [
        'base_url'  => env('TICKETS_API_BASE_URL', env('APP_URL', 'http://localhost')),
        'api_key'   => env('TICKETS_API_KEY', 'dev-local-key-campus-digital-2026'),
        'timeout'   => env('TICKETS_API_TIMEOUT', 5),
    ],
    'cart' => [
        'module_token'  => env('TICKETS_CART_MODULE_TOKEN'),
        'refresh_token' => env('TICKETS_CART_REFRESH_TOKEN'),
    ],
];
