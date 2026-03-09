<template>
    <AuthLayout>
        <div class="max-w-6xl mx-auto space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    Lector RFID/NFC
                </h1>
                <p class="mt-1 text-sm text-slate-400">Panel de operaciones para proveedores de área</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- ── Columna izquierda: panel de escaneo ── -->
                <div class="lg:col-span-2 space-y-4">

                    <!-- Panel lector -->
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 shadow-xl shadow-cyan-500/5 overflow-hidden">

                        <!-- Animación RFID -->
                        <div class="relative bg-gradient-to-br from-cyan-900/30 to-blue-900/20 p-8 flex flex-col items-center justify-center border-b border-cyan-500/20">
                            <div class="relative">
                                <div :class="procesando ? 'animate-ping' : ''"
                                     class="absolute inset-0 w-16 h-16 rounded-full bg-cyan-500/20"></div>
                                <div :class="procesando ? 'animate-ping' : ''"
                                     class="absolute -inset-2 w-20 h-20 rounded-full bg-cyan-500/10"
                                     style="animation-delay:100ms"></div>
                                <div class="relative w-16 h-16 rounded-xl bg-gradient-to-br from-cyan-600 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-4 text-sm font-medium text-cyan-400">
                                {{ procesando ? 'Procesando lectura...' : 'Lector activo — ingresa el UID' }}
                            </p>
                        </div>

                        <div class="p-6 space-y-5">

                            <!-- Módulos -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Módulo</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="m in modulosList" :key="m.value"
                                        type="button"
                                        @click="moduloSeleccionado = m.value"
                                        :class="moduloSeleccionado === m.value
                                            ? 'border-cyan-500 bg-cyan-500/20 text-cyan-400'
                                            : 'border-slate-600 text-slate-400 hover:border-slate-500 hover:text-white'"
                                        class="px-3 py-2.5 border rounded-lg text-xs font-medium transition-all duration-200 flex flex-col items-center gap-1.5">
                                        <component :is="'svg'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="m.icon" />
                                        </component>
                                        {{ m.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Tipos -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Tipo de lectura</label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="t in tiposList" :key="t.value"
                                        type="button"
                                        @click="tipoSeleccionado = t.value"
                                        :class="tipoSeleccionado === t.value
                                            ? 'border-blue-500 bg-blue-500/20 text-blue-400'
                                            : 'border-slate-600 text-slate-400 hover:border-slate-500 hover:text-white'"
                                        class="px-3 py-1.5 border rounded-lg text-xs font-medium transition-all duration-200">
                                        {{ t.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- UID Input -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">
                                    UID de la Tarjeta <span class="text-red-400">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <input
                                        ref="uidRef"
                                        v-model="uid"
                                        type="text"
                                        maxlength="64"
                                        autocomplete="off"
                                        spellcheck="false"
                                        placeholder="Escanea o escribe el UID..."
                                        @keyup.enter="escanear"
                                        @input="uid = uid.toUpperCase()"
                                        class="flex-1 rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 text-sm px-3 py-2.5 font-mono tracking-widest transition-all duration-200 text-center uppercase"
                                        :class="procesando ? 'border-cyan-500/50 animate-pulse' : ''"
                                    />
                                    <button type="button" @click="generarUid"
                                            class="px-3 py-2 border border-cyan-500/30 rounded-lg text-xs text-cyan-400 hover:bg-cyan-500/10 transition-all duration-200 whitespace-nowrap">
                                        Simular
                                    </button>
                                </div>
                            </div>

                            <!-- Botón escanear -->
                            <button @click="escanear" :disabled="procesando || !uid.trim()"
                                    class="w-full py-3 bg-gradient-to-br from-cyan-600 to-blue-600 border border-transparent rounded-lg text-sm font-semibold text-white hover:from-cyan-500 hover:to-blue-500 shadow-lg shadow-cyan-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2">
                                <svg v-if="procesando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                {{ procesando ? 'Procesando...' : 'Procesar Lectura' }}
                            </button>
                        </div>
                    </div>

                    <!-- Resultado del escaneo -->
                    <transition name="fade">
                        <div v-if="resultado"
                             :class="resultado.exito
                                 ? 'border-green-500/40 shadow-green-500/10 from-green-900/30'
                                 : 'border-red-500/40 shadow-red-500/10 from-red-900/30'"
                             class="bg-gradient-to-br to-slate-900 rounded-xl border shadow-xl p-5 space-y-4">

                            <!-- Cabecera resultado -->
                            <div class="flex items-center gap-3">
                                <div :class="resultado.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                     class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="resultado.exito" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p :class="resultado.exito ? 'text-green-400' : 'text-red-400'" class="text-base font-bold">
                                        {{ resultado.exito ? 'Acceso Autorizado' : 'Acceso Denegado' }}
                                    </p>
                                    <p class="text-xs font-mono text-slate-400">{{ resultado.mensaje }}</p>
                                </div>
                            </div>

                            <!-- Info usuario -->
                            <div v-if="resultado.exito && resultado.usuario"
                                 class="bg-slate-800/50 rounded-lg p-3 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    <img v-if="resultado.usuario.foto_url" :src="resultado.usuario.foto_url" class="w-full h-full object-cover" alt="foto"/>
                                    <span v-else>{{ resultado.usuario.nombre?.[0] }}{{ resultado.usuario.apellido?.[0] }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white">{{ resultado.usuario.nombre }} {{ resultado.usuario.apellido }}</p>
                                    <p class="text-xs text-slate-400">{{ resultado.usuario.email }}</p>
                                </div>
                            </div>

                            <!-- Saldo -->
                            <div v-if="resultado.tipo === 'consulta_saldo'"
                                 class="flex items-center gap-3 bg-slate-800/50 rounded-lg p-3">
                                <p class="text-sm text-slate-400">Saldo disponible:</p>
                                <p class="text-2xl font-bold text-cyan-400 font-mono">${{ resultado.saldo }}</p>
                            </div>

                            <!-- Pedidos pendientes -->
                            <div v-if="resultado.tipo === 'confirmacion_entrega'">
                                <div v-if="resultado.pedidos_pendientes?.length > 0" class="space-y-2">
                                    <p class="text-xs text-slate-400 mb-2">Pedidos pendientes en {{ moduloLabel(moduloSeleccionado) }}:</p>
                                    <div v-for="p in resultado.pedidos_pendientes" :key="p.id"
                                         @click="pedidoSeleccionado = p"
                                         :class="pedidoSeleccionado?.id === p.id
                                             ? 'border-blue-500/50 bg-blue-500/10'
                                             : 'border-slate-600 hover:border-slate-500'"
                                         class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all duration-200">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-mono font-bold text-blue-400">{{ p.folio }}</p>
                                            <p class="text-xs text-slate-400 truncate">{{ p.descripcion || 'Sin descripción' }}</p>
                                        </div>
                                        <p class="text-sm font-bold text-cyan-400 font-mono whitespace-nowrap">${{ Number(p.total).toFixed(2) }}</p>
                                        <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 rounded text-xs">{{ p.estado }}</span>
                                    </div>

                                    <!-- Confirmar entrega -->
                                    <div v-if="pedidoSeleccionado" class="mt-3 space-y-3 pt-3 border-t border-slate-700">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" v-model="cobrarDeSaldo"
                                                   class="w-4 h-4 rounded accent-cyan-500 cursor-pointer"/>
                                            <span class="text-sm text-slate-300">
                                                Cobrar del monedero (${{ Number(pedidoSeleccionado.total).toFixed(2) }})
                                            </span>
                                        </label>
                                        <button @click="confirmarEntrega" :disabled="confirmando"
                                                class="w-full py-2.5 bg-gradient-to-br from-teal-600 to-cyan-700 border border-transparent rounded-lg text-sm font-semibold text-white hover:from-teal-500 hover:to-cyan-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-teal-500/20 transition-all duration-200 flex items-center justify-center gap-2">
                                            <svg v-if="confirmando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                            </svg>
                                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ confirmando ? 'Confirmando...' : 'Confirmar entrega de ' + pedidoSeleccionado.folio }}
                                        </button>
                                    </div>
                                </div>

                                <div v-else class="flex items-center gap-2 text-slate-400 text-sm bg-slate-800/50 rounded-lg p-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    Sin pedidos pendientes en este módulo.
                                </div>
                            </div>

                        </div>
                    </transition>
                </div>

                <!-- ── Columna derecha: historial ── -->
                <div class="lg:col-span-1">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden sticky top-6">
                        <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Últimas lecturas
                            </h2>
                            <span class="text-xs text-slate-500 bg-slate-700/50 px-2 py-0.5 rounded-full">{{ lecturas.length }}</span>
                        </div>
                        <div class="max-h-[75vh] overflow-y-auto divide-y divide-slate-700/50">
                            <div v-if="lecturas.length === 0" class="px-4 py-8 text-center text-slate-500 text-sm">
                                Sin lecturas registradas.
                            </div>
                            <div v-for="l in lecturas" :key="l.id"
                                 class="flex items-start gap-3 px-4 py-3 hover:bg-slate-700/20 transition-colors duration-150">
                                <div :class="l.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                     class="w-7 h-7 rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="l.exito" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-mono text-white truncate">{{ l.uid_leido }}</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <span class="px-1.5 py-0.5 bg-blue-500/20 text-blue-400 rounded text-xs">{{ moduloLabel(l.modulo) }}</span>
                                        <span class="px-1.5 py-0.5 bg-cyan-500/15 text-cyan-400 rounded text-xs">{{ tipoLabel(l.tipo_lectura) }}</span>
                                        <span v-if="l.folio_pedido" class="px-1.5 py-0.5 bg-purple-500/15 text-purple-400 rounded text-xs">{{ l.folio_pedido }}</span>
                                    </div>
                                    <p v-if="l.usuario_nombre" class="text-xs text-slate-400 mt-0.5">{{ l.usuario_nombre }}</p>
                                </div>
                                <p class="text-xs text-slate-500 whitespace-nowrap">{{ formatFecha(l.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    lecturasRecientes: { type: Array,  default: () => [] },
    modulos:           { type: Array,  default: () => [] },
    tipos:             { type: Array,  default: () => [] },
    scan_result:       { type: Object, default: null },
});

const uid                = ref('');
const moduloSeleccionado = ref('cafeteria');
const tipoSeleccionado   = ref('acceso');
const procesando         = ref(false);
const confirmando        = ref(false);
const resultado          = ref(props.scan_result ?? null);
const pedidoSeleccionado = ref(null);
const cobrarDeSaldo      = ref(false);
const uidRef             = ref(null);

const lecturas = computed(() => props.lecturasRecientes ?? []);

onMounted(() => uidRef.value?.focus());

const modulosList = [
    { value: 'cafeteria',  label: 'Cafetería',   icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' },
    { value: 'copias',     label: 'Copias',       icon: 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z' },
    { value: 'souvenirs',  label: 'Souvenirs',    icon: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7' },
    { value: 'biblioteca', label: 'Biblioteca',   icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    { value: 'acceso',     label: 'Acceso',       icon: 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z' },
    { value: 'otro',       label: 'Otro',         icon: 'M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z' },
];

const tiposList = [
    { value: 'acceso',                label: 'Acceso' },
    { value: 'consulta_saldo',        label: 'Consultar Saldo' },
    { value: 'confirmacion_entrega',  label: 'Confirmar Entrega' },
    { value: 'consumo',               label: 'Consumo' },
];

function escanear() {
    if (!uid.value.trim() || procesando.value) return;
    procesando.value         = true;
    pedidoSeleccionado.value = null;

    router.post(route('lector.leer'), {
        uid:          uid.value.toUpperCase().trim(),
        modulo:       moduloSeleccionado.value,
        tipo_lectura: tipoSeleccionado.value,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            resultado.value = page.props.flash?.scan_result ?? page.props.scan_result ?? null;
            uid.value = '';
            uidRef.value?.focus();
        },
        onFinish: () => { procesando.value = false; },
    });
}

function confirmarEntrega() {
    if (!pedidoSeleccionado.value || !resultado.value?.tarjeta_id) return;
    confirmando.value = true;

    router.post(route('lector.confirmar-pedido'), {
        pedido_id:  pedidoSeleccionado.value.id,
        tarjeta_id: resultado.value.tarjeta_id,
        modulo:     moduloSeleccionado.value,
        cobrar:     cobrarDeSaldo.value,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            resultado.value          = page.props.flash?.scan_result ?? null;
            pedidoSeleccionado.value = null;
            cobrarDeSaldo.value      = false;
        },
        onFinish: () => { confirmando.value = false; },
    });
}

function generarUid() {
    const hex = () => Math.floor(Math.random() * 256).toString(16).padStart(2, '0').toUpperCase();
    uid.value = `${hex()}${hex()}${hex()}${hex()}`;
}

function formatFecha(d) {
    if (!d) return '';
    return new Date(d).toLocaleString('es-MX', {
        day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
    });
}

function moduloLabel(m) {
    return modulosList.find(x => x.value === m)?.label ?? m;
}

function tipoLabel(t) {
    return tiposList.find(x => x.value === t)?.label ?? t;
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>