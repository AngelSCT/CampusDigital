<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    modulo: String,
    estados: Array
});

const pedidos = ref([]);
const loading = ref(true);
const search = ref('');
const activeTab = ref('pendientes');

// Manual Order State
const showOrderModal = ref(false);
const userSearch = ref('');
const usersFound = ref([]);
const selectedUser = ref(null);

const orderForm = useForm({
    usuario_id: '',
    total: 0,
    descripcion: '',
    notas: ''
});

const fetchPedidos = async () => {
    loading.value = true;
    try {
        const params = {
            modulo: props.modulo,
            per_page: 50
        };
        
        if (activeTab.value === 'pendientes') {
            params.estado = 'creado';
        } else if (activeTab.value === 'proceso') {
            params.estado = 'aceptado,en_proceso'; // Note: existing API might not support comma-separated, will check
        } else if (activeTab.value === 'listos') {
            params.estado = 'listo';
        }

        const response = await axios.get('/proveedor/api/pedidos', { params });
        // Filtering manually if API doesn't support multiple states
        if (activeTab.value === 'proceso') {
             pedidos.value = response.data.data.filter(p => ['aceptado', 'en_proceso'].includes(p.estado));
        } else {
             pedidos.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching pedidos:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchPedidos();
    // Refresh every 30 seconds
    setInterval(fetchPedidos, 30000);
});

const refreshStats = () => {
    router.reload({ only: ['stats'] });
};

const cambiarEstado = async (pedido, nuevoEstado) => {
    try {
        await axios.post(`/proveedor/api/pedidos/${pedido.id}/estado`, {
            estado: nuevoEstado
        });
        fetchPedidos();
        refreshStats();
    } catch (error) {
        alert('Error al actualizar el pedido');
    }
};

const confirmarEntrega = async (pedido) => {
    if (confirm(`¿Confirmar entrega del pedido ${pedido.numero_folio}?`)) {
        try {
            await axios.post(`/proveedor/api/pedidos/${pedido.id}/estado`, {
                estado: 'entregado'
            });
            fetchPedidos();
            refreshStats();
        } catch (error) {
            alert('Error al confirmar entrega');
        }
    }
};

// User Search Logic
watch(userSearch, async (val) => {
    if (val.length >= 3) {
        const res = await axios.get('/proveedor/api/users/search', { params: { q: val } });
        usersFound.value = res.data;
    } else {
        usersFound.value = [];
    }
});

const selectUser = (user) => {
    selectedUser.value = user;
    orderForm.usuario_id = user.id;
    usersFound.value = [];
    userSearch.value = `${user.nombre} ${user.apellido}`;
};

const submitOrder = () => {
    orderForm.post('/proveedor/pedidos', {
        onSuccess: () => {
            showOrderModal.value = false;
            orderForm.reset();
            selectedUser.value = null;
            userSearch.value = '';
            fetchPedidos();
            refreshStats();
        }
    });
};

const filteredPedidos = computed(() => {
    if (!search.value) return pedidos.value;
    return pedidos.value.filter(p => 
        p.numero_folio.toLowerCase().includes(search.value.toLowerCase()) ||
        (p.usuario.nombre + ' ' + p.usuario.apellido).toLowerCase().includes(search.value.toLowerCase())
    );
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
};

const formatFecha = (fecha) => {
    return new Date(fecha).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
};

const setTab = (tab) => {
    activeTab.value = tab;
    fetchPedidos();
};

</script>

<template>
    <Head title="Panel Operativo" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white">Panel Operativo: {{ modulo }}</h1>
                    <p class="text-slate-400">Gestiona los pedidos entrantes y su preparación</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <button 
                        @click="showOrderModal = true"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-600/20"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Pedido
                    </button>
                    <div class="relative min-w-[300px]">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Buscar por folio o usuario..." 
                            class="block w-full pl-10 pr-3 py-2 border border-slate-700 rounded-xl bg-slate-900 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        />
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-slate-800">
                <button 
                    @click="setTab('pendientes')"
                    :class="['px-6 py-3 text-sm font-medium transition-colors border-b-2', activeTab === 'pendientes' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-400 hover:text-slate-200']"
                >
                    Pendientes
                </button>
                <button 
                    @click="setTab('proceso')"
                    :class="['px-6 py-3 text-sm font-medium transition-colors border-b-2', activeTab === 'proceso' ? 'border-yellow-500 text-yellow-400' : 'border-transparent text-slate-400 hover:text-slate-200']"
                >
                    En Preparación
                </button>
                <button 
                    @click="setTab('listos')"
                    :class="['px-6 py-3 text-sm font-medium transition-colors border-b-2', activeTab === 'listos' ? 'border-green-500 text-green-400' : 'border-transparent text-slate-400 hover:text-slate-200']"
                >
                    Listos para Entrega
                </button>
            </div>

            <!-- Content -->
            <div v-if="loading" class="py-12 flex justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            </div>

            <div v-else-if="filteredPedidos.length === 0" class="py-20 text-center bg-slate-800/20 rounded-2xl border border-dashed border-slate-700">
                <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-300">No hay pedidos en este estado</h3>
                <p class="mt-1 text-sm text-slate-500">Los nuevos pedidos aparecerán automáticamente aquí.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="pedido in filteredPedidos" 
                    :key="pedido.id"
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl border border-slate-700 shadow-xl overflow-hidden hover:border-slate-500 transition-all duration-300"
                >
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="text-xs font-mono text-blue-400 bg-blue-400/10 px-2 py-1 rounded">{{ pedido.numero_folio }}</span>
                                <h4 class="text-lg font-bold text-white mt-2">{{ pedido.usuario.nombre }} {{ pedido.usuario.apellido }}</h4>
                            </div>
                            <span class="text-xs text-slate-500">{{ formatFecha(pedido.created_at) }}</span>
                        </div>
                        
                        <div class="space-y-2 mb-6">
                            <p class="text-sm text-slate-300 line-clamp-2">{{ pedido.descripcion || 'Sin descripción' }}</p>
                            <div v-if="pedido.notas" class="text-xs p-2 bg-yellow-500/10 border border-yellow-500/20 rounded text-yellow-200">
                                <strong>Nota:</strong> {{ pedido.notas }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-700">
                            <span class="text-lg font-bold text-white">{{ formatCurrency(pedido.total) }}</span>
                            
                            <div class="flex gap-2">
                                <!-- Actions based on state -->
                                <button 
                                    v-if="pedido.estado === 'creado'"
                                    @click="cambiarEstado(pedido, 'aceptado')"
                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-semibold transition-colors"
                                >
                                    Aceptar
                                </button>
                                
                                <button 
                                    v-if="pedido.estado === 'aceptado'"
                                    @click="cambiarEstado(pedido, 'en_proceso')"
                                    class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg text-sm font-semibold transition-colors"
                                >
                                    Empezar
                                </button>

                                <button 
                                    v-if="pedido.estado === 'en_proceso'"
                                    @click="cambiarEstado(pedido, 'listo')"
                                    class="px-3 py-1.5 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm font-semibold transition-colors"
                                >
                                    Terminar
                                </button>

                                <button 
                                    v-if="pedido.estado === 'listo'"
                                    @click="confirmarEntrega(pedido)"
                                    class="px-3 py-1.5 bg-blue-500 hover:bg-blue-400 text-white rounded-lg text-sm font-semibold transition-colors flex items-center"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Entregar
                                </button>

                                <button 
                                    v-if="pedido.estado === 'creado'"
                                    @click="cambiarEstado(pedido, 'cancelado')"
                                    class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 rounded-lg text-sm font-semibold transition-colors"
                                >
                                    Rechazar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Order Modal -->
            <div v-if="showOrderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                <div class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-white">Crear Nuevo Pedido</h2>
                        <button @click="showOrderModal = false" class="text-slate-500 hover:text-white">&times;</button>
                    </div>
                    
                    <form @submit.prevent="submitOrder" class="space-y-4">
                        <div class="relative">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Cliente (Buscar por nombre o email)</label>
                            <input v-model="userSearch" type="text" placeholder="Escribe para buscar..." class="w-full bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-blue-500"/>
                            
                            <div v-if="usersFound.length" class="absolute z-10 w-full mt-1 bg-slate-800 border border-slate-700 rounded-xl overflow-hidden shadow-2xl max-h-40 overflow-y-auto">
                                <button v-for="u in usersFound" :key="u.id" type="button" @click="selectUser(u)" class="w-full px-4 py-2 text-left hover:bg-slate-700 text-sm text-white border-b border-slate-700 last:border-0 transition-colors">
                                    {{ u.nombre }} {{ u.apellido }} <span class="text-slate-500">({{ u.email }})</span>
                                </button>
                            </div>
                            <p v-if="selectedUser" class="mt-2 text-xs text-green-400 font-bold">Seleccionado: {{ selectedUser.nombre }} {{ selectedUser.apellido }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Descripción del Pedido</label>
                            <textarea v-model="orderForm.descripcion" rows="2" class="w-full bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-blue-500" placeholder="Ej: 2 Cafés, 1 Torta..." required></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Total ($)</label>
                                <input v-model="orderForm.total" type="number" step="0.01" class="w-full bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-blue-500" required/>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Notas</label>
                                <input v-model="orderForm.notas" type="text" class="w-full bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-blue-500" placeholder="Ej: Sin cebolla"/>
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="showOrderModal = false" class="flex-1 px-4 py-2 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 transition-colors">Cancelar</button>
                            <button type="submit" :disabled="orderForm.processing || !selectedUser" class="flex-1 px-4 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-500 transition-colors disabled:opacity-50">
                                {{ orderForm.processing ? 'Creando...' : 'Confirmar Pedido' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
