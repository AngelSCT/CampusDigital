<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a
                        :href="route('admin.tickets.index')"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-700/50 border border-slate-600 text-slate-300 hover:text-white hover:bg-slate-600/50 transition-all duration-200"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </a>
                    <div>
                        <h1
                            class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent"
                        >
                            Ticket #{{ ticket.id_ticket }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-400">
                            Solicitado por
                            {{ fullName(ticket.usuario_solicitante) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        :href="`/admin/tickets/${ticket.id_ticket}/pdf`"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 border border-emerald-500/30 shadow-lg shadow-emerald-500/20 text-sm font-medium rounded-lg text-emerald-400 hover:text-white hover:bg-emerald-600/80 hover:border-emerald-600 transition-all duration-200"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        Descargar PDF
                    </a>
                    <button
                        @click="openEditModal"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-lg shadow-blue-500/30 text-sm font-medium rounded-lg text-white bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 transition-all duration-200"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        Editar
                    </button>
                    <button
                        @click="handleDelete"
                        class="inline-flex items-center px-4 py-2 border border-red-500/30 shadow-lg shadow-red-500/20 text-sm font-medium rounded-lg text-red-400 hover:text-white hover:bg-red-600/80 hover:border-red-600 transition-all duration-200"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        Eliminar
                    </button>
                </div>
            </div>

            <!-- Tarjetas de resumen -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Estado -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 flex items-center gap-3"
                >
                    <div
                        class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                        :class="estadoBg(ticket.estado)"
                    >
                        <svg
                            class="w-5 h-5"
                            :class="estadoIconColor(ticket.estado)"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-xs text-slate-400 uppercase tracking-wider"
                        >
                            Estado
                        </p>
                        <p class="text-sm font-semibold text-white mt-0.5">
                            {{ ticket.estado }}
                        </p>
                    </div>
                </div>

                <!-- Prioridad -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 flex items-center gap-3"
                >
                    <div
                        class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                        :class="prioridadBg(ticket.prioridad)"
                    >
                        <svg
                            class="w-5 h-5"
                            :class="prioridadIconColor(ticket.prioridad)"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-xs text-slate-400 uppercase tracking-wider"
                        >
                            Prioridad
                        </p>
                        <p class="text-sm font-semibold text-white mt-0.5">
                            {{ ticket.prioridad }}
                        </p>
                    </div>
                </div>

                <!-- Categoría -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 flex items-center gap-3"
                >
                    <div
                        class="w-10 h-10 rounded-lg bg-purple-500/20 border border-purple-500/30 flex items-center justify-center shrink-0"
                    >
                        <svg
                            class="w-5 h-5 text-purple-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-xs text-slate-400 uppercase tracking-wider"
                        >
                            Categoría
                        </p>
                        <p class="text-sm font-semibold text-white mt-0.5">
                            {{ ticket.categoria?.nombre_categoria ?? "—" }}
                        </p>
                    </div>
                </div>

                <!-- Área -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 flex items-center gap-3"
                >
                    <div
                        class="w-10 h-10 rounded-lg bg-teal-500/20 border border-teal-500/30 flex items-center justify-center shrink-0"
                    >
                        <svg
                            class="w-5 h-5 text-teal-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-xs text-slate-400 uppercase tracking-wider"
                        >
                            Área
                        </p>
                        <p class="text-sm font-semibold text-white mt-0.5">
                            {{ ticket.categoria?.area?.name_area ?? "—" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detalle completo -->
            <div
                class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 overflow-hidden"
            >
                <div
                    class="px-6 py-4 border-b border-slate-700 flex items-center gap-3"
                >
                    <div
                        class="w-10 h-10 rounded-lg bg-blue-500/20 border border-blue-500/30 flex items-center justify-center"
                    >
                        <svg
                            class="w-5 h-5 text-blue-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-white">
                        Información del Ticket
                    </h2>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >ID Ticket</span
                        >
                        <p class="text-lg font-semibold text-white">
                            #{{ ticket.id_ticket }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Solicitante</span
                        >
                        <div class="flex items-center gap-2 mt-1">
                            <div
                                class="w-7 h-7 rounded-full bg-blue-500/30 flex items-center justify-center text-xs font-bold text-blue-300 shrink-0"
                            >
                                {{ initials(ticket.usuario_solicitante) }}
                            </div>
                            <p class="text-sm font-semibold text-white">
                                {{ fullName(ticket.usuario_solicitante) }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Categoría</span
                        >
                        <p class="text-sm text-white">
                            {{ ticket.categoria?.nombre_categoria ?? "—" }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Área</span
                        >
                        <p class="text-sm text-white">
                            {{ ticket.categoria?.area?.name_area ?? "—" }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Equipo Relacionado</span
                        >
                        <p class="text-sm text-white">
                            {{
                                ticket.equipo?.nombre_equipo ??
                                "Sin equipo asignado"
                            }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Fecha de Creación</span
                        >
                        <p class="text-sm text-white">
                            {{ formatDate(ticket.fecha_creacion) }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Estado</span
                        >
                        <span
                            class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border mt-1"
                            :class="estadoClass(ticket.estado)"
                        >
                            {{ ticket.estado }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Prioridad</span
                        >
                        <span
                            class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border mt-1"
                            :class="prioridadClass(ticket.prioridad)"
                        >
                            {{ ticket.prioridad }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Última Actualización</span
                        >
                        <p class="text-sm text-white">
                            {{ formatDate(ticket.updated_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sección de Gastos (Insumos) -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-purple-500/10 rounded-xl border border-purple-500/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-500/20 border border-purple-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-white">Insumos y Gastos</h2>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Formulario para agregar Insumo -->
                    <form v-if="ticket.estado_pago === 'sin_cobro'" @submit.prevent="agregarGasto" class="flex flex-col sm:flex-row gap-4 items-end bg-slate-800/50 p-4 rounded-lg border border-slate-700">
                        <div class="flex-1 w-full">
                            <label class="block text-sm font-medium text-slate-300 mb-1">Insumo</label>
                            <select v-model="formGasto.id_insumo" required class="block w-full rounded-lg bg-slate-900 border-slate-600 text-white focus:border-purple-500 focus:ring-purple-500">
                                <option value="" disabled>Selecciona un insumo...</option>
                                <option v-for="ins in insumos" :key="ins.id_insumo" :value="ins.id_insumo">
                                    {{ ins.nombre_insumo }} (${{ ins.precio_unitario }})
                                </option>
                            </select>
                        </div>
                        <div class="w-full sm:w-32">
                            <label class="block text-sm font-medium text-slate-300 mb-1">Cantidad</label>
                            <input v-model.number="formGasto.cantidad" type="number" min="1" required class="block w-full rounded-lg bg-slate-900 border-slate-600 text-white focus:border-purple-500 focus:ring-purple-500">
                        </div>
                        <button type="submit" :disabled="processingGasto" class="w-full sm:w-auto px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg disabled:opacity-50 transition-colors">
                            Agregar
                        </button>
                    </form>

                    <!-- Lista de Insumos Agregados -->
                    <div v-if="ticket.gastos && ticket.gastos.length > 0" class="overflow-x-auto rounded-lg border border-slate-700">
                        <table class="min-w-full divide-y divide-slate-700">
                            <thead class="bg-slate-800/50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Insumo</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-slate-300 uppercase tracking-wider">P. Unitario</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-slate-300 uppercase tracking-wider">Cantidad</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-slate-300 uppercase tracking-wider">Subtotal</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-slate-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700 bg-slate-800/20">
                                <tr v-for="gasto in ticket.gastos" :key="gasto.id_gasto">
                                    <td class="px-4 py-3 text-sm text-white">{{ gasto.insumo?.nombre_insumo }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-300 text-right">${{ gasto.insumo?.precio_unitario }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-300 text-right">{{ gasto.cantidad }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-white text-right">
                                        ${{ (parseFloat(gasto.insumo?.precio_unitario || 0) * gasto.cantidad).toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <button v-if="ticket.estado_pago === 'sin_cobro'" @click="eliminarGasto(gasto.id_gasto)" class="text-red-400 hover:text-red-300 transition-colors">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-6 text-slate-400 text-sm italic">
                        No hay insumos o gastos asignados a este ticket.
                    </div>
                </div>
            </div>

            <!-- Sección de Cobro -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-emerald-500/10 rounded-xl border border-emerald-500/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-white">Estado de Cobro</h2>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Costo Total</p>
                        <p class="text-3xl font-bold text-white">${{ ticket.costo_total ?? '0.00' }}</p>
                        
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-slate-400">Estado de pago:</span>
                                <span class="font-medium" :class="{
                                    'text-slate-300': ticket.estado_pago === 'sin_cobro',
                                    'text-yellow-400': ticket.estado_pago === 'pendiente_pago',
                                    'text-emerald-400': ticket.estado_pago === 'pagado',
                                    'text-red-400': ticket.estado_pago === 'cancelado'
                                }">
                                    {{ (ticket.estado_pago || 'sin_cobro').toUpperCase().replace('_', ' ') }}
                                </span>
                            </div>
                            <div v-if="ticket.carrito_uuid" class="flex items-center gap-2 text-sm">
                                <span class="text-slate-400">UUID de Carrito:</span>
                                <span class="font-mono text-xs text-blue-300">{{ ticket.carrito_uuid }}</span>
                            </div>
                            <div v-if="ticket.fecha_pago" class="flex items-center gap-2 text-sm">
                                <span class="text-slate-400">Fecha de pago:</span>
                                <span class="text-emerald-300">{{ formatDate(ticket.fecha_pago) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 justify-center md:items-end">
                        <button
                            v-if="ticket.estado_pago === 'sin_cobro' && parseFloat(ticket.costo_total) > 0"
                            @click="generarCobro"
                            :disabled="processing"
                            class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg shadow-emerald-500/30 text-sm font-medium rounded-lg text-white bg-gradient-to-br from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 disabled:opacity-50 transition-all duration-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ processing ? 'Generando...' : 'Generar Cobro en Carrito' }}
                        </button>

                        <button
                            v-if="ticket.estado_pago === 'pendiente_pago'"
                            @click="confirmarPago"
                            :disabled="processing"
                            class="inline-flex items-center px-6 py-3 border border-emerald-500/30 shadow-lg shadow-emerald-500/20 text-sm font-medium rounded-lg text-emerald-400 hover:text-white hover:bg-emerald-600/80 hover:border-emerald-600 disabled:opacity-50 transition-all duration-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ processing ? 'Confirmando...' : 'Confirmar Pago Manualmente' }}
                        </button>
                        
                        <div v-if="ticket.estado_pago === 'pagado'" class="inline-flex items-center px-4 py-2 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Pago Confirmado
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                        @click="closeModal"
                    ></div>

                    <div
                        class="relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-2xl shadow-blue-500/20 w-full max-w-lg"
                    >
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-700"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Editar Ticket #{{ ticket.id_ticket }}
                            </h3>
                            <button
                                @click="closeModal"
                                class="text-slate-400 hover:text-white transition-colors duration-200"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        <form
                            @submit.prevent="submitForm"
                            class="p-6 space-y-4"
                        >
                            <!-- Categoría -->
                            <div>
                                <label
                                    for="id_categoria"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Categoría
                                    <span class="text-red-400">*</span>
                                </label>
                                <select
                                    v-model="form.id_categoria"
                                    id="id_categoria"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.id_categoria
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                >
                                    <option
                                        v-for="cat in categorias"
                                        :key="cat.id_categoria"
                                        :value="cat.id_categoria"
                                    >
                                        {{ cat.nombre_categoria }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.id_categoria"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.id_categoria }}
                                </p>
                            </div>

                            <!-- Equipo -->
                            <div>
                                <label
                                    for="id_equipo"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Equipo Relacionado
                                    <span class="text-slate-500 text-xs"
                                        >(opcional)</span
                                    >
                                </label>
                                <select
                                    v-model="form.id_equipo"
                                    id="id_equipo"
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.id_equipo
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                >
                                    <option :value="null">
                                        Sin equipo asignado
                                    </option>
                                    <option
                                        v-for="eq in equipos"
                                        :key="eq.id_equipo"
                                        :value="eq.id_equipo"
                                    >
                                        {{ eq.nombre_equipo }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.id_equipo"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.id_equipo }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Estado -->
                                <div>
                                    <label
                                        for="estado"
                                        class="block text-sm font-medium text-white mb-2"
                                    >
                                        Estado
                                        <span class="text-red-400">*</span>
                                    </label>
                                    <select
                                        v-model="form.estado"
                                        id="estado"
                                        required
                                        class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                        :class="
                                            errors.estado
                                                ? 'border-red-500'
                                                : 'border-slate-600 focus:border-blue-500'
                                        "
                                    >
                                        <option
                                            v-for="e in estadosDisponibles"
                                            :key="e"
                                            :value="e"
                                        >
                                            {{ e }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.estado"
                                        class="mt-1 text-sm text-red-400"
                                    >
                                        {{ errors.estado }}
                                    </p>
                                </div>

                                <!-- Prioridad -->
                                <div>
                                    <label
                                        for="prioridad"
                                        class="block text-sm font-medium text-white mb-2"
                                    >
                                        Prioridad
                                        <span class="text-red-400">*</span>
                                    </label>
                                    <select
                                        v-model="form.prioridad"
                                        id="prioridad"
                                        required
                                        class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                        :class="
                                            errors.prioridad
                                                ? 'border-red-500'
                                                : 'border-slate-600 focus:border-blue-500'
                                        "
                                    >
                                        <option
                                            v-for="p in prioridadesDisponibles"
                                            :key="p"
                                            :value="p"
                                        >
                                            {{ p }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.prioridad"
                                        class="mt-1 text-sm text-red-400"
                                    >
                                        {{ errors.prioridad }}
                                    </p>
                                </div>
                            </div>

                            <!-- Fecha Creación -->
                            <div>
                                <label
                                    for="fecha_creacion"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Fecha de Creación
                                </label>
                                <input
                                    v-model="form.fecha_creacion"
                                    id="fecha_creacion"
                                    type="date"
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.fecha_creacion
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                />
                                <p
                                    v-if="errors.fecha_creacion"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.fecha_creacion }}
                                </p>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="px-4 py-2 bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg text-sm font-medium text-white hover:from-blue-500 hover:to-blue-600 shadow-lg shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    {{
                                        processing
                                            ? "Guardando..."
                                            : "Actualizar"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps({
    ticket: Object,
    categorias: Array,
    equipos: Array,
    insumos: Array,
});

const estadosDisponibles = [
    "Abierto",
    "En progreso",
    "Resuelto",
    "Cerrado",
    "Cancelado",
];
const prioridadesDisponibles = ["Baja", "Media", "Alta", "Crítica"];

const showModal = ref(false);
const processing = ref(false);
const errors = ref({});

const form = reactive({
    id_usuario_solicitante: "",
    id_categoria: "",
    id_equipo: null,
    estado: "",
    prioridad: "",
    fecha_creacion: "",
});

const formGasto = reactive({
    id_insumo: "",
    cantidad: 1,
});
const processingGasto = ref(false);

function agregarGasto() {
    processingGasto.value = true;
    router.post(route('admin.gastos-ticket.store'), {
        id_ticket: props.ticket.id_ticket,
        id_insumo: formGasto.id_insumo,
        cantidad: formGasto.cantidad
    }, {
        onSuccess: () => {
            formGasto.id_insumo = "";
            formGasto.cantidad = 1;
        },
        onFinish: () => processingGasto.value = false
    });
}

function eliminarGasto(idGasto) {
    if (confirm('¿Eliminar este insumo del ticket?')) {
        router.delete(route('admin.gastos-ticket.destroy', idGasto));
    }
}

function openEditModal() {
    form.id_usuario_solicitante = props.ticket.id_usuario_solicitante;
    form.id_categoria = props.ticket.id_categoria;
    form.id_equipo = props.ticket.id_equipo;
    form.estado = props.ticket.estado;
    form.prioridad = props.ticket.prioridad;
    form.fecha_creacion = props.ticket.fecha_creacion
        ? props.ticket.fecha_creacion.substring(0, 10)
        : "";
    errors.value = {};
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function submitForm() {
    processing.value = true;
    errors.value = {};

    router.put(route("admin.tickets.update", props.ticket.id_ticket), form, {
        onSuccess: () => {
            showModal.value = false;
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
}

function handleDelete() {
    if (
        confirm(
            `¿Estás seguro de eliminar el ticket #${props.ticket.id_ticket}? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(route("admin.tickets.destroy", props.ticket.id_ticket));
    }
}

function generarCobro() {
    if (confirm('¿Estás seguro de enviar este ticket al Carrito para cobrarlo?')) {
        processing.value = true;
        router.post(route('admin.tickets.generar-cobro', props.ticket.id_ticket), {}, {
            onFinish: () => processing.value = false
        });
    }
}

function confirmarPago() {
    if (confirm('¿Confirmar manualmente que este ticket ha sido pagado?')) {
        processing.value = true;
        router.post(route('admin.tickets.confirmar-pago', props.ticket.id_ticket), {}, {
            onFinish: () => processing.value = false
        });
    }
}

// Helpers
function fullName(usuario) {
    if (!usuario) return "—";
    return `${usuario.nombre} ${usuario.apellido}`;
}

function initials(usuario) {
    if (!usuario) return "?";
    return `${usuario.nombre?.charAt(0) ?? ""}${usuario.apellido?.charAt(0) ?? ""}`.toUpperCase();
}

function estadoClass(estado) {
    const map = {
        Abierto: "bg-blue-500/20 text-blue-400 border-blue-500/30",
        "En progreso": "bg-yellow-500/20 text-yellow-400 border-yellow-500/30",
        Resuelto: "bg-green-500/20 text-green-400 border-green-500/30",
        Cerrado: "bg-slate-500/20 text-slate-400 border-slate-500/30",
        Cancelado: "bg-red-500/20 text-red-400 border-red-500/30",
    };
    return map[estado] ?? "bg-slate-500/20 text-slate-400 border-slate-500/30";
}

function estadoBg(estado) {
    const map = {
        Abierto: "bg-blue-500/20 border border-blue-500/30",
        "En progreso": "bg-yellow-500/20 border border-yellow-500/30",
        Resuelto: "bg-green-500/20 border border-green-500/30",
        Cerrado: "bg-slate-500/20 border border-slate-500/30",
        Cancelado: "bg-red-500/20 border border-red-500/30",
    };
    return map[estado] ?? "bg-slate-500/20 border border-slate-500/30";
}

function estadoIconColor(estado) {
    const map = {
        Abierto: "text-blue-400",
        "En progreso": "text-yellow-400",
        Resuelto: "text-green-400",
        Cerrado: "text-slate-400",
        Cancelado: "text-red-400",
    };
    return map[estado] ?? "text-slate-400";
}

function prioridadClass(prioridad) {
    const map = {
        Baja: "bg-slate-500/20 text-slate-400 border-slate-500/30",
        Media: "bg-blue-500/20 text-blue-400 border-blue-500/30",
        Alta: "bg-orange-500/20 text-orange-400 border-orange-500/30",
        Crítica: "bg-red-500/20 text-red-400 border-red-500/30",
    };
    return (
        map[prioridad] ?? "bg-slate-500/20 text-slate-400 border-slate-500/30"
    );
}

function prioridadBg(prioridad) {
    const map = {
        Baja: "bg-slate-500/20 border border-slate-500/30",
        Media: "bg-blue-500/20 border border-blue-500/30",
        Alta: "bg-orange-500/20 border border-orange-500/30",
        Crítica: "bg-red-500/20 border border-red-500/30",
    };
    return map[prioridad] ?? "bg-slate-500/20 border border-slate-500/30";
}

function prioridadIconColor(prioridad) {
    const map = {
        Baja: "text-slate-400",
        Media: "text-blue-400",
        Alta: "text-orange-400",
        Crítica: "text-red-400",
    };
    return map[prioridad] ?? "text-slate-400";
}

function formatDate(date) {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("es-MX", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}
</script>
