<template>
    <Head title="Recursos infrautilizados" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent">
                        Recursos Infrautilizados
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Recursos con menos de 3 reservas · Del {{ desde }} al {{ hasta }}</p>
                </div>
                <a href="/admin/reservas/dashboard" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg">
                    ← Volver
                </a>
            </div>

            <div class="bg-gradient-to-br from-amber-900/20 to-amber-800/10 rounded-xl border border-amber-500/30 p-4 text-sm text-amber-200">
                <strong>Sugerencia:</strong> Estos recursos podrían reasignarse, promocionarse o darse de baja si la infrautilización persiste.
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-amber-500/20 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase">
                        <tr>
                            <th class="px-4 py-3">Recurso</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-center">Reservas</th>
                            <th class="px-4 py-3 text-center">Horas de uso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="d in datos" :key="d.id_recurso" class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 text-white font-semibold">{{ d.nombre }}</td>
                            <td class="px-4 py-3 text-slate-300 capitalize">{{ d.tipo }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-green-500/20 text-green-300':   d.estado === 'disponible',
                                        'bg-yellow-500/20 text-yellow-300': d.estado === 'mantenimiento',
                                        'bg-slate-500/20 text-slate-300':   d.estado === 'inactivo',
                                    }">
                                    {{ d.estado }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-amber-300 font-mono">{{ d.total_reservas }}</td>
                            <td class="px-4 py-3 text-center text-slate-300">{{ Math.round(d.horas_uso) }}h</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="datos.length === 0" class="text-center py-8 text-slate-400">
                    ¡Excelente! Todos los recursos tienen buen uso.
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    datos: Array,
    desde: String,
    hasta: String,
});
</script>
