<template>
    <AuthLayout>
        <div class="max-w-5xl mx-auto space-y-6">

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

                <!-- Grid principal -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- ── Columna izquierda ── -->
                    <div class="lg:col-span-1 space-y-4">

                        <!-- Tarjeta visual -->
                        <div class="relative overflow-hidden rounded-2xl p-6 h-48"
                             :class="tarjeta.estado === 'activa'
                                 ? 'bg-gradient-to-br from-cyan-700 via-blue-700 to-indigo-800'
                                 : tarjeta.estado === 'bloqueada'
                                     ? 'bg-gradient-to-br from-red-900 via-red-800 to-rose-900'
                                     : 'bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900'">

                            <!-- Patrón de fondo -->
                            <div class="absolute inset-0 opacity-10">
                                <div class="absolute top-4 right-4 w-32 h-32 rounded-full border-4 border-white"></div>
                                <div class="absolute top-8 right-8 w-20 h-20 rounded-full border-4 border-white"></div>
                                <div class="absolute -bottom-4 -left-4 w-24 h-24 rounded-full border-4 border-white"></div>
                            </div>

                            <!-- Ondas RFID animadas -->
                            <div class="absolute right-6 top-1/2 -translate-y-1/2 opacity-20">
                                <div class="rfid-wave w-16 h-16 rounded-full border-2 border-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"></div>
                                <div class="rfid-wave w-10 h-10 rounded-full border-2 border-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style="animation-delay:.4s"></div>
                                <div class="rfid-wave w-5 h-5 rounded-full border-2 border-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style="animation-delay:.8s"></div>
                            </div>

                            <!-- Contenido -->
                            <div class="relative z-10 h-full flex flex-col justify-between">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                        </svg>
                                        <span class="text-white/80 text-xs font-medium tracking-widest uppercase">Campus Digital</span>
                                    </div>
                                    <span :class="tarjeta.estado === 'activa'
                                              ? 'bg-green-500/30 text-green-200 border-green-400/30'
                                              : tarjeta.estado === 'bloqueada'
                                                  ? 'bg-red-500/30 text-red-200 border-red-400/30'
                                                  : 'bg-slate-500/30 text-slate-200 border-slate-400/30'"
                                          class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border capitalize">
                                        {{ tarjeta.estado === 'activa' ? '● Activa' : tarjeta.estado === 'bloqueada' ? '⊘ Bloqueada' : '○ ' + tarjeta.estado }}
                                    </span>
                                </div>

                                <div>
                                    <p class="text-white/60 text-xs mb-1 uppercase tracking-widest">UID</p>
                                    <p class="text-white text-lg font-bold font-mono tracking-widest">
                                        {{ formatUid(tarjeta.uid) }}
                                    </p>
                                    <p class="text-white/80 text-sm mt-1 font-medium">
                                        {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Alerta bloqueada -->
                        <div v-if="tarjeta.estado !== 'activa'"
                             class="bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-400 capitalize">Tarjeta {{ tarjeta.estado }}</p>
                                    <p class="text-sm text-red-300/80 mt-1">{{ tarjeta.motivo_bloqueo ?? 'Sin motivo especificado' }}</p>
                                    <p class="text-xs text-red-400/60 mt-1">Contacta a un administrador para reactivarla.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Alerta PIN no configurado -->
                        <div v-if="!tarjeta.tiene_pin"
                             class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-yellow-400">PIN no configurado</p>
                                    <p class="text-xs text-yellow-300/70 mt-1">Configura tu PIN para iniciar sesión con tu tarjeta.</p>
                                    <Link :href="route('mi-tarjeta.pin')"
                                          class="inline-block mt-2 px-3 py-1 bg-yellow-500/20 border border-yellow-500/30 rounded-lg text-xs font-medium text-yellow-300 hover:bg-yellow-500/30 transition-colors duration-200">
                                        Configurar PIN →
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Saldo del monedero -->
                        <div class="bg-gradient-to-br from-cyan-900/40 to-blue-900/40 border border-cyan-500/20 rounded-xl p-5 text-center">
                            <p class="text-xs text-cyan-400 uppercase tracking-wider mb-1">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Saldo Disponible
                            </p>
                            <p class="text-3xl font-bold text-white font-mono">${{ formatMonto(monedero.saldo_disponible) }}</p>
                            <p v-if="monedero.saldo_retenido > 0" class="text-xs text-slate-400 mt-1">
                                Retenido: ${{ formatMonto(monedero.saldo_retenido) }}
                            </p>
                        </div>

                        <!-- Detalles de tarjeta -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-3">
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Registrada</p>
                                <p class="text-xs font-medium text-white mt-1">{{ formatDate(tarjeta.created_at) }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-3">
                                <p class="text-xs text-cyan-400 uppercase tracking-wider">Lecturas</p>
                                <p class="text-2xl font-bold text-white mt-1">{{ lecturas.length }}</p>
                            </div>
                        </div>

                        <!-- PIN — link a página separada -->
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-white">PIN de Acceso</p>
                                <p class="text-xs text-slate-400 mt-0.5">Para iniciar sesión con tarjeta</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span v-if="tarjeta.tiene_pin"
                                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/20">
                                    ● Configurado
                                </span>
                                <span v-else
                                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/20">
                                    ⚠ Sin configurar
                                </span>
                                <Link :href="route('mi-tarjeta.pin')"
                                      class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 border border-slate-600 rounded-lg text-xs font-medium text-slate-300 transition-colors duration-200">
                                    {{ tarjeta.tiene_pin ? 'Cambiar' : 'Configurar' }}
                                </Link>
                            </div>
                        </div>

                        <!-- Simular escaneo -->
                        <div v-if="tarjeta.estado === 'activa'"
                             class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-700">
                                <h3 class="text-sm font-semibold text-blue-400 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                    </svg>
                                    Simular Escaneo
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Prueba cómo funciona la lectura en cada módulo</p>
                            </div>
                            <div class="p-4 space-y-3">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Módulo</label>
                                    <select v-model="simForm.modulo"
                                            class="w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all duration-200">
                                        <option v-for="m in modulos" :key="m" :value="m">{{ moduloLabel(m) }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Tipo de lectura</label>
                                    <select v-model="simForm.tipo_lectura"
                                            class="w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all duration-200">
                                        <option v-for="t in tipos" :key="t" :value="t">{{ tipoLabel(t) }}</option>
                                    </select>
                                </div>
                                <button @click="simular" :disabled="simProcesando"
                                        class="w-full py-2 bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg text-sm font-medium text-white hover:from-blue-500 hover:to-blue-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-500/20 transition-all duration-200 flex items-center justify-center gap-2">
                                    <svg v-if="simProcesando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ simProcesando ? 'Procesando...' : 'Simular' }}
                                </button>

                                <div v-if="$page.props.flash?.success"
                                     class="flex items-center gap-2 p-3 bg-green-500/10 border border-green-500/20 rounded-lg text-xs text-green-400">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ $page.props.flash.success }}
                                </div>
                                <div v-if="$page.props.flash?.error"
                                     class="flex items-center gap-2 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-xs text-red-400">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $page.props.flash.error }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Columna derecha ── -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Últimos movimientos -->
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-700">
                                <h2 class="text-base font-semibold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                    Últimos Movimientos
                                </h2>
                            </div>
                            <div v-if="movimientos.length === 0" class="px-6 py-8 text-center text-slate-500 text-sm">
                                Sin movimientos aún.
                            </div>
                            <div v-else class="divide-y divide-slate-700/50">
                                <div v-for="m in movimientos" :key="m.id"
                                     class="flex items-center gap-4 px-6 py-3 hover:bg-slate-700/20 transition-colors duration-150">
                                    <div :class="m.tipo === 'abono' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                         class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="m.tipo === 'abono'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white truncate">
                                            {{ m.concepto || moduloLabel(m.modulo) }}
                                        </p>
                                        <p class="text-xs text-slate-400">{{ formatDateTime(m.created_at) }}</p>
                                    </div>
                                    <p :class="m.tipo === 'abono' ? 'text-green-400' : 'text-red-400'"
                                       class="text-sm font-bold font-mono whitespace-nowrap">
                                        {{ m.tipo === 'abono' ? '+' : '-' }}${{ formatMonto(m.monto) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Historial de lecturas -->
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-700">
                                <h2 class="text-base font-semibold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Historial de Lecturas
                                </h2>
                            </div>
                            <div v-if="lecturas.length === 0" class="px-6 py-8 text-center text-slate-500 text-sm">
                                Sin actividad registrada.
                            </div>
                            <div v-else class="divide-y divide-slate-700/50">
                                <div v-for="l in lecturas" :key="l.id"
                                     class="flex items-center gap-4 px-6 py-3 hover:bg-slate-700/20 transition-colors duration-150">
                                    <div :class="l.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                         class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="l.exito" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white">
                                            {{ moduloLabel(l.modulo) }}
                                            <span class="text-slate-400 font-normal">— {{ tipoLabel(l.tipo_lectura) }}</span>
                                        </p>
                                        <p v-if="l.detalle" class="text-xs text-slate-400 truncate">{{ l.detalle }}</p>
                                    </div>
                                    <p class="text-xs text-slate-500 whitespace-nowrap">{{ formatDateTime(l.created_at) }}</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </template>
        </div>

    </AuthLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    tarjeta:     { type: Object, default: null },
    monedero:    { type: Object, default: () => ({ saldo_disponible: 0, saldo_retenido: 0 }) },
    lecturas:    { type: Array,  default: () => [] },
    movimientos: { type: Array,  default: () => [] },
});

