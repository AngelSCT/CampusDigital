<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a
                        :href="route('admin.asignaciones-tecnicas.index')"
                        class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors duration-200"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Asignaciones Técnicas
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="text-sm text-white"
                        >Detalle #{{ asignacion.id_asignacion }}</span
                    >
                </div>
                <div class="flex gap-2">
                    <button
                        @click="openEditModal"
                        class="inline-flex items-center px-4 py-2 border border-blue-500/30 rounded-lg text-sm font-medium text-blue-400 hover:text-white hover:bg-blue-600/20 transition-all duration-200"
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
                        class="inline-flex items-center px-4 py-2 border border-red-500/30 rounded-lg text-sm font-medium text-red-400 hover:text-white hover:bg-red-600/20 transition-all duration-200"
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

            <!-- Tarjetas de estado -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Estado del Ticket -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs text-slate-400 uppercase tracking-wider"
                            >
                                Estado Ticket
                            </p>
                            <p
                                class="mt-1 text-sm font-bold"
                                :class="estadoTicketTextClass"
                            >
                                {{ asignacion.ticket?.estado ?? "—" }}
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center"
                            :class="estadoTicketBgClass"
                        >
                            <svg
                                class="w-5 h-5"
                                :class="estadoTicketTextClass"
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
                        </div>
                    </div>
                </div>

                <!-- Prioridad -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs text-slate-400 uppercase tracking-wider"
                            >
                                Prioridad
                            </p>
                            <p
                                class="mt-1 text-sm font-bold"
                                :class="prioridadTextClass"
                            >
                                {{ asignacion.ticket?.prioridad ?? "—" }}
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center"
                            :class="prioridadBgClass"
                        >
                            <svg
                                class="w-5 h-5"
                                :class="prioridadTextClass"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Técnico -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-sm font-bold shrink-0"
                        >
                            {{ initials(asignacion.tecnico) }}
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-xs text-slate-400 uppercase tracking-wider"
                            >
                                Técnico
                            </p>
                            <p
                                class="mt-0.5 text-sm font-semibold text-white truncate"
                            >
                                {{ fullName(asignacion.tecnico) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ticket ID -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs text-slate-400 uppercase tracking-wider"
                            >
                                Ticket
                            </p>
                            <p class="mt-1 text-xl font-bold text-white">
                                # {{ asignacion.id_ticket }}
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center"
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
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle completo -->
            <div
                class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20"
            >
                <div class="px-6 py-4 border-b border-slate-700">
                    <h3 class="text-base font-semibold text-white">
                        Información de la Asignación
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                ID Asignación
                            </dt>
                            <dd class="mt-1 text-sm text-white font-medium">
                                # {{ asignacion.id_asignacion }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Ticket Asignado
                            </dt>
                            <dd class="mt-1">
                                <a
                                    :href="
                                        route(
                                            'admin.tickets.show',
                                            asignacion.id_ticket,
                                        )
                                    "
                                    class="text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200"
                                >
                                    # {{ asignacion.id_ticket }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Estado del Ticket
                            </dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full border"
                                    :class="estadoTicketBadgeClass"
                                >
                                    {{ asignacion.ticket?.estado ?? "—" }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Prioridad del Ticket
                            </dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full border"
                                    :class="prioridadBadgeClass"
                                >
                                    {{ asignacion.ticket?.prioridad ?? "—" }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Técnico Asignado
                            </dt>
                            <dd class="mt-1 text-sm text-white">
                                {{ fullName(asignacion.tecnico) }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Email del Técnico
                            </dt>
                            <dd class="mt-1 text-sm text-slate-300">
                                {{ asignacion.tecnico?.email ?? "—" }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Fecha de Asignación
                            </dt>
                            <dd class="mt-1 text-sm text-slate-300">
                                {{ formatDate(asignacion.created_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Última Actualización
                            </dt>
                            <dd class="mt-1 text-sm text-slate-300">
                                {{ formatDate(asignacion.updated_at) }}
                            </dd>
                        </div>
                    </dl>
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
                        class="relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-2xl shadow-blue-500/20 w-full max-w-md"
                    >
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-700"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Editar Asignación Técnica
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
import { computed, reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps({
    asignacion: Object,
    tickets: Array,
    tecnicos: Array,
});

// Modal editar
const showModal = ref(false);
const processing = ref(false);
const errors = ref({});

const form = reactive({
    id_ticket: props.asignacion.id_ticket,
    id_usuario_tecnico: props.asignacion.id_usuario_tecnico,
});

function openEditModal() {
    form.id_ticket = props.asignacion.id_ticket;
    form.id_usuario_tecnico = props.asignacion.id_usuario_tecnico;
    errors.value = {};
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function submitForm() {
    processing.value = true;
    errors.value = {};
    router.put(
        route(
            "admin.asignaciones-tecnicas.update",
            props.asignacion.id_asignacion,
        ),
        form,
        {
            onSuccess: () => {
                closeModal();
            },
            onError: (err) => {
                errors.value = err;
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function handleDelete() {
    const tecnico = fullName(props.asignacion.tecnico);
    if (
        confirm(
            `¿Estás seguro de eliminar la asignación del técnico "${tecnico}" al ticket #${props.asignacion.id_ticket}? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(
            route(
                "admin.asignaciones-tecnicas.destroy",
                props.asignacion.id_asignacion,
            ),
        );
    }
}

// Computed badge classes
const estadoTicketTextClass = computed(
    () =>
        ({
            Abierto: "text-blue-400",
            "En progreso": "text-yellow-400",
            Resuelto: "text-green-400",
            Cerrado: "text-slate-400",
            Cancelado: "text-red-400",
        })[props.asignacion.ticket?.estado] ?? "text-slate-400",
);

const estadoTicketBgClass = computed(
    () =>
        ({
            Abierto: "bg-blue-500/10",
            "En progreso": "bg-yellow-500/10",
            Resuelto: "bg-green-500/10",
            Cerrado: "bg-slate-500/10",
            Cancelado: "bg-red-500/10",
        })[props.asignacion.ticket?.estado] ?? "bg-slate-500/10",
);

const estadoTicketBadgeClass = computed(
    () =>
        ({
            Abierto: "bg-blue-500/20 text-blue-400 border-blue-500/30",
            "En progreso":
                "bg-yellow-500/20 text-yellow-400 border-yellow-500/30",
            Resuelto: "bg-green-500/20 text-green-400 border-green-500/30",
            Cerrado: "bg-slate-500/20 text-slate-400 border-slate-500/30",
            Cancelado: "bg-red-500/20 text-red-400 border-red-500/30",
        })[props.asignacion.ticket?.estado] ??
        "bg-slate-500/20 text-slate-400 border-slate-500/30",
);

const prioridadTextClass = computed(
    () =>
        ({
            Baja: "text-slate-400",
            Media: "text-blue-400",
            Alta: "text-orange-400",
            Crítica: "text-red-400",
        })[props.asignacion.ticket?.prioridad] ?? "text-slate-400",
);

const prioridadBgClass = computed(
    () =>
        ({
            Baja: "bg-slate-500/10",
            Media: "bg-blue-500/10",
            Alta: "bg-orange-500/10",
            Crítica: "bg-red-500/10",
        })[props.asignacion.ticket?.prioridad] ?? "bg-slate-500/10",
);

const prioridadBadgeClass = computed(
    () =>
        ({
            Baja: "bg-slate-500/20 text-slate-400 border-slate-500/30",
            Media: "bg-blue-500/20 text-blue-400 border-blue-500/30",
            Alta: "bg-orange-500/20 text-orange-400 border-orange-500/30",
            Crítica: "bg-red-500/20 text-red-400 border-red-500/30",
        })[props.asignacion.ticket?.prioridad] ??
        "bg-slate-500/20 text-slate-400 border-slate-500/30",
);

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

function formatDate(date) {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("es-MX", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}
</script>
