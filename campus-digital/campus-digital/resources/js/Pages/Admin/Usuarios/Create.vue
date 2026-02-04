<template>
    <AuthLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                    Crear Usuario
                </h1>
                <p class="mt-1 text-sm text-white">Completa los datos del nuevo usuario</p>
            </div>

            <form @submit.prevent="submit" class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-6 space-y-6">
                <!-- Información Personal -->
                <div class="border-b border-slate-700 pb-4">
                    <h3 class="text-lg font-medium text-white">Información Personal</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Nombre *</label>
                        <input 
                            v-model="form.nombre" 
                            type="text" 
                            required 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                        <p v-if="errors.nombre" class="mt-1 text-sm text-red-400">{{ errors.nombre }}</p>
                    </div>

                    <!-- Apellido -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Apellido *</label>
                        <input 
                            v-model="form.apellido" 
                            type="text" 
                            required 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                        <p v-if="errors.apellido" class="mt-1 text-sm text-red-400">{{ errors.apellido }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Email *</label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            required 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                        <p v-if="errors.email" class="mt-1 text-sm text-red-400">{{ errors.email }}</p>
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Teléfono</label>
                        <input 
                            v-model="form.telefono" 
                            type="text" 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                        <p v-if="errors.telefono" class="mt-1 text-sm text-red-400">{{ errors.telefono }}</p>
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="border-b border-slate-700 pb-4">
                    <h3 class="text-lg font-medium text-white">Seguridad</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Contraseña *</label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            required 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                        <p class="mt-1 text-xs text-slate-400">Mínimo 8 caracteres</p>
                        <p v-if="errors.password" class="mt-1 text-sm text-red-400">{{ errors.password }}</p>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Confirmar Contraseña *</label>
                        <input 
                            v-model="form.password_confirmation" 
                            type="password" 
                            required 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-white mb-3">Roles *</label>
                    <div class="space-y-3">
                        <div v-for="rol in roles" :key="rol.id" class="flex items-start">
                            <input 
                                :id="`rol-${rol.id}`" 
                                v-model="form.roles" 
                                type="checkbox" 
                                :value="rol.id" 
                                class="h-4 w-4 mt-0.5 text-blue-600 focus:ring-2 focus:ring-blue-500/50 border-slate-600 rounded bg-slate-700/50 transition-colors duration-200"
                            >
                            <label :for="`rol-${rol.id}`" class="ml-3 block text-sm">
                                <span class="text-white font-medium">{{ rol.nombre }}</span>
                                <span v-if="rol.descripcion" class="block text-slate-400 text-xs mt-0.5">{{ rol.descripcion }}</span>
                            </label>
                        </div>
                    </div>
                    <p v-if="errors.roles" class="mt-2 text-sm text-red-400">{{ errors.roles }}</p>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-700">
                    <a 
                        :href="route('admin.usuarios.index')" 
                        class="px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200"
                    >
                        Cancelar
                    </a>
                    <button 
                        type="submit" 
                        :disabled="processing" 
                        class="px-4 py-2 bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg text-sm font-medium text-white hover:from-blue-500 hover:to-blue-600 shadow-lg shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                    >
                        <span v-if="processing">Creando...</span>
                        <span v-else>Crear Usuario</span>
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
    roles: Array,
    errors: Object,
});

const processing = ref(false);

const form = reactive({
    nombre: '',
    apellido: '',
    email: '',
    telefono: '',
    password: '',
    password_confirmation: '',
    roles: [],
});

function submit() {
    processing.value = true;
    router.post(route('admin.usuarios.store'), form, {
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>