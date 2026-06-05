<?php

return [
    'api' => [
        'base_url' => env('CART_API_BASE_URL', env('CART_API_URL', 'http://127.0.0.1:8000/api/cart')),
    ],

    'jwt' => [
        'secret' => env('MODULE_JWT_SECRET', ''),
        'ttl_access' => (int) env('MODULE_JWT_TTL_ACCESS', 604800),
        'ttl_refresh' => (int) env('MODULE_JWT_TTL_REFRESH', 604800),
    ],

    'saldo' => [
        'base_url' => env('CART_SALDO_BASE_URL', 'http://localhost'),
        'timeout' => (int) env('CART_SALDO_TIMEOUT', 3),
        'internal_token' => env('CART_SALDO_INTERNAL_TOKEN', ''),
        'endpoint_reservar' => env('CART_SALDO_ENDPOINT_RESERVAR', '/api/internal/saldo/reservar'),
        'endpoint_confirmar' => env('CART_SALDO_ENDPOINT_CONFIRMAR', '/api/internal/saldo/confirmar/{reserva_id}'),
        'endpoint_liberar' => env('CART_SALDO_ENDPOINT_LIBERAR', '/api/internal/saldo/liberar/{reserva_id}'),
        'endpoint_cargo_forzoso' => env('CART_SALDO_ENDPOINT_CARGO_FORZOSO', '/api/internal/saldo/cargo-forzoso'),
        'tope_pendiente_por_usuario' => (float) env('CART_SALDO_TOPE_USUARIO', 200),
        'tope_pendiente_global' => (float) env('CART_SALDO_TOPE_GLOBAL', 50000),
        'reintentos_max' => (int) env('CART_SALDO_REINTENTOS_MAX', 5),
        'procesando_ttl_minutos' => (int) env('CART_SALDO_PROCESANDO_TTL', 10),
        'local_mode' => (bool) env('CART_SALDO_LOCAL_MODE', false),
    ],

    'checkout' => [
        'procesando_ttl_minutos' => (int) env('CART_CHECKOUT_PROCESANDO_TTL', 10),
    ],

    'pedidos' => [
        'url' => env('PEDIDOS_API_URL', 'http://localhost:8000'),
        'api_key' => env('PEDIDOS_API_KEY', 'dev-local-key-campus-digital-2026'),
    ],

    'catalogo' => [
        'permitir_sin_inventario' => (bool) env('CART_CATALOGO_PERMITIR_SIN_INVENTARIO', true),
    ],

    'client' => [
        'module_token' => env('CART_CLIENT_MODULE_TOKEN'),
        'refresh_token' => env('CART_CLIENT_REFRESH_TOKEN'),
    ],
];