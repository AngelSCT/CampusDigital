<template>
    <Head title="Admin — Recursos" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
                        Recursos Reservables
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Administra salas, laboratorios y equipos</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-700 text-white text-sm font-medium rounded-lg">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Recurso
                </button>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-4">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Buscar</label>
                        <input v-model="filterForm.search" type="text" placeholder="Nombre..."
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Tipo</label>
                        <select v-model="filterForm.tipo" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm">
                            <option value="">Todos</option>
                            <option value="sala">Salas</option>
                            <option value="laboratorio">Laboratorios</option>
                            <option value="equipo">Equipos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Estado</label>
                        <select v-model="filterForm.estado" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm">
                            <option value="">Todos</option>
                            <option value="disponible">Disponible</option>
                            <option value="mantenimiento">Mantenimiento</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase">
                        <tr>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Cap.</th>
                            <th class="px-4 py-3">Costo</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="r in recursos.data" :key="r.id_recurso" class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 text-white">{{ r.nombre }}</td>
                            <td class="px-4 py-3 text-slate-300 capitalize">{{ r.tipo }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ r.capacidad }}</td>
                            <td class="px-4 py-3 text-cyan-300">{{ r.costo_por_hora > 0 ? `$${r.costo_por_hora}/h` : 'Gratis' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-green-500/20 text-green-300':   r.estado === 'disponible',
                                        'bg-yellow-500/20 text-yellow-300': r.estado === 'mantenimiento',
                                        'bg-slate-500/20 text-slate-300':   r.estado === 'inactivo',
                                    }">
                                    {{ r.estado }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a :href="`/admin/reservas/recursos/${r.id_recurso}`" class="text-cyan-400 hover:text-cyan-300 text-sm">Ver</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="!recursos.data || recursos.data.length === 0" class="text-center py-8 text-slate-400">
                    No hay recursos registrados.
                </div>
            </div>

            <nav v-if="recursos.links" class="flex justify-center gap-1">
                <a v-for="link in recursos.links" :key="link.label" :href="link.url || '#'"
                    :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-cyan-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700']"
                    v-html="link.label" />
            </nav>

            <!-- Modal crear -->
            <div v-if="showModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4" @click.self="showModal = false">
                <div class="bg-slate-900 border border-cyan-500/30 rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Nuevo Recurso</h2>
                    <form @submit.prevent="store" class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-slate-300 mb-1">Nombre</label>
                                <input v-model="form.nombre" type="text" required maxlength="150"
                                    class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-slate-300 mb-1">Descripción</label>
                                <textarea v-model="form.descripcion" rows="2"
                                    class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-300 mb-1">Tipo</label>
                                <select v-model="form.tipo" required class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm">
                                    <option value="sala">Sala</option>
                                    <option value="laboratorio">Laboratorio</option>
                                    <option value="equipo">Equipo</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-300 mb-1">Capacidad</label>
                                <input v-model.number="form.capacidad" type="number" min="1" required
                                    class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-300 mb-1">Costo por hora</label>
                                <input v-model.number="form.costo_por_hora" type="number" min="0" step="0.01"
                                    class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-300 mb-1">Estado</label>
                                <select v-model="form.estado" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm">
                                    <option value="disponible">Disponible</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 pt-3">
                            <button type="submit" :disabled="form.processing"
                                class="px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-700 text-white text-sm rounded-lg disabled:opacity-50">
                                {{ form.processing ? 'Creando...' : 'Crear recurso' }}
                            </button>
                            <button type="button" @click="showModal = false"
                                class="px-4 py-2 bg-slate-700 text-white text-sm rounded-lg">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    recursos: Object,
    filters:  Object,
});

const showModal = ref(false);
const filterForm = useForm({
    search: props.filters?.search ?? '',
    tipo:   props.filters?.tipo   ?? '',
    estado: props.filters?.estado ?? '',
});

const form = useForm({
    nombre:         '',
    descripcion:    '',
    tipo:           'sala',
    capacidad:      1,
    estado:         'disponible',
    costo_por_hora: 0,
});

const openCreate = () => {
    form.reset();
    showModal.value = true;
};

const applyFilters = () => filterForm.get('/admin/reservas/recursos', { preserveState: true });

const store = () => {
    form.post('/admin/reservas/recursos', {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; form.reset(); },
    });
};
</script>
