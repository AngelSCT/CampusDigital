<template>
    <AuthLayout>
        <div class="max-w-7xl mx-auto space-y-6">

            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    Dashboard de Recargas
                </h1>
                <p class="mt-1 text-sm text-slate-400">Resumen de tu actividad en el monedero universitario</p>
            </div>

            <div class="flex gap-3 flex-wrap">
                <button
                    v-for="p in periodos"
                    :key="p.value"
                    @click="router.get(route('modulo_8.index', { periodo: p.value }))"
                    :class="periodo == p.value
                        ? 'bg-cyan-600 border-cyan-500 text-white'
                        : 'bg-slate-700 border-slate-600 text-slate-400 hover:border-slate-500'"
                    class="px-4 py-2 border rounded-lg text-sm font-medium transition-all"
                >
                    {{ p.label }}
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="bg-gradient-to-br from-cyan-900/40 to-blue-900/40 border border-cyan-500/20 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-cyan-400 uppercase tracking-wider">Saldo Actual</p>
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-white font-mono">${{ formatMonto(stats.saldo_actual) }}</p>
                </div>

                <div class="bg-gradient-to-br from-purple-900/40 to-pink-900/40 border border-purple-500/20 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-purple-400 uppercase tracking-wider">Recargas</p>
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ stats.recargas_periodo }}</p>
                    <p class="text-xs text-purple-300 mt-1">en {{ periodo }} días</p>
                </div>

                <div class="bg-gradient-to-br from-green-900/40 to-emerald-900/40 border border-green-500/20 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-green-400 uppercase tracking-wider">Exitosas</p>
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ stats.exitosas_periodo }}</p>
                    <p class="text-xs text-green-300 mt-1" v-if="stats.recargas_periodo > 0">{{ stats.ratio_exito }}% éxito</p>
                </div>

                <div class="bg-gradient-to-br from-orange-900/40 to-yellow-900/40 border border-orange-500/20 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-orange-400 uppercase tracking-wider">Total Recargado</p>
                        <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-white font-mono">${{ formatMonto(stats.monto_total_periodo) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-700">
                        <h2 class="text-base font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Métodos de Pago
                        </h2>
                    </div>

                    <div v-if="Object.keys(metodos).length === 0" class="px-6 py-8 text-center text-slate-400">
                        <p class="text-sm">Sin recargas registradas</p>
                    </div>

                    <div v-else class="divide-y divide-slate-700">
                        <div v-for="(cantidad, metodo) in metodos" :key="metodo" class="px-6 py-4 flex items-center justify-between hover:bg-slate-700/20">
                            <span class="flex items-center gap-3">
                                <span class="text-2xl">{{ iconoMetodo(metodo) }}</span>
                                <span class="text-sm text-white capitalize">{{ labelMetodo(metodo) }}</span>
                            </span>
                            <span class="bg-cyan-500/20 text-cyan-300 px-2.5 py-1 rounded-full text-xs font-semibold">{{ cantidad }}</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-700">
                        <h2 class="text-base font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Últimas Recargas
                        </h2>
                    </div>

                    <div v-if="ultimas.length === 0" class="px-6 py-8 text-center text-slate-400">
                        <p class="text-sm">Sin recargas registradas</p>
                    </div>

                    <div v-else class="divide-y divide-slate-700">
                        <div v-for="r in ultimas" :key="r.id" class="px-6 py-4 flex items-center justify-between hover:bg-slate-700/20 transition">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold" :class="badgeEstado(r.estado)">
                                        {{ r.estado.charAt(0).toUpperCase() + r.estado.slice(1) }}
                                    </span>
                                    <span class="text-xs text-slate-400">{{ formatDateTime(r.created_at) }}</span>
                                </div>
                                <p class="text-sm text-slate-300">{{ labelMetodo(r.metodo_pago) }}</p>
                            </div>
                            <p class="text-sm font-bold text-white font-mono ml-4 whitespace-nowrap">${{ formatMonto(r.monto) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link :href="route('modulo_8.reportes.historial', { periodo: periodo })" class="p-6 bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl hover:border-cyan-500 transition group">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-base font-semibold text-white group-hover:text-cyan-400 transition">Historial Completo</h3>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-cyan-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-sm text-slate-400">Ver todas tus recargas</p>
                </Link>

                <Link :href="route('modulo_8.reportes.fallidos', { periodo: periodo })" class="p-6 bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl hover:border-red-500 transition group">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-base font-semibold text-white group-hover:text-red-400 transition">Pagos Fallidos</h3>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-red-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <p class="text-sm text-slate-400">Reintenta pagos fallidos</p>
                </Link>

                <Link :href="route('modulo_8.reportes.conciliacion', { periodo: periodo })" class="p-6 bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl hover:border-purple-500 transition group">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-base font-semibold text-white group-hover:text-purple-400 transition">Conciliación</h3>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-purple-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-slate-400">Análisis por período</p>
                </Link>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    stats: Object,
    metodos: Object,
    ultimas: Array,
    periodo: String,
});

const periodos = [
    { value: '7', label: 'Últimos 7 días' },
    { value: '30', label: 'Últimos 30 días' },
    { value: '90', label: 'Últimos 90 días' },
    { value: 'todos', label: 'Todo el tiempo' },
];

function formatMonto(v) {
    return Number(v).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
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
    if (estado === 'exitosa') return 'bg-green-500/20 text-green-300';
    if (estado === 'fallida') return 'bg-red-500/20 text-red-300';
    return 'bg-yellow-500/20 text-yellow-300';
}
</script>
