<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ regla ? "Editar Regla" : "Nueva Regla de Saldo" }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <form @submit.prevent="guardarRegla">
                        <!-- Usuario -->
                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Usuario <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.usuario_id"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Seleccionar usuario</option>
                                <option
                                    v-for="user in usuarios"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.nombre }}
                                </option>
                            </select>
                            <p
                                v-if="errors.usuario_id"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ errors.usuario_id[0] }}
                            </p>
                        </div>

                        <!-- Tipo Límite -->
                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Tipo Límite <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.tipo_limite"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Seleccionar tipo</option>
                                <option value="diario">Diario</option>
                                <option value="semanal">Semanal</option>
                                <option value="mensual">Mensual</option>
                            </select>
                            <p
                                v-if="errors.tipo_limite"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ errors.tipo_limite[0] }}
                            </p>
                        </div>

                        <!-- Monto Límite -->
                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Monto Límite <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                v-model.number="form.monto_limite"
                                step="0.01"
                                min="0.01"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ej: 100.00"
                            />
                            <p
                                v-if="errors.monto_limite"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ errors.monto_limite[0] }}
                            </p>
                        </div>

                        <!-- Módulo -->
                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Módulo (opcional)
                            </label>
                            <select
                                v-model="form.modulo"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Todos los módulos</option>
                                <option value="cafeteria">Cafetería</option>
                                <option value="copias">
                                    Copias/Impresiones
                                </option>
                                <option value="souvenirs">Souvenirs</option>
                                <option value="biblioteca">Biblioteca</option>
                                <option value="recarga">Recarga</option>
                                <option value="otro">Otro</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Si dejas en blanco, la regla aplica a todos los
                                módulos
                            </p>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Descripción (opcional)
                            </label>
                            <textarea
                                v-model="form.descripcion"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ej: Límite diario para cafetería"
                            ></textarea>
                        </div>

                        <!-- Activo -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form.activo"
                                    class="rounded border-gray-300"
                                />
                                <span class="ml-2 text-sm text-gray-700"
                                    >Regla activa</span
                                >
                            </label>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-4">
                            <button
                                type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                                :disabled="loading"
                            >
                                {{
                                    loading
                                        ? "Guardando..."
                                        : regla
                                          ? "Actualizar"
                                          : "Crear"
                                }}
                                Regla
                            </button>
                            <a
                                href="/admin/monedero/reglas"
                                class="px-6 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition"
                            >
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";

const props = defineProps({
    regla: {
        type: Object,
        default: null,
    },
    usuarios: {
        type: Array,
        required: true,
    },
});

const form = ref({
    usuario_id: props.regla?.usuario_id || "",
    tipo_limite: props.regla?.tipo_limite || "",
    monto_limite: props.regla?.monto_limite || "",
    modulo: props.regla?.modulo || "",
    descripcion: props.regla?.descripcion || "",
    activo: props.regla?.activo ?? true,
});

const errors = ref({});
const loading = ref(false);

async function guardarRegla() {
    loading.value = true;
    errors.value = {};

    try {
        if (props.regla) {
            // Actualizar
            await axios.put(
                `/api/admin/monedero/reglas/${props.regla.id}`,
                form.value,
            );
        } else {
            // Crear
            await axios.post("/api/admin/monedero/reglas", form.value);
        }
        window.location.href = "/admin/monedero/reglas";
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            alert("Error al guardar la regla");
        }
    } finally {
        loading.value = false;
    }
}
</script>
