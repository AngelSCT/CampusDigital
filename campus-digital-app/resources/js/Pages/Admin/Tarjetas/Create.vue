<template>
    <AuthLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        Registrar Tarjeta
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Asocia un chip RFID/NFC a un usuario</p>
                </div>
                <a :href="route('admin.tarjetas.index')"
                   class="inline-flex items-center px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
            </div>

            <form @submit.prevent="submit"
                  class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-cyan-500/10 rounded-xl border border-cyan-500/20 overflow-hidden">
                <div class="p-6 space-y-6">

                    <!-- Simulador UID -->
                    <div class="bg-gradient-to-br from-cyan-500/10 to-blue-500/10 border border-cyan-500/20 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-600 to-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-cyan-400">Simulador de lectura RFID/NFC</p>
                        </div>
                        <button type="button" @click="generarUid"
                                class="w-full py-2 bg-gradient-to-br from-cyan-600/20 to-blue-600/20 border border-cyan-500/30 rounded-lg text-sm text-cyan-400 hover:from-cyan-600/30 hover:to-blue-600/30 transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Simular lectura de tarjeta (generar UID)
                        </button>
                    </div>

                    <!-- UID -->
                    <div>
                        <label for="uid" class="block text-sm font-medium text-white mb-2">
                            UID de la Tarjeta <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.uid"
                                id="uid"
                                type="text"
                                required
                                maxlength="64"
                                placeholder="Ej: A1B2C3D4 o escanea la tarjeta"
                                class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-cyan-500/30 sm:text-sm transition-all duration-200 pr-10 font-mono"
                                :class="errors.uid ? 'border-red-500' : 'border-slate-600 focus:border-cyan-500'"
                            />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg v-if="form.uid" class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Se convertirá a mayúsculas automáticamente</p>
                        <p v-if="errors.uid" class="mt-1 text-sm text-red-400">{{ errors.uid }}</p>
                    </div>

                    <!-- Usuario -->
                    <div>
                        <label for="usuario_id" class="block text-sm font-medium text-white mb-2">
                            Asignar a Usuario <span class="text-red-400">*</span>
                        </label>
                        <input
                            v-model="busquedaUsuario"
                            type="text"
                            placeholder="Buscar por nombre o email..."
                            @input="filtrarUsuarios"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 sm:text-sm px-3 py-2 mb-2 transition-all duration-200"
                        />

                        <!-- Usuario seleccionado -->
                        <div v-if="usuarioSeleccionado"
                             class="flex items-center justify-between p-3 bg-cyan-500/10 border border-cyan-500/20 rounded-lg mb-2">
                            <div>
                                <p class="text-sm font-medium text-white">{{ usuarioSeleccionado.nombre }} {{ usuarioSeleccionado.apellido }}</p>
                                <p class="text-xs text-slate-400">{{ usuarioSeleccionado.email }}</p>
                            </div>
                            <button type="button" @click="deseleccionarUsuario" class="text-slate-400 hover:text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Lista filtrada -->
                        <div v-if="!usuarioSeleccionado && usuariosFiltrados.length > 0"
                             class="border border-slate-600 rounded-lg overflow-hidden max-h-48 overflow-y-auto">
                            <button
                                v-for="u in usuariosFiltrados" :key="u.id"
                                type="button"
                                @click="seleccionarUsuario(u)"
                                class="w-full text-left px-4 py-3 hover:bg-slate-700/50 transition-colors duration-150 border-b border-slate-700/50 last:border-b-0">
                                <p class="text-sm font-medium text-white">{{ u.nombre }} {{ u.apellido }}</p>
                                <p class="text-xs text-slate-400">{{ u.email }}</p>
                            </button>
                        </div>

                        <p v-if="errors.usuario_id" class="mt-1 text-sm text-red-400">{{ errors.usuario_id }}</p>
                    </div>

                    <!-- PIN -->
                    <div>
                        <label for="pin" class="block text-sm font-medium text-white mb-2">
                            PIN de acceso
                            <span class="text-slate-400 font-normal">(opcional, 4 dígitos)</span>
                        </label>
                        <input
                            v-model="form.pin"
                            id="pin"
                            type="password"
                            maxlength="4"
                            inputmode="numeric"
                            pattern="[0-9]{4}"
                            placeholder="••••"
                            class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-cyan-500/30 sm:text-sm transition-all duration-200 px-3 py-2"
                            :class="errors.pin ? 'border-red-500' : 'border-slate-600 focus:border-cyan-500'"
                        />
                        <p class="mt-1 text-xs text-slate-400">Si no se establece, el usuario deberá configurarlo desde su perfil.</p>
                        <p v-if="errors.pin" class="mt-1 text-sm text-red-400">{{ errors.pin }}</p>
                    </div>

                </div>

                <!-- Botones -->
                <div class="bg-slate-900/50 px-6 py-4 flex justify-end gap-3 border-t border-slate-700">
                    <a :href="route('admin.tarjetas.index')"
                       class="px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200">
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        :disabled="processing || !form.uid || !form.usuario_id"
                        class="px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:from-cyan-500 hover:to-blue-500 shadow-lg shadow-cyan-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        {{ processing ? 'Registrando...' : 'Registrar Tarjeta' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    usuarios: Array,
});

const form = reactive({ uid: '', usuario_id: null, pin: '' });
const errors = ref({});
const processing = ref(false);
const busquedaUsuario = ref('');
const usuarioSeleccionado = ref(null);

const usuariosFiltrados = computed(() => {
    if (!busquedaUsuario.value.trim()) return props.usuarios.slice(0, 8);
    const q = busquedaUsuario.value.toLowerCase();
    return props.usuarios.filter(u =>
        u.nombre.toLowerCase().includes(q) ||
        u.apellido.toLowerCase().includes(q) ||
        u.email.toLowerCase().includes(q)
    ).slice(0, 8);
});

function filtrarUsuarios() {}

function seleccionarUsuario(u) {
    usuarioSeleccionado.value = u;
    form.usuario_id = u.id;
    busquedaUsuario.value = '';
}

function deseleccionarUsuario() {
    usuarioSeleccionado.value = null;
    form.usuario_id = null;
}

function generarUid() {
    const hex = () => Math.floor(Math.random() * 256).toString(16).padStart(2, '0').toUpperCase();
    form.uid = `${hex()}${hex()}${hex()}${hex()}`;
}

function submit() {
    processing.value = true;
    errors.value = {};
    router.post(route('admin.tarjetas.store'), { ...form, uid: form.uid.toUpperCase().trim() }, {
        onError: (err) => { errors.value = err; },
        onFinish: () => { processing.value = false; },
    });
}
</script>