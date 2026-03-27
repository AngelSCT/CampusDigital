<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import StatCard from '@/Components/Recargas/StatCard.vue';
import TransactionTable from '@/Components/Recargas/TransactionTable.vue';
import QuickActions from '@/Components/Recargas/QuickActions.vue';
import { useTransactions } from '@/composables/useTransactions.js';
import { useBalance } from '@/composables/useBalance.js';
import { validateAmount, validateRequired } from '@/utils/validators.js';

const props = defineProps({
    saldo:         { type: Object, default: null },
    movimientos:   { type: Array,  default: () => [] },
    estadisticas:  { type: Object, default: null },
});

// ── Composables ────────────────────────────────────────────────
const { formattedBalance, fetchBalance, saldoNumerico } = useBalance(props.saldo);

const {
    movimientosPaginados,
    movimientosFiltrados,
    totalPaginas,
    paginacion,
    loading,
    estadisticas,
    setFiltro,
    limpiarFiltros,
    cambiarPagina,
    fetchMovimientos,
} = useTransactions(props.movimientos);

// ── Refresco de datos ───────────────────────────────────────────
const refreshing = ref(false);

async function refrescarDatos() {
    refreshing.value = true;
    await Promise.all([fetchBalance(), fetchMovimientos()]);
    refreshing.value = false;
}

// ── Modal de pago rápido ────────────────────────────────────────
const pagoModal    = ref(false);
const pagoForm     = ref({ monto: '', concepto: '' });
const pagoError    = ref('');
const pagoLoading  = ref(false);

function abrirPago() {
    pagoForm.value  = { monto: '', concepto: '' };
    pagoError.value = '';
    pagoModal.value = true;
}

function cerrarPago() {
    if (!pagoLoading.value) pagoModal.value = false;
}

function confirmarPago() {
    pagoError.value = '';

    const montoResult = validateAmount(pagoForm.value.monto);
    if (!montoResult.valid) { pagoError.value = montoResult.error; return; }

    const conceptoResult = validateRequired(pagoForm.value.concepto, 'El concepto');
    if (!conceptoResult.valid) { pagoError.value = conceptoResult.error; return; }

    pagoLoading.value = true;
    router.post('/modulo_8/pagar', {
        monto:    pagoForm.value.monto,
        concepto: pagoForm.value.concepto,
    }, {
        onSuccess: () => { pagoModal.value = false; },
        onError:   (errors) => { pagoError.value = errors.monto || errors.concepto || 'Error al procesar el pago.'; },
        onFinish:  () => { pagoLoading.value = false; },
    });
}
</script>

<template>
    <AuthLayout>
        <div class="space-y-6" id="historial">

            <!-- ── Encabezado ── -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    Estado de Cuenta
                </h1>
                <p class="mt-1 text-sm text-slate-400">Resumen de tus movimientos y saldo disponible</p>
            </div>

            <!-- ── Tarjetas de estadísticas ── -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- Saldo disponible -->
                <StatCard
                    label="Saldo disponible"
                    subtitle="Monedero universitario"
                    :value="saldoNumerico"
                    prefix="$"
                    :decimals="2"
                    icon="💰"
                    variant="primary"
                />

                <!-- Movimientos exitosos -->
                <StatCard
                    label="Movimientos exitosos"
                    :subtitle="`de ${estadisticas.total} en total`"
                    :value="estadisticas.exitosos"
                    icon="✅"
                    variant="success"
                />

                <!-- Movimientos fallidos -->
                <StatCard
                    label="Movimientos fallidos"
                    :subtitle="estadisticas.pendientes > 0 ? `${estadisticas.pendientes} pendiente(s)` : null"
                    :value="estadisticas.fallidos"
                    icon="❌"
                    variant="danger"
                />

            </div>

            <!-- ── Acciones rápidas ── -->
            <QuickActions
                :loading="refreshing"
                @pagar="abrirPago"
                @actualizar="refrescarDatos"
            />

            <!-- ── Tabla de movimientos ── -->
            <TransactionTable
                :movimientos="movimientosPaginados"
                :loading="loading"
                :total-filtrados="movimientosFiltrados.length"
                :pagina-actual="paginacion.paginaActual"
                :total-paginas="totalPaginas"
                @filtrar="({ key, value }) => setFiltro(key, value)"
                @limpiar-filtros="limpiarFiltros"
                @cambiar-pagina="cambiarPagina"
            />

        </div>

        <!-- ── Modal pago rápido ── -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="pagoModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
                @click.self="cerrarPago"
            >
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="pagoModal" class="w-full max-w-md bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">

                        <!-- Cabecera modal -->
                        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-white">Realizar pago</h3>
                            <button @click="cerrarPago" class="text-slate-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Cuerpo modal -->
                        <div class="px-6 py-5 space-y-4">
                            <!-- Saldo disponible -->
                            <div class="p-3 rounded-xl bg-slate-700/40 border border-slate-600/40 text-center">
                                <p class="text-xs text-slate-400 mb-1">Saldo disponible</p>
                                <p class="text-2xl font-bold text-white font-mono">{{ formattedBalance }}</p>
                            </div>

                            <!-- Error -->
                            <p v-if="pagoError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
                                {{ pagoError }}
                            </p>

                            <!-- Campo monto -->
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Monto a pagar</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">$</span>
                                    <input
                                        v-model="pagoForm.monto"
                                        type="number"
                                        min="1"
                                        placeholder="0.00"
                                        class="w-full pl-7 pr-4 py-2.5 bg-slate-700/50 border border-slate-600 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/50 transition-all"
                                    />
                                </div>
                            </div>

                            <!-- Campo concepto -->
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Concepto</label>
                                <input
                                    v-model="pagoForm.concepto"
                                    type="text"
                                    maxlength="100"
                                    placeholder="Ej: Cafetería, Biblioteca..."
                                    class="w-full px-4 py-2.5 bg-slate-700/50 border border-slate-600 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/50 transition-all"
                                />
                            </div>
                        </div>

                        <!-- Acciones modal -->
                        <div class="px-6 py-4 border-t border-slate-700 flex gap-3 justify-end">
                            <button
                                @click="cerrarPago"
                                :disabled="pagoLoading"
                                class="px-4 py-2 text-sm text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700/50 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Cancelar
                            </button>
                            <button
                                @click="confirmarPago"
                                :disabled="pagoLoading"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg hover:from-blue-500 hover:to-blue-600 transition-all shadow-lg shadow-blue-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg v-if="pagoLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ pagoLoading ? 'Procesando...' : 'Confirmar pago' }}
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>

    </AuthLayout>
</template>