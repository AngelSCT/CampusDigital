<template>
    <Head title="Cart Admin — Dashboard" />
    <AuthLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-violet-400 to-blue-400 bg-clip-text text-transparent">
                    Dashboard — Módulo Carrito
                </h1>
                <p class="mt-1 text-sm text-slate-400">Métricas generales sobre tablas cart_*</p>
            </div>

            <!-- KPI cards — fila 1 -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-green-500/20 shadow-lg p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Completados</p>
                    <p class="text-3xl font-bold text-green-400">{{ data.checkouts_completados }}</p>
                    <p class="text-xs text-slate-500 mt-1">carrito confirmado</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-yellow-500/20 shadow-lg p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Pend. conciliación</p>
                    <p class="text-3xl font-bold text-yellow-400">{{ data.confirmados_pendientes }}</p>
                    <p class="text-xs text-slate-500 mt-1">pago diferido pendiente</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 shadow-lg p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Abandonados</p>
                    <p class="text-3xl font-bold text-red-400">{{ data.abandonados }}</p>
                    <p class="text-xs text-slate-500 mt-1">cancelados + expirados</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-lg p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Consumo promedio</p>
                    <p class="text-3xl font-bold text-violet-400">${{ data.consumo_promedio.toFixed(2) }}</p>
                    <p class="text-xs text-slate-500 mt-1">por checkout confirmado</p>
                </div>
            </div>

            <!-- KPI cards — fila 2 -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-lg p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Total recaudado</p>
                    <p class="text-3xl font-bold text-blue-400">${{ data.total_recaudado.toFixed(2) }}</p>
                    <p class="text-xs text-slate-500 mt-1">suma de checkouts confirmados</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-orange-500/20 shadow-lg p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tasa de abandono</p>
                    <p class="text-3xl font-bold text-orange-400">{{ data.tasa_abandono }}%</p>
                    <p class="text-xs text-slate-500 mt-1">abandonados / (completados + abandonados)</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-500/20 shadow-lg p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Abiertos vencidos</p>
                    <p class="text-3xl font-bold text-slate-300">{{ data.abiertos_vencidos }}</p>
                    <p class="text-xs text-slate-500 mt-1">carritos abiertos con expira_at pasado</p>
                </div>
            </div>

            <!-- Consumo por horario -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700/50">
                    <h2 class="text-base font-semibold text-white">Checkouts confirmados por hora del día</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Solo horas con actividad</p>
                </div>

                <!-- Estado vacío -->
                <div v-if="horasConActividad.length === 0" class="px-6 py-12 text-center">
                    <p class="text-slate-500 text-sm">Sin checkouts confirmados aún.</p>
                </div>

                <!-- Tabla de barras simples -->
                <div v-else class="p-6 space-y-2">
                    <div v-for="({ hora, count, pct }) in horasConActividad" :key="hora"
                         class="flex items-center gap-3">
                        <span class="text-xs text-slate-400 w-12 text-right shrink-0">{{ hora }}:00</span>
                        <div class="flex-1 bg-slate-700/40 rounded-full h-4 overflow-hidden">
                            <div class="h-4 rounded-full bg-gradient-to-r from-violet-500 to-blue-500 transition-all"
                                 :style="{ width: pct + '%' }" />
                        </div>
                        <span class="text-xs text-white w-6 text-right shrink-0">{{ count }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex gap-3 text-xs text-slate-500">
                <a href="/admin/cart/modulos" class="hover:text-slate-300 transition-colors">← Módulos</a>
                <span>·</span>
                <a href="/admin/cart/solicitudes" class="hover:text-slate-300 transition-colors">Solicitudes</a>
                <span>·</span>
                <a href="/admin/cart/bitacora" class="hover:text-slate-300 transition-colors">Bitácora</a>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    data:    Object,
    horario: Object,
});

// Normaliza el objeto horario { "10": 2, "15": 1 } → array ordenado con porcentaje
const horasConActividad = computed(() => {
    const entries = Object.entries(props.horario ?? {})
        .map(([hora, count]) => ({ hora: parseInt(hora), count: parseInt(count) }))
        .sort((a, b) => a.hora - b.hora);

    const max = Math.max(...entries.map(e => e.count), 1);

    return entries.map(e => ({
        ...e,
        pct: Math.round((e.count / max) * 100),
    }));
});
</script>
