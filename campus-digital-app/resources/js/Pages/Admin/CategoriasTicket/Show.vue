<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Encabezado -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a
                        :href="route('admin.categorias-ticket.index')"
                        class="p-2 rounded-lg border border-slate-600 text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all duration-200"
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
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </a>
                    <div>
                        <h1
                            class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent"
                        >
                            {{ categoria.nombre_categoria }}
                        </h1>
                        <p class="mt-0.5 text-sm text-slate-400">
                            Detalle de la categoría de ticket
                        </p>
                    </div>
                </div>
                <button
                    @click="openEditModal"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-lg shadow-blue-500/30 text-sm font-medium rounded-lg text-white bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 transition-all duration-200"
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
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                        />
                    </svg>
                    Editar categoría
                </button>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Área -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 shadow-xl shadow-blue-500/10"
                >
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 rounded-lg bg-blue-500/10">
                            <svg
                                class="w-4 h-4 text-blue-400"
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
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Área</span
                        >
                    </div>
                    <a
                        :href="
                            route('admin.areas.show', categoria.area?.id_area)
                        "
                        class="text-xl font-bold text-blue-400 hover:text-blue-300 transition-colors duration-200"
                    >
                        {{ categoria.area?.name_area ?? "—" }}
                    </a>
                </div>

                <!-- SLA -->
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-4 shadow-xl shadow-blue-500/10"
                >
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 rounded-lg bg-amber-500/10">
                            <svg
                                class="w-4 h-4 text-amber-400"
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
                        <span
                            class="text-xs font-medium text-slate-400 uppercase tracking-wider"
                            >Tiempo SLA</span
                        >
                    </div>
                    <p class="text-2xl font-bold text-amber-400">
                        {{ categoria.tiempo_sla_horas
                        }}<span
                            class="text-base font-medium text-slate-400 ml-1"
                            >horas</span
                        >
                    </p>
                </div>
            </div>

            <!-- Ficha de datos -->
            <div
                class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/10 overflow-hidden"
            >
                <div
                    class="px-6 py-4 border-b border-slate-700 flex items-center gap-2"
                >
                    <svg
                        class="w-4 h-4 text-blue-400"
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
                    <h2 class="text-sm font-semibold text-white">
                        Información de la Categoría
                    </h2>
                </div>
                <dl class="divide-y divide-slate-700/50">
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-slate-400">ID</dt>
                        <dd class="text-sm text-white col-span-2">
                            {{ categoria.id_categoria }}
                        </dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-slate-400">
                            Nombre
                        </dt>
                        <dd class="text-sm text-white col-span-2">
                            {{ categoria.nombre_categoria }}
                        </dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-slate-400">Área</dt>
                        <dd class="text-sm col-span-2">
                            <a
                                :href="
                                    route(
                                        'admin.areas.show',
                                        categoria.area?.id_area,
                                    )
                                "
                                class="text-blue-400 hover:text-blue-300 transition-colors duration-200"
                            >
                                {{ categoria.area?.name_area ?? "—" }}
                            </a>
                        </dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-slate-400">
                            Tiempo SLA
                        </dt>
                        <dd class="text-sm text-white col-span-2">
                            {{ categoria.tiempo_sla_horas }} horas
                        </dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-slate-400">
                            Fecha de creación
                        </dt>
                        <dd class="text-sm text-white col-span-2">
                            {{ formatDate(categoria.created_at) }}
                        </dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-slate-400">
                            Última actualización
                        </dt>
                        <dd class="text-sm text-white col-span-2">
                            {{ formatDate(categoria.updated_at) }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Acciones peligrosas -->
            <div class="flex justify-end gap-3">
                <button
                    @click="confirmDelete"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-red-500/40 rounded-lg text-sm font-medium text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200"
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
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                    Eliminar categoría
                </button>
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
                                Editar Categoría
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
                            @submit.prevent="submitEdit"
                            class="p-6 space-y-4"
                        >
                            <!-- Nombre -->
                            <div>
                                <label
                                    for="edit_nombre"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Nombre <span class="text-red-400">*</span>
                                </label>
                                <input
                                    v-model="editForm.nombre_categoria"
                                    id="edit_nombre"
                                    type="text"
                                    required
                                    maxlength="120"
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.nombre_categoria
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                />
                                <p
                                    v-if="errors.nombre_categoria"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.nombre_categoria }}
                                </p>
                            </div>

                            <!-- Área -->
                            <div>
                                <label
                                    for="edit_area"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Área <span class="text-red-400">*</span>
                                </label>
                                <select
                                    v-model="editForm.id_area"
                                    id="edit_area"
                                    required
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.id_area
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                >
                                    <option
                                        v-for="area in areas"
                                        :key="area.id_area"
                                        :value="area.id_area"
                                    >
                                        {{ area.name_area }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.id_area"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.id_area }}
                                </p>
                            </div>

                            <!-- SLA -->
                            <div>
                                <label
                                    for="edit_sla"
                                    class="block text-sm font-medium text-white mb-2"
                                >
                                    Tiempo SLA (horas)
                                    <span class="text-red-400">*</span>
                                </label>
                                <input
                                    v-model.number="editForm.tiempo_sla_horas"
                                    id="edit_sla"
                                    type="number"
                                    required
                                    min="1"
                                    class="block w-full rounded-lg bg-slate-700/50 border text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                                    :class="
                                        errors.tiempo_sla_horas
                                            ? 'border-red-500'
                                            : 'border-slate-600 focus:border-blue-500'
                                    "
                                />
                                <p
                                    v-if="errors.tiempo_sla_horas"
                                    class="mt-1 text-sm text-red-400"
                                >
                                    {{ errors.tiempo_sla_horas }}
                                </p>
                            </div>

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
    categoria: Object,
    areas: Array,
});

// Modal editar
const showModal = ref(false);
const processing = ref(false);
const errors = ref({});

const editForm = reactive({
    nombre_categoria: "",
    id_area: "",
    tiempo_sla_horas: "",
});

function openEditModal() {
    editForm.nombre_categoria = props.categoria.nombre_categoria;
    editForm.id_area = props.categoria.id_area;
    editForm.tiempo_sla_horas = props.categoria.tiempo_sla_horas;
    errors.value = {};
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function submitEdit() {
    processing.value = true;
    errors.value = {};

    router.put(
        route("admin.categorias-ticket.update", props.categoria.id_categoria),
        editForm,
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

// Eliminar
function confirmDelete() {
    if (
        confirm(
            `¿Estás seguro de eliminar la categoría "${props.categoria.nombre_categoria}"? Esta acción no se puede deshacer.`,
        )
    ) {
        router.delete(
            route(
                "admin.categorias-ticket.destroy",
                props.categoria.id_categoria,
            ),
        );
    }
}

// Utilitarios
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
