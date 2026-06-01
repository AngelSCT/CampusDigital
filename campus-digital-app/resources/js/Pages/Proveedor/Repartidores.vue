<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    repartidores: Array,
    tienda: Object,
});

const search = ref('');
const showAddModal = ref(false);
const userSearch = ref('');
const usersFound = ref([]);
const loadingSearch = ref(false);

const filteredRepartidores = computed(() => {
    if (!search.value) return props.repartidores;
    return props.repartidores.filter(r => 
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
            const res = await axios.get(route('proveedor.repartidores.search'), { params: { q: val } });
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
    router.post(route('proveedor.repartidores.asignar'), { usuario_id: usuarioId }, {
        onSuccess: () => {
            showAddModal.value = false;
            userSearch.value = '';
            usersFound.value = [];
        }
    });
};

const desvincularRepartidor = (id) => {
    if (confirm('¿Estás seguro de quitar a este repartidor de tu equipo?')) {
        router.delete(route('proveedor.repartidores.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestión de Repartidores" />
    <AuthLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white">Mi Equipo de Reparto</h1>
                    <p class="text-slate-400">Gestiona el personal que realiza las entregas de {{ tienda.nombre }}</p>
                </div>
                <div>
                    <button @click="showAddModal = true"
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-2xl transition-all shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                        </svg>
                        Vincular Repartidor
                    </button>
                </div>
            </div>

            <!-- List -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Personal Registrado</h3>
                    <div class="relative w-64">
                        <input v-model="search" type="text" placeholder="Filtrar..." 
                            class="w-full bg-slate-800 border-slate-700 rounded-xl px-4 py-2 text-sm text-white focus:ring-2 focus:ring-blue-500"/>
                    </div>
                </div>
                
                <table class="w-full text-left">
                    <thead class="bg-slate-800/30">
                        <tr>
                            <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase">Repartidor</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase">Email</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <tr v-for="rep in filteredRepartidores" :key="rep.id" class="hover:bg-slate-800/20 transition-all">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 font-bold border border-blue-500/20">
                                        {{ rep.nombre[0] }}
                                    </div>
                                    <div>
                                        <div class="text-white font-bold">{{ rep.nombre }} {{ rep.apellido }}</div>
                                        <div class="text-[10px] text-slate-500 font-bold uppercase">Miembro del equipo</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">
                                {{ rep.email }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="desvincularRepartidor(rep.id)" class="px-3 py-1.5 bg-red-500/10 text-red-500 hover:bg-red-500 text-white rounded-xl transition-all text-xs font-bold border border-red-500/20">
                                    Desvincular
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredRepartidores.length === 0">
                            <td colspan="3" class="py-20 text-center text-slate-500">
                                <div class="text-4xl mb-4 opacity-10">🚴‍♂️</div>
                                <p class="font-bold">No tienes repartidores vinculados.</p>
                                <p class="text-xs">Agrega a alguien de la comunidad para que aparezca aquí.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Add Modal -->
            <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-sm">
                <div class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-[2rem] overflow-hidden shadow-2xl">
                    <div class="p-8">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-black text-white">Vincular Repartidor</h2>
                            <button @click="showAddModal = false" class="text-slate-500 hover:text-white">✕</button>
                        </div>
                        
                        <p class="text-sm text-slate-400 mb-6">Busca a un usuario por su nombre o correo electrónico para integrarlo a tu equipo de reparto.</p>
                        
                        <div class="relative mb-6">
                            <input v-model="userSearch" type="text" placeholder="Nombre o email..." 
                                class="w-full bg-slate-800 border-slate-700 rounded-2xl p-4 text-white focus:ring-2 focus:ring-emerald-500 outline-none transition-all"/>
                            <div v-if="loadingSearch" class="absolute right-4 top-4">
                                <div class="w-5 h-5 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>

                        <div class="max-h-60 overflow-y-auto space-y-2 custom-scrollbar pr-2">
                            <div v-for="u in usersFound" :key="u.id" class="p-4 bg-slate-800/50 border border-slate-700 rounded-2xl flex justify-between items-center group hover:bg-slate-800 transition-all">
                                <div>
                                    <div class="font-bold text-white group-hover:text-emerald-400 transition-colors">{{ u.nombre }} {{ u.apellido }}</div>
                                    <div class="text-xs text-slate-500">{{ u.email }}</div>
                                </div>
                                <button @click="asignarRepartidor(u.id)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-[10px] uppercase rounded-xl transition-all">
                                    Vincular
                                </button>
                            </div>
                            <div v-if="userSearch.length >= 3 && usersFound.length === 0 && !loadingSearch" class="text-center py-8 text-slate-500 italic">
                                No se encontraron usuarios con ese criterio.
                            </div>
                        </div>

                        <button @click="showAddModal = false" class="w-full mt-8 py-4 bg-slate-800 hover:bg-slate-700 text-slate-400 font-black rounded-2xl uppercase text-[10px] transition-all">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
</style>
