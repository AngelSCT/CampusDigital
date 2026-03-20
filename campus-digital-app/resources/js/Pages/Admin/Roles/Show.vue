<template>
    <AuthLayout>
        <div class="space-y-6">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a :href="route('admin.roles.index')"
                        class="p-2 rounded-lg border border-slate-600 text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent capitalize">
                                {{ rol.nombre }}
                            </h1>
                            <span v-if="rol.activo"
                                class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                                Activo
                            </span>
                            <span v-else
                                class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-500/20 text-gray-400 border border-gray-500/30">
                                Inactivo
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm text-slate-400">{{ rol.descripcion || 'Sin descripción' }}</p>
                    </div>
                </div>
                <a v-if="!isSystemRole(rol.nombre)" :href="route('admin.roles.edit', rol.id)"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-lg shadow-blue-500/30 text-sm font-medium rounded-lg text-white bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar rol
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="stat in statCards" :key="stat.label"
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 shadow-xl shadow-blue-500/10">
                    <div class="flex items-center gap-3 mb-2">
                        <div :class="['p-2 rounded-lg', stat.iconBg]">
                            <component :is="'svg'" class="w-4 h-4" :class="stat.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon"/>
                            </component>
                        </div>
                        <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">{{ stat.label }}</span>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Usuarios con este rol
                        </h2>
                        <span class="text-xs text-slate-400">{{ rol.usuarios.length }} en total</span>
                    </div>

                    <div v-if="rol.usuarios.length > 0" class="divide-y divide-slate-700/50 max-h-96 overflow-y-auto">
                        <div v-for="usuario in rol.usuarios" :key="usuario.id"
                            class="px-5 py-3 flex items-center justify-between hover:bg-slate-700/30 transition-colors duration-150">
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Avatar -->
                                <div class="relative shrink-0">
                                    <img v-if="usuario.foto_url" :src="`/storage/${usuario.foto_url}`"
                                        class="w-9 h-9 rounded-full object-cover border border-slate-600"/>
                                    <div v-else
                                        class="w-9 h-9 rounded-full bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-blue-400 text-xs font-bold">
                                        {{ initials(usuario) }}
                                    </div>
                                    <span v-if="usuario.bloqueado"
                                        class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-red-500 rounded-full border border-slate-800"></span>
                                    <span v-else
                                        class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full border border-slate-800"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-white truncate">
                                        {{ usuario.nombre }} {{ usuario.apellido }}
                                    </p>
                                    <p class="text-xs text-slate-400 truncate">{{ usuario.email }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0 ml-3">
                                <p v-if="usuario.ultimo_login_at" class="text-xs text-slate-400">
                                    Último acceso
                                </p>
                                <p v-if="usuario.ultimo_login_at" class="text-xs text-slate-500">
                                    {{ formatDate(usuario.ultimo_login_at) }}
                                </p>
                                <p v-else class="text-xs text-slate-600">Sin accesos</p>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    Asignado {{ formatDate(usuario.pivot.asignado_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-12 text-center">
                        <svg class="w-10 h-10 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm text-slate-500">Ningún usuario tiene este rol asignado.</p>
                    </div>
                </div>

                <div class="space-y-4">

                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5">
                        <h2 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Información del rol
                        </h2>
                        <dl class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-400">ID</dt>
                                <dd class="text-white font-mono">#{{ rol.id }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-400">Creado</dt>
                                <dd class="text-white">{{ formatDate(rol.created_at) }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-400">Actualizado</dt>
                                <dd class="text-white">{{ formatDate(rol.updated_at) }}</dd>
                            </div>
                            <div v-if="stats.ultima_asignacion" class="flex justify-between text-sm">
                                <dt class="text-slate-400">Última asignación</dt>
                                <dd class="text-white">{{ formatDate(stats.ultima_asignacion) }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-400">Es sistema</dt>
                                <dd>
                                    <span v-if="isSystemRole(rol.nombre)" class="text-amber-400">Sí</span>
                                    <span v-else class="text-slate-400">No</span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div v-if="asignadores.length > 0"
                        class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5">
                        <h2 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Top asignadores
                        </h2>
                        <div class="space-y-2">
                            <div v-for="a in asignadores" :key="a.id" class="flex items-center justify-between text-sm">
                                <span class="text-slate-300 truncate">{{ a.nombre }} {{ a.apellido }}</span>
                                <span class="ml-2 shrink-0 px-2 py-0.5 text-xs rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                    {{ a.veces }} asig.
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-700">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Permisos asignados
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30">
                            {{ rol.permisos.length }}
                        </span>
                    </h2>
                </div>

                <div v-if="Object.keys(permisosPorModulo).length > 0" class="p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div v-for="(permisos, modulo) in permisosPorModulo" :key="modulo"
                        class="bg-slate-900/60 rounded-lg border border-slate-700 p-4">
                        <h3 class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400 inline-block"></span>
                            {{ modulo }}
                        </h3>
                        <div class="space-y-1.5">
                            <div v-for="p in permisos" :key="p.id" class="flex items-start gap-2">
                                <svg class="w-3.5 h-3.5 text-green-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-mono text-slate-200">{{ p.clave }}</p>
                                    <p v-if="p.descripcion" class="text-xs text-slate-500 leading-tight">{{ p.descripcion }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="py-10 text-center">
                    <p class="text-sm text-slate-500">Este rol no tiene permisos asignados.</p>
                </div>
            </div>

            <div v-if="actividadReciente.length > 0"
                class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-700">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Actividad reciente de usuarios con este rol
                    </h2>
                </div>
                <div class="divide-y divide-slate-700/50">
                    <div v-for="(actividad, i) in actividadReciente" :key="i"
                        class="px-5 py-3 flex items-center gap-4 hover:bg-slate-700/20 transition-colors duration-150">
                        <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white truncate">
                                <span class="font-medium">{{ actividad.nombre }} {{ actividad.apellido }}</span>
                                <span class="text-slate-400"> · </span>
                                <span class="text-slate-300">{{ actividad.accion }}</span>
                            </p>
                            <p class="text-xs text-slate-500 truncate">
                                <span class="capitalize">{{ actividad.modulo }}</span>
                            </p>
                        </div>
                        <span class="text-xs text-slate-500 shrink-0">{{ formatDate(actividad.created_at) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    rol: Object,
    stats: Object,
    permisosPorModulo: Object,
    asignadores: Array,
    actividadReciente: Array,
});

const SYSTEM_ROLES = ['administrador', 'docente', 'alumno'];

function isSystemRole(nombre) {
    return SYSTEM_ROLES.includes(nombre?.toLowerCase());
}

function initials(usuario) {
    return ((usuario.nombre?.[0] ?? '') + (usuario.apellido?.[0] ?? '')).toUpperCase();
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
}

const statCards = [
    {
        label: 'Total usuarios',
        value: props.stats.total_usuarios,
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        iconBg: 'bg-blue-500/20',
        iconColor: 'text-blue-400',
        valueColor: 'text-white',
    },
    {
        label: 'Activos',
        value: props.stats.usuarios_activos,
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        iconBg: 'bg-green-500/20',
        iconColor: 'text-green-400',
        valueColor: 'text-green-400',
    },
    {
        label: 'Bloqueados',
        value: props.stats.usuarios_bloqueados,
        icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
        iconBg: 'bg-red-500/20',
        iconColor: 'text-red-400',
        valueColor: props.stats.usuarios_bloqueados > 0 ? 'text-red-400' : 'text-white',
    },
    {
        label: 'Permisos',
        value: props.stats.total_permisos,
        icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        iconBg: 'bg-amber-500/20',
        iconColor: 'text-amber-400',
        valueColor: 'text-white',
    },
];
</script>