<template>
    <AuthLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    Lector RFID/NFC
                </h1>
                <p class="mt-1 text-sm text-slate-400">Panel de lectura y verificación de tarjetas universitarias</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Panel lector -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 shadow-xl shadow-cyan-500/5 overflow-hidden">
                    <!-- Animación RFID -->
                    <div class="relative bg-gradient-to-br from-cyan-900/30 to-blue-900/20 p-8 flex flex-col items-center justify-center border-b border-cyan-500/20">
                        <!-- Ondas animadas -->
                        <div class="relative">
                            <div :class="leyendo ? 'animate-ping' : ''" class="absolute inset-0 w-16 h-16 rounded-full bg-cyan-500/20"></div>
                            <div :class="leyendo ? 'animate-ping' : ''" class="absolute -inset-2 w-20 h-20 rounded-full bg-cyan-500/10" style="animation-delay:100ms"></div>
                            <div class="relative w-16 h-16 rounded-xl bg-gradient-to-br from-cyan-600 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-4 text-sm font-medium text-cyan-400">
                            {{ leyendo ? 'Procesando lectura...' : 'Lector activo — ingresa el UID' }}
                        </p>
                    </div>

                    <!-- Formulario lectura -->
                    <div class="p-6 space-y-4">

                        <!-- UID Input -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">
                                UID de la Tarjeta <span class="text-red-400">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.uid"
                                    ref="uidInput"
                                    type="text"
                                    maxlength="64"
                                    placeholder="Escanea o ingresa el UID..."
                                    @keyup.enter="procesar"
                                    class="flex-1 rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 sm:text-sm px-3 py-2 font-mono transition-all duration-200"
                                />
                                <button type="button" @click="generarUid"
                                        class="px-3 py-2 border border-cyan-500/30 rounded-lg text-xs text-cyan-400 hover:bg-cyan-500/10 transition-all duration-200">
                                    Simular
                                </button>
                            </div>
                        </div>

                        <!-- Módulo -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Módulo</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="m in modulos" :key="m.value"
                                    type="button"
                                    @click="form.modulo = m.value"
                                    :class="form.modulo === m.value
                                        ? 'border-cyan-500 bg-cyan-500/20 text-cyan-400'
                                        : 'border-slate-600 text-slate-400 hover:border-slate-500 hover:text-white'"
                                    class="px-3 py-2 border rounded-lg text-xs font-medium transition-all duration-200 text-left">
                                    {{ m.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Tipo de lectura</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="t in tipos" :key="t.value"
                                    type="button"
                                    @click="form.tipo_lectura = t.value"
                                    :class="form.tipo_lectura === t.value
                                        ? 'border-blue-500 bg-blue-500/20 text-blue-400'
                                        : 'border-slate-600 text-slate-400 hover:border-slate-500 hover:text-white'"
                                    class="px-3 py-2 border rounded-lg text-xs font-medium transition-all duration-200 text-left">
                                    {{ t.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Botón procesar -->
                        <button @click="procesar" :disabled="leyendo || !form.uid.trim()"
                                class="w-full py-3 bg-gradient-to-br from-cyan-600 to-blue-600 border border-transparent rounded-lg text-sm font-semibold text-white hover:from-cyan-500 hover:to-blue-500 shadow-lg shadow-cyan-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            {{ leyendo ? 'Procesando...' : 'Procesar Lectura' }}
                        </button>
                    </div>
                </div>

                <!-- Panel resultado + log -->
                <div class="space-y-4">

                    <!-- Resultado -->
                    <transition name="fade">
                        <div v-if="resultado"
                             :class="resultado.exito
                                 ? 'border-green-500/40 shadow-green-500/10 from-green-900/30'
                                 : 'border-red-500/40 shadow-red-500/10 from-red-900/30'"
                             class="bg-gradient-to-br to-slate-900 rounded-xl border shadow-xl p-5 space-y-3">

                            <!-- Icono resultado -->
                            <div class="flex items-center gap-3">
                                <div :class="resultado.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                     class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="resultado.exito" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p :class="resultado.exito ? 'text-green-400' : 'text-red-400'" class="text-base font-bold">
                                        {{ resultado.exito ? 'Acceso Autorizado' : 'Acceso Denegado' }}
                                    </p>
                                    <p class="text-xs font-mono text-slate-400">{{ resultado.uid }}</p>
                                </div>
                            </div>

                            <!-- Info usuario -->
                            <div v-if="resultado.usuario"
                                 class="bg-slate-800/50 rounded-lg p-3 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    {{ resultado.usuario.nombre[0] }}{{ resultado.usuario.apellido[0] }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white">{{ resultado.usuario.nombre }} {{ resultado.usuario.apellido }}</p>
                                    <p class="text-xs text-slate-400">{{ resultado.usuario.email }}</p>
                                </div>
                            </div>

                            <p class="text-sm text-slate-300">{{ resultado.detalle }}</p>
                        </div>
                    </transition>

                    <!-- Log reciente -->
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-white">Mis lecturas recientes</h2>
                            <span class="text-xs text-slate-500">{{ logLocal.length }}</span>
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-700/50">
                            <div v-if="logLocal.length === 0" class="px-4 py-6 text-center text-slate-500 text-sm">
                                Sin lecturas en esta sesión
                            </div>
                            <div v-for="(item, i) in logLocal" :key="i"
                                 class="flex items-center gap-3 px-4 py-3 hover:bg-slate-700/20 transition-colors duration-150">
                                <div :class="item.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                     class="w-7 h-7 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="item.exito" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white truncate font-mono">{{ item.uid }}</p>
                                    <p class="text-xs text-slate-400 capitalize">{{ item.modulo }} · {{ item.tipo_lectura.replace('_', ' ') }}</p>
                                </div>
                                <p class="text-xs text-slate-500 whitespace-nowrap">{{ item.hora }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { watch } from 'vue';

const props = defineProps({
    lecturasRecientes: Array,
    modulos: Array,
    tipos: Array,
});

const uidInput = ref(null);
const leyendo  = ref(false);
const resultado = ref(null);

const form = reactive({
    uid:          '',
    modulo:       'cafeteria',
    tipo_lectura: 'acceso',
});

// Log local de esta sesión
const logLocal = ref([]);

const page = usePage();
watch(() => page.props.flash?.resultado, (val) => {
    if (val) {
        resultado.value = val;
        logLocal.value.unshift({
            uid:          val.uid,
            exito:        val.exito,
            modulo:       form.modulo,
            tipo_lectura: form.tipo_lectura,
            hora:         new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
        });
        form.uid = '';
        uidInput.value?.focus();
    }
}, { immediate: true });

function procesar() {
    if (!form.uid.trim() || leyendo.value) return;
    leyendo.value = true;
    resultado.value = null;
    router.post(route('lector.leer'), {
        uid:          form.uid.toUpperCase().trim(),
        modulo:       form.modulo,
        tipo_lectura: form.tipo_lectura,
    }, {
        onFinish: () => { leyendo.value = false; },
        preserveScroll: true,
    });
}

function generarUid() {
    const hex = () => Math.floor(Math.random() * 256).toString(16).padStart(2, '0').toUpperCase();
    form.uid = `${hex()}${hex()}${hex()}${hex()}`;
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>