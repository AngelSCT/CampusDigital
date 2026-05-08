<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
        <nav class="bg-gradient-to-r from-slate-900/95 to-slate-800/95 backdrop-blur-xl border-b border-blue-500/20 shadow-lg shadow-blue-500/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="/dashboard" class="text-xl font-bold bg-gradient-to-r from-blue-500 to-blue-400 bg-clip-text text-transparent hover:from-blue-400 hover:to-blue-300 transition-all duration-300">
                                Campus Digital
                            </a>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="/dashboard" class="border-transparent text-white hover:border-blue-500/50 hover:text-blue-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Dashboard
                            </a>

                            <template v-if="isAdmin">
                                <a href="/admin/usuarios" class="border-transparent text-white hover:border-blue-500/50 hover:text-blue-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    Usuarios
                                </a>
                                <a href="/admin/roles" class="border-transparent text-white hover:border-blue-500/50 hover:text-blue-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Roles
                                </a>
                                <a href="/admin/permisos" class="border-transparent text-white hover:border-blue-500/50 hover:text-blue-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                    Permisos
                                </a>
                                <a href="/admin/bitacora/accesos" class="border-transparent text-white hover:border-blue-500/50 hover:text-blue-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Bitácora
                                </a>

                                <!-- Menú desplegable Soporte -->
                                <div class="relative flex items-center">
                                    <button
                                        @click="toggleSoporteMenu"
                                        class="border-transparent text-white hover:border-blue-500/50 hover:text-blue-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300 h-16"
                                    >
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        Soporte
                                        <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="showSoporteMenu ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <transition
                                        enter-active-class="transition ease-out duration-200"
                                        enter-from-class="transform opacity-0 scale-95"
                                        enter-to-class="transform opacity-100 scale-100"
                                        leave-active-class="transition ease-in duration-100"
                                        leave-from-class="transform opacity-100 scale-100"
                                        leave-to-class="transform opacity-0 scale-95"
                                    >
                                        <div v-show="showSoporteMenu" class="origin-top-left absolute left-0 top-full mt-1 w-56 rounded-xl shadow-2xl bg-gradient-to-br from-slate-800 to-slate-900 ring-1 ring-blue-500/30 z-50 overflow-hidden">
                                            <div class="py-1">
                                                <a href="/admin/tickets" class="flex items-center px-4 py-2.5 text-sm text-white hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                                    </svg>
                                                    Tickets
                                                </a>
                                                <a href="/admin/areas" class="flex items-center px-4 py-2.5 text-sm text-white hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                    Áreas
                                                </a>
                                                <a href="/admin/categorias-ticket" class="flex items-center px-4 py-2.5 text-sm text-white hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                    </svg>
                                                    Categorías
                                                </a>
                                                <a href="/admin/ubicaciones" class="flex items-center px-4 py-2.5 text-sm text-white hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    Ubicaciones
                                                </a>
                                                <a href="/admin/equipos-activos" class="flex items-center px-4 py-2.5 text-sm text-white hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                    Equipos Activos
                                                </a>
                                                <a href="/admin/mantenimientos-preventivos" class="flex items-center px-4 py-2.5 text-sm text-white hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    Mantenimientos
                                                </a>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <a href="/admin/tarjetas/dashboard" class="border-transparent text-white hover:border-blue-500/50 hover:text-blue-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    Tarjetas
                                </a>
                            </template>

                            <a href="/archivos" class="border-transparent text-white hover:border-blue-500/50 hover:text-blue-400 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all duration-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                Archivos
                            </a>
                        </div>
                    </div>
                    
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <div class="ml-3 relative">
                            <button @click="toggleUserMenu" type="button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-slate-900 transition-all duration-300 group">
                                <img class="h-8 w-8 rounded-full object-cover ring-2 ring-blue-500/30 group-hover:ring-blue-500/60 transition-all duration-300" :src="userAvatar" :alt="userName">
                                <span class="ml-2 text-white font-medium group-hover:text-blue-400 transition-colors duration-300">{{ userName }}</span>
                                <svg class="ml-1 h-5 w-5 text-white group-hover:text-blue-400 transition-all duration-200" :class="showUserMenu ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-100"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <div v-show="showUserMenu" class="origin-top-right absolute right-0 mt-2 w-56 rounded-xl shadow-2xl bg-gradient-to-br from-slate-800 to-slate-900 ring-1 ring-blue-500/30 z-50 overflow-hidden">
                                    <div class="py-1">
                                        <!-- Usuario Info -->
                                        <div class="px-4 py-3 border-b border-blue-500/20">
                                            <p class="text-sm font-medium text-white">{{ userName }}</p>
                                            <p class="text-xs text-white truncate">{{ userEmail }}</p>
                                            <span v-for="rol in userRoles" :key="rol" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30 mt-1 mr-1">
                                                {{ rol }}
                                            </span>
                                        </div>
                                        
                                        <!-- Mi Perfil -->
                                        <a href="/perfil" class="block px-4 py-2 text-sm text-white hover:bg-blue-500/10 hover:text-blue-400 flex items-center transition-all duration-200">
                                            <svg class="w-4 h-4 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Mi Perfil
                                        </a>

                                        <!-- Mi Tarjeta -->
                                        <a href="/mi-tarjeta" class="block px-4 py-2 text-sm text-white hover:bg-cyan-500/10 hover:text-cyan-400 flex items-center transition-all duration-200">
                                            <svg class="w-4 h-4 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                            Mi Tarjeta
                                        </a>

                                        <!-- Mis Archivos -->
                                        <a href="/archivos" class="block px-4 py-2 text-sm text-white hover:bg-blue-500/10 hover:text-blue-400 flex items-center transition-all duration-200">
                                            <svg class="w-4 h-4 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                            </svg>
                                            Mis Archivos
                                        </a>

                                        <!-- Lector -->
                                        <a href="/lector" class="block px-4 py-2 text-sm text-white hover:bg-blue-500/10 hover:text-blue-400 flex items-center transition-all duration-200">
                                            <svg class="w-4 h-4 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            Lector[EX!]
                                        </a>
                                        
                                        <!-- Cerrar Sesión -->
                                        <button @click="logout" type="button" class="block w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 flex items-center border-t border-blue-500/20 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Cerrar Sesión
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <div class="flex items-center sm:hidden">
                        <button @click="toggleMobileMenu" type="button" class="inline-flex items-center justify-center p-2 rounded-lg text-white hover:text-blue-400 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all duration-300">
                            <svg class="h-6 w-6" :class="{ 'hidden': showMobileMenu, 'block': !showMobileMenu }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg class="h-6 w-6" :class="{ 'block': showMobileMenu, 'hidden': !showMobileMenu }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div v-show="showMobileMenu" class="sm:hidden bg-slate-900/50 backdrop-blur-xl border-t border-blue-500/20">
                    <div class="pt-2 pb-3 space-y-1">
                        <a href="/dashboard" class="border-transparent text-white hover:bg-blue-500/10 hover:border-blue-500 hover:text-blue-400 block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-all duration-300">
                            Dashboard
                        </a>
                        
                        <template v-if="isAdmin">
                            <a href="/admin/usuarios" class="border-transparent text-white hover:bg-blue-500/10 hover:border-blue-500 hover:text-blue-400 block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-all duration-300">
                                Usuarios
                            </a>
                            <a href="/admin/roles" class="border-transparent text-white hover:bg-blue-500/10 hover:border-blue-500 hover:text-blue-400 block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-all duration-300">
                                Roles
                            </a>
                            <a href="/admin/permisos" class="border-transparent text-white hover:bg-blue-500/10 hover:border-blue-500 hover:text-blue-400 block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-all duration-300">
                                Permisos
                            </a>
                            <a href="/admin/bitacora/accesos" class="border-transparent text-white hover:bg-blue-500/10 hover:border-blue-500 hover:text-blue-400 block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-all duration-300">
                                Bitácora
                            </a>
                            <div class="pl-3 pr-4 py-1">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider py-1">Soporte</p>
                                <a href="/admin/tickets" class="text-white hover:text-blue-400 block py-1.5 text-sm transition-all duration-200">Tickets</a>
                                <a href="/admin/areas" class="text-white hover:text-blue-400 block py-1.5 text-sm transition-all duration-200">Áreas</a>
                                <a href="/admin/categorias-ticket" class="text-white hover:text-blue-400 block py-1.5 text-sm transition-all duration-200">Categorías</a>
                                <a href="/admin/ubicaciones" class="text-white hover:text-blue-400 block py-1.5 text-sm transition-all duration-200">Ubicaciones</a>
                                <a href="/admin/equipos-activos" class="text-white hover:text-blue-400 block py-1.5 text-sm transition-all duration-200">Equipos Activos</a>
                                <a href="/admin/mantenimientos-preventivos" class="text-white hover:text-blue-400 block py-1.5 text-sm transition-all duration-200">Mantenimientos</a>
                            </div>
                        </template>
                    </div>
                    
                    <div class="pt-4 pb-3 border-t border-blue-500/20">
                        <div class="flex items-center px-4">
                            <div class="flex-shrink-0">
                                <img class="h-10 w-10 rounded-full object-cover ring-2 ring-blue-500/30" :src="userAvatar" :alt="userName">
                            </div>
                            <div class="ml-3">
                                <div class="text-base font-medium text-white">{{ userName }}</div>
                                <div class="text-sm font-medium text-white">{{ userEmail }}</div>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <a href="/perfil" class="block px-4 py-2 text-base font-medium text-white hover:text-blue-400 hover:bg-blue-500/10 transition-all duration-300">
                                Mi Perfil
                            </a>
                            <button @click="logout" type="button" class="block w-full text-left px-4 py-2 text-base font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all duration-300">
                                Cerrar Sesión
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </nav>

        <div v-if="$page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 border-l-4 border-green-500 rounded-lg p-4 backdrop-blur-sm shadow-lg shadow-green-500/10">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-300 font-medium">{{ $page.props.flash.success }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="$page.props.flash.success = null" class="inline-flex text-green-400 hover:text-green-300 transition-colors duration-200">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="$page.props.flash?.error || $page.props.errors?.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-gradient-to-r from-red-500/10 to-rose-500/10 border-l-4 border-red-500 rounded-lg p-4 backdrop-blur-sm shadow-lg shadow-red-500/10">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-300 font-medium">{{ $page.props.flash.error || $page.props.errors.error }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="$page.props.flash.error = null" class="inline-flex text-red-400 hover:text-red-300 transition-colors duration-200">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const showUserMenu = ref(false);
const showMobileMenu = ref(false);
const showSoporteMenu = ref(false);

const userName = computed(() => {
    const user = page.props.auth?.user;
    return user ? `${user.nombre} ${user.apellido}` : 'Usuario';
});

const userEmail = computed(() => page.props.auth?.user?.email || '');

const userAvatar = computed(() => {
    const user = page.props.auth?.user;
    if (user?.foto_url) {
        return `/storage/${user.foto_url}`;  
    }
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(userName.value)}&background=1E40AF&color=fff`;
});

const userRoles = computed(() => {
    const roles = page.props.auth?.user?.roles || [];
    return roles.map(r => r.nombre);
});

const isAdmin = computed(() => {
    return userRoles.value.includes('administrador');
});

function toggleUserMenu() {
    showUserMenu.value = !showUserMenu.value;
    showMobileMenu.value = false;
    showSoporteMenu.value = false;
}

function toggleMobileMenu() {
    showMobileMenu.value = !showMobileMenu.value;
    showUserMenu.value = false;
    showSoporteMenu.value = false;
}

function toggleSoporteMenu() {
    showSoporteMenu.value = !showSoporteMenu.value;
    showUserMenu.value = false;
}

function logout() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        fetch('/simulador/limpiar-login', { method: 'POST' })
            .catch(() => {}) 
            .finally(() => {
                router.post('/logout');
            });
    }
}

function handleClickOutside(event) {
    const userMenuButton = event.target.closest('button');
    if (!userMenuButton) {
        showUserMenu.value = false;
        showSoporteMenu.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>