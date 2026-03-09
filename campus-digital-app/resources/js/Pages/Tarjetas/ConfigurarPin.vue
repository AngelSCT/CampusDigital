<template>
    <AuthLayout>
        <div class="max-w-md mx-auto space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    {{ tienePin ? 'Cambiar PIN' : 'Configurar PIN' }}
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    {{ tienePin
                        ? 'Actualiza el PIN que usas para autenticarte con tu tarjeta RFID.'
                        : 'Configura un PIN de 4 dígitos para iniciar sesión con tu tarjeta RFID.'
                    }}
                </p>
            </div>

            <!-- Sin tarjeta -->
            <div v-if="!tieneTarjeta"
                 class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm text-yellow-300">
                        No tienes una tarjeta registrada. Acércate a administración para solicitar una.
                    </p>
                </div>
            </div>

            <!-- Formulario -->
            <div v-if="tieneTarjeta"
                 class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">

                <!-- Ícono de encabezado -->
                <div class="px-6 pt-8 pb-4 text-center border-b border-slate-700">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-blue-600/30 to-cyan-600/30 border border-blue-500/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-slate-400">PIN de 4 dígitos numéricos</p>
                </div>

                <div class="p-6 space-y-5">

                    <!-- Alert success local -->
                    <div v-if="alertSuccess"
                         class="flex items-center gap-2 p-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ alertSuccess }}
                    </div>

                    <!-- Alert error local -->
                    <div v-if="alertError"
                         class="flex items-center gap-2 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-sm text-red-400">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ alertError }}
                    </div>

                    <form @submit.prevent="guardar" class="space-y-5">

                        <!-- PIN actual (solo si ya tiene) -->
                        <div v-if="tienePin">
                            <label class="block text-sm font-medium text-white mb-2">
                                PIN Actual
                            </label>
                            <div class="relative">
                                <input
                                    :type="mostrarActual ? 'text' : 'password'"
                                    v-model="form.pin_actual"
                                    maxlength="4"
                                    inputmode="numeric"
                                    placeholder="••••"
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500/20 text-xl px-4 py-3 transition-all duration-200 font-mono tracking-widest text-center"
                                    :class="form.errors.pin_actual
                                        ? 'border-red-500 focus:border-red-500'
                                        : 'border-slate-600 focus:border-cyan-500'"
                                    @input="form.pin_actual = form.pin_actual.replace(/\D/g, '').slice(0, 4)"
                                />
                                <button type="button"
                                        @click="mostrarActual = !mostrarActual"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="mostrarActual" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.274 4.057-5.065 7-9 7S3.274 16.057 2 12C3.274 7.943 7.065 5 12 5s8.726 2.943 10 7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.pin_actual" class="mt-1 text-xs text-red-400">{{ form.errors.pin_actual }}</p>
                        </div>

                        <!-- Nuevo PIN -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">
                                {{ tienePin ? 'Nuevo PIN' : 'PIN (4 dígitos)' }}
                            </label>
                            <!-- Puntos indicadores -->
                            <div class="flex gap-2 justify-center mb-3">
                                <div v-for="i in 4" :key="i"
                                     class="w-3 h-3 rounded-full border-2 transition-all duration-200"
                                     :class="form.pin_nuevo.length >= i
                                         ? 'bg-cyan-400 border-cyan-400 scale-110'
                                         : 'bg-transparent border-slate-600'">
                                </div>
                            </div>
                            <div class="relative">
                                <input
                                    :type="mostrarNuevo ? 'text' : 'password'"
                                    v-model="form.pin_nuevo"
                                    maxlength="4"
                                    inputmode="numeric"
                                    placeholder="••••"
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500/20 text-xl px-4 py-3 transition-all duration-200 font-mono tracking-widest text-center"
                                    :class="form.errors.pin_nuevo
                                        ? 'border-red-500 focus:border-red-500'
                                        : 'border-slate-600 focus:border-cyan-500'"
                                    @input="form.pin_nuevo = form.pin_nuevo.replace(/\D/g, '').slice(0, 4)"
                                />
                                <button type="button"
                                        @click="mostrarNuevo = !mostrarNuevo"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="mostrarNuevo" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.274 4.057-5.065 7-9 7S3.274 16.057 2 12C3.274 7.943 7.065 5 12 5s8.726 2.943 10 7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.pin_nuevo" class="mt-1 text-xs text-red-400">{{ form.errors.pin_nuevo }}</p>
                        </div>

                        <!-- Confirmar PIN -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">Confirmar PIN</label>
                            <div class="relative">
                                <input
                                    :type="mostrarConfirm ? 'text' : 'password'"
                                    v-model="form.pin_confirmar"
                                    maxlength="4"
                                    inputmode="numeric"
                                    placeholder="••••"
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500/20 text-xl px-4 py-3 transition-all duration-200 font-mono tracking-widest text-center"
                                    :class="pinsNoCoinciden
                                        ? 'border-red-500 focus:border-red-500'
                                        : 'border-slate-600 focus:border-cyan-500'"
                                    @input="form.pin_confirmar = form.pin_confirmar.replace(/\D/g, '').slice(0, 4)"
                                />
                                <button type="button"
                                        @click="mostrarConfirm = !mostrarConfirm"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="mostrarConfirm" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.274 4.057-5.065 7-9 7S3.274 16.057 2 12C3.274 7.943 7.065 5 12 5s8.726 2.943 10 7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="pinsNoCoinciden" class="mt-1 text-xs text-red-400">Los PINs no coinciden.</p>
                        </div>

                        <!-- Aviso seguridad -->
                        <div class="flex items-start gap-3 p-3 bg-slate-700/30 border border-slate-600/50 rounded-lg">
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Usa un PIN que no sea obvio (no 1234, 0000, etc.). Este PIN protege el acceso con tu tarjeta física.
                            </p>
                        </div>

                        <!-- Botón guardar -->
                        <button type="submit"
                                :disabled="form.processing || !formValido"
                                class="w-full py-3 bg-gradient-to-br from-cyan-600 to-blue-600 border border-transparent rounded-lg text-sm font-semibold text-white hover:from-cyan-500 hover:to-blue-500 shadow-lg shadow-cyan-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2">
                            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ form.processing ? 'Guardando...' : (tienePin ? 'Actualizar PIN' : 'Configurar PIN') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Volver -->
            <div class="text-center">
                <Link :href="route('mi-tarjeta.show')"
                      class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-200 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver a Mi Tarjeta
                </Link>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    tiene_pin:     { type: Boolean, default: false },
    tiene_tarjeta: { type: Boolean, default: false },
});

