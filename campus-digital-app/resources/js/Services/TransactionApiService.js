/**
 * Servicio de API centralizado para transacciones del Módulo 8
 * Prepara el cliente HTTP para consumir APIs externas del otro equipo
 */

import { API_ENDPOINTS } from '../Utils/config/api.config.js';

/**
 * Realiza una petición fetch con CSRF token para Laravel
 * @param {string} url
 * @param {object} options
 * @returns {Promise<Response>}
 */
async function fetchWithCsrf(url, options = {}) {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
    };

    return fetch(url, {
        ...options,
        headers: {
            ...defaultHeaders,
            ...(options.headers || {}),
        },
    });
}

/**
 * Maneja la respuesta y lanza error si no es exitosa
 * @param {Response} response
 * @returns {Promise<any>}
 */
async function handleResponse(response) {
    if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        const mensaje = data.message || `Error HTTP: ${response.status}`;
        throw new Error(mensaje);
    }
    return response.json();
}

const TransactionApiService = {
    /**
     * Obtiene el saldo del usuario autenticado
     * @returns {Promise<object>}
     */
    async getSaldo() {
        const response = await fetchWithCsrf(API_ENDPOINTS.saldo);
        return handleResponse(response);
    },

    /**
     * Obtiene el historial de movimientos
     * @returns {Promise<Array>}
     */
    async getMovimientos() {
        const response = await fetchWithCsrf(API_ENDPOINTS.movimientos);
        return handleResponse(response);
    },

    /**
     * Obtiene los comprobantes del usuario
     * @returns {Promise<Array>}
     */
    async getComprobantes() {
        const response = await fetchWithCsrf(API_ENDPOINTS.comprobantes);
        return handleResponse(response);
    },

    /**
     * Procesa una recarga de saldo
     * @param {{ monto: number, metodo_pago: string }} datos
     * @returns {Promise<object>}
     */
    async procesarRecarga(datos) {
        const response = await fetchWithCsrf(API_ENDPOINTS.recargar, {
            method: 'POST',
            body: JSON.stringify(datos),
        });
        return handleResponse(response);
    },

    /**
     * Reintenta una recarga fallida
     * @param {number|string} id
     * @returns {Promise<object>}
     */
    async reintentar(id) {
        const response = await fetchWithCsrf(API_ENDPOINTS.reintentarRecarga(id), {
            method: 'POST',
        });
        return handleResponse(response);
    },

    /**
     * Descarga el comprobante de una recarga exitosa
     * @param {number|string} id
     */
    descargarComprobante(id) {
        window.location.href = API_ENDPOINTS.comprobante(id);
    },
};

export default TransactionApiService;
