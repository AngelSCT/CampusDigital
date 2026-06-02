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

            <!-- Módulos del Sistema -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Módulos del Sistema</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                    <!-- Mantenimiento y Tickets -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100 hover:shadow-md transition-shadow duration-200">
                        <div class="p-5">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Mantenimiento y Tickets</h3>
                                    <p class="text-sm text-gray-500">Gestión de soporte técnico y equipos</p>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a :href="route('admin.tickets.index')"
                                   class="flex items-center gap-1.5 px-3 py-2 rounded-md text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors duration-150">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                    Tickets
                                </a>
                                <a :href="route('admin.asignaciones-tecnicas.index')"
                                   class="flex items-center gap-1.5 px-3 py-2 rounded-md text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors duration-150">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Asignaciones
                                </a>
                                <a :href="route('admin.mantenimientos-preventivos.index')"
                                   class="flex items-center gap-1.5 px-3 py-2 rounded-md text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-150">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Mantenimientos
                                </a>
                                <a :href="route('admin.equipos-activos.index')"
                                   class="flex items-center gap-1.5 px-3 py-2 rounded-md text-xs font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 transition-colors duration-150">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Equipos
                                </a>
                                <a :href="route('admin.categorias-ticket.index')"
                                   class="col-span-2 flex items-center gap-1.5 px-3 py-2 rounded-md text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors duration-150">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/>
                                    </svg>
                                    Categorías de Ticket
                                </a>
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