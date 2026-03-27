/**
 * Composable: useBalance
 * Gestión reactiva del saldo del monedero
 */

import { ref, computed, readonly } from 'vue';
import TransactionApiService from '../Services/TransactionApiService.js';
import { formatMonto } from '../Utils/formatters.js';

/**
 * @param {object|null} saldoInicial - datos del saldo recibidos por props de Inertia
 */
export function useBalance(saldoInicial = null) {
    const saldo = ref(saldoInicial);
    const cargando = ref(false);
    const error = ref(null);

    /** Monto numérico del saldo */
    const montoSaldo = computed(() => Number(saldo.value?.saldo ?? 0));

    /** Monto formateado para mostrar */
    const saldoFormateado = computed(() => formatMonto(montoSaldo.value));

    /** Indica si el saldo está disponible */
    const tieneSaldo = computed(() => montoSaldo.value > 0);

    /**
     * Recarga el saldo desde la API
     */
    async function refrescarSaldo() {
        cargando.value = true;
        error.value = null;
        try {
            saldo.value = await TransactionApiService.getSaldo();
        } catch (e) {
            error.value = e.message || 'Error al obtener el saldo';
        } finally {
            cargando.value = false;
        }
    }

    /**
     * Actualiza el saldo localmente (sin llamada a la API)
     * @param {number} nuevoMonto
     */
    function actualizarSaldoLocal(nuevoMonto) {
        if (saldo.value) {
            saldo.value = { ...saldo.value, saldo: nuevoMonto };
        } else {
            saldo.value = { saldo: nuevoMonto };
        }
    }

    return {
        saldo: readonly(saldo),
        montoSaldo,
        saldoFormateado,
        tieneSaldo,
        cargando: readonly(cargando),
        error: readonly(error),
        refrescarSaldo,
        actualizarSaldoLocal,
    };
}
