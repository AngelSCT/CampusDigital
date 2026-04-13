<template>
    <AuthLayout>
        <div class="space-y-6">

            <!-- ── Encabezado ─────────────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                        Mi Carrito
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Revisa y confirma tus productos antes de pagar
                    </p>
                </div>
                <span v-if="!loading && itemsActivos.length"
                      class="px-3 py-1 text-sm font-medium bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-full">
                    {{ itemsActivos.length }} {{ itemsActivos.length === 1 ? 'producto' : 'productos' }}
                </span>
            </div>

            <!-- ── Error global ───────────────────────────────────────────── -->
            <transition name="fade">
                <div v-if="errorGlobal"
                     class="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium">{{ errorGlobal }}</span>
                    <button @click="errorGlobal = null" class="ml-auto text-red-400 hover:text-red-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </transition>

            <!-- ── Éxito checkout ─────────────────────────────────────────── -->
            <transition name="fade">
                <div v-if="exitoCheckout"
                     class="flex items-center gap-3 p-4 bg-green-500/10 border border-green-500/20 rounded-xl text-green-400">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm font-medium">{{ exitoCheckout }}</span>
                </div>
            </transition>

            <!-- ════════════════════════════════════════════════════════════ -->
            <!-- SKELETON                                                     -->
            <!-- ════════════════════════════════════════════════════════════ -->
            <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Skeleton items -->
                <div class="lg:col-span-2 space-y-4">
                    <div v-for="n in 3" :key="n"
                         class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700/50 rounded-xl p-5 animate-pulse">
                        <div class="flex gap-4">
                            <div class="w-20 h-20 bg-slate-700 rounded-lg flex-shrink-0"></div>
                            <div class="flex-1 space-y-3">
                                <div class="h-4 bg-slate-700 rounded w-3/4"></div>
                                <div class="h-3 bg-slate-700 rounded w-1/3"></div>
                                <div class="h-3 bg-slate-700 rounded w-1/4"></div>
                                <div class="flex gap-2 mt-2">
                                    <div class="h-7 w-24 bg-slate-700 rounded-lg"></div>
                                    <div class="h-7 w-24 bg-slate-700 rounded-lg"></div>
                                </div>
                            </div>
                            <div class="text-right space-y-2">
                                <div class="h-5 w-16 bg-slate-700 rounded ml-auto"></div>
                                <div class="h-8 w-8 bg-slate-700 rounded ml-auto"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skeleton sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700/50 rounded-xl p-5 animate-pulse space-y-4">
                        <div class="h-5 bg-slate-700 rounded w-1/2"></div>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <div class="h-4 bg-slate-700 rounded w-1/3"></div>
                                <div class="h-4 bg-slate-700 rounded w-1/4"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="h-4 bg-slate-700 rounded w-1/4"></div>
                                <div class="h-4 bg-slate-700 rounded w-1/5"></div>
                            </div>
                            <div class="h-px bg-slate-700"></div>
                            <div class="flex justify-between">
                                <div class="h-5 bg-slate-700 rounded w-1/3"></div>
                                <div class="h-5 bg-slate-700 rounded w-1/4"></div>
                            </div>
                        </div>
                        <div class="h-12 bg-slate-700 rounded-xl"></div>
                        <div class="h-10 bg-slate-700 rounded-xl"></div>
                    </div>
                </div>
            </div>

            <!-- ════════════════════════════════════════════════════════════ -->
            <!-- CONTENIDO REAL                                               -->
            <!-- ════════════════════════════════════════════════════════════ -->
            <template v-else>

                <!-- Carrito vacío -->
                <div v-if="itemsActivos.length === 0 && itemsGuardados.length === 0"
                     class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-24 h-24 rounded-full bg-slate-800/80 border border-slate-700/50 flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Tu carrito está vacío</h3>
                    <p class="text-slate-400 text-sm mb-6">Explora la tienda y agrega productos para comenzar</p>
                    <a href="/dashboard"
                       class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition-colors duration-200">
                        Ir al Dashboard
                    </a>
                </div>

                <!-- Layout 2 columnas -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- ── Columna izquierda: items ──────────────────────── -->
                    <div class="lg:col-span-2 space-y-4">

                        <!-- ·· Items activos agrupados por tienda (Uber Eats style) ·· -->
                        <template v-if="itemsActivos.length"
                                  v-for="(tiendaItems, tiendaNombre) in itemsActivosPorTienda"
                                  :key="tiendaNombre">
                            <div class="space-y-3">
                                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    {{ tiendaNombre }}
                                </h2>

                                <transition-group name="list" tag="div" class="space-y-3">
                                    <div v-for="item in tiendaItems" :key="item.id"
                                         class="group bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700/40 hover:border-blue-500/30 rounded-xl p-5 transition-all duration-200">
                                        <div class="flex gap-4">

                                            <!-- Imagen producto -->
                                            <div class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-slate-700/50 border border-slate-600/30">
                                                <img v-if="item.producto?.imagen_url"
                                                     :src="item.producto.imagen_url"
                                                     :alt="item.producto.nombre"
                                                     class="w-full h-full object-cover">
                                                <div v-else class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Info -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div>
                                                        <h3 class="text-white font-medium text-sm leading-snug truncate">
                                                            {{ item.producto?.nombre }}
                                                        </h3>
                                                        <p class="text-xs text-slate-500 mt-0.5">
                                                            {{ item.producto?.categoria }}
                                                        </p>
                                                    </div>
                                                    <!-- Badge regalo -->
                                                    <span v-if="item.es_regalo"
                                                          class="flex-shrink-0 flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-pink-500/10 border border-pink-500/30 text-pink-400 rounded-full">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"/>
                                                        </svg>
                                                        Regalo
                                                    </span>
                                                </div>

                                                <!-- Precio unitario -->
                                                <p class="text-blue-400 font-semibold text-sm mt-1">
                                                    ${{ formatPrecio(item.producto?.precio) }}
                                                    <span class="text-slate-500 font-normal">/ ud.</span>
                                                </p>

                                                <!-- Controles -->
                                                <div class="flex flex-wrap items-center gap-2 mt-3">

                                                    <!-- Cantidad -->
                                                    <div class="flex items-center gap-1 bg-slate-700/50 rounded-lg border border-slate-600/40 px-1 py-0.5">
                                                        <button @click="cambiarCantidad(item, -1)"
                                                                :disabled="item.cantidad <= 1 || !!item._cargando"
                                                                class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors rounded">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                            </svg>
                                                        </button>
                                                        <span class="w-7 text-center text-sm font-medium text-white">{{ item.cantidad }}</span>
                                                        <button @click="cambiarCantidad(item, 1)"
                                                                :disabled="!!item._cargando"
                                                                class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors rounded">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <!-- Guardar para después -->
                                                    <button @click="guardarParaDespues(item)"
                                                            :disabled="!!item._cargando"
                                                            class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-slate-400 hover:text-cyan-400 bg-slate-700/30 hover:bg-cyan-500/10 border border-slate-600/30 hover:border-cyan-500/30 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                                        </svg>
                                                        Guardar para después
                                                    </button>

                                                    <!-- Enviar como regalo -->
                                                    <button @click="toggleRegalo(item)"
                                                            :disabled="!!item._cargando"
                                                            :class="item.es_regalo
                                                                ? 'text-pink-400 bg-pink-500/10 border-pink-500/30 hover:bg-pink-500/20'
                                                                : 'text-slate-400 hover:text-pink-400 bg-slate-700/30 hover:bg-pink-500/10 border-slate-600/30 hover:border-pink-500/30'"
                                                            class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium border rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                                                        </svg>
                                                        {{ item.es_regalo ? 'Es regalo ✓' : 'Enviar como regalo' }}
                                                    </button>

                                                </div>

                                                <!-- Validación de destinatario + mensaje (visible al marcar como regalo) -->
                                                <transition name="fade">
                                                    <div v-if="item.es_regalo" class="mt-2 space-y-2">

                                                        <!-- Inputs de destinatario -->
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <input type="text"
                                                                   v-model="item._matricula"
                                                                   autocomplete="off"
                                                                   spellcheck="false"
                                                                   placeholder="Matrícula del destinatario"
                                                                   maxlength="50"
                                                                   class="px-3 py-1.5 text-xs bg-slate-700/50 border border-slate-600/30 rounded-lg text-slate-300 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-400/60 transition-colors" />
                                                            <input type="text"
                                                                   v-model="item._primerNombre"
                                                                   @blur="validarDestinatarioItem(item)"
                                                                   autocomplete="off"
                                                                   spellcheck="false"
                                                                   placeholder="Primer nombre del destinatario"
                                                                   maxlength="100"
                                                                   class="px-3 py-1.5 text-xs bg-slate-700/50 border border-slate-600/30 rounded-lg text-slate-300 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-400/60 transition-colors" />
                                                        </div>

                                                        <!-- Estado de validación del destinatario -->
                                                        <div v-if="item._validandoDestinatario"
                                                             class="flex items-center gap-1.5 text-xs text-slate-400">
                                                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                            </svg>
                                                            Verificando datos...
                                                        </div>
                                                        <div v-else-if="item._destinatarioValido && item._matricula && item._primerNombre"
                                                             class="flex items-center gap-1.5 text-xs text-green-400">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            Destinatario verificado
                                                        </div>
                                                        <div v-else-if="item._errorDestinatario"
                                                             class="flex items-center gap-1.5 text-xs text-red-400">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            {{ item._errorDestinatario }}
                                                        </div>

                                                        <!-- Mensaje dedicatorio -->
                                                        <input type="text"
                                                               v-model="item.mensaje_dedicatorio"
                                                               @blur="guardarMensajeRegalo(item)"
                                                               placeholder="Escribe un mensaje dedicatorio (opcional)..."
                                                               maxlength="200"
                                                               class="w-full px-3 py-1.5 text-xs bg-slate-700/50 border border-pink-500/30 rounded-lg text-slate-300 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-pink-500/40 focus:border-pink-400/60 transition-colors" />
                                                    </div>
                                                </transition>

                                            </div>

                                            <!-- Subtotal + eliminar -->
                                            <div class="flex flex-col items-end justify-between flex-shrink-0">
                                                <p class="text-white font-bold text-sm">
                                                    ${{ formatPrecio(item.cantidad * item.producto?.precio) }}
                                                </p>
                                                <button @click="eliminarItem(item)"
                                                        :disabled="!!item._cargando"
                                                        class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed">
                                                    <svg v-if="!item._cargando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                        <path class="opacity-75" fill="currentColor"
                                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                    </svg>
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </transition-group>
                            </div>
                        </template>

                        <!-- ·· Guardados para después ·· -->
                        <div v-if="itemsGuardados.length" class="space-y-3 pt-2">
                            <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                Guardados para después ({{ itemsGuardados.length }})
                            </h2>

                            <transition-group name="list" tag="div" class="space-y-3">
                                <div v-for="item in itemsGuardados" :key="'saved-' + item.id"
                                     class="bg-slate-800/40 border border-slate-700/30 rounded-xl p-4 opacity-75 hover:opacity-100 transition-opacity duration-200">
                                    <div class="flex gap-4 items-center">
                                        <div class="w-14 h-14 flex-shrink-0 rounded-lg overflow-hidden bg-slate-700/50 border border-slate-600/30">
                                            <img v-if="item.producto?.imagen_url"
                                                 :src="item.producto.imagen_url"
                                                 :alt="item.producto.nombre"
                                                 class="w-full h-full object-cover">
                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-slate-300 font-medium text-sm truncate">{{ item.producto?.nombre }}</h3>
                                            <p class="text-slate-500 text-xs mt-0.5">{{ item.producto?.categoria }}</p>
                                            <p class="text-blue-400 text-sm font-semibold mt-1">
                                                ${{ formatPrecio(item.producto?.precio) }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <button @click="moverAlCarrito(item)"
                                                    :disabled="!!item._cargando"
                                                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-cyan-400 bg-cyan-500/10 border border-cyan-500/30 hover:bg-cyan-500/20 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                Mover al carrito
                                            </button>
                                            <button @click="eliminarItem(item)"
                                                    :disabled="!!item._cargando"
                                                    class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200 disabled:opacity-40">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </transition-group>
                        </div>

                    </div>

                    <!-- ── Sidebar: Resumen de pedido ──────────────────────── -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-6 z-0 space-y-4">

                            <!-- Desglose de totales -->
                            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700/40 rounded-xl p-5">
                                <h3 class="text-white font-semibold text-base mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Resumen del pedido
                                </h3>

                                <div class="space-y-3 text-sm">
                                    <!-- Subtotal -->
                                    <div class="flex justify-between text-slate-400">
                                        <span>Subtotal ({{ totalItems }} art.)</span>
                                        <span class="text-white font-medium">${{ formatPrecio(subtotal) }}</span>
                                    </div>

                                    <!-- Items con regalo -->
                                    <div v-if="itemsRegalo > 0" class="flex justify-between text-pink-400">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"/>
                                            </svg>
                                            {{ itemsRegalo }} como regalo
                                        </span>
                                        <span class="text-xs text-pink-400/70">Empaque incluido</span>
                                    </div>

                                    <!-- Envío -->
                                    <div class="flex justify-between text-slate-400">
                                        <span>Envío</span>
                                        <span class="text-green-400 font-medium">Gratis</span>
                                    </div>

                                    <!-- Cupón -->
                                    <div v-if="!cuponAplicado" class="pt-1">
                                        <div class="flex gap-2">
                                            <input type="text"
                                                   v-model="codigoCupon"
                                                   @keydown.enter="aplicarCupon"
                                                   placeholder="Código de cupón"
                                                   maxlength="20"
                                                   class="flex-1 px-3 py-1.5 text-xs bg-slate-700/50 border border-slate-600/30 rounded-lg text-slate-300 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500/40 focus:border-blue-400/60 transition-colors uppercase" />
                                            <button @click="aplicarCupon"
                                                    class="px-3 py-1.5 text-xs font-medium text-blue-400 bg-blue-500/10 border border-blue-500/30 hover:bg-blue-500/20 rounded-lg transition-all duration-200">
                                                Aplicar
                                            </button>
                                        </div>
                                        <p v-if="errorCupon" class="text-xs text-red-400 mt-1">{{ errorCupon }}</p>
                                    </div>
                                    <div v-else class="flex justify-between text-green-400 text-xs items-center">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M11 7h6M11 11h6M11 15h6"/>
                                            </svg>
                                            {{ codigoCupon.toUpperCase() }} (-10%)
                                            <button @click="cuponAplicado = false; codigoCupon = ''; errorCupon = null"
                                                    class="ml-1 text-green-400/60 hover:text-green-400 leading-none">✕</button>
                                        </span>
                                        <span>-${{ formatPrecio(descuento) }}</span>
                                    </div>

                                    <div class="border-t border-slate-700/50 pt-3 flex justify-between">
                                        <span class="text-white font-semibold">Total</span>
                                        <span class="text-white font-bold text-lg">${{ formatPrecio(totalFinal) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Saldo del monedero -->
                            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border rounded-xl p-5 transition-colors duration-300"
                                 :class="saldoSuficiente
                                    ? 'border-green-500/20'
                                    : 'border-rose-500/30'">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-medium text-slate-400 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Saldo del Monedero
                                    </span>
                                    <span class="text-white text-lg font-bold font-mono">
                                        ${{ formatPrecio(monedero.saldo_disponible) }}
                                    </span>
                                </div>

                                <!-- Barra de progreso de saldo -->
                                <div class="w-full bg-slate-700 rounded-full h-1.5 mb-2 overflow-hidden">
                                    <div class="h-1.5 rounded-full transition-all duration-500"
                                         :class="saldoSuficiente ? 'bg-emerald-500' : 'bg-rose-500'"
                                         :style="{ width: Math.min(100, (monedero.saldo_disponible / Math.max(totalFinal, 0.01)) * 100) + '%' }">
                                    </div>
                                </div>

                                <!-- Alerta saldo insuficiente -->
                                <p v-if="!saldoSuficiente && metodoPago === 'monedero' && itemsActivos.length"
                                   class="text-xs text-slate-300 mt-2 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Saldo insuficiente. Te faltan ${{ formatPrecio(totalFinal - monedero.saldo_disponible) }}
                                </p>
                                <p v-else-if="saldoSuficiente && metodoPago === 'monedero' && itemsActivos.length"
                                   class="text-xs text-green-400/80 mt-2 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Saldo suficiente para esta compra
                                </p>

                                <!-- Retenido -->
                                <p v-if="monedero.saldo_retenido > 0" class="text-xs text-slate-500 mt-1">
                                    ${{ formatPrecio(monedero.saldo_retenido) }} retenidos en proceso
                                </p>
                            </div>

                            <!-- Método de pago -->
                            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700/40 rounded-xl p-5">
                                <h4 class="text-sm font-medium text-slate-400 mb-3">Método de pago</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all duration-200"
                                           :class="metodoPago === 'monedero'
                                               ? 'bg-blue-500/10 border-blue-500/40 text-blue-400'
                                               : 'bg-slate-700/30 border-slate-600/30 text-slate-400 hover:border-slate-500/50'">
                                        <input type="radio" v-model="metodoPago" value="monedero" class="sr-only">
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                                             :class="metodoPago === 'monedero' ? 'border-blue-400' : 'border-slate-500'">
                                            <div v-if="metodoPago === 'monedero'" class="w-2 h-2 rounded-full bg-blue-400"></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-slate-200">Monedero universitario</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all duration-200"
                                           :class="metodoPago === 'efectivo'
                                               ? 'bg-green-500/10 border-green-500/40 text-green-400'
                                               : 'bg-slate-700/30 border-slate-600/30 text-slate-400 hover:border-slate-500/50'">
                                        <input type="radio" v-model="metodoPago" value="efectivo" class="sr-only">
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                                             :class="metodoPago === 'efectivo' ? 'border-green-400' : 'border-slate-500'">
                                            <div v-if="metodoPago === 'efectivo'" class="w-2 h-2 rounded-full bg-green-400"></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-slate-200">Efectivo en caja</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Botón checkout -->
                            <button @click="hacerCheckout"
                                    :disabled="checkoutDeshabilitado"
                                    class="w-full py-3.5 px-6 flex items-center justify-center gap-2 text-sm font-semibold rounded-xl transition-all duration-200"
                                    :class="checkoutDeshabilitado
                                        ? 'bg-slate-700 text-slate-500 cursor-not-allowed'
                                        : 'bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40'">
                                <svg v-if="procesandoCheckout" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ procesandoCheckout ? 'Procesando...' : 'Confirmar pedido' }}
                            </button>

                            <!-- Razón deshabilitado -->
                            <p v-if="itemsActivos.length === 0 && !procesandoCheckout"
                               class="text-xs text-slate-500 text-center -mt-2">
                                Agrega productos activos para continuar
                            </p>
                            <p v-else-if="metodoPago === 'monedero' && !saldoSuficiente && !procesandoCheckout"
                               class="text-xs text-white text-center -mt-2 flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Cambia el método de pago o recarga tu saldo
                            </p>

                        </div>
                    </div>
                </div>
            </template>

        </div>
    </AuthLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    carrito:  { type: Array,  default: () => [] },
    monedero: { type: Object, default: () => ({ saldo_disponible: 0, saldo_retenido: 0 }) },
    total:    { type: Number, default: 0 },
});

