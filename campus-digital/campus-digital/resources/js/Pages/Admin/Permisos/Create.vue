<template>
    <AuthLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Crear Nuevo Permiso
                    </h1>
                    <p class="mt-1 text-sm text-white">Define un nuevo permiso del sistema</p>
                </div>
                <a :href="route('admin.permisos.index')" class="inline-flex items-center px-4 py-2 border border-slate-600 shadow-sm text-sm font-medium rounded-lg text-white bg-gradient-to-br from-slate-700 to-slate-800 hover:from-slate-600 hover:to-slate-700 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 overflow-hidden">
                <div class="p-6 space-y-6">
                    <!-- Clave -->
                    <div>
                        <label for="clave" class="block text-sm font-medium text-white mb-2">
                            Clave del Permiso <span class="text-red-400">*</span>
                        </label>
                        <input
                            v-model="form.clave"
                            id="clave"
                            type="text"
                            required
                            maxlength="100"
                            placeholder="ej: usuarios.crear, roles.editar"
                            class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                            :class="errors.clave ? 'border-red-500' : 'border-slate-600 focus:border-blue-500'"
                        />
                        <p class="mt-1 text-sm text-slate-400">Use puntos para separar niveles (ej: modulo.accion)</p>
                        <p v-if="errors.clave" class="mt-1 text-sm text-red-400">{{ errors.clave }}</p>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-white mb-2">
                            Descripción
                        </label>
                        <textarea
                            v-model="form.descripcion"
                            id="descripcion"
                            rows="3"
                            placeholder="Describe qué permite hacer este permiso..."
                            class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                            :class="errors.descripcion ? 'border-red-500' : 'border-slate-600 focus:border-blue-500'"
                        ></textarea>
                        <p v-if="errors.descripcion" class="mt-1 text-sm text-red-400">{{ errors.descripcion }}</p>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="bg-slate-900/50 px-6 py-4 flex justify-end gap-3 border-t border-slate-700">
                    <a
                        :href="route('admin.permisos.index')"
                        class="px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200"
                    >
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="px-4 py-2 bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg text-sm font-medium text-white hover:from-blue-500 hover:to-blue-600 shadow-lg shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                    >
                        {{ processing ? 'Guardando...' : 'Crear Permiso' }}
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

const form = reactive({
    clave: '',
    descripcion: '',
});

const errors = ref({});
const processing = ref(false);

function submit() {
    processing.value = true;
    errors.value = {};

    router.post(route('admin.permisos.store'), form, {
        onSuccess: () => {
            // Redirige automáticamente
        },
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>