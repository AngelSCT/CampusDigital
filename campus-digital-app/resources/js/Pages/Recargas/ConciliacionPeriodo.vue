<template>
    <AuthLayout>
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Encabezado -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">
                        Conciliación por Período
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Análisis detallado de recargas en el período seleccionado</p>
                </div>
                <Link :href="route('modulo_8.index')" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">
                    ← Dashboard
                </Link>
            </div>

            <!-- Selector de fechas -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-300 mb-4 uppercase tracking-wider">Rango de fechas</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Fecha inicio</label>
                        <input
                            v-model="localFechaInicio"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Fecha fin</label>
                        <input
                            v-model="localFechaFin"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all"
                        />
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="actualizarFechas(localFechaInicio, localFechaFin)"
                            class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-sm font-medium transition"
                        >
                            Consultar
                        </button>
                    </div>
                </div>

                <!-- Accesos rápidos -->
                <div class="flex gap-2 mt-4 flex-wrap">
                    <button
                        v-for="rango in rangosRapidos"
                        :key="rango.label"
                        @click="actualizarFechas(rango.inicio, rango.fin)"
                        class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 border border-slate-600 hover:border-slate-500 text-slate-400 hover:text-slate-200 rounded-lg text-xs font-medium transition"
                    >
                        {{ rango.label }}
                    </button>
                </div>
            </div>

            <!-- Estadísticas del período -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">Total Recargas</p>
                    <p class="text-3xl font-bold text-white">{{ stats.total_recargas }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-900/40 to-emerald-900/40 border border-green-500/20 rounded-xl p-5">
                    <p class="text-xs text-green-400 uppercase tracking-wider mb-2">Exitosas</p>
                    <p class="text-3xl font-bold text-white">{{ stats.exitosas }}</p>
                    <p class="text-xs text-green-300 mt-1">{{ tasaExito }}% éxito</p>
                </div>
                <div class="bg-gradient-to-br from-red-900/40 to-pink-900/40 border border-red-500/20 rounded-xl p-5">
                    <p class="text-xs text-red-400 uppercase tracking-wider mb-2">Fallidas</p>
                    <p class="text-3xl font-bold text-white">{{ stats.fallidas }}</p>
                </div>
                <div class="bg-gradient-to-br from-cyan-900/40 to-blue-900/40 border border-cyan-500/20 rounded-xl p-5">
                    <p class="text-xs text-cyan-400 uppercase tracking-wider mb-2">Monto Acreditado</p>
                    <p class="text-2xl font-bold text-white font-mono">${{ formatMonto(stats.monto_exitoso) }}</p>
                </div>
            </div>

            <!-- Resumen por método de pago -->
            <div v-if="Object.keys(resumen_metodos).length > 0" class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Desglose por Método de Pago
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-900/50">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Método</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-300 uppercase">Total</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-300 uppercase">Exitosas</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-300 uppercase">Fallidas</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-300 uppercase">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="(data, metodo) in resumen_metodos" :key="metodo" class="hover:bg-slate-700/20 transition">
                                <td class="px-6 py-4 text-sm">
                                    <span class="flex items-center gap-3">
                                        <span class="text-xl">{{ iconoMetodo(metodo) }}</span>
                                        <span class="text-white font-medium">{{ labelMetodo(metodo) }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-300 text-right font-mono">{{ data.total }}</td>
                                <td class="px-6 py-4 text-sm text-right">
                                    <span class="text-green-400 font-mono">{{ data.exitosas }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right">
                                    <span class="text-red-400 font-mono">{{ data.fallidas }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-white font-mono">${{ formatMonto(data.monto) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Listado de recargas del período -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Detalle de Recargas ({{ fecha_inicio }} → {{ fecha_fin }})
                    </h2>
                </div>

                <div v-if="recargas.length === 0" class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-slate-500">Sin recargas en el período seleccionado</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-900/50">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Folio</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Método</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-300 uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="r in recargas" :key="r.id" class="hover:bg-slate-700/20 transition">
                                <td class="px-6 py-3 text-sm text-slate-300 font-mono">{{ r.referencia || '-' }}</td>
                                <td class="px-6 py-3 text-sm text-slate-400">{{ formatDate(r.created_at) }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <span class="flex items-center gap-2">
                                        <span>{{ iconoMetodo(r.metodo_pago) }}</span>
                                        <span class="text-slate-300">{{ labelMetodo(r.metodo_pago) }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm font-bold text-white font-mono text-right">${{ formatMonto(r.monto) }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <span :class="badgeEstado(r.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
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
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    recargas: Array,
    stats: Object,
    resumen_metodos: Object,
    fecha_inicio: String,
    fecha_fin: String,
});

const localFechaInicio = ref(props.fecha_inicio);
const localFechaFin    = ref(props.fecha_fin);

const MS_PER_DAY = 864e5;

const tasaExito = computed(() => {
    if (!props.stats || props.stats.total_recargas === 0) return 0;
    return Math.round((props.stats.exitosas / props.stats.total_recargas) * 100);
});

const rangosRapidos = computed(() => {
    const hoy = new Date();
    const fmt  = (d) => d.toISOString().slice(0, 10);
    return [
        { label: 'Hoy',          inicio: fmt(hoy),                                 fin: fmt(hoy) },
        { label: 'Últimos 7 días',  inicio: fmt(new Date(hoy - 6 * MS_PER_DAY)),    fin: fmt(hoy) },
        { label: 'Últimos 30 días', inicio: fmt(new Date(hoy - 29 * MS_PER_DAY)),   fin: fmt(hoy) },
        { label: 'Últimos 90 días', inicio: fmt(new Date(hoy - 89 * MS_PER_DAY)),   fin: fmt(hoy) },
    ];
});

function formatMonto(v) {
    return Number(v).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric',
    });
}

function labelMetodo(m) {
    const labels = {
        tarjeta: 'Tarjeta',
        transferencia: 'Transferencia',
        efectivo: 'Efectivo',
        billetera_digital: 'Billetera Digital',
    };
    return labels[m] || m;
}

function iconoMetodo(m) {
    const iconos = {
        tarjeta: '💳',
        transferencia: '🏦',
        efectivo: '💵',
        billetera_digital: '📱',
    };
    return iconos[m] || '💰';
}

function badgeEstado(estado) {
    if (estado === 'exitosa') return 'bg-green-500/20 text-green-300';
    if (estado === 'fallida') return 'bg-red-500/20 text-red-300';
    return 'bg-yellow-500/20 text-yellow-300';
}

function actualizarFechas(inicio, fin) {
    localFechaInicio.value = inicio;
    localFechaFin.value    = fin;
    router.get(route('modulo_8.reportes.conciliacion', {
        fecha_inicio: inicio,
        fecha_fin: fin,
    }));
}
</script>
