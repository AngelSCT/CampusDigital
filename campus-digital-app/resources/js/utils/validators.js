/**
 * Validaciones reutilizables para el módulo de estado de cuenta.
 */

/**
 * Valida que el monto sea un número positivo dentro del rango permitido.
 * @param {number|string} amount
 * @param {{ min?: number, max?: number }} options
 * @returns {{ valid: boolean, error: string|null }}
 */
export function validateAmount(amount, { min = 1, max = 5000 } = {}) {
    if (amount === '' || amount === null || amount === undefined) {
        return { valid: false, error: 'El monto es requerido' };
    }
    const num = parseFloat(amount);
    if (isNaN(num)) {
        return { valid: false, error: 'El monto debe ser un número válido' };
    }
    if (num < min) {
        return { valid: false, error: `El monto mínimo es $${min}` };
    }
    if (num > max) {
        return { valid: false, error: `El monto máximo es $${max}` };
    }
    return { valid: true, error: null };
}

/**
 * Valida que el método de pago sea uno de los permitidos.
 * @param {string} method
 * @param {string[]} allowed
 * @returns {{ valid: boolean, error: string|null }}
 */
export function validatePaymentMethod(method, allowed = ['tarjeta', 'efectivo', 'transferencia']) {
    if (!method) {
        return { valid: false, error: 'El método de pago es requerido' };
    }
    if (!allowed.includes(method)) {
        return { valid: false, error: 'Método de pago no válido' };
    }
    return { valid: true, error: null };
}

/**
 * Valida que un campo no esté vacío.
 * @param {string} value
 * @param {string} fieldName
 * @returns {{ valid: boolean, error: string|null }}
 */
export function validateRequired(value, fieldName = 'Campo') {
    if (!value || (typeof value === 'string' && value.trim() === '')) {
        return { valid: false, error: `${fieldName} es requerido` };
    }
    return { valid: true, error: null };
}

/**
 * Valida el formulario completo de recarga.
 * @param {{ monto: any, metodo_pago: string }} data
 * @returns {{ valid: boolean, errors: Record<string, string> }}
 */
export function validateRechargeForm(data) {
    const errors = {};

    const montoResult = validateAmount(data.monto);
    if (!montoResult.valid) errors.monto = montoResult.error;

    const metodoResult = validatePaymentMethod(data.metodo_pago);
    if (!metodoResult.valid) errors.metodo_pago = metodoResult.error;

    return { valid: Object.keys(errors).length === 0, errors };
}

/**
 * Valida un rango de fechas para los filtros de tabla.
 * @param {string|null} from
 * @param {string|null} to
 * @returns {{ valid: boolean, error: string|null }}
 */
export function validateDateRange(from, to) {
    if (!from || !to) return { valid: true, error: null };
    if (new Date(from) > new Date(to)) {
        return { valid: false, error: 'La fecha de inicio no puede ser mayor a la fecha de fin' };
    }
    return { valid: true, error: null };
}
