<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Reglas de Saldo
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- BOTÓN CREAR REGLA -->
                <div class="mb-6">
                    <a
                        href="/admin/monedero/reglas/create"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                    >
                        + Nueva Regla
                    </a>
                </div>

                <!-- LISTADO DE REGLAS -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Reglas Activas
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left font-medium text-gray-700"
                                        >
                                            Usuario
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left font-medium text-gray-700"
                                        >
                                            Tipo Límite
                                        </th>
                                        <th
                                            class="px-4 py-2 text-right font-medium text-gray-700"
                                        >
                                            Monto Límite
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left font-medium text-gray-700"
                                        >
                                            Módulo
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left font-medium text-gray-700"
                                        >
                                            Descripción
                                        </th>
                                        <th
                                            class="px-4 py-2 text-center font-medium text-gray-700"
                                        >
                                            Estado
                                        </th>
                                        <th
                                            class="px-4 py-2 text-center font-medium text-gray-700"
                                        >
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="regla in reglas.data"
                                        :key="regla.id"
                                        class="border-b hover:bg-gray-50"
                                    >
                                        <td class="px-4 py-3">
                                            {{ regla.usuario?.nombre }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium"
                                            >
                                                {{
                                                    formatTipoLimite(
                                                        regla.tipo_limite,
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right font-semibold"
                                        >
                                            ${{
                                                formatCurrency(
                                                    regla.monto_limite,
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                v-if="regla.modulo"
                                                class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs"
                                            >
                                                {{ regla.modulo }}
                                            </span>
                                            <span v-else class="text-gray-500"
                                                >Todos</span
                                            >
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ regla.descripcion || "-" }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                v-if="regla.activo"
                                                class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium"
                                            >
                                                ✓ Activo
                                            </span>
                                            <span
                                                v-else
                                                class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium"
                                            >
                                                ✗ Inactivo
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div
                                                class="flex gap-2 justify-center"
                                            >
                                                <a
                                                    :href="`/admin/monedero/reglas/${regla.id}/edit`"
                                                    class="text-blue-600 hover:text-blue-900"
                                                >
                                                    ✏️ Editar
                                                </a>
                                                <button
                                                    @click="
                                                        eliminarRegla(regla.id)
                                                    "
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    🗑️ Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINACIÓN -->
                        <div
                            v-if="reglas.links"
                            class="mt-6 flex justify-center gap-2"
                        >
                            <a
                                v-for="link in reglas.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 rounded text-sm',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : link.url
                                          ? 'bg-gray-200 hover:bg-gray-300'
                                          : 'bg-gray-100 text-gray-400',
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    reglas: {
        type: Object,
        required: true,
    },
});

const reglas = ref(props.reglas);

function formatTipoLimite(tipo) {
    const tipos = {
        diario: "Diario",
        semanal: "Semanal",
        mensual: "Mensual",
    };
    return tipos[tipo] || tipo;
}

function formatCurrency(value) {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value || 0);
}

function eliminarRegla(id) {
    if (confirm("¿Estás seguro de que deseas eliminar esta regla?")) {
        // TODO: Hacer DELETE request
        axios
            .delete(`/admin/monedero/reglas/${id}`)
            .then(() => {
                window.location.reload();
            })
            .catch((error) => console.error("Error:", error));
    }
}
</script>
