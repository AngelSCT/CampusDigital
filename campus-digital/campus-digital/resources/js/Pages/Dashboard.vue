<template>
    <AuthLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard - Módulo de Usuarios y Seguridad</h1>
                <p class="mt-1 text-sm text-gray-600">Indicadores clave del sistema</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Usuarios Activos -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Usuarios Activos</dt>
                                    <dd class="text-3xl font-semibold text-gray-900">{{ stats.usuarios_activos }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Intentos Exitosos -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Accesos Exitosos (7d)</dt>
                                    <dd class="text-3xl font-semibold text-gray-900">{{ accesosExitosos }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Intentos Fallidos -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Accesos Fallidos (7d)</dt>
                                    <dd class="text-3xl font-semibold text-gray-900">{{ accesosFallidos }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usuarios por Rol -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Usuarios por Rol</h3>
                    <div class="mt-5">
                        <div class="space-y-3">
                            <div v-for="rol in stats.usuarios_por_rol" :key="rol.nombre" class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 capitalize">{{ rol.nombre }}</span>
                                <span class="text-sm text-gray-500">{{ rol.total }} usuario(s)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actividad Reciente -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Actividad Reciente</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="actividad in stats.actividad_reciente" :key="actividad.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ actividad.email_intentado || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ actividad.evento }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="actividad.exito ? 'bg-success' : 'bg-error'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full text-white">
                                            {{ actividad.exito ? 'Éxito' : 'Fallido' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(actividad.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Components/AuthLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
});

const accesosExitosos = computed(() => {
    const exitoso = props.stats.intentos_acceso.find(i => i.exito === true);
    return exitoso ? exitoso.total : 0;
});

const accesosFallidos = computed(() => {
    const fallido = props.stats.intentos_acceso.find(i => i.exito === false);
    return fallido ? fallido.total : 0;
});

const formatDate = (date) => {
    return new Date(date).toLocaleString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>