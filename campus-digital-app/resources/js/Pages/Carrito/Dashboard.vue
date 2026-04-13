<template>
    <AuthLayout>
        <div class="space-y-6">

            <!-- ── Encabezado ──────────────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                        Dashboard de Tienda
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Métricas de negocio en tiempo real — Módulo 4.4</p>
                </div>

                <!-- Exportar CSV -->
                <a href="/carrito/dashboard/exportar"
                   class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white
                          bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500
                          rounded-xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40
                          transition-all duration-200 self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Exportar Reporte CSV
                </a>
            </div>

            <!-- ── 3 KPI Cards ─────────────────────────────────────────────── -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                <!-- Tasa de Abandono -->
                <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 to-slate-900
                            border border-blue-500/20 rounded-2xl p-6
                            shadow-xl shadow-blue-500/5">
                    <!-- Glow decorativo -->
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-700
                                        flex items-center justify-center shadow-lg shadow-blue-500/30">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Tasa de Abandono
                            </span>
                        </div>
                        <p class="text-4xl font-black text-white mb-1">{{ tasaAbandono }}<span class="text-2xl text-blue-400">%</span></p>
                        <p class="text-xs text-slate-500">Ítems guardados para después vs total</p>
                        <!-- Mini barra -->
                        <div class="mt-4 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-1.5 rounded-full bg-gradient-to-r from-blue-600 to-blue-400 transition-all duration-1000"
                                 :style="{ width: tasaAbandono + '%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Consumo Promedio -->
                <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 to-slate-900
                            border border-cyan-500/20 rounded-2xl p-6
                            shadow-xl shadow-cyan-500/5">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-600 to-cyan-700
                                        flex items-center justify-center shadow-lg shadow-cyan-500/30">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Consumo Promedio
                            </span>
                        </div>
                        <p class="text-4xl font-black text-white mb-1">
                            <span class="text-2xl text-cyan-400">$</span>{{ formatNum(consumoPromedio) }}
                        </p>
                        <p class="text-xs text-slate-500">Por pedido confirmado</p>
                        <div class="mt-4 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-1.5 rounded-full bg-gradient-to-r from-cyan-600 to-cyan-400"
                                 style="width: 65%"></div>
                        </div>
                    </div>
                </div>

                <!-- Ventas Totales -->
                <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 to-slate-900
                            border border-indigo-500/20 rounded-2xl p-6
                            shadow-xl shadow-indigo-500/5">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-700
                                        flex items-center justify-center shadow-lg shadow-indigo-500/30">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Ventas Totales
                            </span>
                        </div>
                        <p class="text-4xl font-black text-white mb-1">
                            <span class="text-2xl text-indigo-400">$</span>{{ formatNum(ventasTotales) }}
                        </p>
                        <p class="text-xs text-slate-500">{{ totalPedidos }} pedidos confirmados</p>
                        <div class="mt-4 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-1.5 rounded-full bg-gradient-to-r from-indigo-600 to-indigo-400"
                                 style="width: 80%"></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Fila inferior ───────────────────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                <!-- Gráfica de barras: Horarios Pico (span 3) -->
                <div class="lg:col-span-3 bg-gradient-to-br from-slate-800 to-slate-900
                            border border-slate-700/40 rounded-2xl p-6">
                    <h3 class="text-white font-semibold text-base mb-1 flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Horarios Pico
                    </h3>
                    <p class="text-xs text-slate-500 mb-5">Pedidos confirmados por hora del día</p>

                    <div v-if="horariosPico.length" class="space-y-3">
                        <div v-for="(h, idx) in horariosPicoOrdenado" :key="h.hora"
                             class="flex items-center gap-3 group">
                            <!-- Hora -->
                            <span class="w-14 text-right text-xs font-mono text-slate-400 flex-shrink-0">
                                {{ h.etiqueta }}
                            </span>
                            <!-- Barra -->
                            <div class="flex-1 h-7 bg-slate-700/50 rounded-lg overflow-hidden relative">
                                <div class="h-full rounded-lg transition-all duration-700 ease-out"
                                     :class="idx === 0
                                        ? 'bg-gradient-to-r from-blue-600 to-cyan-500'
                                        : idx === 1
                                        ? 'bg-gradient-to-r from-blue-700 to-blue-500'
                                        : 'bg-gradient-to-r from-slate-600 to-slate-500'"
                                     :style="{ width: porcentajePico(h.total) + '%' }">
                                </div>
                                <!-- Label dentro de la barra si hay espacio -->
                                <span class="absolute inset-0 flex items-center px-3 text-xs font-semibold text-white/80">
                                    {{ h.total }} {{ h.total === 1 ? 'pedido' : 'pedidos' }}
                                </span>
                            </div>
                            <!-- Badge top -->
                            <span v-if="idx === 0"
                                  class="flex-shrink-0 text-xs font-bold px-2 py-0.5 rounded-full
                                         bg-yellow-400/10 text-yellow-400 border border-yellow-400/20">
                                PICO
                            </span>
                        </div>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else class="py-12 flex flex-col items-center justify-center text-center">
                        <div class="w-14 h-14 rounded-full bg-slate-700/50 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Sin pedidos registrados</p>
                        <p class="text-xs text-slate-600 mt-1">Los datos aparecerán cuando haya ventas</p>
                    </div>
                </div>

                <!-- Top 5 Productos (span 2) -->
                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800 to-slate-900
                            border border-slate-700/40 rounded-2xl p-6">
                    <h3 class="text-white font-semibold text-base mb-1 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Top Productos
                    </h3>
                    <p class="text-xs text-slate-500 mb-5">Más solicitados en carrito</p>

                    <div v-if="topProductos.length" class="space-y-2.5">
                        <div v-for="(p, idx) in topProductos" :key="p.nombre"
                             class="flex items-center gap-3 p-3 rounded-xl bg-slate-700/20 hover:bg-slate-700/40 transition-colors duration-150">
                            <!-- Medalla -->
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black flex-shrink-0"
                                 :class="idx === 0 ? 'bg-yellow-500/20 text-yellow-400'
                                       : idx === 1 ? 'bg-slate-400/20 text-slate-300'
                                       : idx === 2 ? 'bg-orange-700/20 text-orange-500'
                                       : 'bg-slate-600/20 text-slate-500'">
                                {{ idx + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate leading-tight">{{ p.nombre }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ p.categoria }}</p>
                            </div>
                            <span class="text-sm font-bold text-cyan-400 flex-shrink-0 tabular-nums">
                                {{ p.unidades_vendidas }}
                                <span class="text-xs font-normal text-slate-500">uds</span>
                            </span>
                        </div>
                    </div>

                    <div v-else class="py-12 flex flex-col items-center justify-center text-center">
                        <div class="w-14 h-14 rounded-full bg-slate-700/50 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Sin productos vendidos</p>
                        <p class="text-xs text-slate-600 mt-1">Aparecerán cuando haya pedidos</p>
                    </div>
                </div>

            </div>

            <!-- Acceso rápido -->
            <div class="flex justify-start">
                <a href="/carrito"
                   class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver al carrito
                </a>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    tasaAbandono:    { type: Number, default: 0 },
    consumoPromedio: { type: Number, default: 0 },
    totalPedidos:    { type: Number, default: 0 },
    ventasTotales:   { type: Number, default: 0 },
    horariosPico:    { type: Array,  default: () => [] },
    topProductos:    { type: Array,  default: () => [] },
});

// Ordenar horarios de 00:00 a 23:00 para el eje X
const horariosPicoOrdenado = computed(() =>
    [...props.horariosPico].sort((a, b) => b.total - a.total).slice(0, 10)
);

const maxPico = computed(() =>
    horariosPicoOrdenado.value.length
        ? Math.max(...horariosPicoOrdenado.value.map(h => h.total))
        : 1
);

function porcentajePico(total) {
    return Math.max(4, Math.round((total / maxPico.value) * 100));
}

function formatNum(val) {
    return Number(val ?? 0).toFixed(2);
}
</script>