// ── Estado ────────────────────────────────────────────────────────────────────
const loading            = ref(false);  // datos vienen como props Inertia, no hay fetch inicial
const procesandoCheckout = ref(false);
const errorGlobal        = ref(null);
const exitoCheckout      = ref(null);
const metodoPago         = ref('monedero');

// Cupón
const codigoCupon  = ref('');
const cuponAplicado = ref(false);
const errorCupon   = ref(null);

function aplicarCupon() {
    if (codigoCupon.value.toUpperCase() === 'CAMPUS10') {
        cuponAplicado.value = true;
        errorCupon.value   = null;
    } else {
        cuponAplicado.value = false;
        errorCupon.value   = 'Cupón inválido o no reconocido.';
    }
}

// Inicializa con campos privados (_) para validación de destinatario
const carrito = ref(props.carrito.map(item => ({
    ...item,
    _matricula:             '',
    _primerNombre:          '',
    _destinatarioId:        item.destinatario_id ?? null,
    _destinatarioValido:    !!item.destinatario_id,
    _errorDestinatario:     null,
    _validandoDestinatario: false,
})));
const monedero = ref({ ...props.monedero });

// ── Reset al montar: evita que "Destinatario verificado" quede pegado ─────────
onMounted(() => {
    carrito.value.forEach(item => {
        // Si los campos de texto están vacíos, la validación no debe mostrarse
        if (!item._matricula && !item._primerNombre) {
            item._destinatarioValido = false;
            item._destinatarioId     = null;
            item._errorDestinatario  = null;
        }
    });
});

