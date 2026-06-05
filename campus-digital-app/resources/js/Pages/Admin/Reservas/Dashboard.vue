<template>
    <Head title="Dashboard Reservas" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
                        Dashboard — Módulo 4.7
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Reservas, ocupación y turnos en tiempo real</p>
                </div>
                <a href="/admin/reservas" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg">
                    ← Volver
                </a>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-cyan-900/30 to-cyan-800/20 rounded-xl border border-cyan-500/30 p-5">
                    <p class="text-xs text-cyan-300 uppercase tracking-wider">Reservas hoy</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ kpis.reservas_hoy }}</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-900/30 to-emerald-800/20 rounded-xl border border-emerald-500/30 p-5">
                    <p class="text-xs text-emerald-300 uppercase tracking-wider">Ocupadas ahora</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ kpis.ocupadas_ahora }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-900/30 to-red-800/20 rounded-xl border border-red-500/30 p-5">
                    <p class="text-xs text-red-300 uppercase tracking-wider">Canceladas hoy</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ kpis.canceladas_hoy }}</p>
                </div>
                <div class="bg-gradient-to-br from-yellow-900/30 to-yellow-800/20 rounded-xl border border-yellow-500/30 p-5">
                    <p class="text-xs text-yellow-300 uppercase tracking-wider">No-show (7d)</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ kpis.no_show_7d }}</p>
                </div>
            </div>

            <!-- Gráfica horarios pico -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-6">
                <h2 class="text-sm font-semibold text-white mb-4">Horarios pico (últimos 7 días)</h2>
                <div class="flex items-end justify-between gap-1 h-48">
                    <div v-for="(valor, hora) in horariosPico" :key="hora" class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gradient-to-t from-cyan-600 to-cyan-400 rounded-t hover:from-cyan-500 hover:to-cyan-300 transition-all"
                            :style="{ height: maxH > 0 ? `${(valor / maxH) * 100}%` : '0%' }"
                            :title="`${hora}:00 — ${valor} reservas`"></div>
                        <span v-if="hora % 3 === 0" class="text-xs text-slate-400 mt-1">{{ hora }}h</span>
                    </div>
                </div>
            </div>

            <!-- Tabla de ocupación por recurso -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Ocupación por recurso (7d)</h2>
                    <div class="space-y-2">
                        <div v-for="r in ocupacionPorRecurso" :key="r.id_recurso">
                            <div class="flex justify-between text-xs text-slate-300 mb-1">
                                <span>{{ r.nombre }} <span class="text-slate-500">({{ r.tipo }})</span></span>
                                <span class="font-mono text-cyan-300">{{ r.total_reservas }} reservas · {{ Math.round(r.total_horas) }}h</span>
                            </div>
                            <div class="w-full bg-slate-700/50 rounded-full h-1.5">
                                <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-1.5 rounded-full"
                                    :style="{ width: `${Math.min(100, (r.total_reservas / 20) * 100)}%` }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Cancelaciones y no-shows (7d)</h2>
                    <div v-if="problemasPorRecurso.length === 0" class="text-sm text-slate-400 text-center py-6">
                        Sin cancelaciones ni no-shows.
                    </div>
                    <div v-else class="space-y-2">
                        <div v-for="r in problemasPorRecurso" :key="r.nombre" class="flex justify-between items-center text-sm">
                            <span class="text-slate-300">{{ r.nombre }}</span>
                            <div class="flex gap-3">
                                <span class="text-red-300">{{ r.cancelaciones }} cancel.</span>
                                <span class="text-yellow-300">{{ r.no_shows }} no-show</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reportes -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="/admin/reservas/reportes/por-recurso"
                    class="block bg-gradient-to-br from-cyan-900/30 to-cyan-800/20 rounded-xl border border-cyan-500/30 p-5 hover:border-cyan-500/60 transition-all">
                    <p class="text-sm font-semibold text-cyan-300">Reservas por recurso</p>
                    <p class="text-xs text-slate-400 mt-1">Top recursos más usados</p>
                </a>
                <a href="/admin/reservas/reportes/por-usuario"
                    class="block bg-gradient-to-br from-emerald-900/30 to-emerald-800/20 rounded-xl border border-emerald-500/30 p-5 hover:border-emerald-500/60 transition-all">
                    <p class="text-sm font-semibold text-emerald-300">Reservas por usuario</p>
                    <p class="text-xs text-slate-400 mt-1">Quién reserva más</p>
                </a>
                <a href="/admin/reservas/reportes/infrautilizados"
                    class="block bg-gradient-to-br from-amber-900/30 to-amber-800/20 rounded-xl border border-amber-500/30 p-5 hover:border-amber-500/60 transition-all">
                    <p class="text-sm font-semibold text-amber-300">Recursos infrautilizados</p>
                    <p class="text-xs text-slate-400 mt-1">Con menos de 3 reservas</p>
                </a>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    kpis:                Object,
    ocupacionPorRecurso: Array,
    horariosPico:        Object,
    tendencia:           Array,
    problemasPorRecurso: Array,
});

const maxH = computed(() => {
    return Math.max(1, ...Object.values(props.horariosPico || {}));
});
</script>
