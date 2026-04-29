<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    tiendas: Array,
    repartidores: Array,
    pedidosRecientes: Array,
    tipos: Object,
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

const TIPO_ICONS = {
    cafeteria:  '☕',
    papeleria:  '📄',
    kermesse:   '🎡',
    mercadito:  '🛍️',
    estudiante: '🎒',
    otro:       '🏪',
};

const activeTab = ref('tiendas');
const search = ref('');

// Repartidores Section State
const showAddModal = ref(false);
const userSearch = ref('');
const usersFound = ref([]);
const loadingSearch = ref(false);

const totalPendientes = () => props.tiendas.reduce((acc, t) => acc + (t.pedidos_pendientes ?? 0), 0);
const totalProceso = () => props.tiendas.reduce((acc, t) => acc + (t.pedidos_proceso ?? 0), 0);
const totalListos = () => props.tiendas.reduce((acc, t) => acc + (t.pedidos_listos ?? 0), 0);

const filteredTiendas = computed(() => {
    if (!search.value) return props.tiendas;
    return props.tiendas.filter(t => t.nombre.toLowerCase().includes(search.value.toLowerCase()));
});

const filteredRepartidores = computed(() => {
    if (!search.value) return props.repartidores ?? [];
    return (props.repartidores ?? []).filter(r => 
        r.nombre.toLowerCase().includes(search.value.toLowerCase()) || 
        r.apellido.toLowerCase().includes(search.value.toLowerCase()) || 
        r.email.toLowerCase().includes(search.value.toLowerCase())
    );
});

// User Search logic
watch(userSearch, async (val) => {
    if (val.length >= 3) {
        loadingSearch.value = true;
        try {
            const res = await axios.get('/admin/api/usuarios/buscar', { params: { q: val } });
            usersFound.value = res.data;
        } catch (e) {
            console.error(e);
        } finally {
            loadingSearch.value = false;
        }
    } else {
        usersFound.value = [];
    }
});

const asignarRepartidor = (usuarioId) => {
    router.post('/admin/repartidores/asignar', { usuario_id: usuarioId }, {
        onSuccess: () => {
            showAddModal.value = false;
            userSearch.value = '';
            usersFound.value = [];
        }
    });
};

const removerRepartidor = (id) => {
    if (confirm('¿Estás seguro de quitar a este usuario de la lista de repartidores?')) {
        router.delete(`/admin/repartidores/${id}`);
    }
};
</script>

