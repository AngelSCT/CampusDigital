<template>
    <AuthLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    Mi Tarjeta Universitaria
                </h1>
                <p class="mt-1 text-sm text-slate-400">Identificación RFID/NFC del campus</p>
            </div>

            <!-- Sin tarjeta -->
            <div v-if="!tarjeta"
                 class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-10 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-slate-700 to-slate-800 border border-slate-600 flex items-center justify-center">
                    <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-white mb-2">No tienes una tarjeta registrada</h2>
                <p class="text-sm text-slate-400 max-w-sm mx-auto">
                    Acude con un administrador del campus para registrar tu tarjeta RFID/NFC universitaria.
                </p>
            </div>

            <!-- Con tarjeta -->
            <template v-else>
                <!-- Tarjeta visual -->
                <div class="relative overflow-hidden rounded-2xl p-6 h-48"
                     :class="tarjeta.estado === 'activa'
                         ? 'bg-gradient-to-br from-cyan-700 via-blue-700 to-indigo-800'
                         : 'bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900'">

                    <!-- Patrón de fondo -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-4 right-4 w-32 h-32 rounded-full border-4 border-white"></div>
                        <div class="absolute top-8 right-8 w-20 h-20 rounded-full border-4 border-white"></div>
                        <div class="absolute -bottom-4 -left-4 w-24 h-24 rounded-full border-4 border-white"></div>
                    </div>

                    <!-- Contenido tarjeta -->
                    <div class="relative z-10 h-full flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-6 h-6 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                </svg>
                                <span class="text-white/80 text-sm font-medium">Campus Digital</span>
                            </div>
                            <span :class="tarjeta.estado === 'activa' ? 'bg-green-500/30 text-green-200 border-green-400/30' : 'bg-red-500/30 text-red-200 border-red-400/30'"
                                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border capitalize">
                                {{ tarjeta.estado === 'activa' ? '● Activa' : '⚠ ' + tarjeta.estado }}
                            </span>
                        </div>

                        <div>
                            <p class="text-white/60 text-xs mb-1 uppercase tracking-widest">UID</p>
                            <p class="text-white text-xl font-bold font-mono tracking-widest">
                                {{ formatUid(tarjeta.uid) }}
                            </p>
                            <p class="text-white/80 text-sm mt-2 font-medium">
                                {{ usuario.nombre }} {{ usuario.apellido }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info bloqueada -->
                <div v-if="tarjeta.estado !== 'activa'"
                     class="bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-400 capitalize">Tarjeta {{ tarjeta.estado }}</p>
                            <p class="text-sm text-red-300/80 mt-1">{{ tarjeta.motivo_bloqueo ?? 'Sin motivo especificado' }}</p>
                            <p class="text-xs text-red-400/60 mt-2">Contacta a un administrador para reactivarla.</p>
                        </div>
                    </div>
                </div>

                <!-- Detalles -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider">Registrada</p>
                        <p class="text-sm font-medium text-white mt-1">{{ formatDate(tarjeta.created_at) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-4">
                        <p class="text-xs text-slate-400 uppercase tracking-wider">Registrada por</p>
                        <p class="text-sm font-medium text-white mt-1">
                            {{ tarjeta.registrado_por ? `${tarjeta.registrado_por.nombre} ${tarjeta.registrado_por.apellido}` : 'Administrador' }}
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-4">
                        <p class="text-xs text-cyan-400 uppercase tracking-wider">Lecturas Totales</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ lecturas.length }}</p>
                    </div>
                </div>

                <!-- Actividad reciente -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-700">
                        <h2 class="text-base font-semibold text-white">Actividad Reciente</h2>
                    </div>
                    <div v-if="lecturas.length === 0" class="px-6 py-8 text-center text-slate-500 text-sm">
                        Sin actividad registrada
                    </div>
                    <div v-else class="divide-y divide-slate-700/50">
                        <div v-for="l in lecturas" :key="l.id"
                             class="flex items-center gap-4 px-6 py-3 hover:bg-slate-700/20 transition-colors duration-150">
                            <!-- Icono módulo -->
                            <div :class="l.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                 class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="l.exito" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white capitalize">
                                    {{ l.modulo.replace('_', ' ') }} — {{ l.tipo_lectura.replace('_', ' ') }}
                                </p>
                                <p class="text-xs text-slate-400 truncate">{{ l.detalle }}</p>
                            </div>
                            <p class="text-xs text-slate-500 whitespace-nowrap">{{ formatDateTime(l.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    tarjeta: Object,
    lecturas: Array,
    usuario: Object,
});

function formatUid(uid) {
    // Formato: XXXX XXXX
    return uid?.match(/.{1,4}/g)?.join(' ') ?? uid;
}
function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' }) : '-';
}
function formatDateTime(d) {
    return d ? new Date(d).toLocaleString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-';
}
</script>