import { ref, computed } from 'vue';
import { transactionApi } from '../services/transactionApi.js';
import { formatCurrency } from '../utils/formatters.js';

/**
 * Composable para gestionar el saldo del usuario.
 * Mantiene estado reactivo y provee métodos para obtener y actualizar el saldo.
 *
 * @param {object|null} initialBalance - Objeto Saldo inicial (puede venir de props de Inertia)
 */
export function useBalance(initialBalance = null) {
    const balance     = ref(initialBalance);
    const loading     = ref(false);
    const error       = ref(null);
    const lastUpdated = ref(null);

    // ── Computeds ──────────────────────────────────────────────────
    const formattedBalance = computed(() =>
        formatCurrency(balance.value?.saldo ?? 0)
    );

    const saldoNumerico = computed(() =>
        parseFloat(balance.value?.saldo ?? 0)
    );

    const tieneSaldo = computed(() => saldoNumerico.value > 0);

    // ── Métodos ────────────────────────────────────────────────────

    /** Refresca el saldo desde la API interna. */
    async function fetchBalance() {
        loading.value = true;
        error.value   = null;
        try {
            const data    = await transactionApi.getSaldo();
            balance.value = data;
            lastUpdated.value = new Date();
        } catch (e) {
            error.value = e.message || 'Error al obtener el saldo';
        } finally {
            loading.value = false;
        }
    }

    /** Reemplaza el saldo con un nuevo objeto (sin petición HTTP). */
    function updateBalance(newBalance) {
        balance.value     = newBalance;
        lastUpdated.value = new Date();
    }

    /** Incrementa el saldo en `amount` de forma optimista. */
    function incrementBalance(amount) {
        if (balance.value) {
            balance.value = {
                ...balance.value,
                saldo: parseFloat(balance.value.saldo || 0) + parseFloat(amount),
            };
        }
    }

    return {
        balance,
        loading,
        error,
        lastUpdated,
        formattedBalance,
        saldoNumerico,
        tieneSaldo,
        fetchBalance,
        updateBalance,
        incrementBalance,
    };
}
