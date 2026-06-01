<template>
    <AuthLayout>
        <div class="max-w-5xl mx-auto space-y-6">

            <!-- Encabezado -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    Mi Saldo
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    Consulta tu saldo disponible y movimientos recientes
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Columna izquierda: saldo y resumen -->
                <div class="lg:col-span-1 space-y-4">

                    <!-- Saldo actual -->
                    <div class="bg-gradient-to-br from-cyan-900/40 to-blue-900/40 border border-cyan-500/20 rounded-xl p-5 text-center">
                        <p class="text-xs text-cyan-400 uppercase tracking-wider mb-1">
                            Saldo Disponible
                        </p>
                        <p class="text-4xl font-bold text-white font-mono">
                            ${{ formatMonto(monedero?.saldo_disponible ?? 0) }}
                        </p>
                        <p v-if="monedero?.saldo_retenido > 0" class="text-xs text-yellow-400 mt-2">
                            ${{ formatMonto(monedero.saldo_retenido) }} retenidos
                        </p>
                    </div>

                    <!-- Resumen rápido -->
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-5 space-y-3">
                        <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Resumen (últimos movimientos)
                        </h2>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-300">Abonos</span>
                            <span class="text-sm font-bold text-green-400">+${{ formatMonto(resumen.total_abonos) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-300">Cargos</span>
                            <span class="text-sm font-bold text-red-400">-${{ formatMonto(resumen.total_cargos) }}</span>
                        </div>
                        <div class="border-t border-slate-700 pt-2 flex justify-between items-center">
                            <span class="text-sm font-semibold text-white">Total movimientos</span>
                            <span class="text-sm font-bold text-white">{{ resumen.cantidad }}</span>
                        </div>
                    </div>

                    <!-- Botón recargar -->
                    <a :href="route('monedero.recargas')"
                       class="block w-full py-3 bg-gradient-to-br from-cyan-600 to-blue-700 border border-transparent rounded-xl text-sm font-semibold text-white hover:from-cyan-500 hover:to-blue-600 shadow-lg shadow-cyan-500/20 transition-all duration-200 text-center">
                        + Ir a Recargar
                    </a>
                </div>

                <!-- Columna derecha: movimientos -->
                <div class="lg:col-span-2">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Últimos Movimientos
                            </h2>
                            <span class="text-xs text-slate-400">{{ movimientos.length }} registros</span>
                        </div>

                        <div v-if="movimientos.length === 0" class="px-6 py-12 text-center">
                            <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-slate-500 text-sm">No hay movimientos registrados</p>
                        </div>

                        <div v-else class="divide-y divide-slate-700/50">
                            <div v-for="mov in movimientos" :key="mov.id"
                                 class="flex items-center gap-4 px-6 py-4 hover:bg-slate-700/20 transition-colors duration-150">

                                <!-- Ícono tipo -->
                                <div :class="mov.tipo === 'abono'
                                     ? 'bg-green-500/20 text-green-400'
                                     : 'bg-red-500/20 text-red-400'"
                                     class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="mov.tipo === 'abono'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white">
                                        {{ mov.concepto }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ mov.modulo }} · {{ formatDateTime(mov.created_at) }}
                                    </p>
                                </div>

                                <!-- Monto -->
                                <div class="text-right">
                                    <p :class="mov.tipo === 'abono' ? 'text-green-400' : 'text-red-400'"
                                       class="text-sm font-bold font-mono">
                                        {{ mov.tipo === 'abono' ? '+' : '-' }}${{ formatMonto(mov.monto) }}
                                    </p>
                                    <p class="text-xs text-slate-500 font-mono">
                                        saldo ${{ formatMonto(mov.saldo_nuevo) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    monedero:   { type: Object, default: null },
    movimientos:{ type: Array,  default: () => [] },
    resumen:    { type: Object, default: () => ({ saldo_actual: 0, total_abonos: 0, total_cargos: 0, cantidad: 0 }) },
});

function formatMonto(v) {
    return Number(v).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDateTime(d) {
    return d ? new Date(d).toLocaleString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }) : '-';
}
</script>
