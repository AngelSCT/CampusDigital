<template>
    <Head title="Reporte: por recurso" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
                        Reporte: Reservas por Recurso
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Del {{ desde }} al {{ hasta }}</p>
                </div>
                <a href="/admin/reservas/dashboard" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg">
                    ← Volver
                </a>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase">
                        <tr>
                            <th class="px-4 py-3">Recurso</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3 text-center">Total</th>
                            <th class="px-4 py-3 text-center">Conf.</th>
                            <th class="px-4 py-3 text-center">Cancel.</th>
                            <th class="px-4 py-3 text-center">No-show</th>
                            <th class="px-4 py-3 text-center">Compl.</th>
                            <th class="px-4 py-3 text-center">Horas</th>
                            <th class="px-4 py-3 text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="d in datos" :key="d.id_recurso" class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 text-white font-semibold">{{ d.nombre }}</td>
                            <td class="px-4 py-3 text-slate-300 capitalize">{{ d.tipo }}</td>
                            <td class="px-4 py-3 text-center text-cyan-300 font-mono">{{ d.total_reservas }}</td>
                            <td class="px-4 py-3 text-center text-emerald-300">{{ d.confirmadas }}</td>
                            <td class="px-4 py-3 text-center text-red-300">{{ d.canceladas }}</td>
                            <td class="px-4 py-3 text-center text-yellow-300">{{ d.no_shows }}</td>
                            <td class="px-4 py-3 text-center text-slate-300">{{ d.completadas }}</td>
                            <td class="px-4 py-3 text-center text-slate-300">{{ Math.round(d.horas_totales) }}h</td>
                            <td class="px-4 py-3 text-right text-cyan-300">${{ Number(d.ingresos).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="datos.length === 0" class="text-center py-8 text-slate-400">
                    Sin datos para el período.
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    datos:  Array,
    desde:  String,
    hasta:  String,
});
</script>
