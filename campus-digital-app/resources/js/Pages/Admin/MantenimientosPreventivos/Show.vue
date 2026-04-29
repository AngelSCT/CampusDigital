<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a
                        :href="route('admin.mantenimientos-preventivos.index')"
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
                        Mantenimientos Preventivos
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="text-sm text-white"
                        >Detalle #{{ preventivo.id_preventivo }}</span
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
                <!-- Estado de fecha -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs text-slate-400 uppercase tracking-wider"
                            >
                                Estado
                            </p>
                            <p
                                class="mt-1 text-xl font-bold"
                                :class="estadoFechaTextClass"
                            >
                                {{ estadoFecha }}
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center"
                            :class="estadoFechaBgClass"
                        >
                            <svg
                                class="w-5 h-5"
                                :class="estadoFechaTextClass"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Equipo -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5"
                >
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p
                                class="text-xs text-slate-400 uppercase tracking-wider"
                            >
                                Equipo
                            </p>
                            <p
                                class="mt-1 text-sm font-semibold text-white truncate"
                            >
                                {{ preventivo.equipo?.nombre_equipo ?? "—" }}
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-500/10"
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
                    </div>
                </div>

                <!-- Categoría -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5"
                >
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p
                                class="text-xs text-slate-400 uppercase tracking-wider"
                            >
                                Categoría
                            </p>
                            <p
                                class="mt-1 text-sm font-semibold text-white truncate"
                            >
                                {{
                                    preventivo.equipo?.categoria
                                        ?.nombre_categoria ?? "—"
                                }}
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center bg-purple-500/10"
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
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Días restantes -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 p-5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs text-slate-400 uppercase tracking-wider"
                            >
                                Días restantes
                            </p>
                            <p
                                class="mt-1 text-xl font-bold"
                                :class="diasRestantesClass"
                            >
                                {{ diasRestantesLabel }}
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center"
                            :class="diasRestantesBgClass"
                        >
                            <svg
                                class="w-5 h-5"
                                :class="diasRestantesClass"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
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
                        Información del Mantenimiento
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                ID Mantenimiento
                            </dt>
                            <dd class="mt-1 text-sm text-white font-medium">
                                # {{ preventivo.id_preventivo }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Equipo
                            </dt>
                            <dd class="mt-1 text-sm text-white">
                                {{ preventivo.equipo?.nombre_equipo ?? "—" }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Categoría del Equipo
                            </dt>
                            <dd class="mt-1 text-sm text-white">
                                {{
                                    preventivo.equipo?.categoria
                                        ?.nombre_categoria ?? "—"
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Ubicación del Equipo
                            </dt>
                            <dd class="mt-1 text-sm text-white">
                                <span v-if="preventivo.equipo?.ubicacion">
                                    {{ preventivo.equipo.ubicacion.edificio }} —
                                    {{
                                        preventivo.equipo.ubicacion
                                            .aula_departamento
                                    }}
                                </span>
                                <span v-else>—</span>
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Próxima Fecha Programada
                            </dt>
                            <dd
                                class="mt-1 text-sm font-medium"
                                :class="estadoFechaTextClass"
                            >
                                {{
                                    formatDate(
                                        preventivo.proxima_fecha_programada,
                                    )
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Estado del Equipo
                            </dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full border"
                                    :class="
                                        estadoEquipoClass(
                                            preventivo.equipo?.estado_actual,
                                        )
                                    "
                                >
                                    {{
                                        preventivo.equipo?.estado_actual ?? "—"
                                    }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Creado
                            </dt>
                            <dd class="mt-1 text-sm text-slate-300">
                                {{ formatDate(preventivo.created_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >
                                Última Actualización
                            </dt>
                            <dd class="mt-1 text-sm text-slate-300">
                                {{ formatDate(preventivo.updated_at) }}
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
                                Editar Mantenimiento Preventivo
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
                            <!-- Equipo -->
                            <div>
                                <label
                                    for="id_equipo"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Equipo <span class="text-red-400">*</span>
                                </label>
                                <select
                                    v-model="form.id_equipo"
                                    id="id_equipo"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.id_equipo
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                >
                                    <option value="" disabled>
                                        Selecciona un equipo
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

                            <!-- Próxima Fecha Programada -->
                            <div>
                                <label
                                    for="proxima_fecha_programada"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Próxima Fecha Programada
                                    <span class="text-red-400">*</span>
                                </label>
                                <input
                                    v-model="form.proxima_fecha_programada"
                                    id="proxima_fecha_programada"
                                    type="date"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.proxima_fecha_programada
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                />
                                <p
                                    v-if="errors.proxima_fecha_programada"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.proxima_fecha_programada }}
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
    preventivo: Object,
    equipos: Array,
});

// Cálculo de días restantes
const diasRestantes = computed(() => {
    if (!props.preventivo.proxima_fecha_programada) return null;
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const f = new Date(props.preventivo.proxima_fecha_programada);
    return Math.ceil((f - hoy) / (1000 * 60 * 60 * 24));
});

const estadoFecha = computed(() => {
    const d = diasRestantes.value;
    if (d === null) return "—";
    if (d < 0) return "Vencido";
    if (d === 0) return "Hoy";
    if (d <= 7) return "Próximo";
    return "Programado";
});

const estadoFechaTextClass = computed(
    () =>
        ({
            Vencido: "text-red-400",
            Hoy: "text-yellow-400",
            Próximo: "text-orange-400",
            Programado: "text-green-400",
        })[estadoFecha.value] ?? "text-slate-400",
);

const estadoFechaBgClass = computed(
    () =>
        ({
            Vencido: "bg-red-500/10",
            Hoy: "bg-yellow-500/10",
            Próximo: "bg-orange-500/10",
            Programado: "bg-green-500/10",
        })[estadoFecha.value] ?? "bg-slate-500/10",
);

const diasRestantesLabel = computed(() => {
    const d = diasRestantes.value;
    if (d === null) return "—";
    if (d < 0) return `${Math.abs(d)} días`;
    if (d === 0) return "Hoy";
    return `${d} días`;
});

const diasRestantesClass = computed(() => estadoFechaTextClass.value);
const diasRestantesBgClass = computed(() => estadoFechaBgClass.value);

// Modal editar
const showModal = ref(false);
const processing = ref(false);
const errors = ref({});

const form = reactive({
    id_equipo: props.preventivo.id_equipo,
    proxima_fecha_programada: props.preventivo.proxima_fecha_programada
        ? props.preventivo.proxima_fecha_programada.substring(0, 10)
        : "",
});

function openEditModal() {
    form.id_equipo = props.preventivo.id_equipo;
    form.proxima_fecha_programada = props.preventivo.proxima_fecha_programada
        ? props.preventivo.proxima_fecha_programada.substring(0, 10)
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
    router.put(
        route(
            "admin.mantenimientos-preventivos.update",
            props.preventivo.id_preventivo,
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
    const equipo =
        props.preventivo.equipo?.nombre_equipo ??
        `ID ${props.preventivo.id_equipo}`;
    if (
        confirm(
            `¿Estás seguro de eliminar el mantenimiento preventivo del equipo "${equipo}"? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(
            route(
                "admin.mantenimientos-preventivos.destroy",
                props.preventivo.id_preventivo,
            ),
        );
    }
}

// Helpers
function estadoEquipoClass(estado) {
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
    if (!date) return "—";
    return new Date(date).toLocaleDateString("es-MX", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}
</script>
