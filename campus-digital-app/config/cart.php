<?php

return [
    'jwt' => [
        // Secreto HS256 de 256 bits (64 chars hex). Generar con: php artisan carrito:gen-secret
        'secret'      => env('MODULE_JWT_SECRET', ''),
        'ttl_access'  => (int) env('MODULE_JWT_TTL_ACCESS', 3600),    // segundos
        'ttl_refresh' => (int) env('MODULE_JWT_TTL_REFRESH', 604800), // 7 días
    ],

    // Integración con módulo Saldo Digital Universitario (C5)
    'saldo' => [
        // URL base del módulo Saldo — ajustar según entorno
        'base_url'                   => env('CART_SALDO_BASE_URL', 'http://localhost'),
        // Timeout en segundos para cada llamada HTTP
        'timeout'                    => (int) env('CART_SALDO_TIMEOUT', 3),
        // Endpoints — configurables por si el equipo de Saldo cambia el contrato
        'endpoint_reservar'          => env('CART_SALDO_ENDPOINT_RESERVAR', '/api/internal/saldo/reservar'),
        'endpoint_cargo_forzoso'     => env('CART_SALDO_ENDPOINT_CARGO_FORZOSO', '/api/internal/saldo/cargo-forzoso'),
        // Topes de exposición por pago diferido (MXN)
        'tope_pendiente_por_usuario' => (float) env('CART_SALDO_TOPE_USUARIO', 200),
        'tope_pendiente_global'      => (float) env('CART_SALDO_TOPE_GLOBAL', 50000),
        // Reintentos máximos antes de marcar como requiere_revision_manual
        'reintentos_max'             => (int) env('CART_SALDO_REINTENTOS_MAX', 5),
    ],
];
