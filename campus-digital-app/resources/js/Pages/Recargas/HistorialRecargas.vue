<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    recargas: Object, // Paginado
    stats: Object,
    filtro_estado: String,
    filtro_periodo: String
});

const estado = ref(props.filtro_estado);
const periodo = ref(props.filtro_periodo);

// Actualizar automáticamente al cambiar filtros
watch([estado, periodo], () => {
    router.get(route('modulo_8.reportes.historial'), {
        estado: estado.value,
        periodo: periodo.value
    }, { preserveState: true });
});
</script>

<template>
    <Head title="Historial de Recargas" />

    <div class="p-6 bg-[#0f172a] min-h-screen text-slate-200">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Historial de Recargas</h1>
                <p class="text-slate-400">Consulta y filtra todos los movimientos de tu wallet</p>
            </div>

            <div class="flex gap-4">
                <select v-model="estado" class="bg-slate-800 border-slate-700 rounded-lg text-sm">
                    <option value="todos">Todos los estados</option>
                    <option value="exitosa">Exitosas</option>
                    <option value="fallida">Fallidas</option>
                    <option value="pendiente">Pendientes</option>
                </select>
                <select v-model="periodo" class="bg-slate-800 border-slate-700 rounded-lg text-sm">
                    <option value="7">Últimos 7 días</option>
                    <option value="30">Últimos 30 días</option>
                    <option value="todos">Todo el tiempo</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-slate-800/50 p-5 rounded-2xl border border-slate-700">
                <p class="text-slate-400 text-sm">Total Transacciones</p>
                <p class="text-2xl font-bold text-white">{{ stats.total_recargas }}</p>
            </div>
            <div class="bg-slate-800/50 p-5 rounded-2xl border border-slate-700">
                <p class="text-slate-400 text-sm">Monto Total Exitoso</p>
                <p class="text-2xl font-bold text-emerald-400">${{ stats.monto_total }}</p>
            </div>
        </div>

        <div class="bg-slate-800/30 rounded-2xl border border-slate-700 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-800/80 text-slate-300 uppercase text-xs font-semibold">
                    <tr>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Referencia</th>
                        <th class="p-4">Método</th>
                        <th class="p-4">Monto</th>
                        <th class="p-4">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    <tr v-for="recarga in recargas.data" :key="recarga.id" class="hover:bg-slate-700/20 transition">
                        <td class="p-4 text-sm">{{ new Date(recarga.created_at).toLocaleString() }}</td>
                        <td class="p-4 text-xs font-mono text-slate-400">{{ recarga.referencia_pago }}</td>
                        <td class="p-4 text-sm">{{ recarga.metodo_pago }}</td>
                        <td class="p-4 font-bold text-white">${{ recarga.monto }}</td>
                        <td class="p-4">
                            <span :class="{
                                'bg-emerald-500/10 text-emerald-400': recarga.estado === 'exitosa',
                                'bg-red-500/10 text-red-400': recarga.estado === 'fallida',
                                'bg-amber-500/10 text-amber-400': recarga.estado === 'pendiente'
                            }" class="px-3 py-1 rounded-full text-xs font-medium uppercase">
                                {{ recarga.estado }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
