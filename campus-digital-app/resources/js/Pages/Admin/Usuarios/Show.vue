<template>
    <AuthLayout>
        <div class="max-w-4xl mx-auto space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Detalle de Usuario
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Información completa del usuario</p>
                </div>
                <div class="flex gap-3">
                    <a
                        :href="route('admin.usuarios.index')"
                        class="inline-flex items-center px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver
                    </a>
                    <a
                        :href="route('admin.usuarios.edit', usuario.id)"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg text-sm font-medium text-white hover:from-blue-500 hover:to-blue-600 shadow-lg shadow-blue-500/30 transition-all duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 overflow-hidden">

                <div class="h-24 bg-gradient-to-r from-blue-600/30 to-slate-700/30 relative">
                    <div class="absolute -bottom-10 left-6">
                        <img
                            :src="usuario.foto_url || '/default-avatar.png'"
                            :alt="`${usuario.nombre} ${usuario.apellido}`"
                            class="h-20 w-20 rounded-full ring-4 ring-slate-800 object-cover"
                        >
                    </div>
                    <div class="absolute top-4 right-4">
                        <span
                            v-if="!usuario.bloqueado"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400 border border-green-500/30"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                            Activo
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400 border border-red-500/30"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                            Bloqueado
                        </span>
                    </div>
                </div>

                <div class="pt-14 px-6 pb-6">
                    <h2 class="text-xl font-semibold text-white">
                        {{ usuario.nombre }} {{ usuario.apellido }}
                    </h2>
                    <p class="text-sm text-slate-400 mt-0.5">{{ usuario.email }}</p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span
                            v-for="rol in usuario.roles"
                            :key="rol.id"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30"
                        >
                            {{ rol.nombre }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-6 space-y-4">
                    <h3 class="text-base font-semibold text-white border-b border-slate-700 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Información Personal
                    </h3>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">ID</span>
                            <span class="text-sm text-white font-mono">#{{ usuario.id }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Nombre</span>
                            <span class="text-sm text-white">{{ usuario.nombre }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Apellido</span>
                            <span class="text-sm text-white">{{ usuario.apellido }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Teléfono</span>
                            <span class="text-sm text-white">{{ usuario.telefono || '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-6 space-y-4">
                    <h3 class="text-base font-semibold text-white border-b border-slate-700 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Cuenta y Seguridad
                    </h3>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Email</span>
                            <span class="text-sm text-white truncate max-w-[180px]">{{ usuario.email }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Email verificado</span>
                            <span
                                v-if="usuario.email_verificado"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30"
                            >
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Verificado
                            </span>
                            <span v-else class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                Pendiente
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Estado cuenta</span>
                            <span
                                :class="usuario.bloqueado
                                    ? 'bg-red-500/20 text-red-400 border-red-500/30'
                                    : 'bg-green-500/20 text-green-400 border-green-500/30'"
                                class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border"
                            >
                                {{ usuario.bloqueado ? 'Bloqueada' : 'Activa' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Creado</span>
                            <span class="text-sm text-white">{{ formatDate(usuario.created_at) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Última actualización</span>
                            <span class="text-sm text-white">{{ formatDate(usuario.updated_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="usuario.tarjeta"
                class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-6"
            >
                <h3 class="text-base font-semibold text-white border-b border-slate-700 pb-3 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Tarjeta Universitaria (RFID/NFC)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex justify-between md:flex-col md:gap-1">
                        <span class="text-sm text-slate-400">UID</span>
                        <span class="text-sm text-white font-mono">{{ maskUid(usuario.tarjeta.uid) }}</span>
                    </div>
                    <div class="flex justify-between md:flex-col md:gap-1">
                        <span class="text-sm text-slate-400">Estado tarjeta</span>
                        <span
                            :class="usuario.tarjeta.bloqueada
                                ? 'bg-red-500/20 text-red-400 border-red-500/30'
                                : 'bg-green-500/20 text-green-400 border-green-500/30'"
                            class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border w-fit"
                        >
                            {{ usuario.tarjeta.bloqueada ? 'Bloqueada' : 'Activa' }}
                        </span>
                    </div>
                    <div class="flex justify-between md:flex-col md:gap-1">
                        <span class="text-sm text-slate-400">Registrada</span>
                        <span class="text-sm text-white">{{ formatDate(usuario.tarjeta.created_at) }}</span>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-6"
            >
                <h3 class="text-base font-semibold text-white border-b border-slate-700 pb-3 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Tarjeta Universitaria (RFID/NFC)
                </h3>
                <p class="text-sm text-slate-400">Este usuario no tiene ninguna tarjeta asociada.</p>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-red-500/5 rounded-xl border border-red-500/20 p-6">
                <h3 class="text-base font-semibold text-white border-b border-slate-700 pb-3 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Zona de peligro
                </h3>
                <div class="flex flex-wrap gap-3">
                    <button
                        @click="toggleBlock"
                        :class="usuario.bloqueado
                            ? 'border-green-500/30 text-green-400 hover:bg-green-500/10'
                            : 'border-yellow-500/30 text-yellow-400 hover:bg-yellow-500/10'"
                        class="inline-flex items-center gap-2 px-4 py-2 border rounded-lg text-sm font-medium transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="usuario.bloqueado" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        {{ usuario.bloqueado ? 'Desbloquear usuario' : 'Bloquear usuario' }}
                    </button>

                    <button
                        @click="confirmDelete"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-red-500/30 text-red-400 hover:bg-red-500/10 rounded-lg text-sm font-medium transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Eliminar usuario
                    </button>
                </div>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    usuario: Object,
});

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function maskUid(uid) {
    if (!uid) return '—';
    return uid.slice(0, 4) + '****' + uid.slice(-4);
}

function toggleBlock() {
    const accion = props.usuario.bloqueado ? 'desbloquear' : 'bloquear';
    if (confirm(`¿Estás seguro de ${accion} a ${props.usuario.email}?`)) {
        router.post(route('admin.usuarios.toggle-block', props.usuario.id), {}, {
            preserveScroll: true,
        });
    }
}

function confirmDelete() {
    if (confirm(`¿Eliminar a ${props.usuario.email}? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.usuarios.destroy', props.usuario.id));
    }
}
</script>