// ── Watch: limpia validación cuando el usuario edita los campos de destinatario
watch(
    carrito,
    (items) => {
        items.forEach(item => {
            if (!item._matricula || !item._primerNombre) {
                item._destinatarioValido = false;
                item._destinatarioId     = null;
                item._errorDestinatario  = null;
            }
        });
    },
    { deep: true }
);

// ── Computed ──────────────────────────────────────────────────────────────────
const itemsActivos   = computed(() => carrito.value.filter(i => !i.guardado_para_despues && !i.en_wishlist));
const itemsGuardados = computed(() => carrito.value.filter(i => i.guardado_para_despues || i.en_wishlist));

// Agrupa items activos por tienda (campo producto.tienda, default 'General')
const itemsActivosPorTienda = computed(() => {
    return itemsActivos.value.reduce((grupos, item) => {
        const tienda = item.producto?.tienda ?? 'General';
        if (!grupos[tienda]) grupos[tienda] = [];
        grupos[tienda].push(item);
        return grupos;
    }, {});
});

const totalItems  = computed(() => itemsActivos.value.reduce((s, i) => s + i.cantidad, 0));
const subtotal    = computed(() => itemsActivos.value.reduce((s, i) => s + i.cantidad * (i.producto?.precio ?? 0), 0));
const itemsRegalo = computed(() => itemsActivos.value.filter(i => i.es_regalo).length);

