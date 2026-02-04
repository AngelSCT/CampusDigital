<template>
    <AuthLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900">
                        Reporte de Accesos
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Historial de intentos de acceso al sistema (exitosos y fallidos).
                    </p>
                </div>

                <!-- Filtros y Exportar -->
                <div class="bg-white shadow rounded-lg mb-6 p-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                            <select 
                                v-model="form.evento" 
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                @change="filtrar"
                            >
                                <option value="">Todos</option>
                                <option value="login">Login</option>
                                <option value="login_failed">Login Fallido</option>
                                <option value="logout">Logout</option>
                                <option value="registro">Registro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Resultado</label>
                            <select 
                                v-model="form.exito" 
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                @change="filtrar"
                            >
                                <option value="">Todos</option>
                                <option value="true">Exitoso</option>
                                <option value="false">Fallido</option>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email Intentado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resultado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="acceso in accesos.data" :key="acceso.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ acceso.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ acceso.usuario ? `${acceso.usuario.nombre} ${acceso.usuario.apellido}` : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ acceso.email_intentado }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="capitalize">{{ acceso.evento.replace('_', ' ') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="acceso.exito ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        >
                                            {{ acceso.exito ? 'Éxito' : 'Fallido' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ acceso.ip || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDateTime(acceso.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="accesos.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Mostrando {{ accesos.from }} a {{ accesos.to }} de {{ accesos.total }} resultados
                            </div>
                            <div class="flex space-x-2">
                                <a 
                                    v-for="(link, index) in accesos.links" 
                                    :key="index"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 text-sm rounded',
                                        link.active 
                                            ? 'bg-blue-600 text-white' 
                                            : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                    ]"
                                    :disabled="!link.url"
                                ></a>
                            </div>
                        </div>
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
    accesos: Object,
    filters: Object,
});

const form = reactive({
    evento: props.filters.evento || '',
    exito: props.filters.exito || '',
    fecha_desde: props.filters.fecha_desde || '',
    fecha_hasta: props.filters.fecha_hasta || '',
});

const filtrar = () => {
    router.get(route('admin.reportes.accesos'), form, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportarCSV = () => {
    const params = new URLSearchParams({
        ...form,
        export: 'csv'
    });
    window.location.href = route('admin.reportes.accesos') + '?' + params.toString();
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>