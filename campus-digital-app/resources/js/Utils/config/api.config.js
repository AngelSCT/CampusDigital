/**
 * Configuración de endpoints de la API
 * Módulo 8 - Recargas y Monedero
 */

export const API_BASE_URL = '/modulo_8';

export const API_ENDPOINTS = {
    // Wallet & Saldo
    saldo: `${API_BASE_URL}/saldo`,
    movimientos: `${API_BASE_URL}/movimientos`,
    comprobantes: `${API_BASE_URL}/comprobantes`,

    // Recargas
    recargar: `${API_BASE_URL}/recargar`,
    reintentarRecarga: (id) => `${API_BASE_URL}/recargar/${id}/reintentar`,
    comprobante: (id) => `${API_BASE_URL}/recargar/${id}/comprobante`,

    // Pagos
    pagar: `${API_BASE_URL}/pagar`,
};

export const LIMITES_DEFAULT = {
    montoMinimo: 1,
    montoMaximo: 5000,
    maxRecargasDia: 3,
    intervaloMinutos: 5,
};

export const METODOS_PAGO = [
    { value: 'tarjeta', label: 'Tarjeta', icon: '💳' },
    { value: 'transferencia', label: 'Transferencia', icon: '🏦' },
    { value: 'efectivo', label: 'Efectivo', icon: '💵' },
    { value: 'billetera_digital', label: 'Billetera', icon: '📱' },
];

export const MONTOS_RAPIDOS = [50, 100, 200, 300, 500, 1000];