const descuento  = computed(() => cuponAplicado.value ? subtotal.value * 0.1 : 0);
const totalFinal = computed(() => subtotal.value - descuento.value);

const saldoSuficiente = computed(() => monedero.value.saldo_disponible >= totalFinal.value);

const checkoutDeshabilitado = computed(() =>
    procesandoCheckout.value
    || itemsActivos.value.length === 0
    || (metodoPago.value === 'monedero' && !saldoSuficiente.value)
);

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatPrecio(val) {
    return Number(val ?? 0).toFixed(2);
}

function csrfToken() {
    return document.cookie.split('; ')
        .find(r => r.startsWith('XSRF-TOKEN='))
        ?.split('=')[1]
        ? decodeURIComponent(document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN=')).split('=')[1])
        : document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-XSRF-TOKEN': csrfToken(),
            ...options.headers,
        },
        credentials: 'same-origin',
        ...options,
    });
    if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.mensaje || body.message || `Error ${res.status}`);
    }
    return res.json();
}

// ── Refresco post-checkout ────────────────────────────────────────────────────
// No se llama al montar (los datos vienen como props Inertia).
// Se usa para limpiar el estado local tras confirmar el pedido.
async function refrescarCarrito() {
    try {
        const data = await apiFetch('/api/carrito/');
        carrito.value  = (data.carrito ?? []).map(item => ({
            ...item,
            _matricula:             '',
            _primerNombre:          '',
            _destinatarioId:        item.destinatario_id ?? null,
            _destinatarioValido:    !!item.destinatario_id,
            _errorDestinatario:     null,
            _validandoDestinatario: false,
        }));
        monedero.value = data.monedero ?? { saldo_disponible: 0, saldo_retenido: 0 };
    } catch {
        // silencioso — el checkout ya fue exitoso
    }
}

