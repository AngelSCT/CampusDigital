<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
    porEstado: Object, porModulo: Object, kpis: Object,
    ultimos7dias: Array, topUsuarios: Array, estados: Array, modulos: Array,
})

const coloresBadge = {
    creado:     'bg-blue-900/40 text-blue-300 border border-blue-700',
    aceptado:   'bg-yellow-900/40 text-yellow-300 border border-yellow-700',
    en_proceso: 'bg-orange-900/40 text-orange-300 border border-orange-700',
    listo:      'bg-purple-900/40 text-purple-300 border border-purple-700',
    entregado:  'bg-green-900/40 text-green-300 border border-green-700',
    cancelado:  'bg-red-900/40 text-red-300 border border-red-700',
}
const etiquetas = {
    creado:'Creado', aceptado:'Aceptado', en_proceso:'En proceso',
    listo:'Listo', entregado:'Entregado', cancelado:'Cancelado',
}

const maxDia    = computed(() => Math.max(...props.ultimos7dias.map(d => d.total), 1))
const maxModulo = computed(() => Math.max(...props.modulos.map(m => props.porModulo[m] ?? 0), 1))
const maxEstado = computed(() => Math.max(...Object.values(props.porEstado), 1))
</script>

<template>
    <AuthLayout title="Dashboard — Pedidos">
        <div class="max-w-7xl mx-auto px-4 py-8">

            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-white">Dashboard de Pedidos</h1>
                    <p class="text-sm text-gray-400 mt-1">Módulo 4.5 — Indicadores en tiempo real</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/pedidos/panel/reportes"
                        class="px-4 py-2 bg-[#2d2d3f] text-gray-300 rounded-lg text-sm font-medium hover:bg-[#3d3d4f] transition">
                        Ver Reportes
                    </Link>
                    <Link href="/pedidos/panel/operador"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition">
                        Panel Operador
                    </Link>
                    <Link href="/pedidos"
                        class="px-4 py-2 bg-[#16213e] text-gray-300 rounded-lg text-sm font-medium hover:bg-[#1e2d4e] transition">
                        Lista Pedidos
                    </Link>
                </div>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-4 text-center">
                    <p class="text-2xl font-bold text-purple-400">{{ kpis.hoy }}</p>
                    <p class="text-xs text-gray-400 mt-1">Hoy</p>
                </div>
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-4 text-center">
                    <p class="text-2xl font-bold text-orange-400">{{ kpis.activos }}</p>
                    <p class="text-xs text-gray-400 mt-1">Activos</p>
                </div>
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-4 text-center">
                    <p class="text-2xl font-bold text-white">{{ kpis.total }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total</p>
                </div>
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-4 text-center">
                    <p class="text-2xl font-bold text-red-400">{{ kpis.cancelados }}</p>
                    <p class="text-xs text-gray-400 mt-1">Cancelados</p>
                </div>
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-4 text-center">
                    <p class="text-2xl font-bold text-red-300">{{ kpis.tasaCancelacion }}%</p>
                    <p class="text-xs text-gray-400 mt-1">Tasa cancelación</p>
                </div>
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-4 text-center">
                    <p class="text-2xl font-bold text-green-400">{{ kpis.tiempoPromedioMin }}</p>
                    <p class="text-xs text-gray-400 mt-1">Min. promedio</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <!-- Pedidos por estado -->
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-6">
                    <h2 class="font-bold text-white mb-4">Pedidos por estado</h2>
                    <div class="space-y-3">
                        <div v-for="e in estados" :key="e" class="flex items-center gap-3">
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold w-24 text-center shrink-0', coloresBadge[e]]">
                                {{ etiquetas[e] }}
                            </span>
                            <div class="flex-1 bg-[#16213e] rounded-full h-4 overflow-hidden">
                                <div :class="['h-4 rounded-full transition-all',
                                    e === 'cancelado' ? 'bg-red-500' :
                                    e === 'entregado' ? 'bg-green-500' :
                                    e === 'listo'     ? 'bg-purple-500' :
                                    e === 'en_proceso'? 'bg-orange-500' :
                                    e === 'aceptado'  ? 'bg-yellow-500' : 'bg-blue-500']"
                                    :style="{ width: `${((porEstado[e] ?? 0) / maxEstado) * 100}%` }" />
                            </div>
                            <span class="text-sm font-bold text-gray-300 w-8 text-right">{{ porEstado[e] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Últimos 7 días -->
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-6">
                    <h2 class="font-bold text-white mb-4">Últimos 7 días</h2>
                    <div class="flex items-end gap-2 h-32">
                        <div v-for="d in ultimos7dias" :key="d.dia"
                            class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-xs font-bold text-purple-400">{{ d.total }}</span>
                            <div class="w-full bg-purple-500 rounded-t-md transition-all"
                                :style="{ height: `${(d.total / maxDia) * 96}px`, minHeight: '4px' }" />
                            <span class="text-xs text-gray-500" style="font-size:9px">
                                {{ new Date(d.dia).toLocaleDateString('es-MX', { day:'2-digit', month:'short' }) }}
                            </span>
                        </div>
                        <div v-if="!ultimos7dias.length" class="w-full text-center text-gray-500 text-sm">Sin datos</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Por módulo -->
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-6">
                    <h2 class="font-bold text-white mb-4">Pedidos por módulo</h2>
                    <div class="space-y-3">
                        <div v-for="m in modulos" :key="m" class="flex items-center gap-3">
                            <span class="text-xs text-gray-400 capitalize w-20 shrink-0">{{ m }}</span>
                            <div class="flex-1 bg-[#16213e] rounded-full h-4 overflow-hidden">
                                <div class="h-4 bg-teal-500 rounded-full transition-all"
                                    :style="{ width: `${((porModulo[m] ?? 0) / maxModulo) * 100}%` }" />
                            </div>
                            <span class="text-sm font-bold text-gray-300 w-8 text-right">{{ porModulo[m] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Top usuarios -->
                <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-6">
                    <h2 class="font-bold text-white mb-4">Top 5 usuarios</h2>
                    <div class="space-y-3">
                        <div v-if="!topUsuarios.length" class="text-gray-500 text-sm">Sin datos.</div>
                        <div v-for="(u, i) in topUsuarios" :key="u.usuario_id"
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-[#16213e] transition">
                            <div :class="['w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0',
                                i === 0 ? 'bg-yellow-500' : i === 1 ? 'bg-gray-500' : i === 2 ? 'bg-orange-500' : 'bg-purple-500']">
                                {{ i + 1 }}
                            </div>
                            <p class="flex-1 text-sm text-gray-300">
                                {{ u.usuario?.nombre }} {{ u.usuario?.apellido }}
                            </p>
                            <span class="text-sm font-bold text-purple-400">{{ u.total }} pedidos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>