<template>
    <AuthLayout title="Mi Panel de Entregas">
        <div class="space-y-6">
            <!-- Header & Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-6 shadow-lg shadow-blue-500/20 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Pedidos Pendientes</p>
                            <h3 class="text-3xl font-bold mt-1">{{ stats.pendientes }}</h3>
                        </div>
                        <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-2xl p-6 shadow-lg shadow-emerald-500/20 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm font-medium">Entregas de Hoy</p>
                            <h3 class="text-3xl font-bold mt-1">{{ stats.entregados_hoy }}</h3>
                        </div>
                        <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 shadow-lg border border-slate-700 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Total Histórico</p>
                            <h3 class="text-3xl font-bold mt-1">{{ stats.total_historico }}</h3>
                        </div>
                        <div class="bg-slate-700 p-3 rounded-xl">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Deliveries Section -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-2xl border border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Entregas Activas
                    </h2>
                    <span class="px-3 py-1 bg-blue-500/10 text-blue-400 text-xs font-bold rounded-full uppercase tracking-wider">
                        Asignadas a ti
                    </span>
                </div>

                <div class="p-6">
                    <div v-if="pedidosActivos.length === 0" class="text-center py-12">
                        <div class="bg-slate-800 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <h3 class="text-slate-400 font-medium">No tienes entregas pendientes por ahora</h3>
                        <p class="text-slate-500 text-sm mt-1">¡Buen trabajo! Descansa un poco.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div v-for="pedido in pedidosActivos" :key="pedido.id" 
                             class="group bg-slate-800/40 border border-slate-700/50 rounded-xl p-5 hover:border-blue-500/50 transition-all duration-300">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span class="text-xs font-bold text-blue-400 uppercase tracking-widest">{{ pedido.numero_folio }}</span>
                                    <h4 class="text-lg font-bold text-white mt-1">{{ pedido.usuario.nombre }} {{ pedido.usuario.apellido }}</h4>
                                </div>
                                <div :class="getStatusClass(pedido.estado)" class="px-2 py-1 rounded text-[10px] font-bold uppercase">
                                    {{ pedido.estado.replace('_', ' ') }}
                                </div>
                            </div>

                            <div class="space-y-3 mb-6">
                                <div class="flex items-center text-slate-400 text-sm">
                                    <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>Origen: <b class="text-slate-300">{{ pedido.tienda.nombre }}</b></span>
                                </div>
                                <div class="flex items-center text-slate-400 text-sm">
                                    <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Destino: <b class="text-slate-300">{{ pedido.descripcion }}</b></span>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button v-if="pedido.estado === 'listo'" 
                                        @click="updateStatus(pedido.id, 'en_proceso')"
                                        class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded-lg transition-colors text-sm">
                                    Iniciar Entrega
                                </button>
                                <button v-if="pedido.estado === 'en_proceso'" 
                                        @click="updateStatus(pedido.id, 'entregado')"
                                        class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 rounded-lg transition-colors text-sm">
                                    Marcar como Entregado
                                </button>
                                <button v-if="pedido.estado === 'en_proceso'" 
                                        @click="updateStatus(pedido.id, 'cancelado')"
                                        class="bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold py-2 px-4 rounded-lg transition-colors text-sm">
                                    Cancelar
                                </button>
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
import { router } from '@inertiajs/vue3';

const props = defineProps({
    pedidosActivos: Array,
    stats: Object
});

function getStatusClass(estado) {
    switch (estado) {
        case 'listo': return 'bg-amber-500/20 text-amber-500 border border-amber-500/30';
        case 'en_proceso': return 'bg-blue-500/20 text-blue-500 border border-blue-500/30';
        case 'entregado': return 'bg-emerald-500/20 text-emerald-500 border border-emerald-500/30';
        default: return 'bg-slate-500/20 text-slate-500 border border-slate-500/30';
    }
}

function updateStatus(pedidoId, nuevoEstado) {
    if (confirm(`¿Confirmas el cambio a estado: ${nuevoEstado.replace('_', ' ')}?`)) {
        router.post(route('repartidor.pedido.estado', pedidoId), {
            estado: nuevoEstado
        });
    }
}
</script>
