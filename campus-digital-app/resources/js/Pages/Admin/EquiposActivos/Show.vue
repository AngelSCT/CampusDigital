<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a
                        :href="route('admin.equipos-activos.index')"
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
                            Detalle de Equipo Activo
                        </h1>
                        <p class="mt-1 text-sm text-slate-400">
                            {{ equipo.nombre_equipo }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
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

            <!-- Tarjetas de estado rápido -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Estado -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 flex items-center gap-4"
                >
                    <div
                        class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                        :class="estadoBg(equipo.estado_actual)"
                    >
                        <svg
                            class="w-5 h-5"
                            :class="estadoIcon(equipo.estado_actual)"
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
                            Estado Actual
                        </p>
                        <p class="text-sm font-semibold text-white mt-0.5">
                            {{ equipo.estado_actual }}
                        </p>
                    </div>
                </div>

                <!-- Categoría -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 flex items-center gap-4"
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
                            {{ equipo.categoria?.nombre_categoria ?? "—" }}
                        </p>
                    </div>
                </div>

                <!-- Ubicación -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 flex items-center gap-4"
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
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-xs text-slate-400 uppercase tracking-wider"
                        >
                            Ubicación
                        </p>
                        <p class="text-sm font-semibold text-white mt-0.5">
                            <span v-if="equipo.ubicacion">
                                {{ equipo.ubicacion.edificio }} —
                                {{ equipo.ubicacion.aula_departamento }}
                            </span>
                            <span v-else>—</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de información completa -->
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
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-white">
                        Información del Equipo
                    </h2>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >ID Equipo</span
                        >
                        <p class="text-lg font-semibold text-white">
                            {{ equipo.id_equipo }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Nombre del Equipo</span
                        >
                        <p class="text-lg font-semibold text-white">
                            {{ equipo.nombre_equipo }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Categoría</span
                        >
                        <p class="text-sm text-white">
                            {{ equipo.categoria?.nombre_categoria ?? "—" }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Ubicación</span
                        >
                        <p class="text-sm text-white">
                            <span v-if="equipo.ubicacion">
                                {{ equipo.ubicacion.edificio }} —
                                {{ equipo.ubicacion.aula_departamento }}
                            </span>
                            <span v-else>—</span>
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Estado Actual</span
                        >
                        <span
                            class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border"
                            :class="estadoClass(equipo.estado_actual)"
                        >
                            {{ equipo.estado_actual }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Fecha Creación</span
                        >
                        <p class="text-sm text-white">
                            {{ formatDate(equipo.created_at) }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Última Actualización</span
                        >
                        <p class="text-sm text-white">
                            {{ formatDate(equipo.updated_at) }}
                        </p>
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
                                Editar Equipo Activo
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
    equipo: Object,
    categorias: Array,
    ubicaciones: Array,
});

const estadosDisponibles = [
    "Activo",
    "En mantenimiento",
    "Dado de baja",
    "En reparación",
];

// Modal
const showModal = ref(false);
const processing = ref(false);
const errors = ref({});

const form = reactive({
    nombre_equipo: "",
    id_categoria: "",
    id_ubicacion: "",
    estado_actual: "",
});

function openEditModal() {
    form.nombre_equipo = props.equipo.nombre_equipo;
    form.id_categoria = props.equipo.id_categoria;
    form.id_ubicacion = props.equipo.id_ubicacion;
    form.estado_actual = props.equipo.estado_actual;
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
        route("admin.equipos-activos.update", props.equipo.id_equipo),
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
}

function handleDelete() {
    if (
        confirm(
            `¿Estás seguro de eliminar el equipo "${props.equipo.nombre_equipo}"? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(
            route("admin.equipos-activos.destroy", props.equipo.id_equipo),
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

function estadoBg(estado) {
    const map = {
        Activo: "bg-green-500/20 border border-green-500/30",
        "En mantenimiento": "bg-yellow-500/20 border border-yellow-500/30",
        "En reparación": "bg-orange-500/20 border border-orange-500/30",
        "Dado de baja": "bg-red-500/20 border border-red-500/30",
    };
    return map[estado] ?? "bg-slate-500/20 border border-slate-500/30";
}

function estadoIcon(estado) {
    const map = {
        Activo: "text-green-400",
        "En mantenimiento": "text-yellow-400",
        "En reparación": "text-orange-400",
        "Dado de baja": "text-red-400",
    };
    return map[estado] ?? "text-slate-400";
}

function formatDate(date) {
    return new Date(date).toLocaleDateString("es-MX", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}
</script>
