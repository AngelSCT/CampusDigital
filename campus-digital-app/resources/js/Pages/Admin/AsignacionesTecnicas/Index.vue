<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex justify-between items-center">
                <div>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent"
                    >
                        Asignaciones Técnicas
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Gestiona la asignación de técnicos a los tickets de
                        soporte
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
                    Nueva Asignación
                </button>
            </div>

            <!-- Filtros -->
            <div
                class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-4"
            >
                <form
                    @submit.prevent="applyFilters"
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                >
                    <div>
                        <label class="block text-sm font-medium text-white mb-2"
                            >Ticket</label
                        >
                        <select
                            v-model="filterForm.ticket"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todos los tickets</option>
                            <option
                                v-for="t in tickets"
                                :key="t.id_ticket"
                                :value="t.id_ticket"
                            >
                                #{{ t.id_ticket }} — {{ t.estado }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2"
                            >Técnico</label
                        >
                        <select
                            v-model="filterForm.tecnico"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todos los técnicos</option>
                            <option
                                v-for="u in tecnicos"
                                :key="u.id"
                                :value="u.id"
                            >
                                {{ u.nombre }} {{ u.apellido }}
                            </option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-2">
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
                                    Ticket
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Estado Ticket
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Prioridad
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Técnico Asignado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Fecha Asignación
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
                                v-for="asignacion in asignaciones.data"
                                :key="asignacion.id_asignacion"
                                class="hover:bg-slate-700/30 transition-colors duration-150"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-white"
                                >
                                    {{ asignacion.id_asignacion }}
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
                                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                                            />
                                        </svg>
                                        <span
                                            class="text-sm font-medium text-white"
                                            ># {{ asignacion.id_ticket }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                        :class="
                                            estadoTicketClass(
                                                asignacion.ticket?.estado,
                                            )
                                        "
                                    >
                                        {{ asignacion.ticket?.estado ?? "—" }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                        :class="
                                            prioridadClass(
                                                asignacion.ticket?.prioridad,
                                            )
                                        "
                                    >
                                        {{
                                            asignacion.ticket?.prioridad ?? "—"
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xs font-bold shrink-0"
                                        >
                                            {{ initials(asignacion.tecnico) }}
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-medium text-white"
                                            >
                                                {{
                                                    fullName(asignacion.tecnico)
                                                }}
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                {{
                                                    asignacion.tecnico?.email ??
                                                    ""
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-300"
                                >
                                    {{ formatDate(asignacion.created_at) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <div class="flex justify-end gap-2">
                                        <a
                                            :href="
                                                route(
                                                    'admin.asignaciones-tecnicas.show',
                                                    asignacion.id_asignacion,
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
                                            @click="openEditModal(asignacion)"
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
                                            @click="confirmDelete(asignacion)"
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
                    v-if="asignaciones.data.length > 0"
                    class="bg-slate-900/50 px-4 py-3 border-t border-slate-700 sm:px-6"
                >
                    <div
                        class="hidden sm:flex sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-slate-300">
                            Mostrando
                            <span class="font-medium text-white">{{
                                asignaciones.from
                            }}</span>
                            a
                            <span class="font-medium text-white">{{
                                asignaciones.to
                            }}</span>
                            de
                            <span class="font-medium text-white">{{
                                asignaciones.total
                            }}</span>
                            resultados
                        </p>
                        <nav
                            class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px"
                        >
                            <a
                                v-for="link in asignaciones.links"
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
                <div
                    v-if="asignaciones.data.length === 0"
                    class="text-center py-12"
                >
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
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-white">
                        No hay asignaciones técnicas
                    </h3>
                    <p class="mt-1 text-sm text-slate-400">
                        Comienza asignando un técnico a un ticket.
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
                        class="relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-2xl shadow-blue-500/20 w-full max-w-md"
                    >
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-700"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                {{
                                    isEditing
                                        ? "Editar Asignación Técnica"
                                        : "Nueva Asignación Técnica"
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
                            <!-- Ticket -->
                            <div>
                                <label
                                    for="id_ticket"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Ticket <span class="text-red-400">*</span>
                                </label>
                                <select
                                    v-model="form.id_ticket"
                                    id="id_ticket"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.id_ticket
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                >
                                    <option value="" disabled>
                                        Selecciona un ticket
                                    </option>
                                    <option
                                        v-for="t in tickets"
                                        :key="t.id_ticket"
                                        :value="t.id_ticket"
                                    >
                                        #{{ t.id_ticket }} — {{ t.estado }} ({{
                                            t.prioridad
                                        }})
                                    </option>
                                </select>
                                <p
                                    v-if="errors.id_ticket"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.id_ticket }}
                                </p>
                            </div>

                            <!-- Técnico -->
                            <div>
                                <label
                                    for="id_usuario_tecnico"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Técnico <span class="text-red-400">*</span>
                                </label>
                                <select
                                    v-model="form.id_usuario_tecnico"
                                    id="id_usuario_tecnico"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.id_usuario_tecnico
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                >
                                    <option value="" disabled>
                                        Selecciona un técnico
                                    </option>
                                    <option
                                        v-for="u in tecnicos"
                                        :key="u.id"
                                        :value="u.id"
                                    >
                                        {{ u.nombre }} {{ u.apellido }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.id_usuario_tecnico"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.id_usuario_tecnico }}
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
                                              : "Asignar"
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
    asignaciones: Object,
    tickets: Array,
    tecnicos: Array,
    filters: Object,
});

// Filtros
const filterForm = reactive({
    ticket: props.filters.ticket || "",
    tecnico: props.filters.tecnico || "",
});

function applyFilters() {
    router.get(route("admin.asignaciones-tecnicas.index"), filterForm, {
        preserveState: true,
    });
}

function clearFilters() {
    filterForm.ticket = "";
    filterForm.tecnico = "";
    router.get(
        route("admin.asignaciones-tecnicas.index"),
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
    id_ticket: "",
    id_usuario_tecnico: "",
});

function openCreateModal() {
    isEditing.value = false;
    editingId.value = null;
    form.id_ticket = "";
    form.id_usuario_tecnico = "";
    errors.value = {};
    showModal.value = true;
}

function openEditModal(asignacion) {
    isEditing.value = true;
    editingId.value = asignacion.id_asignacion;
    form.id_ticket = asignacion.id_ticket;
    form.id_usuario_tecnico = asignacion.id_usuario_tecnico;
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
            route("admin.asignaciones-tecnicas.update", editingId.value),
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
        router.post(route("admin.asignaciones-tecnicas.store"), form, {
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

function confirmDelete(asignacion) {
    const tecnico = fullName(asignacion.tecnico);
    if (
        confirm(
            `¿Estás seguro de eliminar la asignación del técnico "${tecnico}" al ticket #${asignacion.id_ticket}? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(
            route(
                "admin.asignaciones-tecnicas.destroy",
                asignacion.id_asignacion,
            ),
            { preserveScroll: true },
        );
    }
}

// Helpers
function initials(tecnico) {
    if (!tecnico) return "?";
    const n = tecnico.nombre?.[0] ?? "";
    const a = tecnico.apellido?.[0] ?? "";
    return (n + a).toUpperCase() || "?";
}

function fullName(tecnico) {
    if (!tecnico) return "—";
    return `${tecnico.nombre ?? ""} ${tecnico.apellido ?? ""}`.trim() || "—";
}

function estadoTicketClass(estado) {
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
