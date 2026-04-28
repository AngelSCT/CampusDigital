<template>
    <AuthLayout>
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Encabezado -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">
                        Conciliación por Período
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Resumen de recargas entre fechas seleccionadas</p>
                </div>
                <Link :href="route('modulo_8.index')" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">
                    Volver al Dashboard
                </Link>
            </div>

            <!-- Filtro de fechas -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-white mb-4">Seleccionar Período</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5 uppercase">Fecha Inicio</label>
                        <input
                            v-model="filtroInicio"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5 uppercase">Fecha Fin</label>
                        <input
                            v-model="filtroFin"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition"
                        />
                    </div>
                    <button
                        @click="aplicarFiltro"
                        class="px-6 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-sm font-medium transition"
                    >
                        Consultar
                    </button>
                </div>
            </div>

            <!-- Estadísticas del período -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase mb-1">Total Transacciones</p>
                    <p class="text-2xl font-bold text-white">{{ stats.total_recargas }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-900/40 to-emerald-900/40 border border-green-500/20 rounded-xl p-4">
                    <p class="text-xs text-green-400 uppercase mb-1">Exitosas</p>
                    <p class="text-2xl font-bold text-white">{{ stats.exitosas }}</p>
                    <p class="text-xs text-green-300 mt-1">${{ formatMonto(stats.monto_exitoso) }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-900/40 to-pink-900/40 border border-red-500/20 rounded-xl p-4">
                    <p class="text-xs text-red-400 uppercase mb-1">Fallidas</p>
                    <p class="text-2xl font-bold text-white">{{ stats.fallidas }}</p>
                    <p class="text-xs text-red-300 mt-1">${{ formatMonto(stats.monto_fallido) }}</p>
                </div>
                <div class="bg-gradient-to-br from-yellow-900/40 to-orange-900/40 border border-yellow-500/20 rounded-xl p-4">
                    <p class="text-xs text-yellow-400 uppercase mb-1">Pendientes</p>
                    <p class="text-2xl font-bold text-white">{{ stats.pendientes }}</p>
                </div>
            </div>

            <!-- Resumen por método de pago -->
            <div v-if="Object.keys(resumen_metodos).length > 0" class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h2 class="text-sm font-semibold text-white">Resumen por Método de Pago</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-900/50">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Exitosas</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Fallidas</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="(datos, metodo) in resumen_metodos" :key="metodo" class="hover:bg-slate-700/20 transition">
                                <td class="px-6 py-4 text-sm">
                                    <span class="flex items-center gap-2">
                                        <span>{{ iconoMetodo(metodo) }}</span>
                                        <span class="text-slate-300">{{ labelMetodo(metodo) }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-white font-medium">{{ datos.total }}</td>
                                <td class="px-6 py-4 text-sm text-green-400">{{ datos.exitosas }}</td>
                                <td class="px-6 py-4 text-sm text-red-400">{{ datos.fallidas }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-white font-mono">${{ formatMonto(datos.monto) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detalle de transacciones -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">
                        Detalle de Transacciones
                        <span class="ml-2 text-xs text-slate-400 font-normal">
                            {{ formatDate(fecha_inicio) }} – {{ formatDate(fecha_fin) }}
                        </span>
                    </h2>
                </div>

                <div v-if="recargas.length === 0" class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <p class="text-slate-500">Sin transacciones en este período</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-900/50">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Folio</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="r in recargas" :key="r.id" class="hover:bg-slate-700/20 transition">
                                <td class="px-6 py-4 text-sm text-slate-300 font-mono">{{ r.referencia ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-400">{{ formatDateTime(r.created_at) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="flex items-center gap-2">
                                        <span>{{ iconoMetodo(r.metodo_pago) }}</span>
                                        <span class="text-slate-300">{{ labelMetodo(r.metodo_pago) }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-white font-mono">${{ formatMonto(r.monto) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span :class="badgeEstado(r.estado)" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium capitalize">
                                        {{ r.estado }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    recargas: Array,
    stats: Object,
    resumen_metodos: Object,
    fecha_inicio: String,
    fecha_fin: String,
});

const filtroInicio = ref(props.fecha_inicio);
const filtroFin = ref(props.fecha_fin);

function aplicarFiltro() {
    router.get(route('modulo_8.reportes.conciliacion'), {
        fecha_inicio: filtroInicio.value,
        fecha_fin: filtroFin.value,
    });
}

function formatMonto(v) {
    return Number(v ?? 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDate(d) {
    if (!d) return '-';
    const date = new Date(d);
    if (isNaN(date.getTime())) return d;
    return date.toLocaleDateString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC'
    });
}

function formatDateTime(d) {
    if (!d) return '-';
    return new Date(d).toLocaleString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function labelMetodo(m) {
    const labels = {
        tarjeta: 'Tarjeta',
        transferencia: 'Transferencia',
        efectivo: 'Efectivo',
        billetera_digital: 'Billetera Digital'
    };
    return labels[m] || m;
}

function iconoMetodo(m) {
    const iconos = {
        tarjeta: '💳',
        transferencia: '🏦',
        efectivo: '💵',
        billetera_digital: '📱'
    };
    return iconos[m] || '💰';
}

function badgeEstado(estado) {
    const clases = {
        exitosa: 'bg-green-500/20 text-green-400 border border-green-500/30',
        fallida: 'bg-red-500/20 text-red-400 border border-red-500/30',
        pendiente: 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30',
    };
    return clases[estado] ?? 'bg-slate-500/20 text-slate-400 border border-slate-500/30';
}
</script>
