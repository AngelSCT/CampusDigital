<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex justify-between items-center">
                <div>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent"
                    >
                        Gestión de Tickets
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Administra las solicitudes de soporte del sistema
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-lg shadow-blue-500/30 text-sm font-medium rounded-lg text-white bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 transition-all duration-200"
                >
                    <svg
                        class="w-5 h-5 mr-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Nuevo Ticket
                </button>
            </div>

            <!-- Filtros -->
            <div
                class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-4"
            >
                <form
                    @submit.prevent="applyFilters"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                >
                    <div>
                        <label class="block text-sm font-medium text-white mb-2"
                            >Buscar usuario</label
                        >
                        <input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Nombre o apellido..."
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2"
                            >Estado</label
                        >
                        <select
                            v-model="filterForm.estado"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todos</option>
                            <option
                                v-for="e in estadosDisponibles"
                                :key="e"
                                :value="e"
                            >
                                {{ e }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2"
                            >Prioridad</label
                        >
                        <select
                            v-model="filterForm.prioridad"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todas</option>
                            <option
                                v-for="p in prioridadesDisponibles"
                                :key="p"
                                :value="p"
                            >
                                {{ p }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2"
                            >Categoría</label
                        >
                        <select
                            v-model="filterForm.categoria"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todas</option>
                            <option
                                v-for="cat in categorias"
                                :key="cat.id_categoria"
                                :value="cat.id_categoria"
                            >
                                {{ cat.nombre_categoria }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2"
                            >Desde</label
                        >
                        <input
                            v-model="filterForm.desde"
                            type="date"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2"
                            >Hasta</label
                        >
                        <input
                            v-model="filterForm.hasta"
                            type="date"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        />
                    </div>
                    <div
                        class="sm:col-span-2 lg:col-span-3 flex justify-end gap-2"
                    >
                        <button
                            type="button"
                            @click="clearFilters"
                            class="px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200"
                        >
                            Limpiar
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg text-sm font-medium text-white hover:from-blue-500 hover:to-blue-600 shadow-lg shadow-blue-500/30 transition-all duration-200"
                        >
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div
                class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    ID
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Solicitante
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Categoría
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Prioridad
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Fecha Creación
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-slate-800/30 divide-y divide-slate-700"
                        >
                            <tr
                                v-for="ticket in tickets.data"
                                :key="ticket.id_ticket"
                                class="hover:bg-slate-700/30 transition-colors duration-150"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-white"
                                >
                                    #{{ ticket.id_ticket }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-full bg-blue-500/30 flex items-center justify-center text-xs font-bold text-blue-300 shrink-0"
                                        >
                                            {{
                                                initials(
                                                    ticket.usuario_solicitante,
                                                )
                                            }}
                                        </div>
                                        <span class="text-sm text-white">
                                            {{
                                                fullName(
                                                    ticket.usuario_solicitante,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-300"
                                >
                                    {{
                                        ticket.categoria?.nombre_categoria ??
                                        "—"
                                    }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                        :class="estadoClass(ticket.estado)"
                                    >
                                        {{ ticket.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                        :class="
                                            prioridadClass(ticket.prioridad)
                                        "
                                    >
                                        {{ ticket.prioridad }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-300"
                                >
                                    {{ formatDate(ticket.fecha_creacion) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <div class="flex justify-end gap-2">
                                        <a
                                            :href="
                                                route(
                                                    'admin.tickets.show',
                                                    ticket.id_ticket,
                                                )
                                            "
                                            class="text-slate-400 hover:text-white transition-colors duration-200"
                                            title="Ver detalle"
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
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                        </a>
                                        <button
                                            @click="openEditModal(ticket)"
                                            class="text-blue-400 hover:text-blue-300 transition-colors duration-200"
                                            title="Editar"
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
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            @click="confirmDelete(ticket)"
                                            class="text-red-400 hover:text-red-300 transition-colors duration-200"
                                            title="Eliminar"
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
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="tickets.data.length > 0"
                    class="bg-slate-900/50 px-4 py-3 border-t border-slate-700 sm:px-6"
                >
                    <div
                        class="hidden sm:flex sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-slate-300">
                            Mostrando
                            <span class="font-medium text-white">{{
                                tickets.from
                            }}</span>
                            a
                            <span class="font-medium text-white">{{
                                tickets.to
                            }}</span>
                            de
                            <span class="font-medium text-white">{{
                                tickets.total
                            }}</span>
                            resultados
                        </p>
                        <nav
                            class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px"
                        >
                            <a
                                v-for="link in tickets.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors duration-200',
                                    link.active
                                        ? 'z-10 bg-gradient-to-br from-blue-600 to-blue-700 border-blue-500 text-white shadow-lg shadow-blue-500/30'
                                        : 'bg-slate-700/50 border-slate-600 text-slate-300 hover:bg-slate-600/50',
                                    !link.url
                                        ? 'opacity-40 cursor-not-allowed pointer-events-none'
                                        : '',
                                ]"
                            ></a>
                        </nav>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div v-if="tickets.data.length === 0" class="text-center py-12">
                    <div
                        class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-700/50 flex items-center justify-center"
                    >
                        <svg
                            class="h-8 w-8 text-slate-400"
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
                    <h3 class="mt-2 text-sm font-medium text-white">
                        No hay tickets
                    </h3>
                    <p class="mt-1 text-sm text-slate-400">
                        No se encontraron tickets con los filtros aplicados.
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal Crear / Editar -->
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
                        <!-- Header -->
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-700"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                {{
                                    isEditing ? "Editar Ticket" : "Nuevo Ticket"
                                }}
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
                            <!-- Usuario Solicitante -->
                            <div>
                                <label
                                    for="id_usuario_solicitante"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Usuario Solicitante
                                    <span class="text-red-400">*</span>
                                </label>
                                <select
                                    v-model="form.id_usuario_solicitante"
                                    id="id_usuario_solicitante"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.id_usuario_solicitante
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                >
                                    <option value="" disabled>
                                        Selecciona un usuario
                                    </option>
                                    <option
                                        v-for="u in usuarios"
                                        :key="u.id"
                                        :value="u.id"
                                    >
                                        {{ u.nombre }} {{ u.apellido }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.id_usuario_solicitante"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.id_usuario_solicitante }}
                                </p>
                            </div>

                            <!-- Área (Módulo 4.9) -->
                            <div>
                                <label for="id_area" class="block text-sm font-medium text-white mb-2">
                                    Área de Soporte
                                    <span class="text-red-400">*</span>
                                </label>
                                <select
                                    v-model="form.id_area"
                                    id="id_area"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                >
                                    <option value="" disabled>Selecciona un área</option>
                                    <option v-for="a in areas" :key="a.id_area" :value="a.id_area">
                                        {{ a.name_area }}
                                    </option>
                                </select>
                            </div>

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
                                    <option value="" disabled>
                                        Selecciona una categoría
                                    </option>
                                    <option
                                        v-for="cat in categoriasFiltradas"
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

                            <!-- Equipo (opcional) -->
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
                                        <option value="" disabled>
                                            Seleccionar
                                        </option>
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
                                        <option value="" disabled>
                                            Seleccionar
                                        </option>
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
                                    <span class="text-slate-500 text-xs"
                                        >(opcional)</span
                                    >
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
                                            : isEditing
                                              ? "Actualizar"
                                              : "Crear Ticket"
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
import { reactive, ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps({
    tickets: Object,
    categorias: Array,
    equipos: Array,
    usuarios: Array,
    areas: Array,
    filters: Object,
});

// Filtrar las categorías según el área seleccionada en el formulario
const categoriasFiltradas = computed(() => {
    if (!form.id_area) {
        return props.categorias; // Si no hay área elegida, muestra todas
    }
    // Si hay un área seleccionada, filtra solo las categorías de esa área
    return props.categorias.filter(cat => cat.id_area === form.id_area);
});

const estadosDisponibles = [
    "Abierto",
    "En progreso",
    "Resuelto",
    "Cerrado",
    "Cancelado",
];
const prioridadesDisponibles = ["Baja", "Media", "Alta", "Crítica"];

// Filtros
const filterForm = reactive({
    search: props.filters.search || "",
    estado: props.filters.estado || "",
    prioridad: props.filters.prioridad || "",
    categoria: props.filters.categoria || "",
    desde: props.filters.desde || "",
    hasta: props.filters.hasta || "",
});

function applyFilters() {
    router.get(route("admin.tickets.index"), filterForm, {
        preserveState: true,
    });
}

function clearFilters() {
    filterForm.search = "";
    filterForm.estado = "";
    filterForm.prioridad = "";
    filterForm.categoria = "";
    filterForm.desde = "";
    filterForm.hasta = "";
    router.get(route("admin.tickets.index"), {}, { preserveState: true });
}

// Modal
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const processing = ref(false);
const errors = ref({});

const form = reactive({
    id_usuario_solicitante: "",
    id_area: "",
    id_categoria: "",
    id_equipo: null,
    estado: "",
    prioridad: "",
    fecha_creacion: "",
});

function openCreateModal() {
    isEditing.value = false;
    editingId.value = null;
    form.id_usuario_solicitante = "";
    form.id_area = "";
    form.id_categoria = "";
    form.id_equipo = null;
    form.estado = "";
    form.prioridad = "";
    form.fecha_creacion = "";
    errors.value = {};
    showModal.value = true;
}

function openEditModal(ticket) {
    isEditing.value = true;
    editingId.value = ticket.id_ticket;
    form.id_usuario_solicitante = ticket.id_usuario_solicitante;
    form.id_area = ticket.categoria?.id_area || "";
    form.id_categoria = ticket.id_categoria;
    form.id_equipo = ticket.id_equipo;
    form.estado = ticket.estado;
    form.prioridad = ticket.prioridad;
    form.fecha_creacion = ticket.fecha_creacion
        ? ticket.fecha_creacion.substring(0, 10)
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

    if (isEditing.value) {
        router.put(route("admin.tickets.update", editingId.value), form, {
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
    } else {
        router.post(route("admin.tickets.store"), form, {
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
}

function confirmDelete(ticket) {
    if (
        confirm(
            `¿Estás seguro de eliminar el ticket #${ticket.id_ticket}? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(route("admin.tickets.destroy", ticket.id_ticket), {
            preserveScroll: true,
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

function formatDate(date) {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("es-MX", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}
</script>