// ── Acciones de ítem ──────────────────────────────────────────────────────────
async function cambiarCantidad(item, delta) {
    const nueva = item.cantidad + delta;
    if (nueva < 1) return;
    item._cargando = true;
    try {
        await apiFetch('/api/carrito/', {
            method: 'POST',
            body: JSON.stringify({ producto_id: item.producto_id, cantidad: delta }),
        });
        item.cantidad = nueva;
    } catch (e) {
        errorGlobal.value = e.message;
    } finally {
        item._cargando = false;
    }
}

async function guardarParaDespues(item) {
    item._cargando = true;
    errorGlobal.value = null;
    try {
        const data = await apiFetch(`/carrito/${item.id}/guardar`, { method: 'PATCH' });
        // Ruta web con sesión — sin "Unauthenticated"
        item.guardado_para_despues = true;
        item.en_wishlist           = false;
        if (data.item) Object.assign(item, data.item);
    } catch (e) {
        errorGlobal.value = e.message;
    } finally {
        item._cargando = false;
    }
}

async function moverAlCarrito(item) {
    item._cargando = true;
    errorGlobal.value = null;
    try {
        const data = await apiFetch(`/carrito/${item.id}/mover-al-carrito`, { method: 'PATCH' });
        item.guardado_para_despues = false;
        item.en_wishlist           = false;
        // fresh('producto') trae el precio actual desde la BD
        if (data.item) Object.assign(item, data.item);
    } catch (e) {
        errorGlobal.value = e.message
        // Si el producto fue eliminado del catálogo, retirarlo del carrito local
        if (e.message.includes('ya no está disponible')) {
            carrito.value = carrito.value.filter(i => i.id !== item.id)
        }
    } finally {
        item._cargando = false;
    }
}

