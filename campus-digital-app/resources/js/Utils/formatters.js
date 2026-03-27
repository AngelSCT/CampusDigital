/**
 * Utilidades de formato para el Módulo 8 - Recargas y Monedero
 */

/**
 * Formatea un valor numérico como moneda (MXN)
 * @param {number|string} valor
 * @param {boolean} incluirSimbolo
 * @returns {string}
 */
export function formatMonto(valor, incluirSimbolo = true) {
    const num = Number(valor) || 0;
    const formateado = num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return incluirSimbolo ? `$${formateado}` : formateado;
}

/**
 * Formatea una fecha y hora en formato legible (español México)
 * @param {string|Date} fecha
 * @returns {string}
 */
export function formatDateTime(fecha) {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleString('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

/**
 * Formatea solo la fecha (sin hora)
 * @param {string|Date} fecha
 * @returns {string}
 */
export function formatDate(fecha) {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

/**
 * Formatea una hora a partir de una fecha
 * @param {string|Date} fecha
 * @returns {string}
 */
export function formatTime(fecha) {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit',
    });
}

/**
 * Retorna la etiqueta legible del método de pago
 * @param {string} metodo
 * @returns {string}
 */
export function metodoLabel(metodo) {
    const labels = {
        tarjeta: 'Tarjeta',
        transferencia: 'Transferencia',
        efectivo: 'Efectivo',
        billetera_digital: 'Billetera Digital',
    };
    return labels[metodo] || metodo;
}

/**
 * Retorna la clase CSS Tailwind para el badge de estado
 * @param {string} estado
 * @returns {string}
 */
export function badgeClass(estado) {
    switch (estado) {
        case 'exitoso':
        case 'exitosa':
            return 'bg-green-500/20 text-green-400 border-green-500/30';
        case 'fallido':
        case 'fallida':
            return 'bg-red-500/20 text-red-400 border-red-500/30';
        case 'pendiente':
            return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
        default:
            return 'bg-slate-500/20 text-slate-400 border-slate-500/30';
    }
}

/**
 * Retorna el ícono SVG path para el estado
 * @param {string} estado
 * @returns {string}
 */
export function estadoIconPath(estado) {
    switch (estado) {
        case 'exitoso':
        case 'exitosa':
            return 'M5 13l4 4L19 7';
        case 'fallido':
        case 'fallida':
            return 'M6 18L18 6M6 6l12 12';
        default:
            return 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
    }
}

/**
 * Capitaliza la primera letra de un string
 * @param {string} str
 * @returns {string}
 */
export function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
