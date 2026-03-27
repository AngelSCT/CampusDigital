import { ref, computed } from 'vue';
import { transactionApi } from '../services/transactionApi.js';

/**
 * Composable para gestionar el historial de transacciones.
 * Incluye filtrado reactivo, paginación y estadísticas calculadas.
 *
 * @param {Array} initialMovimientos - Lista inicial (puede venir de props de Inertia)
 */
export function useTransactions(initialMovimientos = []) {
    const movimientos = ref(initialMovimientos);
    const loading     = ref(false);
    const error       = ref(null);

    // ── Filtros ────────────────────────────────────────────────────
    const filtros = ref({
        tipo:       '',
        estado:     '',
        fechaDesde: '',
        fechaHasta: '',
        busqueda:   '',
    });

    // ── Paginación ─────────────────────────────────────────────────
    const paginacion = ref({
        paginaActual: 1,
        porPagina:    10,
    });

    // ── Computeds ──────────────────────────────────────────────────
    const movimientosFiltrados = computed(() => {
        let result = movimientos.value || [];

        if (filtros.value.tipo) {
            result = result.filter((m) => m.tipo === filtros.value.tipo);
        }

        if (filtros.value.estado) {
            const e = filtros.value.estado;
            result = result.filter(
                (m) => m.estado === e || m.estado === `${e}a` || m.estado === `${e}o`
            );
        }

        if (filtros.value.fechaDesde) {
            const desde = new Date(filtros.value.fechaDesde);
            result = result.filter((m) => new Date(m.created_at) >= desde);
        }

        if (filtros.value.fechaHasta) {
            const hasta = new Date(filtros.value.fechaHasta);
            hasta.setHours(23, 59, 59, 999);
            result = result.filter((m) => new Date(m.created_at) <= hasta);
        }

        if (filtros.value.busqueda) {
            const q = filtros.value.busqueda.toLowerCase();
            result = result.filter(
                (m) =>
                    String(m.id).includes(q) ||
                    (m.tipo && m.tipo.toLowerCase().includes(q)) ||
                    (m.estado && m.estado.toLowerCase().includes(q)) ||
                    String(m.monto).includes(q)
            );
        }

        return result;
    });

    const totalPaginas = computed(() =>
        Math.max(1, Math.ceil(movimientosFiltrados.value.length / paginacion.value.porPagina))
    );

    const movimientosPaginados = computed(() => {
        const inicio = (paginacion.value.paginaActual - 1) * paginacion.value.porPagina;
        return movimientosFiltrados.value.slice(inicio, inicio + paginacion.value.porPagina);
    });

    const estadisticas = computed(() => {
        const todos = movimientos.value || [];
        return {
            total:             todos.length,
            exitosos:          todos.filter((m) => ['exitosa', 'exitoso'].includes(m.estado)).length,
            fallidos:          todos.filter((m) => ['fallida', 'fallido'].includes(m.estado)).length,
            pendientes:        todos.filter((m) => m.estado === 'pendiente').length,
            montoTotalRecargado: todos
                .filter((m) => m.tipo === 'recarga' && ['exitosa', 'exitoso'].includes(m.estado))
                .reduce((sum, m) => sum + parseFloat(m.monto || 0), 0),
        };
    });

    // ── Métodos ────────────────────────────────────────────────────

    /** Actualiza un filtro y reinicia la paginación. */
    function setFiltro(key, value) {
        filtros.value[key]             = value;
        paginacion.value.paginaActual  = 1;
    }

    /** Limpia todos los filtros y reinicia la paginación. */
    function limpiarFiltros() {
        filtros.value = { tipo: '', estado: '', fechaDesde: '', fechaHasta: '', busqueda: '' };
        paginacion.value.paginaActual = 1;
    }

    /** Cambia la página actual (con validación de rango). */
    function cambiarPagina(pagina) {
        if (pagina >= 1 && pagina <= totalPaginas.value) {
            paginacion.value.paginaActual = pagina;
        }
    }

    /** Refresca los movimientos desde la API interna. */
    async function fetchMovimientos() {
        loading.value = true;
        error.value   = null;
        try {
            const data       = await transactionApi.getMovimientos();
            movimientos.value = data;
        } catch (e) {
            error.value = e.message || 'Error al obtener movimientos';
        } finally {
            loading.value = false;
        }
    }

    /** Reemplaza la lista de movimientos (sin petición HTTP). */
    function setMovimientos(data) {
        movimientos.value = data;
    }

    return {
        movimientos,
        loading,
        error,
        filtros,
        paginacion,
        movimientosFiltrados,
        totalPaginas,
        movimientosPaginados,
        estadisticas,
        setFiltro,
        limpiarFiltros,
        cambiarPagina,
        fetchMovimientos,
        setMovimientos,
    };
}