async function toggleRegalo(item) {
    item._cargando = true;
    errorGlobal.value = null;
    const nuevo = !item.es_regalo;
    try {
        const data = await apiFetch(`/carrito/${item.id}/regalo`, {
            method: 'PATCH',
            body: JSON.stringify({
                es_regalo:           nuevo,
                mensaje_dedicatorio: item.mensaje_dedicatorio ?? null,
            }),
        });
        item.es_regalo           = data.es_regalo;
        item.regalo_hash         = data.regalo_hash ?? null;
        item.mensaje_dedicatorio = data.mensaje_dedicatorio ?? null;
    } catch (e) {
        errorGlobal.value = e.message;
    } finally {
        item._cargando = false;
    }
}

// Valida matrícula + primer nombre contra el servidor (Módulo 4.10)
// y persiste el destinatario_id si es correcto.
async function validarDestinatarioItem(item) {
    if (!item._matricula || !item._primerNombre) return;
    item._validandoDestinatario = true;
    item._errorDestinatario     = null;
    item._destinatarioValido    = false;
    try {
        const data = await apiFetch('/carrito/validar-destinatario', {
            method: 'POST',
            body: JSON.stringify({
                matricula:     item._matricula,
                primer_nombre: item._primerNombre,
            }),
        });
        item._destinatarioId     = data.destinatario_id;
        item._destinatarioValido = true;
        // Persiste automáticamente el destinatario validado en el ítem
        await apiFetch(`/carrito/${item.id}/regalo`, {
            method: 'PATCH',
            body: JSON.stringify({
                es_regalo:           true,
                mensaje_dedicatorio: item.mensaje_dedicatorio ?? null,
                destinatario_id:     data.destinatario_id,
            }),
        });
    } catch (e) {
        item._errorDestinatario  = e.message;
        item._destinatarioValido = false;
        item._destinatarioId     = null;
    } finally {
        item._validandoDestinatario = false;
    }
}

