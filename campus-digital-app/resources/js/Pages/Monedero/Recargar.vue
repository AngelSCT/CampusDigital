<template>
    <AuthLayout>
        <div class="recargar-container max-w-6xl mx-auto space-y-6">

            <!-- ─── Encabezado ──────────────────────────────────────────────── -->
            <div class="fade-in">
                <h1 class="text-3xl font-extrabold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    Recargar Saldo
                </h1>
                <p class="mt-1 text-sm text-slate-400">Carga dinero a tu monedero universitario de forma segura</p>
            </div>

            <!-- ─── Notificaciones ─────────────────────────────────────────── -->
            <transition name="notif">
                <div
                    v-if="notification"
                    class="flex items-center gap-3 p-4 rounded-xl border text-sm font-medium"
                    :class="notifClass(notification.type)"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="notifIcon(notification.type)" />
                    </svg>
                    <span class="flex-1">{{ notification.message }}</span>
                    <button @click="clearNotification" class="ml-auto opacity-70 hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </transition>

            <!-- Notificaciones flash de Inertia -->
            <div v-if="$page.props.flash?.success"
                class="flex items-center gap-3 p-4 rounded-xl border border-green-500/20 bg-green-500/10 text-green-400 text-sm font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ $page.props.flash.success }}
            </div>

            <div v-if="$page.props.flash?.error"
                class="flex items-center gap-3 p-4 rounded-xl border border-red-500/20 bg-red-500/10 text-red-400 text-sm font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $page.props.flash.error }}
            </div>

            <!-- ─── Tarjetas de estadísticas ───────────────────────────────── -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 fade-in-delay-1">
                <StatCard
                    variant="primary"
                    label="Saldo Disponible"
                    :value="saldoFormateado"
                    icon="💰"
                    :subtitle="`Actualizado: ${formatDateTime(new Date())}`"
                />
                <StatCard
                    variant="success"
                    label="Recargas Exitosas"
                    :value="String(totalExitosas)"
                    icon="✅"
                />
                <StatCard
                    variant="error"
                    label="Recargas Fallidas"
                    :value="String(totalFallidas)"
                    icon="❌"
                />
            </div>

            <!-- ─── Grid principal ─────────────────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Columna izquierda: Formulario + Límites + Acciones -->
                <div class="lg:col-span-1 space-y-4 fade-in-delay-2">

                    <!-- Formulario de recarga -->
                    <div class="rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl">
                        <div class="px-5 py-4 border-b border-slate-700/60 flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <h2 class="text-sm font-semibold text-white">Nueva Recarga</h2>
                        </div>

                        <div class="p-5 space-y-4">

                            <!-- Campo de monto -->
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Monto a recargar</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">$</span>
                                    <input
                                        v-model.number="form.monto"
                                        type="number"
                                        min="1"
                                        max="5000"
                                        placeholder="0.00"
                                        class="w-full pl-7 pr-4 py-2.5 rounded-lg bg-slate-700/50 border text-white text-sm transition-all duration-200 focus:outline-none focus:ring-2"
                                        :class="errors.monto
                                            ? 'border-red-500 focus:ring-red-500/20'
                                            : 'border-slate-600 focus:border-cyan-500 focus:ring-cyan-500/20'"
                                    />
                                </div>
                                <p v-if="errors.monto" class="text-xs text-red-400 mt-1">{{ errors.monto }}</p>

                                <!-- Montos rápidos -->
                                <div class="grid grid-cols-3 gap-2 mt-2">
                                    <button
                                        v-for="m in montosRapidos"
                                        :key="m"
                                        @click="seleccionarMonto(m)"
                                        type="button"
                                        class="py-1.5 text-xs font-medium rounded-lg border transition-all duration-200 active:scale-95"
                                        :class="Number(form.monto) === m
                                            ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-300'
                                            : 'bg-slate-700/50 border-slate-600 text-slate-400 hover:border-slate-500 hover:text-slate-300'"
                                    >
                                        ${{ m }}
                                    </button>
                                </div>
                            </div>

                            <!-- Método de pago -->
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Método de pago</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="mp in metodosPago"
                                        :key="mp.value"
                                        @click="form.metodo_pago = mp.value"
                                        type="button"
                                        class="py-2.5 flex flex-col items-center gap-1 rounded-lg border text-xs font-medium transition-all duration-200 active:scale-95"
                                        :class="form.metodo_pago === mp.value
                                            ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-300 shadow-md shadow-cyan-900/20'
                                            : 'bg-slate-700/50 border-slate-600 text-slate-400 hover:border-slate-500 hover:text-slate-300'"
                                    >
                                        <span class="text-lg">{{ mp.icon }}</span>
                                        {{ mp.label }}
                                    </button>
                                </div>
                                <p v-if="errors.metodo_pago" class="text-xs text-red-400 mt-1">{{ errors.metodo_pago }}</p>
                            </div>

                            <!-- Botón de recarga -->
                            <button
                                @click="procesarRecarga"
                                :disabled="procesando"
                                class="w-full py-3 rounded-lg text-sm font-bold text-white transition-all duration-200 flex items-center justify-center gap-2 active:scale-98"
                                :class="procesando
                                    ? 'bg-slate-700 cursor-not-allowed opacity-60'
                                    : 'bg-gradient-to-br from-cyan-600 to-blue-700 hover:from-cyan-500 hover:to-blue-600 shadow-lg shadow-cyan-900/30 hover:shadow-cyan-900/50 hover:scale-[1.02]'"
                            >
                                <LoadingSpinner v-if="procesando" size="sm" />
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ procesando ? 'Procesando...' : 'Realizar Recarga' }}
                            </button>
                        </div>
                    </div>

                    <!-- Límites informativos -->
                    <div class="rounded-xl border border-slate-700/60 bg-slate-900/60 p-4 space-y-1.5">
                        <p class="text-xs font-semibold text-slate-300 mb-2">Límites de Recarga</p>
                        <div class="space-y-1 text-xs text-slate-400">
                            <p>✓ Monto: ${{ limites.monto_minimo ?? 1 }} - ${{ limites.monto_maximo ?? 5000 }}</p>
                            <p>✓ Máximo: {{ limites.max_recargas_dia ?? 3 }} recargas/día</p>
                            <p>✓ Intervalo: {{ limites.intervalo_minutos ?? 5 }} minutos entre recargas</p>
                        </div>
                    </div>

                    <!-- Advertencia de límites -->
                    <div
                        v-if="advertenciaLimites"
                        class="rounded-xl border border-yellow-500/20 bg-yellow-500/10 p-3 flex items-start gap-2 text-xs text-yellow-400"
                    >
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ advertenciaLimites }}
                    </div>

                    <!-- Acciones rápidas -->
                    <div>
                        <p class="text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Acciones</p>
                        <QuickActions :actions="accionesRapidas" />
                    </div>
                </div>

                <!-- Columna derecha: Historial de recargas -->
                <div class="lg:col-span-2 fade-in-delay-2">
                    <TransactionTable
                        title="Historial de Recargas"
                        :rows="recargasTabla"
                        :active-filter="filtroEstado"
                        empty-message="Sin recargas registradas"
                        @filter="cambiarFiltro"
                    >
                        <template #actions="{ row }">
                            <!-- Descargar comprobante -->
                            <button
                                v-if="row.esExitosa"
                                @click="descargarComprobante(row.id)"
                                type="button"
                                class="p-2 text-slate-400 hover:text-cyan-400 hover:bg-slate-700/50 rounded-lg transition-all duration-200"
                                title="Descargar comprobante"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </button>
                            <!-- Reintentar -->
                            <button
                                v-if="row.esFallida"
                                @click="reintentar(row.id)"
                                type="button"
                                class="p-2 text-slate-400 hover:text-yellow-400 hover:bg-slate-700/50 rounded-lg transition-all duration-200"
                                title="Reintentar recarga"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </template>
                    </TransactionTable>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import StatCard from '@/Components/Monedero/StatCard.vue';
