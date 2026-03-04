<template>
    <AuthLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        Editar Tarjeta
                    </h1>
                    <p class="mt-1 text-sm text-slate-400 font-mono">{{ tarjeta.uid }}</p>
                </div>
                <a :href="route('admin.tarjetas.show', tarjeta.id)"
                   class="inline-flex items-center px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
            </div>

            <!-- Info del usuario (solo lectura) -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-4">
                <p class="text-xs text-slate-400 mb-2 uppercase tracking-wider">Usuario asociado</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center text-white font-bold">
                        {{ tarjeta.usuario?.nombre?.[0] }}{{ tarjeta.usuario?.apellido?.[0] }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ tarjeta.usuario?.nombre }} {{ tarjeta.usuario?.apellido }}</p>
                        <p class="text-xs text-slate-400">{{ tarjeta.usuario?.email }}</p>
                    </div>
                    <span :class="badgeClass(tarjeta.estado)" class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                        {{ tarjeta.estado }}
                    </span>
                </div>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit"
                  class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-cyan-500/10 rounded-xl border border-cyan-500/20 overflow-hidden">
                <div class="p-6 space-y-6">

                    <!-- UID -->
                    <div>
                        <label for="uid" class="block text-sm font-medium text-white mb-2">
                            UID de la Tarjeta <span class="text-red-400">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input
                                v-model="form.uid"
                                id="uid"
                                type="text"
                                required
                                maxlength="64"
                                placeholder="UID del chip"
                                class="flex-1 rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-cyan-500/30 sm:text-sm transition-all duration-200 font-mono px-3 py-2"
                                :class="errors.uid ? 'border-red-500' : 'border-slate-600 focus:border-cyan-500'"
                            />
                            <button type="button" @click="generarUid"
                                    class="px-3 py-2 border border-cyan-500/30 rounded-lg text-xs text-cyan-400 hover:bg-cyan-500/10 transition-all duration-200 whitespace-nowrap">
                                Nuevo UID
                            </button>
                        </div>
                        <p v-if="errors.uid" class="mt-1 text-sm text-red-400">{{ errors.uid }}</p>
                    </div>

                    <!-- Advertencia si cambia UID -->
                    <div v-if="form.uid !== tarjeta.uid"
                         class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-3">
                        <p class="text-sm text-yellow-400">⚠️ Estás cambiando el UID. Asegúrate de que corresponde al chip físico correcto.</p>
                    </div>
                </div>

                <div class="bg-slate-900/50 px-6 py-4 flex justify-end gap-3 border-t border-slate-700">
                    <a :href="route('admin.tarjetas.show', tarjeta.id)"
                       class="px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200">
                        Cancelar
                    </a>
                    <button type="submit" :disabled="processing"
                            class="px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:from-cyan-500 hover:to-blue-500 shadow-lg shadow-cyan-500/20 disabled:opacity-50 transition-all duration-200">
                        {{ processing ? 'Guardando...' : 'Guardar Cambios' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({ tarjeta: Object });

const form = reactive({ uid: props.tarjeta.uid });
const errors = ref({});
const processing = ref(false);

function generarUid() {
    const hex = () => Math.floor(Math.random() * 256).toString(16).padStart(2, '0').toUpperCase();
    form.uid = `${hex()}${hex()}${hex()}${hex()}`;
}

function submit() {
    processing.value = true;
    errors.value = {};
    router.put(route('admin.tarjetas.update', props.tarjeta.id), { uid: form.uid.toUpperCase().trim() }, {
        onError: (err) => { errors.value = err; },
        onFinish: () => { processing.value = false; },
    });
}

function badgeClass(estado) {
    const map = { activa: 'bg-green-500/20 text-green-400', bloqueada: 'bg-red-500/20 text-red-400', perdida: 'bg-yellow-500/20 text-yellow-400', cancelada: 'bg-slate-500/20 text-slate-400' };
    return map[estado] ?? map.cancelada;
}
</script>