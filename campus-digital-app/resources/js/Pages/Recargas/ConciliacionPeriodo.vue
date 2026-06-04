<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    recargas: Array,
    stats: Object,
    resumen_metodos: Object,
    fecha_inicio: String,
    fecha_fin: String,
    periodo: String
});
</script>

<template>
    <Head title="Conciliación" />
    <div class="p-6 bg-[#0f172a] min-h-screen text-slate-200">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Conciliación Bancaria</h1>
            <p class="text-sky-400">Período: {{ fecha_inicio }} al {{ fecha_fin }}</p>
        </div>

        <h2 class="text-lg font-semibold mb-4 text-slate-300">Resumen por Método de Pago</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div v-for="(info, metodo) in resumen_metodos" :key="metodo"
                 class="bg-slate-800/80 p-5 rounded-2xl border-t-4 border-sky-500 shadow-xl">
                <p class="text-sky-400 font-bold uppercase text-xs mb-2">{{ metodo }}</p>
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-2xl font-bold text-white">${{ info.monto }}</p>
                        <p class="text-xs text-slate-500">{{ info.exitosas }} exitosas de {{ info.total }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-700 overflow-hidden">
            <div class="p-4 bg-slate-800/50 border-b border-slate-700">
                <h3 class="font-bold">Detalle de Auditoría</h3>
            </div>
            <table class="w-full text-left border-collapse text-sm">
                <thead class="bg-slate-800 text-slate-400">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Método</th>
                        <th class="p-3">Monto</th>
                        <th class="p-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="recarga in recargas" :key="recarga.id" class="border-b border-slate-800 hover:bg-slate-800/30">
                        <td class="p-3 font-mono text-xs text-slate-500">{{ recarga.id }}</td>
                        <td class="p-3">{{ recarga.metodo_pago }}</td>
                        <td class="p-3 font-bold">${{ recarga.monto }}</td>
                        <td class="p-3">
                            <span :class="recarga.estado === 'exitosa' ? 'text-emerald-400' : 'text-slate-500'">
                                {{ recarga.estado }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
