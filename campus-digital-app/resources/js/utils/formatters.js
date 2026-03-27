/**
 * Funciones de formateo reutilizables para el módulo de estado de cuenta.
 */

/**
 * Formatea un número con separadores de miles y 2 decimales.
 * @param {number|string} amount
 * @returns {string}
 */
export function formatAmount(amount) {
    const num = parseFloat(amount) || 0;
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Formatea un número como moneda mexicana (MXN).
 * @param {number|string} amount
 * @returns {string}
 */
export function formatCurrency(amount) {
    const num = parseFloat(amount) || 0;
    return new Intl.NumberFormat('es-MX', {
        style:                 'currency',
        currency:              'MXN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num);
}

/**
 * Formatea una fecha ISO/Date como fecha legible (dd/mm/yyyy).
 * @param {string|Date} date
 * @returns {string}
 */
export function formatDate(date) {
    if (!date) return '—';
    const d = date instanceof Date ? date : new Date(date);
    if (isNaN(d.getTime())) return '—';
    return new Intl.DateTimeFormat('es-MX', {
        day:   '2-digit',
        month: '2-digit',
        year:  'numeric',
    }).format(d);
}

/**
 * Formatea una fecha ISO/Date como fecha y hora (dd/mm/yyyy hh:mm).
 * @param {string|Date} date
 * @returns {string}
 */
export function formatDateTime(date) {
    if (!date) return '—';
    const d = date instanceof Date ? date : new Date(date);
    if (isNaN(d.getTime())) return '—';
    return new Intl.DateTimeFormat('es-MX', {
        day:    '2-digit',
        month:  '2-digit',
        year:   'numeric',
        hour:   '2-digit',
        minute: '2-digit',
    }).format(d);
}

/**
 * Formatea una fecha como tiempo relativo ("hace 2 horas").
 * @param {string|Date} date
 * @returns {string}
 */
export function formatRelativeTime(date) {
    if (!date) return '—';
    const d    = date instanceof Date ? date : new Date(date);
    const diff = Date.now() - d.getTime();
    const secs = Math.floor(diff / 1000);
    const mins = Math.floor(secs / 60);
    const hrs  = Math.floor(mins / 60);
    const days = Math.floor(hrs / 24);

    if (secs < 60)  return 'hace un momento';
    if (mins < 60)  return `hace ${mins} minuto${mins !== 1 ? 's' : ''}`;
    if (hrs  < 24)  return `hace ${hrs} hora${hrs !== 1 ? 's' : ''}`;
    if (days < 7)   return `hace ${days} día${days !== 1 ? 's' : ''}`;
    return formatDate(d);
}

/**
 * Capitaliza la primera letra de un string.
 * @param {string} str
 * @returns {string}
 */
export function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

/**
 * Devuelve la etiqueta legible del método de pago.
 * @param {string} method
 * @returns {string}
 */
export function formatPaymentMethod(method) {
    const map = {
        tarjeta:           'Tarjeta',
        efectivo:          'Efectivo',
        transferencia:     'Transferencia',
        billetera_digital: 'Billetera Digital',
    };
    return map[method] || capitalize(method || '');
}

/**
 * Devuelve la etiqueta legible del tipo de movimiento.
 * @param {string} type
 * @returns {string}
 */
export function formatMovementType(type) {
    const map = {
        recarga:       'Recarga',
        pago:          'Pago',
        transferencia: 'Transferencia',
        devolucion:    'Devolución',
    };
    return map[type] || capitalize(type || '');
}

/**
 * Devuelve la etiqueta legible del estado de una transacción.
 * @param {string} status
 * @returns {string}
 */
export function formatStatus(status) {
    const map = {
        exitoso:    'Exitoso',
        exitosa:    'Exitosa',
        fallido:    'Fallido',
        fallida:    'Fallida',
        pendiente:  'Pendiente',
        completado: 'Completado',
        cancelado:  'Cancelado',
    };
    return map[status] || capitalize(status || '');
}
