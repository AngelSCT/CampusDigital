<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex justify-between items-center">
                <div>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent"
                    >
                        Gestión de Equipos Activos
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Administra los equipos registrados en el sistema
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
                    Nuevo Equipo
                </button>
            </div>

            <!-- Filtros -->
            <div
                class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-4"
            >
                <form
                    @submit.prevent="applyFilters"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <div>
                        <label class="block text-sm font-medium text-white mb-2"
                            >Buscar</label
                        >
                        <input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Nombre del equipo..."
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        />
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
                            >Ubicación</label
                        >
                        <select
                            v-model="filterForm.ubicacion"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todas</option>
                            <option
                                v-for="ub in ubicaciones"
                                :key="ub.id_ubicacion"
                                :value="ub.id_ubicacion"
                            >
                                {{ ub.edificio }} — {{ ub.aula_departamento }}
                            </option>
                        </select>
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
                    <div
                        class="sm:col-span-2 lg:col-span-4 flex justify-end gap-2"
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
                                    Nombre Equipo
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Categoría
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Ubicación
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Estado
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
                                v-for="equipo in equipos.data"
                                :key="equipo.id_equipo"
                                class="hover:bg-slate-700/30 transition-colors duration-150"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-white"
                                >
                                    {{ equipo.id_equipo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg
                                            class="w-4 h-4 text-blue-400 shrink-0"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <span
                                            class="text-sm font-medium text-white"
                                            >{{ equipo.nombre_equipo }}</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-300"
                                >
                                    {{
                                        equipo.categoria?.nombre_categoria ??
                                        "—"
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-300"
                                >
                                    <span v-if="equipo.ubicacion">
                                        {{ equipo.ubicacion.edificio }} —
                                        {{ equipo.ubicacion.aula_departamento }}
                                    </span>
                                    <span v-else>—</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                        :class="
                                            estadoClass(equipo.estado_actual)
                                        "
                                    >
                                        {{ equipo.estado_actual }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-300"
                                >
                                    {{ formatDate(equipo.created_at) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <div class="flex justify-end gap-2">
                                        <a
                                            :href="
                                                route(
                                                    'admin.equipos-activos.show',
                                                    equipo.id_equipo,
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
                                            @click="openEditModal(equipo)"
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
                                            @click="confirmDelete(equipo)"
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
                    v-if="equipos.data.length > 0"
                    class="bg-slate-900/50 px-4 py-3 border-t border-slate-700 sm:px-6"
                >
                    <div
                        class="hidden sm:flex sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-slate-300">
                            Mostrando
                            <span class="font-medium text-white">{{
                                equipos.from
                            }}</span>
                            a
                            <span class="font-medium text-white">{{
                                equipos.to
                            }}</span>
                            de
                            <span class="font-medium text-white">{{
                                equipos.total
                            }}</span>
                            resultados
                        </p>
                        <nav
                            class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px"
                        >
                            <a
                                v-for="link in equipos.links"
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
                <div v-if="equipos.data.length === 0" class="text-center py-12">
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
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-white">
                        No hay equipos activos
                    </h3>
                    <p class="mt-1 text-sm text-slate-400">
                        Comienza registrando un nuevo equipo.
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
                                    isEditing
                                        ? "Editar Equipo Activo"
                                        : "Nuevo Equipo Activo"
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

                        <!-- Formulario -->
                        <form
                            @submit.prevent="submitForm"
                            class="p-6 space-y-4"
                        >
                            <!-- Nombre Equipo -->
                            <div>
                                <label
                                    for="nombre_equipo"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Nombre del Equipo
                                    <span class="text-red-400">*</span>
                                </label>
                                <input
                                    v-model="form.nombre_equipo"
                                    id="nombre_equipo"
                                    type="text"
                                    required
                                    maxlength="120"
                                    placeholder="Ej. Laptop Dell Latitude, Proyector Epson..."
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.nombre_equipo
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                />
                                <p
                                    v-if="errors.nombre_equipo"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.nombre_equipo }}
                                </p>
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

                            <!-- Ubicación -->
                            <div>
                                <label
                                    for="id_ubicacion"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Ubicación
                                    <span class="text-red-400">*</span>
                                </label>
                                <select
                                    v-model="form.id_ubicacion"
                                    id="id_ubicacion"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.id_ubicacion
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                >
                                    <option value="" disabled>
                                        Selecciona una ubicación
                                    </option>
                                    <option
                                        v-for="ub in ubicaciones"
                                        :key="ub.id_ubicacion"
                                        :value="ub.id_ubicacion"
                                    >
                                        {{ ub.edificio }} —
                                        {{ ub.aula_departamento }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.id_ubicacion"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.id_ubicacion }}
                                </p>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label
                                    for="estado_actual"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Estado Actual
                                    <span class="text-red-400">*</span>
                                </label>
                                <select
                                    v-model="form.estado_actual"
                                    id="estado_actual"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.estado_actual
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                >
                                    <option value="" disabled>
                                        Selecciona un estado
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
                                    v-if="errors.estado_actual"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.estado_actual }}
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
                                              : "Crear Equipo"
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
    equipos: Object,
    categorias: Array,
    ubicaciones: Array,
    filters: Object,
});

const estadosDisponibles = [
    "Activo",
    "En mantenimiento",
    "Dado de baja",
    "En reparación",
];

// Filtros
const filterForm = reactive({
    search: props.filters.search || "",
    categoria: props.filters.categoria || "",
    ubicacion: props.filters.ubicacion || "",
    estado: props.filters.estado || "",
});

function applyFilters() {
    router.get(route("admin.equipos-activos.index"), filterForm, {
        preserveState: true,
    });
}

function clearFilters() {
    filterForm.search = "";
    filterForm.categoria = "";
    filterForm.ubicacion = "";
    filterForm.estado = "";
    router.get(
        route("admin.equipos-activos.index"),
        {},
        { preserveState: true },
    );
}

// Modal
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const processing = ref(false);
const errors = ref({});

const form = reactive({
    nombre_equipo: "",
    id_categoria: "",
    id_ubicacion: "",
    estado_actual: "",
});

function openCreateModal() {
    isEditing.value = false;
    editingId.value = null;
    form.nombre_equipo = "";
    form.id_categoria = "";
    form.id_ubicacion = "";
    form.estado_actual = "";
    errors.value = {};
    showModal.value = true;
}

function openEditModal(equipo) {
    isEditing.value = true;
    editingId.value = equipo.id_equipo;
    form.nombre_equipo = equipo.nombre_equipo;
    form.id_categoria = equipo.id_categoria;
    form.id_ubicacion = equipo.id_ubicacion;
    form.estado_actual = equipo.estado_actual;
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
        router.put(
            route("admin.equipos-activos.update", editingId.value),
            form,
            {
                onSuccess: () => {
                    showModal.value = false;
                },
                onError: (err) => {
                    errors.value = err;
                },
                onFinish: () => {
                    processing.value = false;
                },
            },
        );
    } else {
        router.post(route("admin.equipos-activos.store"), form, {
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

function confirmDelete(equipo) {
    if (
        confirm(
            `¿Estás seguro de eliminar el equipo "${equipo.nombre_equipo}"? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(
            route("admin.equipos-activos.destroy", equipo.id_equipo),
            { preserveScroll: true },
        );
    }
}

function estadoClass(estado) {
    const map = {
        Activo: "bg-green-500/20 text-green-400 border-green-500/30",
        "En mantenimiento":
            "bg-yellow-500/20 text-yellow-400 border-yellow-500/30",
        "En reparación":
            "bg-orange-500/20 text-orange-400 border-orange-500/30",
        "Dado de baja": "bg-red-500/20 text-red-400 border-red-500/30",
    };
    return map[estado] ?? "bg-slate-500/20 text-slate-400 border-slate-500/30";
}

function formatDate(date) {
    return new Date(date).toLocaleDateString("es-MX", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}
</script>
