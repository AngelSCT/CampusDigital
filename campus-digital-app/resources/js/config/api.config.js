/**
 * Configuración de endpoints para las APIs externas e internas.
 * Cuando el equipo backend proporcione sus URLs reales, actualizar VITE_EXTERNAL_API_URL en .env
 */
export const API_CONFIG = {
    // URL base de la API externa del equipo de backend (recargas/saldos)
    EXTERNAL_API_BASE_URL: import.meta.env.VITE_EXTERNAL_API_URL || 'https://api.campus-recargas.example.com',

    // Endpoints internos (nuestro propio backend)
    ENDPOINTS: {
        SALDO:         '/modulo_8/saldo',
        MOVIMIENTOS:   '/modulo_8/movimientos',
        ESTADISTICAS:  '/modulo_8/estadisticas',
        COMPROBANTES:  '/modulo_8/comprobantes',
        RECARGAR:      '/modulo_8/recargar',
        PAGAR:         '/modulo_8/pagar',
    },

    // Endpoints de la API externa (equipo backend)
    EXTERNAL_ENDPOINTS: {
        SALDO:          '/saldo',
        TRANSACCIONES:  '/transacciones',
        RECARGAR:       '/recargar',
        PAGAR:          '/pagar',
        HISTORIAL:      '/historial',
        ESTADISTICAS:   '/estadisticas',
    },

    // Timeout para peticiones en milisegundos
    TIMEOUT: 10000,

    DEFAULT_HEADERS: {
        'Content-Type': 'application/json',
        'Accept':       'application/json',
    },
};

export default API_CONFIG;
