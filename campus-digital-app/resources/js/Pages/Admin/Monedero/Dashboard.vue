<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                Dashboard de Monedero Digital
            </h2>
        </template>

        <div class="space-y-6">
            <!-- FILTROS -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Fecha Inicio</label>
                            <input
                                type="date"
                                v-model="filters.desde"
                                @change="loadData"
                                class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Fecha Fin</label>
                            <input
                                type="date"
                                v-model="filters.hasta"
                                @change="loadData"
                                class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Módulo</label>
                            <select
                                v-model="filters.modulo"
                                @change="loadData"
                                class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                            >
                                <option value="">Todos</option>
                                <option value="cafeteria">Cafetería</option>
                                <option value="copias">Copias/Impresiones</option>
                                <option value="souvenirs">Souvenirs</option>
                                <option value="biblioteca">Biblioteca</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                @click="loadData"
                                class="flex-1 px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-cyan-500/20 hover:shadow-xl hover:shadow-cyan-500/30 transition-all duration-200"
                            >
                                Filtrar
                            </button>
                            <button
                                @click="limpiarFiltros"
                                class="flex-1 px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30 transition-all duration-200"
                            >
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

                <!-- KPI CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-slate-800 border border-blue-500/30 rounded-xl p-6">
                        <h3 class="text-sm font-medium text-slate-300">Saldo Total Administrado</h3>
                        <p class="text-3xl font-bold text-blue-400 mt-2">{{ formatCurrency(kpis.saldoTotal) }}</p>
                        <p class="text-xs text-slate-400 mt-1">Saldo disponible</p>
                    </div>
                    <div class="bg-slate-800 border border-green-500/30 rounded-xl p-6">
                        <h3 class="text-sm font-medium text-slate-300">Total Cargado</h3>
                        <p class="text-3xl font-bold text-green-400 mt-2">{{ formatCurrency(kpis.totalAbonos) }}</p>
                        <p class="text-xs text-slate-400 mt-1">Recargas exitosas</p>
                    </div>
                    <div class="bg-slate-800 border border-red-500/30 rounded-xl p-6">
                        <h3 class="text-sm font-medium text-slate-300">Total Gastado</h3>
                        <p class="text-3xl font-bold text-red-400 mt-2">{{ formatCurrency(kpis.totalCargos) }}</p>
                        <p class="text-xs text-slate-400 mt-1">En el período</p>
                    </div>
                    <div class="bg-slate-800 border border-purple-500/30 rounded-xl p-6">
                        <h3 class="text-sm font-medium text-slate-300">Usuarios Activos</h3>
                        <p class="text-3xl font-bold text-purple-400 mt-2">{{ kpis.usuariosActivos }}</p>
                        <p class="text-xs text-slate-400 mt-1">Con movimientos</p>
                    </div>
                </div>

                <!-- GRÁFICOS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Distribución: Cargos vs Abonos</h3>
                        <canvas id="pieChart" class="max-w-sm mx-auto"></canvas>
                    </div>
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Evolución del Saldo Total</h3>
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>

                <!-- TOP USUARIOS + CONSUMO POR MÓDULO -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Top 10 Usuarios por Consumo</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-700/50 border-b border-slate-600">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Usuario</th>
                                        <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Consumo</th>
                                        <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Transacciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(user, idx) in topUsuarios" :key="idx" class="border-b border-slate-700 hover:bg-slate-700/30">
                                        <td class="px-4 py-3 text-slate-200">{{ user.nombre }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-slate-200">{{ formatCurrency(user.total_consumo) }}</td>
                                        <td class="px-4 py-3 text-right text-slate-200">{{ user.cantidad_movimientos }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Consumo por Categoría</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-700/50 border-b border-slate-600">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Categoría</th>
                                        <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Monto</th>
                                        <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Transacciones</th>
                                        <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(modulo, idx) in consumoModulos" :key="idx" class="border-b border-slate-700 hover:bg-slate-700/30">
                                        <td class="px-4 py-3 text-slate-200 font-medium">{{ modulo.modulo }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-slate-200">{{ formatCurrency(modulo.monto) }}</td>
                                        <td class="px-4 py-3 text-right text-slate-200">{{ modulo.cantidad }}</td>
                                        <td class="px-4 py-3 text-right text-slate-200">{{ modulo.porcentaje }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ÚLTIMOS MOVIMIENTOS -->
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Últimos 20 Movimientos</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-700/50 border-b border-slate-600">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Fecha</th>
                                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Usuario</th>
                                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Tipo</th>
                                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Módulo</th>
                                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Concepto</th>
                                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Monto</th>
                                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Saldo Nuevo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="mov in ultimosMovimientos" :key="mov.id" class="border-b border-slate-700 hover:bg-slate-700/30">
                                    <td class="px-4 py-3 text-slate-200">{{ formatDate(mov.created_at) }}</td>
                                    <td class="px-4 py-3 text-slate-200">{{ mov.usuario.nombre }}</td>
                                    <td class="px-4 py-3">
                                        <span v-if="mov.tipo === 'abono'" class="text-green-400 font-semibold">✓ Abono</span>
                                        <span v-else class="text-red-400 font-semibold">✗ Cargo</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-200">{{ mov.modulo }}</td>
                                    <td class="px-4 py-3 text-slate-200">{{ mov.concepto }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-200">{{ formatCurrency(mov.monto) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-200">{{ formatCurrency(mov.saldo_nuevo) }}</td>
                                </tr>
                            </tbody>
                        </table>
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
            "/api/v1/admin/monedero/analytics/dashboard",
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

function limpiarFiltros() {
    filters.value = {
        desde: getFecha30DiasAtras(),
        hasta: new Date().toISOString().split("T")[0],
        modulo: "",
    };
    loadData();
}

function renderCharts(data) {
    const gc = 'rgba(30,58,138,0.18)';
    const tc = '#94a3b8';

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
                    borderColor: ["#1e293b", "#1e293b"],
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: { color: tc },
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
                    pointBackgroundColor: "#3b82f6",
                    pointRadius: 3,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "top",
                    labels: { color: tc },
                },
            },
            scales: {
                x: {
                    grid: { color: gc },
                    ticks: { color: tc },
                },
                y: {
                    grid: { color: gc },
                    ticks: { color: tc },
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