// Persiste el mensaje dedicatorio al salir del input (onblur)
async function guardarMensajeRegalo(item) {
    if (!item.es_regalo) return;
    try {
        await apiFetch(`/carrito/${item.id}/regalo`, {
            method: 'PATCH',
            body: JSON.stringify({
                es_regalo:           true,
                mensaje_dedicatorio: item.mensaje_dedicatorio ?? null,
                destinatario_id:     item._destinatarioId ?? null,
            }),
        });
    } catch (e) {
        errorGlobal.value = e.message;
    }
}

async function eliminarItem(item) {
    // Eliminar visualmente de inmediato (optimista)
    const index = carrito.value.findIndex(i => i.id === item.id);
    if (index === -1) return;
    item._cargando = true;
    // Mover a guardado como proxy de "eliminar" usando wishlist toggle si no hay DELETE endpoint
    // El backend limpiará mediante limpiarInactivos; aquí lo removemos localmente
    carrito.value.splice(index, 1);
}

// ── Checkout ──────────────────────────────────────────────────────────────────
// Usa router.post de Inertia para que el token CSRF se envíe automáticamente
// y el backend pueda redirigir a /carrito/exito tras confirmar el pedido.
function hacerCheckout() {
    if (checkoutDeshabilitado.value) return;
    procesandoCheckout.value = true;
    errorGlobal.value = null;

    router.post('/carrito/confirmar', {
        metodo_pago:  metodoPago.value,
        cupon_codigo: cuponAplicado.value ? codigoCupon.value.toUpperCase() : null,
    }, {
        preserveState: true,   // conserva los campos de destinatario si hay error
        onError: (errors) => {
            // errors.mensaje → ValidationException (rate limiting, etc.)
            // errors.error   → back()->withErrors(['error' => ...])
            errorGlobal.value = errors.mensaje ?? errors.error ?? 'Error al procesar el pedido.';
            procesandoCheckout.value = false;
        },
        onFinish: () => {
            // Solo se llega aquí si hubo un error (en éxito Inertia redirige a /carrito/exito)
            procesandoCheckout.value = false;
        },
    });
}

</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }

.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from                       { opacity: 0; transform: translateY(-8px); }
.list-leave-to                         { opacity: 0; transform: translateX(12px); }
.list-leave-active                     { position: absolute; width: 100%; }
</style>
