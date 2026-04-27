<template>
    <AuthLayout>
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Encabezado -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        Historial de Recargas
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Todas tus transacciones de recarga</p>
                </div>
                <Link :href="route('modulo_8.recargar.form')" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg text-sm font-medium transition">
                    Nueva Recarga
                </Link>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase mb-1">Total de Recargas</p>
                    <p class="text-2xl font-bold text-white">{{ stats.total_recargas }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-900/40 to-emerald-900/40 border border-green-500/20 rounded-xl p-4">
                    <p class="text-xs text-green-400 uppercase mb-1">Exitosas</p>
                    <p class="text-2xl font-bold text-white">{{ stats.recargas_exitosas }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-900/40 to-pink-900/40 border border-red-500/20 rounded-xl p-4">
                    <p class="text-xs text-red-400 uppercase mb-1">Fallidas</p>
                    <p class="text-2xl font-bold text-white">{{ stats.recargas_fallidas }}</p>
                </div>
                <div class="bg-gradient-to-br from-cyan-900/40 to-blue-900/40 border border-cyan-500/20 rounded-xl p-4">
                    <p class="text-xs text-cyan-400 uppercase mb-1">Monto Total</p>
                    <p class="text-2xl font-bold text-white">${{ formatMonto(stats.monto_total) }}</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-6 space-y-4">
                <h2 class="text-base font-semibold text-white">Filtros</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Estado -->
                    <div>
                        <label class="block text-xs text-slate-400 mb-2 uppercase">Estado</label>
                        <div class="flex gap-2 flex-wrap">
                            <button 
                                v-for="e in ['todos', 'exitosa', 'fallida']" 
                                :key="e"
                                @click="$router.get(route('modulo_8.reportes.historial', { estado: e, periodo: filtro_periodo }))"
                                :class="filtro_estado === e
                                    ? 'bg-cyan-600 border-cyan-500 text-white'
                                    : 'bg-slate-700 border-slate-600 text-slate-400 hover:border-slate-500'"
                                class="px-3 py-1.5 border rounded-lg text-xs font-medium transition-all capitalize"
                            >
                                {{ e }}
                            </button>
                        </div>
                    </div>

                    <!-- Período -->
                    <div>
                        <label class="block text-xs text-slate-400 mb-2 uppercase">Período</label>
                        <select 
                            @change="e => $router.get(route('modulo_8.reportes.historial', { estado: filtro_estado, periodo: e.target.value }))"
                            :value="filtro_periodo"
                            class="w-full px-3 py-1.5 rounded-lg bg-slate-700 border border-slate-600 text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                        >
                            <option value="todos">Todo el tiempo</option>
                            <option value="7">Últimos 7 días</option>
                            <option value="30">Últimos 30 días</option>
                            <option value="90">Últimos 90 días</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabla de recargas -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl overflow-hidden">
                <div v-if="recargas.data.length === 0" class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <p class="text-slate-500">Sin recargas en este período</p>
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
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-300 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="r in recargas.data" :key="r.id" class="hover:bg-slate-700/20 transition">
                                <td class="px-6 py-3 text-sm text-slate-300 font-mono">{{ r.referencia }}</td>
                                <td class="px-6 py-3 text-sm text-slate-400">{{ formatDateTime(r.created_at) }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <span class="flex items-center gap-2">
                                        <span>{{ iconoMetodo(r.metodo_pago) }}</span>
                                        {{ labelMetodo(r.metodo_pago) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm font-bold text-white font-mono">${{ formatMonto(r.monto) }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <span :class="badgeEstado(r.estado)" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium capitalize">
                                        {{ r.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button 
                                            v-if="r.estado === 'exitosa'"
                                            @click="descargarComprobante(r.id)"
                                            class="text-slate-400 hover:text-cyan-400 transition p-1"
                                            title="Descargar comprobante"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="recargas.last_page > 1" class="flex items-center justify-center gap-2">
                <Link 
                    v-if="recargas.current_page > 1"
                    :href="recargas.prev_page_url"
                    class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm transition"
                >
                    Anterior
                </Link>

                <div class="flex gap-1">
                    <button 
                        v-for="page in recargas.last_page" 
                        :key="page"
                        @click="$router.get(route('modulo_8.reportes.historial', { page, estado: filtro_estado, periodo: filtro_periodo }))"
                        :class="page === recargas.current_page
                            ? 'bg-cyan-600 text-white'
                            : 'bg-slate-700 text-slate-400 hover:bg-slate-600'"
                        class="px-2.5 py-1.5 rounded-lg text-sm font-medium transition"
                    >
                        {{ page }}
                    </button>
                </div>

                <Link 
                    v-if="recargas.current_page < recargas.last_page"
                    :href="recargas.next_page_url"
                    class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm transition"
                >
                    Siguiente
                </Link>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    recargas: Object,
    stats: Object,
    filtro_estado: String,
    filtro_periodo: String,
});

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
    if (estado === 'exitosa') return 'bg-green-500/20 text-green-300 border border-green-500/20';
    if (estado === 'fallida') return 'bg-red-500/20 text-red-300 border border-red-500/20';
    return 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/20';
}

function descargarComprobante(id) {
    window.location.href = route('modulo_8.comprobante', { id });
}
</script>