import TransactionTable from '@/Components/Monedero/TransactionTable.vue';
import QuickActions from '@/Components/Monedero/QuickActions.vue';
import LoadingSpinner from '@/Components/Monedero/LoadingSpinner.vue';
import { useTransactions } from '@/Composables/useTransactions.js';
import { useBalance } from '@/Composables/useBalance.js';
import { useNotification } from '@/Composables/useNotification.js';
import { formatDateTime, formatMonto } from '@/Utils/formatters.js';

const props = defineProps({
    monedero: { type: Object, default: null },
    recargas: { type: Array, default: () => [] },
    limites: {
        type: Object,
        default: () => ({
            monto_minimo: 1,
            monto_maximo: 5000,
            max_recargas_dia: 3,
            intervalo_minutos: 5,
        }),
    },
});

// ─── Composables ──────────────────────────────────────────────────────────────
const { saldoFormateado } = useBalance(props.monedero);
const { notification, clearNotification } = useNotification();

const {
    form,
    errors,
    procesando,
    filtroEstado,
    recargasFiltradas,
    totalExitosas,
    totalFallidas,
    advertenciaLimites,
    montosRapidos,
    metodosPago,
    seleccionarMonto,
    cambiarFiltro,
    procesarRecarga,
    reintentar,
    descargarComprobante,
} = useTransactions(props.recargas, props.limites);

