<template>
    <AuthLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900">
                        Reporte de Usuarios
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Consulta y exporta información detallada de usuarios registrados.
                    </p>
                </div>

                <!-- Filtros y Exportar -->
                <div class="bg-white shadow rounded-lg mb-6 p-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                            <select 
                                v-model="form.rol" 
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                @change="filtrar"
                            >
                                <option value="">Todos los roles</option>
                                <option value="estudiante">Estudiante</option>
                                <option value="proveedor_area">Proveedor/Área</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                            <input 
                                v-model="form.fecha_desde" 
                                type="date" 
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                @change="filtrar"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                            <input 
                                v-model="form.fecha_hasta" 
                                type="date" 
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                @change="filtrar"
                            >
                        </div>
                        <div class="flex items-end">
                            <button 
                                @click="exportarCSV"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Exportar CSV
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="usuario in usuarios" :key="usuario.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ usuario.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ usuario.nombre }} {{ usuario.apellido }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ usuario.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ usuario.telefono || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            v-for="rol in usuario.roles" 
                                            :key="rol.id"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 mr-1"
                                        >
                                            {{ rol.nombre }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="usuario.bloqueado ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        >
                                            {{ usuario.bloqueado ? 'Bloqueado' : 'Activo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(usuario.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Total -->
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                        <p class="text-sm text-gray-700">
                            Total de usuarios: <span class="font-semibold">{{ usuarios.length }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Components/AuthLayout.vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    usuarios: Array,
    filters: Object,
});

const form = reactive({
    rol: props.filters.rol || '',
    fecha_desde: props.filters.fecha_desde || '',
    fecha_hasta: props.filters.fecha_hasta || '',
});

const filtrar = () => {
    router.get(route('admin.reportes.usuarios'), form, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportarCSV = () => {
    const params = new URLSearchParams({
        ...form,
        export: 'csv'
    });
    window.location.href = route('admin.reportes.usuarios') + '?' + params.toString();
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>