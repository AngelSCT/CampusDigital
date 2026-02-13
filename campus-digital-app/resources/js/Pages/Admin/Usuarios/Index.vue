<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Gestión de Usuarios
                    </h1>
                    <p class="mt-1 text-sm text-white">Administra los usuarios del sistema</p>
                </div>
                <div class="flex gap-3">
                <div class="relative" v-click-away="() => showExportMenu = false">
                    <button @click="toggleExportMenu" type="button" class="inline-flex items-center px-4 py-2 border border-slate-600 shadow-sm text-sm font-medium rounded-lg text-white bg-gradient-to-br from-slate-700 to-slate-800 hover:from-slate-600 hover:to-slate-700 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
<!-- Menú desplegable -->
<div v-if="showExportMenu" class="absolute right-0 mt-2 w-64 rounded-lg shadow-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-blue-500/20 z-10">
    <div class="py-1">
        <!-- Exportar todos - CSV -->
        <a :href="route('admin.usuarios.export')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200">
            <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <div>
                <div class="font-medium">Todos los usuarios (CSV)</div>
                <div class="text-xs text-slate-400">Exportar lista completa</div>
            </div>
        </a>

            <!-- Exportar todos - PDF -->
            <a :href="route('admin.usuarios.export-pdf')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200 border-t border-slate-700">
                <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <div>
                    <div class="font-medium">Todos los usuarios (PDF)</div>
                    <div class="text-xs text-slate-400">Documento completo</div>
                </div>
            </a>
            
            <!-- Exportar por rol - CSV -->
            <a :href="route('admin.usuarios.export-by-role')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200 border-t border-slate-700">
                <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <div>
                    <div class="font-medium">Reporte por roles (CSV)</div>
                    <div class="text-xs text-slate-400">Usuarios agrupados</div>
                </div>
            </a>

            <!-- Exportar por rol - PDF -->
            <a :href="route('admin.usuarios.export-by-role-pdf')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200 border-t border-slate-700">
                <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <div>
                    <div class="font-medium">Reporte por roles (PDF)</div>
                    <div class="text-xs text-slate-400">Documento profesional</div>
                </div>
            </a>
        </div>
    </div>
                </div>
                    <a :href="route('admin.usuarios.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-lg shadow-blue-500/30 text-sm font-medium rounded-lg text-white bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Usuario
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-4">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Buscar</label>
                        <input 
                            v-model="filterForm.search" 
                            type="text" 
                            placeholder="Nombre, email..." 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Rol</label>
                        <select 
                            v-model="filterForm.rol" 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todos</option>
                            <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Estado</label>
                        <select 
                            v-model="filterForm.estado" 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>

                    <!-- Email Verificado -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Email Verificado</label>
                        <select 
                            v-model="filterForm.verificado" 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todos</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="md:col-span-4 flex justify-end gap-2">
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
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla con scroll horizontal -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th @click="sortBy('id')" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap hover:text-blue-400 transition-colors duration-200">
                                    ID
                                    <span v-if="filters.sort === 'id'" class="text-blue-400">{{ filters.direction === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th @click="sortBy('nombre')" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap hover:text-blue-400 transition-colors duration-200">
                                    Usuario
                                    <span v-if="filters.sort === 'nombre'" class="text-blue-400">{{ filters.direction === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">Roles</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">Estado</th>
                                <th @click="sortBy('created_at')" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap hover:text-blue-400 transition-colors duration-200">
                                    Fecha Creación
                                    <span v-if="filters.sort === 'created_at'" class="text-blue-400">{{ filters.direction === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-slate-800/30 divide-y divide-slate-700">
                            <tr v-for="usuario in usuarios.data" :key="usuario.id" class="hover:bg-slate-700/30 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ usuario.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center min-w-[200px]">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full ring-2 ring-slate-600" :src="usuario.foto_url || '/default-avatar.png'" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-white">{{ usuario.nombre }} {{ usuario.apellido }}</div>
                                            <div class="text-sm text-slate-400">{{ usuario.telefono }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="min-w-[200px]">
                                        <div class="text-sm text-white">{{ usuario.email }}</div>
                                        <span v-if="usuario.email_verificado" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Verificado
                                        </span>
                                        <span v-else class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                            Pendiente
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="min-w-[150px]">
                                        <span v-for="rol in usuario.roles" :key="rol.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30 mr-1 mb-1">
                                            {{ rol.nombre }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="!usuario.bloqueado" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                                        Activo
                                    </span>
                                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-500/20 text-red-400 border border-red-500/30">
                                        Bloqueado
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                    {{ formatDate(usuario.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2 min-w-[120px]">
                                        <a :href="route('admin.usuarios.edit', usuario.id)" class="text-blue-400 hover:text-blue-300 transition-colors duration-200" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button @click="toggleBlock(usuario)" :class="usuario.bloqueado ? 'text-green-400 hover:text-green-300' : 'text-yellow-400 hover:text-yellow-300'" :title="usuario.bloqueado ? 'Desbloquear' : 'Bloquear'" class="transition-colors duration-200">
                                            <svg v-if="usuario.bloqueado" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </button>
                                        <button @click="confirmDelete(usuario)" class="text-red-400 hover:text-red-300 transition-colors duration-200" title="Eliminar">
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
                <div v-if="usuarios.data.length > 0" class="bg-slate-900/50 px-4 py-3 border-t border-slate-700 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <a v-if="usuarios.prev_page_url" :href="usuarios.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-lg text-white bg-slate-700 hover:bg-slate-600 transition-colors duration-200">
                                Anterior
                            </a>
                            <a v-if="usuarios.next_page_url" :href="usuarios.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-lg text-white bg-slate-700 hover:bg-slate-600 transition-colors duration-200">
                                Siguiente
                            </a>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-slate-300">
                                    Mostrando <span class="font-medium text-white">{{ usuarios.from }}</span> a <span class="font-medium text-white">{{ usuarios.to }}</span> de <span class="font-medium text-white">{{ usuarios.total }}</span> resultados
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px">
                                    <a v-for="link in usuarios.links" :key="link.label" :href="link.url" v-html="link.label" :class="[
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
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    usuarios: Object,
    roles: Array,
    filters: Object,
});

const filterForm = reactive({
    search: props.filters.search || '',
    rol: props.filters.rol || '',
    estado: props.filters.estado || '',
    verificado: props.filters.verificado || '',
});

const showExportMenu = ref(false);

function toggleExportMenu() {
    showExportMenu.value = !showExportMenu.value;
}

function applyFilters() {
    router.get(route('admin.usuarios.index'), filterForm, { preserveState: true });
}

function clearFilters() {
    filterForm.search = '';
    filterForm.rol = '';
    filterForm.estado = '';
    filterForm.verificado = '';
    router.get(route('admin.usuarios.index'), {}, { preserveState: true });
}

function sortBy(field) {
    const direction = props.filters.sort === field && props.filters.direction === 'asc' ? 'desc' : 'asc';
    router.get(route('admin.usuarios.index'), { ...filterForm, sort: field, direction }, { preserveState: true });
}

function toggleBlock(usuario) {
    if (confirm(`¿Estás seguro de ${usuario.bloqueado ? 'desbloquear' : 'bloquear'} a ${usuario.email}?`)) {
        router.post(route('admin.usuarios.toggle-block', usuario.id), {}, {
            preserveScroll: true,
        });
    }
}

function confirmDelete(usuario) {
    if (confirm(`¿Estás seguro de eliminar a ${usuario.email}? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.usuarios.destroy', usuario.id), {
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