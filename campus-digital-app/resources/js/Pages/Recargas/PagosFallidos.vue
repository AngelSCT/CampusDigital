<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    recargas: Object,
    stats: Object,
    filtro_periodo: String
});
</script>

<template>
    <Head title="Pagos Fallidos" />
    <div class="p-6 bg-[#0f172a] min-h-screen text-slate-200">
        <div class="mb-8 border-l-4 border-red-500 pl-4">
            <h1 class="text-3xl font-bold text-white">Pagos Fallidos</h1>
            <p class="text-slate-400">Análisis de transacciones no completadas en el período</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-red-500/10 p-6 rounded-2xl border border-red-500/20">
                <p class="text-red-400 text-sm font-semibold uppercase tracking-wider">Intentos Fallidos</p>
                <p class="text-4xl font-bold text-white">{{ stats.total_fallidas }}</p>
            </div>
            <div class="bg-slate-800/50 p-6 rounded-2xl border border-slate-700">
                <p class="text-slate-400 text-sm font-semibold uppercase tracking-wider">Monto No Recaudado</p>
                <p class="text-4xl font-bold text-slate-300">${{ stats.monto_intentado }}</p>
            </div>
        </div>

        <div class="bg-slate-800/30 rounded-2xl border border-slate-700">
            <div v-if="recargas.data.length === 0" class="p-12 text-center text-slate-500">
                No hay pagos fallidos registrados en este período.
            </div>
            <div v-else v-for="recarga in recargas.data" :key="recarga.id" class="p-4 border-b border-slate-700/50 flex justify-between items-center">
                <div>
                    <p class="text-white font-semibold">Ref: {{ recarga.referencia_pago }}</p>
                    <p class="text-xs text-slate-500">{{ new Date(recarga.created_at).toLocaleString() }}</p>
                </div>
                <div class="text-right">
                    <p class="text-red-400 font-bold">${{ recarga.monto }}</p>
                    <p class="text-xs text-slate-400">{{ recarga.metodo_pago }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
