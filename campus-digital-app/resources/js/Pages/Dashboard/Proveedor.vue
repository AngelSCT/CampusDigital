<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    pedidos_recientes: Array,
    tienda: Object,
    tipos: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const formatFecha = (fecha) => {
    if (!fecha) return '---';
    return new Date(fecha).toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadgeClass = (estado) => {
    const classes = {
        'creado': 'bg-blue-500/10 text-blue-400 border-blue-500/30',
        'aceptado': 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
        'en_proceso': 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
        'listo': 'bg-green-500/10 text-green-400 border-green-500/30',
        'entregado': 'bg-slate-500/10 text-slate-400 border-slate-500/30',
        'cancelado': 'bg-red-500/10 text-red-400 border-red-500/30',
    };
    return classes[estado] || 'bg-slate-500/10 text-slate-400 border-slate-500/30';
};
</script>

<template>
    <Head title="Panel de Proveedor" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Panel de {{ tienda?.nombre || 'Cafetería' }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Gestiona pedidos de tu {{ tipos[tienda?.tipo] || 'negocio' }} en tiempo real</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Estado del Área</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                        Operativo
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Pedidos Pendientes -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-yellow-500/10 rounded-xl border border-yellow-500/20 group hover:border-yellow-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-600 to-yellow-700 flex items-center justify-center shadow-lg shadow-yellow-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Por Aceptar</dt>
                                    <dd class="text-3xl font-bold text-white">{{ stats.pedidos_pendientes }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-yellow-500/20">
                        <Link :href="route('proveedor.operativo.index')" class="text-sm text-yellow-400 hover:text-yellow-300 transition-colors duration-200 flex items-center">
                            Ver nuevos 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- En Proceso -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 group hover:border-blue-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Preparando</dt>
                                    <dd class="text-3xl font-bold text-white">{{ stats.pedidos_en_proceso }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-blue-500/20">
                        <Link :href="route('proveedor.operativo.index')" class="text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200 flex items-center">
                            Ver operativos 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Completados Hoy -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-green-500/10 rounded-xl border border-green-500/20 group hover:border-green-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Entregados Hoy</dt>
                                    <dd class="text-3xl font-bold text-white">{{ stats.pedidos_completados_hoy }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-green-500/20">
                        <Link :href="route('proveedor.operativo.index')" class="text-sm text-green-400 hover:text-green-300 transition-colors duration-200 flex items-center">
                            Ver historial 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Ventas -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-purple-500/10 rounded-xl border border-purple-500/20 group hover:border-purple-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Ventas Hoy</dt>
                                    <dd class="text-3xl font-bold text-white">{{ formatCurrency(stats.ventas_hoy) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-purple-500/20">
                        <Link :href="route('proveedor.reportes.index')" class="text-sm text-purple-400 hover:text-purple-300 transition-colors duration-200 flex items-center">
                            Ver reportes 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Acciones Rápidas -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-6">
                        <h3 class="text-lg font-bold text-white mb-4">Métricas de Atención</h3>
                        <div class="space-y-4">
                            <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700">
                                <p class="text-xs text-slate-400 uppercase font-semibold">Tiempo Promedio</p>
                                <div class="flex items-end justify-between mt-1">
                                    <p class="text-2xl font-bold text-blue-400">{{ stats.tiempo_avg || 0 }} <span class="text-sm font-normal text-slate-500">min</span></p>
                                    <span class="text-xs text-green-400 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                        Óptimo
                                    </span>
                                </div>
                            </div>
                            <!-- Otros indicadores pueden ir aquí -->
                        </div>

                        <h3 class="text-lg font-bold text-white mt-8 mb-4">Acciones Rápidas</h3>
                        <div class="grid grid-cols-1 gap-3">
                            <Link :href="route('proveedor.operativo.index')" class="flex items-center p-3 bg-blue-500/10 border border-blue-500/30 rounded-xl hover:bg-blue-500/20 transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-white">Panel Operativo</span>
                            </Link>

                            <Link :href="route('proveedor.inventario.index')" class="flex items-center p-3 bg-green-500/10 border border-green-500/30 rounded-xl hover:bg-green-500/20 transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-white">Gestionar Inventario</span>
                            </Link>

                            <Link :href="route('proveedor.reportes.index')" class="flex items-center p-3 bg-purple-500/10 border border-purple-500/30 rounded-xl hover:bg-purple-500/20 transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-white">Generar Reporte</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pedidos Recientes -->
                <div class="lg:col-span-2">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-700 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-white">Pedidos Recientes</h3>
                            <Link href="#" class="text-xs font-semibold text-blue-400 hover:text-blue-300 uppercase tracking-widest">Ver todo</Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-900/30 text-slate-400 text-xs uppercase tracking-wider">
                                        <th class="px-6 py-3 font-semibold">Folio</th>
                                        <th class="px-6 py-3 font-semibold">Usuario</th>
                                        <th class="px-6 py-3 font-semibold text-center">Estado</th>
                                        <th class="px-6 py-3 font-semibold text-right">Total</th>
                                        <th class="px-6 py-3 font-semibold text-right">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700/50">
                                    <tr v-for="pedido in pedidos_recientes" :key="pedido.id" class="hover:bg-slate-700/20 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-blue-400 font-mono">{{ pedido.numero_folio }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-white font-medium">{{ pedido.usuario_nombre }} {{ pedido.usuario_apellido }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold border', getStatusBadgeClass(pedido.estado)]">
                                                {{ pedido.estado.toUpperCase() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="text-sm font-bold text-white">{{ formatCurrency(pedido.total) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs text-slate-400">
                                            {{ formatFecha(pedido.created_at) }}
                                        </td>
                                    </tr>
                                    <tr v-if="pedidos_recientes.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-800 flex items-center justify-center text-slate-500">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                            </div>
                                            <p class="text-sm text-slate-400">No hay pedidos registrados en esta área.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>