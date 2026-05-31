<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Monedero Digital
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- FILTROS -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Fecha Inicio</label
                                >
                                <input
                                    type="date"
                                    v-model="filters.desde"
                                    @change="loadData"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Fecha Fin</label
                                >
                                <input
                                    type="date"
                                    v-model="filters.hasta"
                                    @change="loadData"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Módulo</label
                                >
                                <select
                                    v-model="filters.modulo"
                                    @change="loadData"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                                >
                                    <option value="">Todos</option>
                                    <option value="cafeteria">Cafetería</option>
                                    <option value="copias">
                                        Copias/Impresiones
                                    </option>
                                    <option value="souvenirs">Souvenirs</option>
                                    <option value="biblioteca">
                                        Biblioteca
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button
                                    @click="loadData"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                                >
                                    Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div
                        class="bg-blue-50 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3 class="text-sm font-medium text-gray-700">
                            Saldo Total Administrado
                        </h3>
                        <p class="text-3xl font-bold text-blue-600 mt-2">
                            ${{ formatCurrency(kpis.saldoTotal) }}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            Saldo disponible
                        </p>
                    </div>
                    <div
                        class="bg-green-50 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3 class="text-sm font-medium text-gray-700">
                            Total Cargado
                        </h3>
                        <p class="text-3xl font-bold text-green-600 mt-2">
                            ${{ formatCurrency(kpis.totalAbonos) }}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            Recargas exitosas
                        </p>
                    </div>
                    <div
                        class="bg-red-50 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3 class="text-sm font-medium text-gray-700">
                            Total Gastado
                        </h3>
                        <p class="text-3xl font-bold text-red-600 mt-2">
                            ${{ formatCurrency(kpis.totalCargos) }}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">En el período</p>
                    </div>
                    <div
                        class="bg-purple-50 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3 class="text-sm font-medium text-gray-700">
                            Usuarios Activos
                        </h3>
                        <p class="text-3xl font-bold text-purple-600 mt-2">
                            {{ kpis.usuariosActivos }}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            Con movimientos
                        </p>
                    </div>
                </div>

                <!-- GRÁFICOS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Pie Chart -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Distribución: Cargos vs Abonos
                        </h3>
                        <canvas id="pieChart" class="max-w-sm mx-auto"></canvas>
                    </div>

                    <!-- Line Chart -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Evolución del Saldo Total
                        </h3>
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>

                <!-- TOP USUARIOS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Top 10 Usuarios por Consumo
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
                                            class="px-4 py-2 text-right font-medium text-gray-700"
                                        >
                                            Consumo
                                        </th>
                                        <th
                                            class="px-4 py-2 text-right font-medium text-gray-700"
                                        >
                                            Transacciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(user, idx) in topUsuarios"
                                        :key="idx"
                                        class="border-b hover:bg-gray-50"
                                    >
                                        <td class="px-4 py-3">
                                            {{ user.nombre }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right font-semibold"
                                        >
                                            ${{
                                                formatCurrency(
                                                    user.total_consumo,
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            {{ user.cantidad_movimientos }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- CONSUMO POR MÓDULO -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Consumo por Categoría
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left font-medium text-gray-700"
                                        >
                                            Categoría
                                        </th>
                                        <th
                                            class="px-4 py-2 text-right font-medium text-gray-700"
                                        >
                                            Monto
                                        </th>
                                        <th
                                            class="px-4 py-2 text-right font-medium text-gray-700"
                                        >
                                            Transacciones
                                        </th>
                                        <th
                                            class="px-4 py-2 text-right font-medium text-gray-700"
                                        >
                                            %
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(modulo, idx) in consumoModulos"
                                        :key="idx"
                                        class="border-b hover:bg-gray-50"
                                    >
                                        <td class="px-4 py-3">
                                            {{ modulo.modulo }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right font-semibold"
                                        >
                                            ${{ formatCurrency(modulo.monto) }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            {{ modulo.cantidad }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            {{ modulo.porcentaje }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ÚLTIMOS MOVIMIENTOS -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Últimos 20 Movimientos
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left font-medium text-gray-700"
                                    >
                                        Fecha
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium text-gray-700"
                                    >
                                        Usuario
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium text-gray-700"
                                    >
                                        Tipo
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium text-gray-700"
                                    >
                                        Módulo
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium text-gray-700"
                                    >
                                        Concepto
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right font-medium text-gray-700"
                                    >
                                        Monto
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right font-medium text-gray-700"
                                    >
                                        Saldo Nuevo
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="mov in ultimosMovimientos"
                                    :key="mov.id"
                                    class="border-b hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3">
                                        {{ formatDate(mov.created_at) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ mov.usuario.nombre }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="mov.tipo === 'abono'"
                                            class="text-green-600 font-semibold"
                                            >✓ Abono</span
                                        >
                                        <span
                                            v-else
                                            class="text-red-600 font-semibold"
                                            >✗ Cargo</span
                                        >
                                    </td>
                                    <td class="px-4 py-3">{{ mov.modulo }}</td>
                                    <td class="px-4 py-3">
                                        {{ mov.concepto }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-semibold"
                                    >
                                        ${{ formatCurrency(mov.monto) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        ${{ formatCurrency(mov.saldo_nuevo) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Chart from "chart.js/auto";
import axios from "axios";

const filters = ref({
    desde: getFecha30DiasAtras(),
    hasta: new Date().toISOString().split("T")[0],
    modulo: "",
});

const kpis = ref({
    saldoTotal: 0,
    totalAbonos: 0,
    totalCargos: 0,
    usuariosActivos: 0,
});

const topUsuarios = ref([]);
const consumoModulos = ref([]);
const ultimosMovimientos = ref([]);
const charts = ref({});

function getFecha30DiasAtras() {
    const d = new Date();
    d.setDate(d.getDate() - 30);
    return d.toISOString().split("T")[0];
}

function formatCurrency(value) {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value || 0);
}

function formatDate(date) {
    return new Date(date).toLocaleString("es-AR");
}

async function loadData() {
    try {
        const response = await axios.get(
            "/api/admin/monedero/analytics/dashboard",
            {
                params: filters.value,
            },
        );

        kpis.value = response.data.kpis;
        topUsuarios.value = response.data.topUsuarios;
        consumoModulos.value = response.data.consumoModulos;
        ultimosMovimientos.value = response.data.ultimosMovimientos;

        renderCharts(response.data.chartsData);
    } catch (error) {
        console.error("Error loading dashboard:", error);
    }
}

function renderCharts(data) {
    // Pie Chart
    if (charts.value.pie) charts.value.pie.destroy();
    charts.value.pie = new Chart(document.getElementById("pieChart"), {
        type: "pie",
        data: {
            labels: ["Cargos", "Abonos"],
            datasets: [
                {
                    data: [data.totalCargos, data.totalAbonos],
                    backgroundColor: ["#ef4444", "#22c55e"],
                    borderColor: ["#ffffff", "#ffffff"],
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "bottom",
                },
            },
        },
    });

    // Line Chart
    if (charts.value.line) charts.value.line.destroy();
    charts.value.line = new Chart(document.getElementById("lineChart"), {
        type: "line",
        data: {
            labels: data.timeseriesFechas,
            datasets: [
                {
                    label: "Saldo Total ($)",
                    data: data.timeseriesSaldos,
                    borderColor: "#3b82f6",
                    backgroundColor: "rgba(59, 130, 246, 0.1)",
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "top",
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
}

onMounted(() => {
    loadData();
});
</script>

<style scoped>
/* Estilos adicionales si es necesario */
</style>
