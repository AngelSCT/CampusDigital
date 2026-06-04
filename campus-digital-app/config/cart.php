<?php

return [
    'jwt' => [
        // Secreto HS256 de 256 bits (64 chars hex). Generar con: php artisan carrito:gen-secret
        'secret'      => env('MODULE_JWT_SECRET', ''),
        'ttl_access'  => (int) env('MODULE_JWT_TTL_ACCESS', 604800),    // segundos
        'ttl_refresh' => (int) env('MODULE_JWT_TTL_REFRESH', 604800), // 7 días
    ],

    // Integración con módulo Saldo Digital Universitario (C5)
    'saldo' => [
        // URL base del módulo Saldo — ajustar según entorno
        'base_url'                   => env('CART_SALDO_BASE_URL', 'http://localhost'),
        // Timeout en segundos para cada llamada HTTP
        'timeout'                    => (int) env('CART_SALDO_TIMEOUT', 3),
        // Endpoints — configurables por si el equipo de Saldo cambia el contrato
        // Secreto compartido para el header X-Internal-Token
        'internal_token'             => env('CART_SALDO_INTERNAL_TOKEN', ''),
        // Endpoints — configurables por si el equipo de Saldo cambia el contrato
        'endpoint_reservar'          => env('CART_SALDO_ENDPOINT_RESERVAR',      '/api/internal/saldo/reservar'),
        'endpoint_confirmar'         => env('CART_SALDO_ENDPOINT_CONFIRMAR',      '/api/internal/saldo/confirmar/{reserva_id}'),
        'endpoint_liberar'           => env('CART_SALDO_ENDPOINT_LIBERAR',        '/api/internal/saldo/liberar/{reserva_id}'),
        'endpoint_cargo_forzoso'     => env('CART_SALDO_ENDPOINT_CARGO_FORZOSO',  '/api/internal/saldo/cargo-forzoso'),
        // Topes de exposición por pago diferido (MXN)
        'tope_pendiente_por_usuario' => (float) env('CART_SALDO_TOPE_USUARIO', 200),
        'tope_pendiente_global'      => (float) env('CART_SALDO_TOPE_GLOBAL', 50000),
        // Reintentos máximos antes de marcar como requiere_revision_manual
        'reintentos_max'             => (int) env('CART_SALDO_REINTENTOS_MAX', 5),
        // TTL (minutos) para limpiar conciliaciones huérfanas en estado 'procesando'.
        // Si un job muere entre TX1 y TX2, la conciliación queda en 'procesando'.
        // Pasado este TTL → requiere_revision_manual (NUNCA → pendiente).
        'procesando_ttl_minutos'     => (int) env('CART_SALDO_PROCESANDO_TTL', 10),
        // true → usa LocalSaldoClient (SaldoMonedero directo) en lugar de HTTP hacia M4.2
        'local_mode'                 => (bool) env('CART_SALDO_LOCAL_MODE', false),
    ],

    // Configuración del checkout del Carrito
    'checkout' => [
        // TTL (minutos) para limpiar carritos huérfanos en estado 'procesando_checkout'.
        // DEBE ser mayor al TTL de reserva del Módulo 4.2 para garantizar que
        // la reserva expiró antes de reabrir el carrito.
        'procesando_ttl_minutos' => (int) env('CART_CHECKOUT_PROCESANDO_TTL', 10),
    ],

    // Integración con Módulo 4.5 — Pedidos y Seguimiento
    'pedidos' => [
        'url'     => env('PEDIDOS_API_URL', 'http://localhost:8000'),
        'api_key' => env('PEDIDOS_API_KEY', 'dev-local-key-campus-digital-2026'),
    ],

    // Configuración para módulos CLIENTES que consumen la API del Carrito
    'client' => [
        // JWT emitido por el panel admin al aprobar la solicitud del módulo
        'module_token'  => env('CART_CLIENT_MODULE_TOKEN'),
        'refresh_token' => env('CART_CLIENT_REFRESH_TOKEN'),
        // URL base: ahora en config/services.php → services.cart.url (CART_API_URL)
    ],
];
