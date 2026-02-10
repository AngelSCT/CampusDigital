<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
        <!-- Navbar -->
        <nav class="bg-gradient-to-r from-slate-900/95 to-slate-800/95 backdrop-blur-xl border-b border-blue-500/20 shadow-lg shadow-blue-500/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a :href="route('dashboard')" class="text-xl font-bold bg-gradient-to-r from-blue-500 to-blue-400 bg-clip-text text-transparent hover:from-blue-400 hover:to-blue-300 transition-all duration-300">
                                Campus Digital
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a 
                                :href="route('dashboard')"
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300',
                                    estaActiva('dashboard') 
                                        ? 'border-blue-500 text-blue-400 shadow-lg shadow-blue-500/20' 
                                        : 'border-transparent text-slate-400 hover:border-blue-500/50 hover:text-slate-200'
                                ]"
                            >
                                Dashboard
                            </a>

                            <!-- Links de Administrador -->
                            <template v-if="tieneRol('administrador')">
                                <a 
                                    :href="route('admin.usuarios.index')"
                                    :class="[
                                        'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300',
                                        estaActiva('admin.usuarios') 
                                            ? 'border-blue-500 text-blue-400 shadow-lg shadow-blue-500/20' 
                                            : 'border-transparent text-slate-400 hover:border-blue-500/50 hover:text-slate-200'
                                    ]"
                                >
                                    Usuarios
                                </a>

                                <div class="relative inline-flex items-center px-1 pt-1">
                                    <button 
                                        @click="mostrarMenuReportes = !mostrarMenuReportes"
                                        :class="[
                                            'inline-flex items-center px-1 border-b-2 text-sm font-medium transition-all duration-300',
                                            estaActiva('admin.reportes') 
                                                ? 'border-blue-500 text-blue-400 shadow-lg shadow-blue-500/20' 
                                                : 'border-transparent text-slate-400 hover:border-blue-500/50 hover:text-slate-200'
                                        ]"
                                    >
                                        Reportes
                                        <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="mostrarMenuReportes ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <transition
                                        enter-active-class="transition ease-out duration-200"
                                        enter-from-class="opacity-0 scale-95"
                                        enter-to-class="opacity-100 scale-100"
                                        leave-active-class="transition ease-in duration-150"
                                        leave-from-class="opacity-100 scale-100"
                                        leave-to-class="opacity-0 scale-95"
                                    >
                                        <div 
                                            v-show="mostrarMenuReportes"
                                            @click="mostrarMenuReportes = false"
                                            class="absolute top-full left-0 mt-2 w-48 rounded-xl shadow-2xl bg-gradient-to-br from-slate-800 to-slate-900 ring-1 ring-blue-500/30 z-50 overflow-hidden"
                                        >
                                            <div class="py-1">
                                                <a :href="route('admin.reportes.usuarios')" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                                    Usuarios
                                                </a>
                                                <a :href="route('admin.reportes.accesos')" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                                    Accesos
                                                </a>
                                                <a :href="route('admin.reportes.actividad')" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                                    Actividad
                                                </a>
                                            </div>
                                        </div>
                                    </transition>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <div class="relative">
                            <button 
                                @click="mostrarMenuUsuario = !mostrarMenuUsuario"
                                class="flex items-center text-sm font-medium text-slate-300 hover:text-blue-400 focus:outline-none transition-all duration-300 group"
                            >
                                <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold mr-2 shadow-lg shadow-blue-500/30 group-hover:shadow-blue-500/50 group-hover:scale-105 transition-all duration-300">
                                    {{ $page.props.auth?.user?.nombre?.charAt(0) || 'U' }}{{ $page.props.auth?.user?.apellido?.charAt(0) || '' }}
                                </div>
                                <span class="group-hover:text-blue-400 transition-colors duration-300">{{ $page.props.auth?.user?.nombre || 'Usuario' }}</span>
                                <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="mostrarMenuUsuario ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div 
                                    v-show="mostrarMenuUsuario"
                                    @click="mostrarMenuUsuario = false"
                                    class="absolute right-0 mt-2 w-48 rounded-xl shadow-2xl bg-gradient-to-br from-slate-800 to-slate-900 ring-1 ring-blue-500/30 z-50 overflow-hidden"
                                >
                                    <div class="py-1">
                                        <a :href="route('perfil.show')" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                            Mi Perfil
                                        </a>
                                        <form @submit.prevent="logout" class="block">
                                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-slate-300 hover:bg-red-500/10 hover:text-red-400 transition-all duration-200">
                                                Cerrar Sesión
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex items-center sm:hidden">
                        <button 
                            @click="mostrarMenuMovil = !mostrarMenuMovil"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-800 transition-all duration-300"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div v-show="mostrarMenuMovil" class="sm:hidden border-t border-blue-500/20 bg-slate-900/50 backdrop-blur-xl">
                    <div class="pt-2 pb-3 space-y-1">
                        <a 
                            :href="route('dashboard')" 
                            :class="[
                                'block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-all duration-300',
                                estaActiva('dashboard')
                                    ? 'border-blue-500 text-blue-400 bg-blue-500/10'
                                    : 'border-transparent text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                            ]"
                        >
                            Dashboard
                        </a>
                        
                        <template v-if="tieneRol('administrador')">
                            <a 
                                :href="route('admin.usuarios.index')" 
                                :class="[
                                    'block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-all duration-300',
                                    estaActiva('admin.usuarios')
                                        ? 'border-blue-500 text-blue-400 bg-blue-500/10'
                                        : 'border-transparent text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                                ]"
                            >
                                Usuarios
                            </a>
                            <a 
                                :href="route('admin.reportes.usuarios')" 
                                :class="[
                                    'block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-all duration-300',
                                    estaActiva('admin.reportes')
                                        ? 'border-blue-500 text-blue-400 bg-blue-500/10'
                                        : 'border-transparent text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                                ]"
                            >
                                Reportes
                            </a>
                        </template>
                    </div>
                    
                    <div class="pt-4 pb-3 border-t border-blue-500/20">
                        <div class="px-4">
                            <div class="text-base font-medium text-slate-200">{{ $page.props.auth?.user?.nombre || 'Usuario' }}</div>
                            <div class="text-sm font-medium text-slate-400">{{ $page.props.auth?.user?.email || '' }}</div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <a :href="route('perfil.show')" class="block px-4 py-2 text-base font-medium text-slate-400 hover:bg-slate-800/50 hover:text-blue-400 transition-all duration-300">
                                Mi Perfil
                            </a>
                            <form @submit.prevent="logout">
                                <button type="submit" class="w-full text-left block px-4 py-2 text-base font-medium text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all duration-300">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Page Content -->
        <main>
            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 border-l-4 border-green-500 rounded-lg p-4 mb-4 backdrop-blur-sm shadow-lg shadow-green-500/10">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-300 font-medium">{{ $page.props.flash.success }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="$page.props.flash?.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-gradient-to-r from-red-500/10 to-rose-500/10 border-l-4 border-red-500 rounded-lg p-4 mb-4 backdrop-blur-sm shadow-lg shadow-red-500/10">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-300 font-medium">{{ $page.props.flash.error }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const mostrarMenuUsuario = ref(false);
const mostrarMenuReportes = ref(false);
const mostrarMenuMovil = ref(false);

const tieneRol = (rol) => {
    const roles = page.props.auth?.user?.roles || [];
    return roles.some(r => r.nombre === rol);
};

const estaActiva = (rutaNombre) => {
    return window.location.pathname.includes(rutaNombre.replace('.', '/'));
};

const logout = () => {
    router.post(route('logout'));
};
</script>