const modulos = ['cafeteria', 'copias', 'souvenirs', 'biblioteca', 'acceso', 'otro'];
const tipos   = ['acceso', 'consulta_saldo', 'confirmacion_entrega', 'consumo'];

const simForm        = reactive({ modulo: 'cafeteria', tipo_lectura: 'acceso' });
const simProcesando  = ref(false);
const simAlertOk     = ref('');
const simAlertErr    = ref('');
const simFormInertia = useForm({ modulo: 'cafeteria', tipo_lectura: 'acceso' });

function simular() {
    simProcesando.value         = true;
    simAlertOk.value            = '';
    simAlertErr.value           = '';
    simFormInertia.modulo       = simForm.modulo;
    simFormInertia.tipo_lectura = simForm.tipo_lectura;
    simFormInertia.post(route('mi-tarjeta.escanear'), {
        onSuccess: () => {
            simAlertOk.value = 'Escaneo simulado correctamente en módulo: ' + moduloLabel(simForm.modulo);
        },
        onError: () => {
            simAlertErr.value = 'No se pudo simular el escaneo.';
        },
        onFinish: () => { simProcesando.value = false; },
    });
}

function formatUid(uid) {
    return uid?.match(/.{1,4}/g)?.join(' ') ?? uid;
}

function formatMonto(v) {
    return Number(v).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' }) : '-';
}

function formatDateTime(d) {
    return d ? new Date(d).toLocaleString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-';
}

function moduloLabel(m) {
    const l = {
        cafeteria: 'Cafetería', copias: 'Copias/Impresiones',
        souvenirs: 'Souvenirs', biblioteca: 'Biblioteca',
        acceso: 'Acceso', otro: 'Otro', rfid: 'RFID', recarga: 'Recarga',
    };
    return l[m] ?? m;
}

function tipoLabel(t) {
    const l = {
        acceso: 'Acceso', consulta_saldo: 'Consulta de Saldo',
        confirmacion_entrega: 'Confirmación de Entrega', consumo: 'Consumo',
    };
    return l[t] ?? t;
}
</script>

<style scoped>
@keyframes rfid-wave {
    0%   { opacity: .6; transform: translate(-50%, -50%) scale(1); }
    100% { opacity: 0;  transform: translate(-50%, -50%) scale(2); }
}
.rfid-wave {
    animation: rfid-wave 2s ease-out infinite;
}
</style>