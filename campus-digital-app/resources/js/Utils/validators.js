/**
 * Validaciones para el Módulo 8 - Recargas y Monedero
 */

import { LIMITES_DEFAULT } from './config/api.config.js';

/**
 * Valida el monto de recarga
 * @param {number|string} monto
 * @param {object} limites
 * @returns {{ valido: boolean, error: string }}
 */
export function validarMonto(monto, limites = LIMITES_DEFAULT) {
    const valor = Number(monto);

    if (!monto && monto !== 0) {
        return { valido: false, error: 'El monto es requerido' };
    }

    if (isNaN(valor)) {
        return { valido: false, error: 'El monto debe ser un número válido' };
    }

    const min = limites.monto_minimo ?? limites.montoMinimo;
    if (valor < min) {
        return { valido: false, error: `El monto debe ser mayor o igual a $${min}` };
    }

    const max = limites.monto_maximo ?? limites.montoMaximo;
    if (valor > max) {
        return { valido: false, error: `El monto no puede exceder $${max}` };
    }

    return { valido: true, error: '' };
}

/**
 * Valida que se haya seleccionado un método de pago
 * @param {string} metodo
 * @returns {{ valido: boolean, error: string }}
 */
export function validarMetodoPago(metodo) {
    const metodosValidos = ['tarjeta', 'transferencia', 'efectivo', 'billetera_digital'];

    if (!metodo) {
        return { valido: false, error: 'Selecciona un método de pago' };
    }

    if (!metodosValidos.includes(metodo)) {
        return { valido: false, error: 'Método de pago no válido' };
    }

    return { valido: true, error: '' };
}

/**
 * Valida que no se excedan los límites diarios de recarga
 * @param {Array} recargas - historial de recargas del usuario
 * @param {object} limites
 * @returns {{ valido: boolean, error: string }}
 */
export function validarLimiteDiario(recargas = [], limites = LIMITES_DEFAULT) {
    const maxDia = limites.max_recargas_dia ?? limites.maxRecargasDia;
    const hoy = new Date().toDateString();

    const recargasHoy = recargas.filter((r) => {
        const fecha = new Date(r.created_at);
        const estadoExitoso = r.estado === 'exitoso' || r.estado === 'exitosa';
        return fecha.toDateString() === hoy && estadoExitoso;
    });

    if (recargasHoy.length >= maxDia) {
        return {
            valido: false,
            error: `Has alcanzado el límite de ${maxDia} recargas por día.`,
        };
    }

    return { valido: true, error: '' };
}

/**
 * Valida el intervalo mínimo entre recargas
 * @param {Array} recargas - historial de recargas del usuario
 * @param {object} limites
 * @returns {{ valido: boolean, error: string, minutosRestantes: number }}
 */
export function validarIntervalo(recargas = [], limites = LIMITES_DEFAULT) {
    const intervalo = limites.intervalo_minutos ?? limites.intervaloMinutos;
    const ultimaExitosa = recargas.find((r) => r.estado === 'exitoso' || r.estado === 'exitosa');

    if (!ultimaExitosa) {
        return { valido: true, error: '', minutosRestantes: 0 };
    }

    const fechaUltima = new Date(ultimaExitosa.created_at);
    const ahora = new Date();
    const minutosDiferencia = Math.floor((ahora - fechaUltima) / 60000);

    if (minutosDiferencia < intervalo) {
        const minutosRestantes = intervalo - minutosDiferencia;
        return {
            valido: false,
            error: `Debes esperar ${minutosRestantes} minuto(s) entre recargas.`,
            minutosRestantes,
        };
    }

    return { valido: true, error: '', minutosRestantes: 0 };
}

/**
 * Ejecuta todas las validaciones del formulario de recarga
 * @param {object} form - { monto, metodo_pago }
 * @param {Array} recargas
 * @param {object} limites
 * @returns {{ valido: boolean, errores: object }}
 */
export function validarFormRecarga(form, recargas = [], limites = LIMITES_DEFAULT) {
    const errores = {};

    const montoRes = validarMonto(form.monto, limites);
    if (!montoRes.valido) errores.monto = montoRes.error;

    const metodoRes = validarMetodoPago(form.metodo_pago);
    if (!metodoRes.valido) errores.metodo_pago = metodoRes.error;

    const limiteRes = validarLimiteDiario(recargas, limites);
    if (!limiteRes.valido) errores.limite = limiteRes.error;

    const intervaloRes = validarIntervalo(recargas, limites);
    if (!intervaloRes.valido) errores.intervalo = intervaloRes.error;

    return {
        valido: Object.keys(errores).length === 0,
        errores,
    };
}
