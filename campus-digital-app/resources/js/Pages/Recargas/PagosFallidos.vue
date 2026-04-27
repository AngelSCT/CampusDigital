<template>
    <AuthLayout>
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Encabezado -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-red-400 to-pink-500 bg-clip-text text-transparent">
                        Pagos Fallidos
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Reintentar transacciones rechazadas</p>
                </div>
                <Link :href="route('modulo_8.recargar.form')" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg text-sm font-medium transition">
                    Nueva Recarga
                </Link>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-red-900/40 to-pink-900/40 border border-red-500/20 rounded-xl p-6">
                    <p class="text-xs text-red-400 uppercase mb-1">Total Fallidos</p>
                    <p class="text-3xl font-bold text-white">{{ stats.total_fallidas }}</p>
                </div>
                <div class="bg-gradient-to-br from-yellow-900/40 to-orange-900/40 border border-yellow-500/20 rounded-xl p-6">
                    <p class="text-xs text-yellow-400 uppercase mb-1">Monto Intentado</p>
                    <p class="text-3xl font-bold text-white">${{ formatMonto(stats.monto_intentado) }}</p>
                </div>
            </div>

            <!-- Filtro período -->
            <div class="flex gap-2">
                <button 
                    v-for="p in periodos" 
                    :key="p.value"
                    @click="$router.get(route('modulo_8.reportes.fallidos', { periodo: p.value }))"
                    :class="filtro_periodo === p.value
                        ? 'bg-red-600 border-red-500 text-white'
                        : 'bg-slate-700 border-slate-600 text-slate-400 hover:border-slate-500'"
                    class="px-4 py-2 border rounded-lg text-sm font-medium transition-all"
                >
                    {{ p.label }}
                </button>
            </div>

            <!-- Tabla de fallidos -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl overflow-hidden">
                <div v-if="recargas.length === 0" class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-slate-500 text-lg font-medium">¡Excelente!</p>
                    <p class="text-slate-400 text-sm mt-1">No tienes pagos fallidos en este período</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-900/50">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Folio</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Motivo del Fallo</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-300 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="r in recargas" :key="r.id" class="hover:bg-slate-700/20 transition">
                                <td class="px-6 py-4 text-sm text-slate-300 font-mono">{{ r.referencia }}</td>
                                <td class="px-6 py-4 text-sm text-slate-400">{{ formatDateTime(r.created_at) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="flex items-center gap-2">
                                        <span>{{ iconoMetodo(r.metodo_pago) }}</span>
                                        {{ labelMetodo(r.metodo_pago) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-white font-mono">${{ formatMonto(r.monto) }}</td>
                                <td class="px-6 py-4 text-sm text-red-300">{{ r.razon_fallo }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        @click="reintentar(r.id)"
                                        :disabled="reintentando === r.id"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-yellow-600 hover:bg-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg text-sm font-medium transition"
                                    >
                                        <svg v-if="reintentando === r.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                        </svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        {{ reintentando === r.id ? 'Reintentando...' : 'Reintentar' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Info útil -->
            <div class="bg-blue-900/20 border border-blue-500/20 rounded-xl p-6">
                <div class="flex gap-3">
                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-300 mb-1">¿Qué hacer si falla un pago?</h3>
                        <ul class="text-sm text-blue-200 space-y-1 list-disc list-inside">
                            <li>Verifica tu conexión a internet</li>
                            <li>Comprueba que tengas fondos disponibles</li>
                            <li>Intenta con otro método de pago</li>
                            <li>Contacta a soporte si el problema persiste</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    recargas: Array,
    stats: Object,
    filtro_periodo: String,
});

const reintentando = ref(null);

const periodos = [
    { value: 'todos', label: 'Todo el tiempo' },
    { value: '7', label: 'Últimos 7 días' },
    { value: '30', label: 'Últimos 30 días' },
    { value: '90', label: 'Últimos 90 días' },
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

function reintentar(id) {
    reintentando.value = id;
    router.post(route('modulo_8.recargar.reintentar', { id }), {}, {
        onSuccess: () => {
            reintentando.value = null;
            router.get(route('modulo_8.reportes.fallidos'));
        },
        onError: () => {
            reintentando.value = null;
        }
    });
}
</script>
