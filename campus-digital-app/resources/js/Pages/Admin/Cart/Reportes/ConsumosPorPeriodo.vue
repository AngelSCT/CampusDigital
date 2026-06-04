<template>
    <Head title="Reporte — Consumos por Período" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <a href="/admin/cart/dashboard" class="text-slate-400 hover:text-slate-300 text-sm transition-colors">← Dashboard</a>
                <span class="text-slate-600">/</span>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-violet-400 to-blue-400 bg-clip-text text-transparent">
                    Consumos por Período
                </h1>
            </div>

            <!-- Filtros -->
            <form @submit.prevent="aplicar" class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 p-5 flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Desde</label>
                    <input v-model="form.desde" type="date"
                           class="bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm [color-scheme:dark] focus:outline-none focus:border-violet-500/50" />
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Hasta</label>
                    <input v-model="form.hasta" type="date"
                           class="bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm [color-scheme:dark] focus:outline-none focus:border-violet-500/50" />
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Módulo (slug)</label>
                    <input v-model="form.modulo_slug" type="text" placeholder="ej. catalogo"
                           class="bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-violet-500/50" />
                </div>
                <button type="submit" class="px-4 py-2 bg-gradient-to-br from-violet-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 text-white text-sm font-medium rounded-lg transition-all">
                    Filtrar
                </button>
                <a :href="csvUrl" class="px-4 py-2 bg-slate-700/50 border border-slate-600 text-slate-300 hover:text-white text-sm rounded-lg transition-colors">
                    Exportar CSV
                </a>
            </form>

            <!-- KPI cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-green-500/20 p-5">
                    <p class="text-xs text-slate-400 uppercase mb-1">Total confirmado</p>
                    <p class="text-2xl font-bold text-green-400">${{ datos.total_confirmado.toFixed(2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-yellow-500/20 p-5">
                    <p class="text-xs text-slate-400 uppercase mb-1">Total pendiente</p>
                    <p class="text-2xl font-bold text-yellow-400">${{ datos.total_pendiente.toFixed(2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-5">
                    <p class="text-xs text-slate-400 uppercase mb-1">Checkouts</p>
                    <p class="text-2xl font-bold text-blue-400">{{ datos.numero_checkouts }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 p-5">
                    <p class="text-xs text-slate-400 uppercase mb-1">Promedio</p>
                    <p class="text-2xl font-bold text-violet-400">${{ datos.promedio_consumo.toFixed(2) }}</p>
                </div>
            </div>

            <!-- Tabla detalle -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700/50">
                    <h2 class="text-sm font-semibold text-white">Detalle ({{ datos.detalle.length }} registros)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th v-for="col in ['UUID', 'Estado', 'Total', 'Usuario', 'Módulo', 'Confirmado']" :key="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">{{ col }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in datos.detalle" :key="r.carrito_uuid"
                                class="border-t border-slate-700/40 hover:bg-slate-700/20">
                                <td class="px-4 py-3 font-mono text-xs text-slate-400">{{ r.carrito_uuid.substring(0,8) }}…</td>
                                <td class="px-4 py-3"><span :class="estadoBadge(r.estado)" class="px-2 py-0.5 rounded text-xs border">{{ r.estado }}</span></td>
                                <td class="px-4 py-3 text-white">${{ r.total.toFixed(2) }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ r.usuario_ref }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ r.modulo }}</td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ r.confirmed_at ? new Date(r.confirmed_at).toLocaleString('es-MX') : '—' }}</td>
                            </tr>
                            <tr v-if="!datos.detalle.length">
                                <td colspan="6" class="px-4 py-12 text-center text-slate-500">Sin datos en el período.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({ datos: Object, filtros: Object });

const form = reactive({
    desde:       props.filtros?.desde       ?? '',
    hasta:       props.filtros?.hasta       ?? '',
    modulo_slug: props.filtros?.modulo_slug ?? '',
});

const csvUrl = computed(() => {
    const p = new URLSearchParams({ ...form, format: 'csv' });
    return '/admin/cart/reportes/consumos-por-periodo?' + p.toString();
});

function aplicar() {
    router.get('/admin/cart/reportes/consumos-por-periodo', form, { preserveState: true });
}

function estadoBadge(e) {
    if (e === 'confirmado') return 'bg-green-500/20 text-green-400 border-green-500/30';
    if (e === 'confirmado_pendiente_conciliacion') return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
    return 'bg-slate-700/50 text-slate-400 border-slate-600/40';
}
</script>