const tienePin     = props.tiene_pin;
const tieneTarjeta = props.tiene_tarjeta;

const mostrarActual  = ref(false);
const mostrarNuevo   = ref(false);
const mostrarConfirm = ref(false);
const alertSuccess   = ref('');
const alertError     = ref('');

const form = useForm({
    pin_actual:    '',
    pin_nuevo:     '',
    pin_confirmar: '',
});

const pinsNoCoinciden = computed(() =>
    form.pin_nuevo.length === 4 && form.pin_confirmar.length > 0 && form.pin_nuevo !== form.pin_confirmar
);

const formValido = computed(() => {
    if (form.pin_nuevo.length !== 4 || form.pin_confirmar.length !== 4) return false;
    if (form.pin_nuevo !== form.pin_confirmar) return false;
    if (tienePin && form.pin_actual.length !== 4) return false;
    return true;
});

function guardar() {
    alertSuccess.value = '';
    alertError.value   = '';
    form.post(route('mi-tarjeta.pin.store'), {
        onSuccess: () => {
            form.reset();
            alertSuccess.value = tienePin ? 'PIN actualizado correctamente.' : 'PIN configurado correctamente.';
        },
        onError: (errors) => {
            alertError.value = errors.general ?? 'Ocurrió un error al guardar el PIN.';
        },
    });
}
</script>