<template>
    <AuthLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Editar Rol
                    </h1>
                    <p class="mt-1 text-sm text-white">Modifica el rol "{{ rol.nombre }}"</p>
                </div>
                <a :href="route('admin.roles.index')" class="inline-flex items-center px-4 py-2 border border-slate-600 shadow-sm text-sm font-medium rounded-lg text-white bg-gradient-to-br from-slate-700 to-slate-800 hover:from-slate-600 hover:to-slate-700 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 overflow-hidden">
                <div class="p-6 space-y-6">
                    <!-- Información Básica -->
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-white mb-2">
                                Nombre del Rol <span class="text-red-400">*</span>
                            </label>
                            <input
                                v-model="form.nombre"
                                id="nombre"
                                type="text"
                                required
                                maxlength="50"
                                class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                :class="errors.nombre ? 'border-red-500' : 'border-slate-600 focus:border-blue-500'"
                            />
                            <p v-if="errors.nombre" class="mt-1 text-sm text-red-400">{{ errors.nombre }}</p>
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
                                class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                :class="errors.descripcion ? 'border-red-500' : 'border-slate-600 focus:border-blue-500'"
                            ></textarea>
                            <p v-if="errors.descripcion" class="mt-1 text-sm text-red-400">{{ errors.descripcion }}</p>
                        </div>
                    </div>

                    <!-- Permisos -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-white">
                                Permisos Asignados
                            </label>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="selectAllPermisos"
                                    class="text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200"
                                >
                                    Seleccionar todos
                                </button>
                                <span class="text-slate-600">|</span>
                                <button
                                    type="button"
                                    @click="deselectAllPermisos"
                                    class="text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200"
                                >
                                    Deseleccionar todos
                                </button>
                            </div>
                        </div>

                        <div class="border border-slate-700 rounded-lg p-4 max-h-96 overflow-y-auto bg-slate-900/30">
                            <div v-if="permisos.length === 0" class="text-center py-8">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-700/50 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <p class="text-slate-400">No hay permisos disponibles</p>
                            </div>
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div
                                    v-for="permiso in permisos"
                                    :key="permiso.id"
                                    class="flex items-start hover:bg-slate-700/20 rounded-lg p-2 transition-colors duration-150"
                                >
                                    <input
                                        :id="`permiso-${permiso.id}`"
                                        v-model="form.permisos"
                                        :value="permiso.id"
                                        type="checkbox"
                                        class="h-4 w-4 text-blue-600 focus:ring-2 focus:ring-blue-500/50 border-slate-600 rounded bg-slate-700/50 mt-1 transition-colors duration-200"
                                    />
                                    <label
                                        :for="`permiso-${permiso.id}`"
                                        class="ml-3 block cursor-pointer flex-1"
                                    >
                                        <span class="text-sm font-medium text-white">{{ permiso.clave }}</span>
                                        <p v-if="permiso.descripcion" class="text-xs text-slate-400 mt-0.5">{{ permiso.descripcion }}</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-slate-400">
                            <span class="text-blue-400 font-medium">{{ form.permisos.length }}</span> permiso(s) seleccionado(s)
                        </p>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="bg-slate-900/50 px-6 py-4 flex justify-end gap-3 border-t border-slate-700">
                    <a
                        :href="route('admin.roles.index')"
                        class="px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200"
                    >
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="px-4 py-2 bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg text-sm font-medium text-white hover:from-blue-500 hover:to-blue-600 shadow-lg shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                    >
                        {{ processing ? 'Guardando...' : 'Actualizar Rol' }}
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

const props = defineProps({
    rol: Object,
    permisos: Array,
});

const form = reactive({
    nombre: props.rol.nombre,
    descripcion: props.rol.descripcion || '',
    permisos: props.rol.permisos?.map(p => p.id) || [],
});

const errors = ref({});
const processing = ref(false);

function selectAllPermisos() {
    form.permisos = props.permisos.map(p => p.id);
}

function deselectAllPermisos() {
    form.permisos = [];
}

function submit() {
    processing.value = true;
    errors.value = {};

    router.put(route('admin.roles.update', props.rol.id), form, {
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