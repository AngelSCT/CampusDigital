<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    modulo: String
});

const loading = ref(true);
const reports = ref(null);
const periodo = ref('hoy');

const fetchReports = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/proveedor/api/reports', {
            params: { periodo: periodo.value }
        });
        reports.value = response.data;
    } catch (error) {
        console.error('Error fetching reports:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchReports);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
};

const setPeriodo = (p) => {
    periodo.value = p;
    fetchReports();
};
</script>

<template>
    <Head title="Reportes de Área" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white">Reportes y Rendimiento</h1>
                    <p class="text-slate-400">Análisis detallado de ventas y tiempos de atención</p>
                </div>
                
                <div class="flex bg-slate-900 p-1 rounded-xl border border-slate-800">
                    <button 
                        @click="setPeriodo('hoy')"
                        :class="['px-4 py-1.5 text-xs font-semibold rounded-lg transition-all', periodo === 'hoy' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200']"
                    >
                        Hoy
                    </button>
                    <button 
                        @click="setPeriodo('semana')"
                        :class="['px-4 py-1.5 text-xs font-semibold rounded-lg transition-all', periodo === 'semana' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200']"
                    >
                        Semana
                    </button>
                    <button 
                        @click="setPeriodo('mes')"
                        :class="['px-4 py-1.5 text-xs font-semibold rounded-lg transition-all', periodo === 'mes' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200']"
                    >
                        Mes
                    </button>
                </div>
            </div>

            <div v-if="loading" class="py-12 flex justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            </div>

            <div v-else-if="reports">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-2xl border border-blue-500/20 shadow-xl">
                        <p class="text-sm font-medium text-slate-400 uppercase tracking-wider">Total Ventas ({{ periodo }})</p>
                        <p class="text-3xl font-black text-white mt-2">{{ formatCurrency(reports.total_ventas) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-2xl border border-green-500/20 shadow-xl">
                        <p class="text-sm font-medium text-slate-400 uppercase tracking-wider">Pedidos Atendidos</p>
                        <p class="text-3xl font-black text-white mt-2">{{ reports.total_pedidos }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-2xl border border-purple-500/20 shadow-xl">
                        <p class="text-sm font-medium text-slate-400 uppercase tracking-wider">Tiempo Promedio</p>
                        <p class="text-3xl font-black text-white mt-2">{{ reports.tiempo_avg || 0 }} <span class="text-sm text-slate-500">min</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Ventas por Producto -->
                    <div class="bg-slate-900/50 rounded-2xl border border-slate-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-800 bg-slate-900/80">
                            <h3 class="text-lg font-bold text-white">Ventas por Producto</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div v-for="item in reports.ventas_por_producto" :key="item.producto" class="flex items-center justify-between p-3 bg-slate-800/40 rounded-xl">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-600/20 flex items-center justify-center text-blue-400 font-bold text-xs">
                                            {{ item.cantidad }}
                                        </div>
                                        <span class="text-sm font-medium text-slate-200">{{ item.producto }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-white">{{ formatCurrency(item.total) }}</span>
                                </div>
                                <div v-if="!reports.ventas_por_producto?.length" class="text-center py-6 text-slate-500 italic">
                                    No hay datos de productos vendidos.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rendimiento por Turno -->
                    <div class="bg-slate-900/50 rounded-2xl border border-slate-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-800 bg-slate-900/80">
                            <h3 class="text-lg font-bold text-white">Rendimiento por Turno</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div v-for="turno in reports.rendimiento_turno" :key="turno.turno" class="flex items-center justify-between p-4 bg-slate-800 rounded-xl border border-slate-700">
                                    <div>
                                        <p class="text-sm font-bold text-white">{{ turno.turno }}</p>
                                        <p class="text-xs text-slate-400">{{ turno.cantidad }} pedidos</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-blue-400">{{ parseFloat(turno.tiempo_promedio).toFixed(1) }} min</p>
                                        <p class="text-xs text-slate-500 uppercase font-semibold tracking-tighter">Tiempo Avg</p>
                                    </div>
                                </div>
                                <div v-if="reports.rendimiento_turno.length === 0" class="text-center py-6 text-slate-500 italic">
                                    No hay datos suficientes para este periodo.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ventas por Día -->
                    <div class="bg-slate-900/50 rounded-2xl border border-slate-800 overflow-hidden lg:col-span-2">
                        <div class="px-6 py-4 border-b border-slate-800 bg-slate-900/80">
                            <h3 class="text-lg font-bold text-white">Ventas por Día</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-1">
                                <div v-for="dia in reports.ventas_por_dia" :key="dia.fecha" class="flex flex-col">
                                    <div class="flex justify-between text-xs text-slate-400 mb-1">
                                        <span>{{ dia.fecha }}</span>
                                        <span class="font-bold text-white">{{ formatCurrency(dia.total) }}</span>
                                    </div>
                                    <div class="w-full bg-slate-800 rounded-full h-2 mb-4">
                                        <div 
                                            class="bg-blue-500 h-2 rounded-full transition-all duration-1000" 
                                            :style="{ width: (dia.total / reports.total_ventas * 100) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                                <div v-if="reports.ventas_por_dia.length === 0" class="text-center py-6 text-slate-500 italic">
                                    No hay registros históricos en este periodo.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Atención -->
                <div class="mt-6 bg-slate-900/50 rounded-2xl border border-slate-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-800 bg-slate-900/80">
                        <h3 class="text-lg font-bold text-white">Historial de Atención (Últimos {{ periodo }})</h3>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-300">
                                <thead class="bg-slate-800/50 text-xs uppercase text-slate-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Folio</th>
                                        <th scope="col" class="px-6 py-3">Cliente</th>
                                        <th scope="col" class="px-6 py-3">Tiempo (min)</th>
                                        <th scope="col" class="px-6 py-3">Total</th>
                                        <th scope="col" class="px-6 py-3">Hora Entrega</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="pedido in reports.historial_atencion" :key="pedido.id" class="border-b border-slate-700/50 hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 font-mono text-blue-400 font-bold">
                                            {{ pedido.numero_folio }}
                                        </td>
                                        <td class="px-6 py-4 font-medium text-white">
                                            {{ pedido.usuario?.nombre }} {{ pedido.usuario?.apellido }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold" :class="((new Date(pedido.confirmado_at) - new Date(pedido.created_at))/60000) > 15 ? 'bg-orange-500/20 text-orange-400' : 'bg-emerald-500/20 text-emerald-400'">
                                                {{ Math.round((new Date(pedido.confirmado_at) - new Date(pedido.created_at)) / 60000) }} min
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-white">
                                            {{ formatCurrency(pedido.total) }}
                                        </td>
                                        <td class="px-6 py-4 text-xs text-slate-400">
                                            {{ new Date(pedido.confirmado_at).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="!reports.historial_atencion?.length" class="text-center py-8 text-slate-500 italic">
                                No hay pedidos entregados recientemente.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
