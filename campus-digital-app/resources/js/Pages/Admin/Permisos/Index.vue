<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Gestión de Permisos
                    </h1>
                    <p class="mt-1 text-sm text-white">Administra los permisos del sistema</p>
                </div>
                <a :href="route('admin.permisos.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-lg shadow-blue-500/30 text-sm font-medium rounded-lg text-white bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Permiso
                </a>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-4">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <!-- Búsqueda -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-white mb-2">Buscar</label>
                        <input 
                            v-model="filterForm.search" 
                            type="text" 
                            placeholder="Clave, descripción..." 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                    </div>

                    <!-- Botones -->
                    <div class="flex items-end gap-2">
                        <button 
                            type="button" 
                            @click="clearFilters" 
                            class="px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200"
                        >
                            Limpiar
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg text-sm font-medium text-white hover:from-blue-500 hover:to-blue-600 shadow-lg shadow-blue-500/30 transition-all duration-200"
                        >
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">Clave</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">Fecha Creación</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-slate-800/30 divide-y divide-slate-700">
                            <tr v-for="permiso in permisos.data" :key="permiso.id" class="hover:bg-slate-700/30 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ permiso.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-white">{{ permiso.clave }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-300 max-w-md truncate">{{ permiso.descripcion || 'Sin descripción' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="permiso.activo" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                                        Activo
                                    </span>
                                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-500/20 text-gray-400 border border-gray-500/30">
                                        Inactivo
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                    {{ formatDate(permiso.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2 min-w-[100px]">
                                        <a :href="route('admin.permisos.edit', permiso.id)" class="text-blue-400 hover:text-blue-300 transition-colors duration-200" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button @click="confirmDelete(permiso)" class="text-red-400 hover:text-red-300 transition-colors duration-200" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="permisos.data.length > 0" class="bg-slate-900/50 px-4 py-3 border-t border-slate-700 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <a v-if="permisos.prev_page_url" :href="permisos.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-lg text-white bg-slate-700 hover:bg-slate-600 transition-colors duration-200">
                                Anterior
                            </a>
                            <a v-if="permisos.next_page_url" :href="permisos.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-lg text-white bg-slate-700 hover:bg-slate-600 transition-colors duration-200">
                                Siguiente
                            </a>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-slate-300">
                                    Mostrando <span class="font-medium text-white">{{ permisos.from }}</span> a <span class="font-medium text-white">{{ permisos.to }}</span> de <span class="font-medium text-white">{{ permisos.total }}</span> resultados
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px">
                                    <a v-for="link in permisos.links" :key="link.label" :href="link.url" v-html="link.label" :class="[
                                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors duration-200',
                                        link.active 
                                            ? 'z-10 bg-gradient-to-br from-blue-600 to-blue-700 border-blue-500 text-white shadow-lg shadow-blue-500/30' 
                                            : 'bg-slate-700/50 border-slate-600 text-slate-300 hover:bg-slate-600/50'
                                    ]"></a>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="permisos.data.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-700/50 flex items-center justify-center">
                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-white">No hay permisos</h3>
                    <p class="mt-1 text-sm text-slate-400">Comienza creando un nuevo permiso.</p>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    permisos: Object,
    filters: Object,
});

const filterForm = reactive({
    search: props.filters.search || '',
});

function applyFilters() {
    router.get(route('admin.permisos.index'), filterForm, { preserveState: true });
}

function clearFilters() {
    filterForm.search = '';
    router.get(route('admin.permisos.index'), {}, { preserveState: true });
}

function confirmDelete(permiso) {
    if (confirm(`¿Estás seguro de eliminar el permiso "${permiso.clave}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.permisos.destroy', permiso.id), {
            preserveScroll: true,
        });
    }
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}
</script>