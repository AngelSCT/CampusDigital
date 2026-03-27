import axios from 'axios';
import { API_CONFIG } from '../config/api.config.js';

/**
 * Servicio centralizado para consumir APIs de transacciones.
 *
 * Actualmente apunta a los endpoints internos de Laravel.
 * Cuando el equipo de backend entregue su API externa, cambiar las llamadas
 * correspondientes a `externalApi` y ajustar las URLs en api.config.js.
 */

// Cliente HTTP para nuestra API interna (Laravel + Inertia)
const internalApi = axios.create({
    baseURL: '/',
    timeout: API_CONFIG.TIMEOUT,
    headers: {
        ...API_CONFIG.DEFAULT_HEADERS,
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
});

// Cliente HTTP para la API externa del equipo de backend
// Activar cuando la API esté disponible: descomentarlo y ajustar los métodos al final de este archivo.
// const externalApi = axios.create({
//     baseURL: API_CONFIG.EXTERNAL_API_BASE_URL,
//     timeout: API_CONFIG.TIMEOUT,
//     headers: API_CONFIG.DEFAULT_HEADERS,
// });

// Inyectar token CSRF en cada petición interna
internalApi.interceptors.request.use((config) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) config.headers['X-CSRF-TOKEN'] = token;
    return config;
});

// Manejador de errores compartido
function handleError(error) {
    if (error.response) {
        const { status, data } = error.response;
        if (status === 401) throw new Error('Sesión expirada. Por favor inicia sesión nuevamente.');
        if (status === 403) throw new Error('No tienes permiso para realizar esta acción.');
        if (status === 422) throw new Error(data.message || 'Datos inválidos.');
        if (status >= 500) throw new Error('Error del servidor. Intenta más tarde.');
        throw new Error(data.message || `Error ${status}`);
    }
    if (error.request) throw new Error('Sin respuesta del servidor. Verifica tu conexión.');
    throw new Error(error.message || 'Error desconocido.');
}

internalApi.interceptors.response.use((r) => r, handleError);
// externalApi.interceptors.response.use((r) => r, handleError);

/**
 * API de transacciones.
 * Todos los métodos devuelven una Promise con los datos de la respuesta.
 */
export const transactionApi = {
    /**
     * Obtiene el saldo del usuario autenticado.
     * @returns {Promise<{ saldo: number, usuario_id: number }>}
     */
    async getSaldo() {
        const { data } = await internalApi.get(API_CONFIG.ENDPOINTS.SALDO);
        return data;
    },

    /**
     * Obtiene el historial de movimientos.
     * @param {object} params - Filtros opcionales (tipo, estado, etc.)
     * @returns {Promise<Array>}
     */
    async getMovimientos(params = {}) {
        const { data } = await internalApi.get(API_CONFIG.ENDPOINTS.MOVIMIENTOS, { params });
        return data;
    },

    /**
     * Obtiene las estadísticas del dashboard (saldo, exitosos, fallidos).
     * @returns {Promise<object>}
     */
    async getEstadisticas() {
        const { data } = await internalApi.get(API_CONFIG.ENDPOINTS.ESTADISTICAS);
        return data;
    },

    /**
     * Obtiene los comprobantes del usuario.
     * @returns {Promise<Array>}
     */
    async getComprobantes() {
        const { data } = await internalApi.get(API_CONFIG.ENDPOINTS.COMPROBANTES);
        return data;
    },

    /**
     * Realiza una recarga de saldo.
     * @param {{ monto: number, metodo_pago: string }} payload
     * @returns {Promise<object>}
     */
    async recargar(payload) {
        const { data } = await internalApi.post(API_CONFIG.ENDPOINTS.RECARGAR, payload);
        return data;
    },

    /**
     * Realiza un pago con el saldo disponible.
     * @param {{ monto: number, concepto: string }} payload
     * @returns {Promise<object>}
     */
    async pagar(payload) {
        const { data } = await internalApi.post(API_CONFIG.ENDPOINTS.PAGAR, payload);
        return data;
    },

    // ──────────────────────────────────────────────────────────────────
    // Métodos para consumir la API EXTERNA del equipo de backend.
    // Descomentar y ajustar cuando la API esté disponible.
    // ──────────────────────────────────────────────────────────────────

    // async getSaldoExterno(token) {
    //     externalApi.defaults.headers['Authorization'] = `Bearer ${token}`;
    //     const { data } = await externalApi.get(API_CONFIG.EXTERNAL_ENDPOINTS.SALDO);
    //     return data;
    // },

    // async getTransaccionesExternas(token, params = {}) {
    //     externalApi.defaults.headers['Authorization'] = `Bearer ${token}`;
    //     const { data } = await externalApi.get(API_CONFIG.EXTERNAL_ENDPOINTS.TRANSACCIONES, { params });
    //     return data;
    // },
};

export default transactionApi;