// ─── Computed ─────────────────────────────────────────────────────────────────

/** Mapea las recargas al formato esperado por TransactionTable */
const recargasTabla = computed(() =>
    recargasFiltradas.value.map((r) => ({
        id: r.id,
        descripcion: `Recarga vía ${metodoLabel(r.metodo_pago)}`,
        fecha: formatDateTime(r.created_at),
        monto: formatMonto(r.monto),
        estado: r.estado,
        razonFallo: r.razon_fallo || null,
        esExitosa: r.estado === 'exitoso' || r.estado === 'exitosa',
        esFallida: r.estado === 'fallido' || r.estado === 'fallida',
    }))
);

const accionesRapidas = [
    { key: 'movimientos', label: 'Ver Movimientos', icon: '📋', href: '/modulo_8', variant: 'primary' },
    { key: 'comprobantes', label: 'Comprobantes', icon: '🧾', href: '/modulo_8/comprobantes', variant: 'secondary' },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────
function metodoLabel(metodo) {
    const labels = {
        tarjeta: 'Tarjeta',
        transferencia: 'Transferencia',
        efectivo: 'Efectivo',
        billetera_digital: 'Billetera Digital',
    };
    return labels[metodo] || metodo;
}

function notifClass(type) {
    switch (type) {
        case 'success': return 'bg-green-500/10 border-green-500/20 text-green-400';
        case 'error':   return 'bg-red-500/10 border-red-500/20 text-red-400';
        case 'warning': return 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400';
        default:        return 'bg-blue-500/10 border-blue-500/20 text-blue-400';
    }
}

function notifIcon(type) {
    switch (type) {
        case 'success': return 'M5 13l4 4L19 7';
        case 'error':   return 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        case 'warning': return 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        default:        return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    }
}
</script>

<style scoped>
/* ─── Animaciones de entrada ──────────────────────────────────────────────── */
.fade-in {
    animation: fadeInUp 0.5s ease-out both;
}

.fade-in-delay-1 {
    animation: fadeInUp 0.5s ease-out 0.1s both;
}

.fade-in-delay-2 {
    animation: fadeInUp 0.5s ease-out 0.2s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ─── Transición de notificaciones ───────────────────────────────────────── */
.notif-enter-active,
.notif-leave-active {
    transition: all 0.3s ease;
}

.notif-enter-from,
.notif-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* ─── Escala activa en botón ──────────────────────────────────────────────── */
.active\:scale-98:active {
    transform: scale(0.98);
}
</style>
