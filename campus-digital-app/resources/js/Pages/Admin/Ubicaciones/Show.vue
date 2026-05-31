<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a
                        :href="route('admin.ubicaciones.index')"
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
                            Detalle de Ubicación
                        </h1>
                        <p class="mt-1 text-sm text-slate-400">
                            {{ ubicacion.edificio }} —
                            {{ ubicacion.aula_departamento }}
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

            <!-- Tarjeta de información -->
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
                    <h2 class="text-lg font-semibold text-white">
                        Información de Ubicación
                    </h2>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- ID -->
                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >ID Ubicación</span
                        >
                        <p class="text-lg font-semibold text-white">
                            {{ ubicacion.id_ubicacion }}
                        </p>
                    </div>

                    <!-- Edificio -->
                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Edificio</span
                        >
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
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                            <p class="text-lg font-semibold text-white">
                                {{ ubicacion.edificio }}
                            </p>
                        </div>
                    </div>

                    <!-- Aula / Departamento -->
                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Aula / Departamento</span
                        >
                        <span
                            class="inline-flex px-3 py-1 rounded-full text-sm font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30"
                        >
                            {{ ubicacion.aula_departamento }}
                        </span>
                    </div>

                    <!-- Fecha Creación -->
                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Fecha Creación</span
                        >
                        <p class="text-sm text-white">
                            {{ formatDate(ubicacion.created_at) }}
                        </p>
                    </div>

                    <!-- Última Actualización -->
                    <div class="space-y-1">
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Última Actualización</span
                        >
                        <p class="text-sm text-white">
                            {{ formatDate(ubicacion.updated_at) }}
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
                        class="relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-2xl shadow-blue-500/20 w-full max-w-md"
                    >
                        <!-- Header -->
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-700"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Editar Ubicación
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
                            <!-- Edificio -->
                            <div>
                                <label
                                    for="edificio"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Edificio <span class="text-red-400">*</span>
                                </label>
                                <input
                                    v-model="form.edificio"
                                    id="edificio"
                                    type="text"
                                    required
                                    maxlength="120"
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.edificio
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                />
                                <p
                                    v-if="errors.edificio"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.edificio }}
                                </p>
                            </div>

                            <!-- Aula / Departamento -->
                            <div>
                                <label
                                    for="aula_departamento"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Aula / Departamento
                                    <span class="text-red-400">*</span>
                                </label>
                                <input
                                    v-model="form.aula_departamento"
                                    id="aula_departamento"
                                    type="text"
                                    required
                                    maxlength="120"
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.aula_departamento
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                />
                                <p
                                    v-if="errors.aula_departamento"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.aula_departamento }}
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
    ubicacion: Object,
});

// Modal
const showModal = ref(false);
const processing = ref(false);
const errors = ref({});

const form = reactive({
    edificio: "",
    aula_departamento: "",
});

function openEditModal() {
    form.edificio = props.ubicacion.edificio;
    form.aula_departamento = props.ubicacion.aula_departamento;
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
        route("admin.ubicaciones.update", props.ubicacion.id_ubicacion),
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
            `¿Estás seguro de eliminar la ubicación "${props.ubicacion.edificio} - ${props.ubicacion.aula_departamento}"? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(
            route("admin.ubicaciones.destroy", props.ubicacion.id_ubicacion),
        );
    }
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
