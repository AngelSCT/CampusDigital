<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                    ¡Bienvenido, {{ $page.props.auth.user.nombre }}!
                </h1>
                <p class="mt-1 text-sm text-white">Dashboard de Estudiante</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Saldo Disponible -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-green-500/10 rounded-xl border border-green-500/20">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center shadow-lg shadow-green-500/30">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-white truncate">Saldo Disponible</dt>
                                    <dd class="text-3xl font-semibold text-white">$0.00</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-green-500/20">
                        <a href="#" class="text-sm text-green-400 hover:text-green-300 transition-colors duration-200 flex items-center">
                            Recargar saldo 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Pedidos Activos -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/30">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-white truncate">Pedidos Activos</dt>
                                    <dd class="text-3xl font-semibold text-white">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-blue-500/20">
                        <a href="#" class="text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200 flex items-center">
                            Ver pedidos 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Reservas -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-purple-500/10 rounded-xl border border-purple-500/20">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center shadow-lg shadow-purple-500/30">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-white truncate">Reservas Activas</dt>
                                    <dd class="text-3xl font-semibold text-white">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-purple-500/20">
                        <a href="#" class="text-sm text-purple-400 hover:text-purple-300 transition-colors duration-200 flex items-center">
                            Ver reservas 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Tickets -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl shadow-yellow-500/10 rounded-xl border border-yellow-500/20">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-600 to-yellow-700 flex items-center justify-center shadow-lg shadow-yellow-500/30">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-white truncate">Tickets Abiertos</dt>
                                    <dd class="text-3xl font-semibold text-white">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-5 py-3 border-t border-yellow-500/20">
                        <a href="#" class="text-sm text-yellow-400 hover:text-yellow-300 transition-colors duration-200 flex items-center">
                            Ver tickets 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Accesos Rápidos -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-white mb-4">Servicios Disponibles</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Cafetería -->
                        <a href="#" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-orange-500/10 to-orange-600/10 border border-orange-500/30 rounded-xl hover:shadow-lg hover:shadow-orange-500/20 hover:scale-105 transition-all duration-300 group">
                            <svg class="w-12 h-12 text-orange-400 mb-3 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                            <span class="font-medium text-white">Cafetería</span>
                            <span class="text-xs text-slate-400 mt-1">Ordena tu comida</span>
                        </a>

                        <!-- Copias/Impresiones -->
                        <a href="#" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-blue-500/10 to-blue-600/10 border border-blue-500/30 rounded-xl hover:shadow-lg hover:shadow-blue-500/20 hover:scale-105 transition-all duration-300 group">
                            <svg class="w-12 h-12 text-blue-400 mb-3 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span class="font-medium text-white">Copias/Impresiones</span>
                            <span class="text-xs text-slate-400 mt-1">Imprime tus documentos</span>
                        </a>

                        <!-- Souvenirs -->
                        <a href="#" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-pink-500/10 to-pink-600/10 border border-pink-500/30 rounded-xl hover:shadow-lg hover:shadow-pink-500/20 hover:scale-105 transition-all duration-300 group">
                            <svg class="w-12 h-12 text-pink-400 mb-3 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                            <span class="font-medium text-white">Souvenirs</span>
                            <span class="text-xs text-slate-400 mt-1">Artículos del campus</span>
                        </a>

                        <!-- Reservar Sala -->
                        <a href="#" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-purple-500/10 to-purple-600/10 border border-purple-500/30 rounded-xl hover:shadow-lg hover:shadow-purple-500/20 hover:scale-105 transition-all duration-300 group">
                            <svg class="w-12 h-12 text-purple-400 mb-3 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="font-medium text-white">Reservar Sala</span>
                            <span class="text-xs text-slate-400 mt-1">Salas de estudio</span>
                        </a>

                        <!-- Tickets -->
                        <a href="#" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-yellow-500/10 to-yellow-600/10 border border-yellow-500/30 rounded-xl hover:shadow-lg hover:shadow-yellow-500/20 hover:scale-105 transition-all duration-300 group">
                            <svg class="w-12 h-12 text-yellow-400 mb-3 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="font-medium text-white">Soporte</span>
                            <span class="text-xs text-slate-400 mt-1">Crear ticket</span>
                        </a>

                        <!-- Mi Tarjeta -->
                        <a href="#" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-green-500/10 to-green-600/10 border border-green-500/30 rounded-xl hover:shadow-lg hover:shadow-green-500/20 hover:scale-105 transition-all duration-300 group">
                            <svg class="w-12 h-12 text-green-400 mb-3 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span class="font-medium text-white">Mi Tarjeta</span>
                            <span class="text-xs text-slate-400 mt-1">Tarjeta universitaria</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Últimas Transacciones -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-white">Últimos Movimientos</h3>
                        <a href="#" class="text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200 flex items-center">
                            Ver todos 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-700/50 flex items-center justify-center">
                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <p class="mt-2 text-sm text-white">No hay movimientos recientes</p>
                        <p class="text-xs text-slate-400 mt-1">Tus transacciones aparecerán aquí</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>