<template>
    <AuthLayout>
        <div class="space-y-6">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a :href="route('admin.permisos.index')"
                        class="p-2 rounded-lg border border-slate-600 text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="px-2 py-0.5 text-xs font-bold rounded bg-blue-500/20 text-blue-400 border border-blue-500/30 uppercase tracking-widest font-mono">
                                {{ stats.modulo }}
                            </span>
                            <h1 class="text-2xl font-bold font-mono text-white tracking-tight">
                                {{ permiso.clave }}
                            </h1>
                            <span v-if="permiso.activo"
                                class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                                Activo
                            </span>
                            <span v-else
                                class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-500/20 text-gray-400 border border-gray-500/30">
                                Inactivo
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-400">{{ permiso.descripcion || 'Sin descripción' }}</p>
                    </div>
                </div>
                <a :href="route('admin.permisos.edit', permiso.id)"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-lg shadow-blue-500/30 text-sm font-medium rounded-lg text-white bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar permiso
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="stat in statCards" :key="stat.label"
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 shadow-xl shadow-blue-500/10">
                    <div class="flex items-center gap-3 mb-2">
                        <div :class="['p-2 rounded-lg', stat.iconBg]">
                            <svg class="w-4 h-4" :class="stat.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-slate-400 uppercase tracking-wider leading-tight">{{ stat.label }}</span>
                    </div>
                    <p :class="['text-2xl font-bold', stat.valueColor]">{{ stat.value }}</p>
                    <p v-if="stat.sub" class="text-xs text-slate-500 mt-0.5">{{ stat.sub }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-700 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Roles que incluyen este permiso
                        </h2>
                        <span class="text-xs text-slate-400">{{ permiso.roles.length }} rol(es)</span>
                    </div>

                    <div v-if="permiso.roles.length > 0" class="divide-y divide-slate-700/50">
                        <div v-for="rol in permiso.roles" :key="rol.id"
                            class="px-5 py-4 flex items-center justify-between hover:bg-slate-700/20 transition-colors duration-150">
                            <div class="flex items-center gap-3">
                                <!-- Icono de rol -->
                                <div class="w-9 h-9 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white capitalize">{{ rol.nombre }}</p>
                                    <p class="text-xs text-slate-400 truncate max-w-xs">{{ rol.descripcion || 'Sin descripción' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 ml-4">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                    {{ rol.usuarios_count }} usuario(s)
                                </span>
                                <span v-if="rol.activo"
                                    class="px-2 py-0.5 text-xs rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                                    Activo
                                </span>
                                <span v-else
                                    class="px-2 py-0.5 text-xs rounded-full bg-gray-500/20 text-gray-400 border border-gray-500/30">
                                    Inactivo
                                </span>
                                <a :href="route('admin.roles.show', rol.id)"
                                    class="text-slate-500 hover:text-blue-400 transition-colors duration-150" title="Ver rol">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-12 text-center">
                        <svg class="w-10 h-10 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <p class="text-sm text-slate-500">Ningún rol tiene asignado este permiso.</p>
                    </div>
                </div>

                <div class="space-y-4">

                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5">
                        <h2 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Información
                        </h2>
                        <dl class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-400">ID</dt>
                                <dd class="text-white font-mono">#{{ permiso.id }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-400">Módulo</dt>
                                <dd class="font-mono text-blue-400 uppercase text-xs font-bold">{{ stats.modulo }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-400">Creado</dt>
                                <dd class="text-white">{{ formatDate(permiso.created_at) }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-400">Actualizado</dt>
                                <dd class="text-white">{{ formatDate(permiso.updated_at) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div v-if="permisosHermanos.length > 0"
                        class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5">
                        <h2 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Otros permisos de
                            <span class="font-mono text-blue-400 uppercase text-xs">{{ stats.modulo }}</span>
                        </h2>
                        <div class="space-y-2">
                            <div v-for="p in permisosHermanos" :key="p.id"
                                class="flex items-center justify-between gap-2">
                                <a :href="route('admin.permisos.show', p.id)"
                                    class="text-xs font-mono text-slate-300 hover:text-blue-400 transition-colors truncate">
                                    {{ p.clave }}
                                </a>
                                <svg class="w-3 h-3 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div v-if="actividadReciente.length > 0"
                class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-700">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Actividad reciente relacionada
                    </h2>
                </div>
                <div class="divide-y divide-slate-700/50">
                    <div v-for="(a, i) in actividadReciente" :key="i"
                        class="px-5 py-3 flex items-center gap-4 hover:bg-slate-700/20 transition-colors duration-150">
                        <!-- Éxito/error indicator -->
                        <div :class="['w-2 h-2 rounded-full shrink-0', a.exito ? 'bg-green-400' : 'bg-red-400']"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white truncate">
                                <span class="font-medium">{{ a.nombre }} {{ a.apellido }}</span>
                                <span class="text-slate-400"> · </span>
                                <span class="text-slate-300 font-mono text-xs">{{ a.accion }}</span>
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ a.modulo }}
                                <span v-if="a.ip"> · {{ a.ip }}</span>
                            </p>
                        </div>
                        <span class="text-xs text-slate-500 shrink-0">{{ formatDate(a.created_at) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    permiso: Object,
    stats: Object,
    permisosHermanos: Array,
    actividadReciente: Array,
});

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
}

const statCards = [
    {
        label: 'Roles asignados',
        value: props.stats.total_roles,
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        iconBg: 'bg-blue-500/20',
        iconColor: 'text-blue-400',
        valueColor: 'text-white',
    },
    {
        label: 'Roles activos',
        value: props.stats.roles_activos,
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        iconBg: 'bg-green-500/20',
        iconColor: 'text-green-400',
        valueColor: 'text-green-400',
    },
    {
        label: 'Usuarios con acceso',
        value: props.stats.usuarios_con_acceso,
        sub: 'vía cualquier rol',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        iconBg: 'bg-amber-500/20',
        iconColor: 'text-amber-400',
        valueColor: 'text-white',
    },
    {
        label: 'Módulo',
        value: props.stats.modulo,
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        iconBg: 'bg-purple-500/20',
        iconColor: 'text-purple-400',
        valueColor: 'text-purple-400 uppercase text-lg font-mono',
    },
];
</script>