<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    stats: Object,
    tienda: Object,
    tipos: Object
});

const apiStats = ref({
    pedidos_pendientes: 0,
    pedidos_en_proceso: 0,
    pedidos_listos: 0,
    entregados_hoy: 0,
    ventas_hoy: 0,
    tiempo_avg: 0
});

const loadingApi = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/api/proveedor/metrics');
        apiStats.value = response.data;
    } catch (error) {
        console.error('Error cargando métricas API:', error);
    } finally {
        loadingApi.value = false;
    }
});
</script>

<template>
    <Head title="Panel de Proveedor" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Panel de {{ tienda?.nombre || 'Mi Tienda' }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Gestión de inventario y personal para {{ (tipos && tienda) ? tipos[tienda.tipo] : 'tu negocio' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Estado</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                        {{ tienda?.activo ? 'Activo' : 'Cerrado' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Productos en Inventario -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 group hover:border-blue-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Productos en Catálogo</dt>
                                    <dd class="text-3xl font-bold text-white">{{ stats.productos_count }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-blue-500/20">
                        <Link href="/proveedor/inventario" class="text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200 flex items-center">
                            Gestionar Catálogo
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Repartidores Asignados -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-emerald-500/10 rounded-xl border border-emerald-500/20 group hover:border-emerald-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Repartidores Activos</dt>
                                    <dd class="text-3xl font-bold text-white">{{ stats.repartidores_count }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-emerald-500/20">
                        <Link href="/proveedor/repartidores" class="text-sm text-emerald-400 hover:text-emerald-300 transition-colors duration-200 flex items-center">
                            Gestionar Equipo
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Configuración -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-purple-500/10 rounded-xl border border-purple-500/20 group hover:border-purple-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Configuración</dt>
                                    <dd class="text-3xl font-bold text-white">Tienda</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-purple-500/20">
                        <Link href="#" class="text-sm text-purple-400 hover:text-purple-300 transition-colors duration-200 flex items-center">
                            Editar Información
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- NEW: Pedidos Pendientes (API) -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-orange-500/10 rounded-xl border border-orange-500/20 group hover:border-orange-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-600 to-orange-700 flex items-center justify-center shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <span class="text-2xl">⏳</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Pedidos Pendientes</dt>
                                    <dd class="text-3xl font-bold text-white">
                                        <span v-if="loadingApi" class="animate-pulse">...</span>
                                        <span v-else>{{ apiStats.pedidos_pendientes }}</span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NEW: Tiempo Promedio (API) -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-sky-500/10 rounded-xl border border-sky-500/20 group hover:border-sky-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-sky-600 to-sky-700 flex items-center justify-center shadow-lg shadow-sky-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <span class="text-2xl">⏱️</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Tiempo Prom. Preparación</dt>
                                    <dd class="text-3xl font-bold text-white">
                                        <span v-if="loadingApi" class="animate-pulse">...</span>
                                        <span v-else>{{ apiStats.tiempo_avg }} <span class="text-sm">min</span></span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NEW: Ventas Hoy (API) -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-pink-500/10 rounded-xl border border-pink-500/20 group hover:border-pink-500/40 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-pink-600 to-pink-700 flex items-center justify-center shadow-lg shadow-pink-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <span class="text-2xl">💰</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Ventas/Consumos Hoy</dt>
                                    <dd class="text-3xl font-bold text-white">
                                        <span v-if="loadingApi" class="animate-pulse">...</span>
                                        <span v-else>${{ Number(apiStats.ventas_hoy).toFixed(2) }}</span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Acciones Rápidas -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Acciones de Gestión</h3>
                    <div class="grid grid-cols-1 gap-3">
                        <Link href="/proveedor/inventario" class="flex items-center p-3 bg-blue-500/10 border border-blue-500/30 rounded-xl hover:bg-blue-500/20 transition-all group">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-white">Administrar Inventario</span>
                        </Link>

                        <Link href="/proveedor/repartidores" class="flex items-center p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl hover:bg-emerald-500/20 transition-all group">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-white">Gestionar Repartidores</span>
                        </Link>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-slate-500/10 rounded-xl border border-slate-700 p-6 flex flex-col justify-center text-center">
                    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-700">
                        <span class="text-2xl">🏪</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">{{ tienda?.nombre }}</h3>
                    <p class="text-slate-400 text-sm mb-4">{{ tienda?.descripcion || 'Sin descripción disponible' }}</p>
                    <div class="flex justify-center gap-4">
                        <div class="text-center">
                            <div class="text-xs text-slate-500 uppercase font-black">Tipo</div>
                            <div class="text-sm font-bold text-blue-400">{{ (tipos && tienda) ? tipos[tienda.tipo] : 'No asignado' }}</div>
                        </div>
                        <div class="w-px h-8 bg-slate-700"></div>
                        <div class="text-center">
                            <div class="text-xs text-slate-500 uppercase font-black">Ubicación</div>
                            <div class="text-sm font-bold text-slate-300">{{ tienda?.ubicacion || 'No especificada' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>