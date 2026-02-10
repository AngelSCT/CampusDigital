<template>
    <AuthLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold leading-7 text-white">
                        Mi Perfil
                    </h2>
                    <p class="mt-1 text-sm text-white">
                        Administra tu información personal y preferencias.
                    </p>
                </div>

                <!-- Información Personal -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl mb-6 border border-blue-500/20">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Información Personal
                        </h3>
                        
                        <form @submit.prevent="actualizarPerfil" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Nombre -->
                                <div>
                                    <label class="block text-sm font-medium text-white">Nombre</label>
                                    <input 
                                        v-model="formPerfil.nombre" 
                                        type="text" 
                                        disabled
                                        class="mt-1 bg-slate-900/50 text-slate-400 shadow-sm block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 focus:outline-none"
                                    >
                                    <p class="mt-1 text-xs text-slate-400">Contacta al administrador para cambiar tu nombre</p>
                                </div>

                                <!-- Apellido -->
                                <div>
                                    <label class="block text-sm font-medium text-white">Apellido</label>
                                    <input 
                                        v-model="formPerfil.apellido" 
                                        type="text" 
                                        disabled
                                        class="mt-1 bg-slate-900/50 text-slate-400 shadow-sm block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 focus:outline-none"
                                    >
                                    <p class="mt-1 text-xs text-slate-400">Contacta al administrador para cambiar tu apellido</p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-medium text-white">Email</label>
                                    <input 
                                        v-model="formPerfil.email" 
                                        type="email" 
                                        disabled
                                        class="mt-1 bg-slate-900/50 text-slate-400 shadow-sm block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 focus:outline-none"
                                    >
                                    <p class="mt-1 text-xs text-slate-400">Contacta al administrador para cambiar tu email</p>
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label class="block text-sm font-medium text-white">Teléfono</label>
                                    <input 
                                        v-model="formPerfil.telefono" 
                                        type="tel" 
                                        class="mt-1 bg-slate-900 text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 transition-all duration-200"
                                    >
                                </div>

                                <!-- Fecha de Nacimiento -->
                                <div>
                                    <label class="block text-sm font-medium text-white">Fecha de Nacimiento</label>
                                    <input 
                                        v-model="formPerfil.fecha_nacimiento" 
                                        type="date" 
                                        class="mt-1 bg-slate-900 text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 transition-all duration-200"
                                    >
                                </div>

                                <!-- Género -->
                                <div>
                                    <label class="block text-sm font-medium text-white">Género</label>
                                    <select 
                                        v-model="formPerfil.genero" 
                                        class="mt-1 bg-slate-900 text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 transition-all duration-200"
                                    >
                                        <option value="">Seleccionar...</option>
                                        <option value="masculino">Masculino</option>
                                        <option value="femenino">Femenino</option>
                                        <option value="otro">Otro</option>
                                        <option value="prefiero_no_decir">Prefiero no decir</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div>
                                <label class="block text-sm font-medium text-white">Dirección</label>
                                <textarea 
                                    v-model="formPerfil.direccion" 
                                    rows="3"
                                    class="mt-1 bg-slate-900 text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 transition-all duration-200"
                                ></textarea>
                            </div>

                            <!-- Botón Guardar -->
                            <div class="flex justify-end">
                                <button 
                                    type="submit" 
                                    :disabled="formPerfil.processing"
                                    class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg shadow-blue-500/30"
                                >
                                    {{ formPerfil.processing ? 'Guardando...' : 'Guardar Cambios' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Foto de Perfil -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl mb-6 border border-blue-500/20">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Foto de Perfil
                        </h3>
                        
                        <div class="flex items-center space-x-6">
                            <div class="flex-shrink-0">
                                <div v-if="usuario.foto_url" class="h-24 w-24 rounded-full overflow-hidden ring-4 ring-blue-500/30">
                                    <img :src="`/storage/${usuario.foto_url}`" alt="Foto de perfil" class="h-full w-full object-cover">
                                </div>
                                <div v-else class="h-24 w-24 rounded-full bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-500/30">
                                    {{ usuario.nombre.charAt(0) }}{{ usuario.apellido.charAt(0) }}
                                </div>
                            </div>
                            
                            <div class="flex-1">
                                <input 
                                    ref="fotoInput"
                                    type="file" 
                                    accept="image/*"
                                    class="hidden"
                                    @change="subirFoto"
                                >
                                
                                <div class="flex space-x-3">
                                    <button 
                                        @click="$refs.fotoInput.click()"
                                        type="button"
                                        class="px-4 py-2 border border-slate-600 rounded-lg shadow-sm text-sm font-medium text-white bg-slate-700 hover:bg-slate-600 transition-all duration-200"
                                    >
                                        {{ usuario.foto_url ? 'Cambiar Foto' : 'Subir Foto' }}
                                    </button>
                                    
                                    <button 
                                        v-if="usuario.foto_url"
                                        @click="eliminarFoto"
                                        type="button"
                                        class="px-4 py-2 border border-red-500/50 rounded-lg shadow-sm text-sm font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 transition-all duration-200"
                                    >
                                        Eliminar Foto
                                    </button>
                                </div>
                                
                                <p class="mt-2 text-xs text-slate-400">
                                    JPG, PNG o GIF. Máximo 2MB.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Cuenta -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl mb-6 border border-blue-500/20">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Información de Cuenta
                        </h3>
                        
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-slate-400">Roles</dt>
                                <dd class="mt-1">
                                    <span 
                                        v-for="rol in usuario.roles" 
                                        :key="rol.id"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 mr-1"
                                    >
                                        {{ rol.nombre }}
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-slate-400">Estado de Email</dt>
                                <dd class="mt-1">
                                    <span 
                                        :class="usuario.email_verificado ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30'"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                    >
                                        {{ usuario.email_verificado ? 'Verificado' : 'No Verificado' }}
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-slate-400">Miembro desde</dt>
                                <dd class="mt-1 text-sm text-white">{{ formatDate(usuario.created_at) }}</dd>
                            </div>
                            
                            <div v-if="usuario.ultimo_login_at">
                                <dt class="text-sm font-medium text-slate-400">Último acceso</dt>
                                <dd class="mt-1 text-sm text-white">{{ formatDateTime(usuario.ultimo_login_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Cambiar Contraseña
                        </h3>
                        
                        <form @submit.prevent="cambiarPassword" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-white">Contraseña Actual</label>
                                <input 
                                    v-model="formPassword.current_password" 
                                    type="password" 
                                    class="mt-1 bg-slate-900 text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 transition-all duration-200"
                                >
                                <p v-if="formPassword.errors.current_password" class="mt-1 text-sm text-red-400">
                                    {{ formPassword.errors.current_password }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white">Nueva Contraseña</label>
                                <input 
                                    v-model="formPassword.password" 
                                    type="password" 
                                    class="mt-1 bg-slate-900 text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 transition-all duration-200"
                                >
                                <p v-if="formPassword.errors.password" class="mt-1 text-sm text-red-400">
                                    {{ formPassword.errors.password }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white">Confirmar Nueva Contraseña</label>
                                <input 
                                    v-model="formPassword.password_confirmation" 
                                    type="password" 
                                    class="mt-1 bg-slate-900 text-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-slate-700 rounded-lg px-3 py-2 transition-all duration-200"
                                >
                            </div>

                            <div class="flex justify-end">
                                <button 
                                    type="submit" 
                                    :disabled="formPassword.processing"
                                    class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg shadow-blue-500/30"
                                >
                                    {{ formPassword.processing ? 'Cambiando...' : 'Cambiar Contraseña' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Components/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    usuario: Object,
});

const formPerfil = useForm({
    nombre: props.usuario.nombre,
    apellido: props.usuario.apellido,
    email: props.usuario.email,
    telefono: props.usuario.telefono || '',
    fecha_nacimiento: props.usuario.perfil?.fecha_nacimiento || '',
    genero: props.usuario.perfil?.genero || '',
    direccion: props.usuario.perfil?.direccion || '',
});

const formPassword = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const actualizarPerfil = () => {
    formPerfil.post(route('perfil.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Mensaje de éxito
        },
    });
};

const cambiarPassword = () => {
    formPassword.put(route('user-password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            formPassword.reset();
        },
    });
};

const subirFoto = (event) => {
    const file = event.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append('photo', file);
        
        useForm(formData).post(route('perfil.photo.update'), {
            preserveScroll: true,
        });
    }
};

const eliminarFoto = () => {
    if (confirm('¿Estás seguro de eliminar tu foto de perfil?')) {
        useForm({}).delete(route('perfil.photo.delete'), {
            preserveScroll: true,
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>