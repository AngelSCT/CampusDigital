<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex justify-between items-center">
                <div>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent"
                    >
                        Gestión de Áreas
                    </h1>
                    <p class="mt-1 text-sm text-white">
                        Administra las áreas del sistema
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
                    Nueva Área
                </button>
            </div>

            <!-- Filtro de búsqueda -->
            <div
                class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-4"
            >
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-white mb-2"
                            >Buscar</label
                        >
                        <input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Nombre del área..."
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        />
                    </div>
                    <div class="flex items-end gap-2">
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
                                    Nombre
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
                                v-for="area in areas.data"
                                :key="area.id_area"
                                class="hover:bg-slate-700/30 transition-colors duration-150"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-white"
                                >
                                    {{ area.id_area }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-white">
                                        {{ area.name_area }}
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-300"
                                >
                                    {{ formatDate(area.created_at) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <div class="flex justify-end gap-2">
                                        <a
                                            :href="
                                                route(
                                                    'admin.areas.show',
                                                    area.id_area,
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
                                            @click="openEditModal(area)"
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
                                            @click="confirmDelete(area)"
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
                    v-if="areas.data.length > 0"
                    class="bg-slate-900/50 px-4 py-3 border-t border-slate-700 sm:px-6"
                >
                    <div
                        class="hidden sm:flex sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-slate-300">
                            Mostrando
                            <span class="font-medium text-white">{{
                                areas.from
                            }}</span>
                            a
                            <span class="font-medium text-white">{{
                                areas.to
                            }}</span>
                            de
                            <span class="font-medium text-white">{{
                                areas.total
                            }}</span>
                            resultados
                        </p>
                        <nav
                            class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px"
                        >
                            <a
                                v-for="link in areas.links"
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
                <div v-if="areas.data.length === 0" class="text-center py-12">
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
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-white">
                        No hay áreas
                    </h3>
                    <p class="mt-1 text-sm text-slate-400">
                        Comienza creando una nueva área.
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
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="closeModal"
            >
                <div class="flex min-h-full items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div
                        class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                        @click="closeModal"
                    ></div>

                    <!-- Panel -->
                    <div
                        class="relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-2xl shadow-blue-500/20 w-full max-w-md"
                    >
                        <!-- Encabezado del modal -->
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-700"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                {{ isEditing ? "Editar Área" : "Nueva Área" }}
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
                            <div>
                                <label
                                    for="name_area"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Nombre del Área
                                    <span class="text-red-400">*</span>
                                </label>
                                <input
                                    v-model="form.name_area"
                                    id="name_area"
                                    type="text"
                                    required
                                    maxlength="120"
                                    placeholder="Ej. Tecnología, Administración..."
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.name_area
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                />
                                <p
                                    v-if="errors.name_area"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.name_area }}
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
                                              : "Crear Área"
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
    areas: Object,
    filters: Object,
});

// Filtros
const filterForm = reactive({
    search: props.filters.search || "",
});

function applyFilters() {
    router.get(route("admin.areas.index"), filterForm, { preserveState: true });
}

function clearFilters() {
    filterForm.search = "";
    router.get(route("admin.areas.index"), {}, { preserveState: true });
}

// Modal
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const processing = ref(false);
const errors = ref({});

const form = reactive({ name_area: "" });

function openCreateModal() {
    isEditing.value = false;
    editingId.value = null;
    form.name_area = "";
    errors.value = {};
    showModal.value = true;
}

function openEditModal(area) {
    isEditing.value = true;
    editingId.value = area.id_area;
    form.name_area = area.name_area;
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
        router.put(route("admin.areas.update", editingId.value), form, {
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
        router.post(route("admin.areas.store"), form, {
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

// Eliminar
function confirmDelete(area) {
    if (
        confirm(
            `¿Estás seguro de eliminar el área "${area.name_area}"? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(route("admin.areas.destroy", area.id_area), {
            preserveScroll: true,
        });
    }
}

// Utilitarios
function formatDate(date) {
    return new Date(date).toLocaleDateString("es-MX", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}
</script>
