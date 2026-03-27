/**
 * Composable: useTransactions
 * Lógica de transacciones y recargas para el Módulo 8
 */

import { ref, computed, reactive, readonly } from 'vue';
import { router } from '@inertiajs/vue3';
import { validarFormRecarga, validarLimiteDiario, validarIntervalo } from '../Utils/validators.js';
import { useNotification } from './useNotification.js';
import { METODOS_PAGO, MONTOS_RAPIDOS } from '../Utils/config/api.config.js';

/**
 * @param {Array} recargasIniciales - datos recibidos por props de Inertia
 * @param {object} limites - límites recibidos por props de Inertia
 */
export function useTransactions(recargasIniciales = [], limites = {}) {
    const { notifySuccess, notifyError, notifyWarning } = useNotification();

    const recargas = ref(recargasIniciales);
    const procesando = ref(false);
    const filtroEstado = ref('todos');

    const form = reactive({
        monto: '',
        metodo_pago: 'tarjeta',
    });

    const errors = reactive({
        monto: '',
        metodo_pago: '',
    });

    // ─── Computed ─────────────────────────────────────────────────────────────

    /** Recargas filtradas por estado */
    const recargasFiltradas = computed(() => {
        if (filtroEstado.value === 'todos') return recargas.value;
        return recargas.value.filter((r) => {
            const estado = r.estado;
            const filtro = filtroEstado.value;
            // compatibilidad con 'exitoso'/'exitosa' y 'fallido'/'fallida'
            if (filtro === 'exitoso') return estado === 'exitoso' || estado === 'exitosa';
            if (filtro === 'fallido') return estado === 'fallido' || estado === 'fallida';
            return estado === filtro;
        });
    });

    /** Total de recargas exitosas */
    const totalExitosas = computed(() =>
        recargas.value.filter((r) => r.estado === 'exitoso' || r.estado === 'exitosa').length
    );

    /** Total de recargas fallidas */
    const totalFallidas = computed(() =>
        recargas.value.filter((r) => r.estado === 'fallido' || r.estado === 'fallida').length
    );

    /** Suma del monto de recargas exitosas */
    const montoTotalRecargado = computed(() =>
        recargas.value
            .filter((r) => r.estado === 'exitoso' || r.estado === 'exitosa')
            .reduce((acc, r) => acc + Number(r.monto), 0)
    );

    /** Advertencia sobre límites (si aplica) */
    const advertenciaLimites = computed(() => {
        const limiteRes = validarLimiteDiario(recargas.value, limites);
        if (!limiteRes.valido) return limiteRes.error;

        const intervaloRes = validarIntervalo(recargas.value, limites);
        if (!intervaloRes.valido) return intervaloRes.error;

        return null;
    });

    // ─── Métodos ──────────────────────────────────────────────────────────────

    /** Limpia los errores del formulario */
    function limpiarErrors() {
        errors.monto = '';
        errors.metodo_pago = '';
    }

    /** Establece monto rápido */
    function seleccionarMonto(monto) {
        form.monto = monto;
        errors.monto = '';
    }

    /** Cambia el filtro de la tabla */
    function cambiarFiltro(estado) {
        filtroEstado.value = estado;
    }

    /**
     * Procesa la recarga usando Inertia router
     */
    function procesarRecarga() {
        limpiarErrors();

        const { valido, errores } = validarFormRecarga(form, recargas.value, limites);

        if (!valido) {
            if (errores.monto) errors.monto = errores.monto;
            if (errores.metodo_pago) errors.metodo_pago = errores.metodo_pago;
            if (errores.limite) notifyWarning(errores.limite);
            if (errores.intervalo) notifyWarning(errores.intervalo);
            return;
        }

        procesando.value = true;

        router.post(
            '/modulo_8/recargar',
            { monto: form.monto, metodo_pago: form.metodo_pago },
            {
                onSuccess: () => {
                    notifySuccess(`Recarga de $${form.monto} procesada exitosamente.`);
                    form.monto = '';
                    form.metodo_pago = 'tarjeta';
                    setTimeout(() => router.reload(), 1500);
                },
                onError: (err) => {
                    const mensaje = Object.values(err)[0] || 'Error al procesar la recarga.';
                    notifyError(mensaje);
                },
                onFinish: () => {
                    procesando.value = false;
                },
            }
        );
    }

    /**
     * Reintenta una recarga fallida
     * @param {number|string} id
     */
    function reintentar(id) {
        router.post(
            `/modulo_8/recargar/${id}/reintentar`,
            {},
            {
                onSuccess: () => {
                    notifySuccess('Reintento procesado. Revisa el resultado.');
                    setTimeout(() => router.reload(), 1500);
                },
                onError: () => {
                    notifyError('Error al reintentar la recarga.');
                },
            }
        );
    }

    /**
     * Descarga el comprobante
     * @param {number|string} id
     */
    function descargarComprobante(id) {
        window.location.href = `/modulo_8/recargar/${id}/comprobante`;
    }

    return {
        // Estado
        recargas: readonly(recargas),
        procesando: readonly(procesando),
        filtroEstado,
        form,
        errors,

        // Computed
        recargasFiltradas,
        totalExitosas,
        totalFallidas,
        montoTotalRecargado,
        advertenciaLimites,

        // Constantes
        metodosPago: METODOS_PAGO,
        montosRapidos: MONTOS_RAPIDOS,

        // Métodos
        seleccionarMonto,
        cambiarFiltro,
        procesarRecarga,
        reintentar,
        descargarComprobante,
    };
}