<template>
    <Head title="Panel de Proveedores" />
    <AuthLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white">Panel de Proveedores</h1>
                    <p class="text-slate-400">Visión general y gestión de repartidores</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.proveedores.manage')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl transition-all border border-slate-700">
                        ⚙️ Gestión Proveedores
                    </Link>
                    <Link :href="route('admin.tiendas.manage')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl transition-all border border-slate-700">
                        🏪 Configuración Tiendas
                    </Link>
                    <button v-if="activeTab === 'repartidores'" 
                        @click="showAddModal = true"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-emerald-600/20">
                        + Alta Repartidor
                    </button>
                </div>
            </div>

            <!-- Tabs & Search -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 border-b border-slate-800">
                <div class="flex gap-4">
                    <button @click="activeTab = 'tiendas'" 
                        :class="['px-6 py-3 text-sm font-bold uppercase tracking-wider transition-all border-b-2', activeTab === 'tiendas' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-300']">
                        🏪 Tiendas
                    </button>
                    <button @click="activeTab = 'repartidores'" 
                        :class="['px-6 py-3 text-sm font-bold uppercase tracking-wider transition-all border-b-2', activeTab === 'repartidores' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-500 hover:text-slate-300']">
                        🚴‍♂️ Repartidores
                    </button>
                </div>
                <div class="relative w-full sm:w-64 mb-2">
                    <input v-model="search" type="text" :placeholder="activeTab === 'tiendas' ? 'Buscar tienda...' : 'Buscar repartidor...'" 
                        class="w-full bg-slate-900 border-slate-800 rounded-xl px-4 py-2 text-sm text-white focus:ring-2 focus:ring-blue-500"/>
                </div>
            </div>

            <!-- Tab content: Tiendas -->
            <div v-if="activeTab === 'tiendas'" class="space-y-4">
                <div v-for="tienda in filteredTiendas" :key="tienda.id"
                    class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-600 transition-all shadow-xl"
                >
                    <div class="h-1" :style="{ backgroundColor: tienda.color ?? '#3b82f6' }"></div>
                    <div class="p-5">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <span class="text-4xl">{{ TIPO_ICONS[tienda.tipo] ?? '🏪' }}</span>
                                <div class="w-full">
                                    <h3 class="text-xl font-bold text-white">{{ tienda.nombre }}</h3>
                                    <p class="text-sm text-slate-400">{{ tipos[tienda.tipo] }} · {{ tienda.operadores_count }} operador(es)</p>
                                </div>
                            </div>
                            <div class="flex gap-3 items-center flex-wrap">
                                <div class="flex items-center gap-2 px-3 py-2 bg-blue-500/10 border border-blue-500/20 rounded-xl">
                                    <span class="text-sm text-blue-300 font-bold">{{ tienda.operadores_count ?? 0 }} Operadores</span>
                                </div>
                                <Link :href="route('admin.tiendas.manage')"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-sm transition-colors shadow-lg shadow-blue-600/20">
                                    Gestionar Tienda →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="filteredTiendas.length === 0" class="py-20 text-center text-slate-500">No se encontraron tiendas.</div>
            </div>

            <!-- Tab content: Repartidores -->
            <div v-if="activeTab === 'repartidores'" class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
                <table class="w-full text-left">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase">Repartidor</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase">Contacto</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase text-center">Tienda</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <tr v-for="repartidor in filteredRepartidores" :key="repartidor.id" class="hover:bg-slate-800/30">
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400 font-bold">{{ repartidor.nombre[0] }}</div>
                                <div>
                                    <div class="text-white font-bold">{{ repartidor.nombre }} {{ repartidor.apellido }}</div>
                                    <div class="text-[10px] text-emerald-500 font-black uppercase">Activo</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-300">{{ repartidor.email }}</div>
                                <div class="text-xs text-slate-500">{{ repartidor.telefono || 'Sin tel' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 bg-slate-800 rounded-lg text-white font-bold">{{ repartidor.tienda?.nombre || 'General' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="removerRepartidor(repartidor.id)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-xl transition-all">🗑️</button>
                            </td>
                        </tr>
                        <tr v-if="filteredRepartidores.length === 0">
                            <td colspan="4" class="py-12 text-center text-slate-500">No hay repartidores registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Add Repartidor Modal -->
            <transition name="fade">
                <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/95 backdrop-blur-md">
                    <div class="bg-slate-900 border border-slate-800 w-full max-w-xl rounded-[2.5rem] p-8 shadow-2xl">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-black text-white">Alta de Repartidor</h2>
                            <button @click="showAddModal = false" class="text-slate-500 hover:text-white transition-colors">✕</button>
                        </div>
                        <input v-model="userSearch" type="text" placeholder="Buscar por nombre o email..." 
                            class="w-full bg-slate-800 border-slate-700 rounded-2xl p-4 text-white focus:ring-2 focus:ring-emerald-500 mb-4 transition-all outline-none"/>
                        <div class="max-h-64 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                             <div v-for="u in usersFound" :key="u.id" class="p-4 bg-slate-800/50 border border-slate-700 rounded-2xl flex justify-between items-center hover:bg-slate-700/50 transition-all group">
                                <div>
                                    <div class="font-bold text-white group-hover:text-emerald-400 transition-colors">{{ u.nombre }} {{ u.apellido }}</div>
                                    <div class="text-xs text-slate-500">{{ u.email }}</div>
                                </div>
                                <button @click="asignarRepartidor(u.id)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-[10px] uppercase rounded-xl transition-all">Asignar</button>
                             </div>
                             <div v-if="loadingSearch" class="text-center py-4 text-emerald-400">
                                 <div class="inline-block w-5 h-5 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin mr-2"></div>
                                 Buscando...
                             </div>
                        </div>
                        <button @click="showAddModal = false" class="w-full mt-6 py-4 bg-slate-800 hover:bg-slate-700 text-slate-400 font-black rounded-2xl uppercase text-[10px] transition-all">Cerrar</button>
                    </div>
                </div>
            </transition>
        </div>
    </AuthLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>
