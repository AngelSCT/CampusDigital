<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tiendas: Array,
    stats: Object,
    tipos: Object,
});

const TIPO_ICONS = {
    cafeteria:  '☕',
    papeleria:  '📄',
    tienda:     '🛍️',
};

const search = ref('');

const filteredTiendas = computed(() => {
    if (!search.value) return props.tiendas;
    return props.tiendas.filter(t => t.nombre.toLowerCase().includes(search.value.toLowerCase()));
});

const totalProductos = computed(() => props.tiendas.reduce((acc, t) => acc + (t.productos_count ?? 0), 0));
const totalPedidos = computed(() => props.tiendas.reduce((acc, t) => acc + (t.pedidos_count ?? 0), 0));

</script>

<template>
    <Head title="Panel de Tiendas" />
    <AuthLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white">Dashboard de Tiendas</h1>
                    <p class="text-slate-400">Visión general de los puntos de venta del campus</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.tiendas.manage')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-600/20">
                        ⚙️ Gestión Completa
                    </Link>
                </div>
            </div>

            <!-- Summary Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-blue-500/10 border border-blue-500/30 rounded-2xl p-4">
                    <div class="text-sm font-bold text-blue-400 uppercase mb-1">Total Tiendas</div>
                    <div class="text-4xl font-bold text-white">{{ stats.total }}</div>
                </div>
                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-4">
                    <div class="text-sm font-bold text-emerald-400 uppercase mb-1">Puntos Activos</div>
                    <div class="text-4xl font-bold text-white">{{ stats.activas }}</div>
                </div>
                <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-2xl p-4">
                    <div class="text-sm font-bold text-indigo-400 uppercase mb-1">Total Productos</div>
                    <div class="text-4xl font-bold text-white">{{ totalProductos }}</div>
                </div>
                <div class="bg-purple-500/10 border border-purple-500/30 rounded-2xl p-4">
                    <div class="text-sm font-bold text-purple-400 uppercase mb-1">Pedidos Históricos</div>
                    <div class="text-4xl font-bold text-white">{{ totalPedidos }}</div>
                </div>
            </div>

            <!-- Stats by Type -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="(count, type) in stats.por_tipo" :key="type" 
                    class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex items-center justify-between hover:border-slate-600 transition-all">
                    <div class="flex items-center gap-4">
                        <span class="text-3xl">{{ TIPO_ICONS[type] ?? '🏪' }}</span>
                        <div>
                            <h4 class="text-white font-bold text-lg">{{ tipos[type] }}</h4>
                            <p class="text-slate-500 text-sm">Distribución de red</p>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-white bg-slate-800 px-3 py-1 rounded-xl">
                        {{ count }}
                    </div>
                </div>
            </div>

            <!-- Search and List -->
            <div class="relative w-full sm:w-64">
                <input v-model="search" type="text" placeholder="Filtrar tiendas..." 
                    class="w-full bg-slate-900 border-slate-800 rounded-xl px-4 py-2 text-sm text-white focus:ring-2 focus:ring-blue-500"/>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="tienda in filteredTiendas" :key="tienda.id"
                    class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-600 transition-all shadow-xl group"
                >
                    <div class="h-24 bg-slate-800 relative overflow-hidden">
                         <img v-if="tienda.imagen_url" :src="'/storage/' + tienda.imagen_url" class="absolute inset-0 w-full h-full object-cover opacity-30 group-hover:scale-110 transition-transform duration-500">
                         <div class="absolute inset-0 flex items-center justify-center">
                             <span class="text-4xl group-hover:scale-125 transition-transform duration-300">{{ TIPO_ICONS[tienda.tipo] ?? '🏪' }}</span>
                         </div>
                    </div>
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-white">{{ tienda.nombre }}</h3>
                            <span :class="['px-2 py-0.5 rounded-full text-[10px] uppercase font-black', tienda.activo ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400']">
                                {{ tienda.activo ? 'Activo' : 'Cerrado' }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-400 line-clamp-1 mb-4">{{ tienda.descripcion }}</p>
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-800">
                            <div>
                                <div class="text-[10px] text-slate-500 uppercase font-black">Productos</div>
                                <div class="text-lg font-bold text-white">{{ tienda.productos_count }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-500 uppercase font-black">Pedidos</div>
                                <div class="text-lg font-bold text-white">{{ tienda.pedidos_count }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
