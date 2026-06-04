<template>
    <Head title="Reporte — Consumo por Categoría" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <a href="/admin/cart/dashboard" class="text-slate-400 hover:text-slate-300 text-sm transition-colors">← Dashboard</a>
                <span class="text-slate-600">/</span>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">
                    Consumo por Categoría
                </h1>
            </div>

            <!-- Filtros -->
            <form @submit.prevent="aplicar" class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-emerald-500/20 p-5 flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Desde</label>
                    <input v-model="form.desde" type="date" class="bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm [color-scheme:dark] focus:outline-none" />
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Hasta</label>
                    <input v-model="form.hasta" type="date" class="bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm [color-scheme:dark] focus:outline-none" />
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Categoría (slug)</label>
                    <input v-model="form.categoria_slug" type="text" placeholder="ej. cafeteria"
                           class="bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 rounded-lg px-3 py-2 text-sm focus:outline-none" />
                </div>
                <button type="submit" class="px-4 py-2 bg-gradient-to-br from-emerald-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 text-white text-sm font-medium rounded-lg transition-all">
                    Filtrar
                </button>
                <a :href="csvUrl" class="px-4 py-2 bg-slate-700/50 border border-slate-600 text-slate-300 hover:text-white text-sm rounded-lg transition-colors">
                    Exportar CSV
                </a>
            </form>

            <!-- Tabla agrupada por categoría -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-emerald-500/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700/50">
                    <h2 class="text-sm font-semibold text-white">{{ datos.length }} categorías con actividad</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">Categoría</th>
                                <th class="px-4 py-3 text-right text-xs text-slate-400 uppercase">Ítems</th>
                                <th class="px-4 py-3 text-right text-xs text-slate-400 uppercase">Unidades</th>
                                <th class="px-4 py-3 text-right text-xs text-slate-400 uppercase">Total consumido</th>
                                <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">Barra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in datos" :key="r.categoria_slug"
                                class="border-t border-slate-700/40 hover:bg-slate-700/20">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-white text-sm">{{ r.categoria_nombre }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ r.categoria_slug }}</div>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-300">{{ r.cantidad_items }}</td>
                                <td class="px-4 py-3 text-right text-slate-300">{{ r.total_unidades }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-400">${{ r.total_consumido.toFixed(2) }}</td>
                                <td class="px-4 py-3 w-32">
                                    <div class="bg-slate-700/40 rounded-full h-2">
                                        <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500"
                                             :style="{ width: pct(r) + '%' }" />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!datos.length">
                                <td colspan="5" class="px-4 py-12 text-center text-slate-500">Sin datos en el período.</td>
                            </tr>
                        </tbody>
                        <tfoot v-if="datos.length" class="border-t border-slate-700/50 bg-slate-900/30">
                            <tr>
                                <td class="px-4 py-3 text-xs font-semibold text-slate-400">TOTAL</td>
                                <td class="px-4 py-3 text-right text-xs font-semibold text-white">{{ totalItems }}</td>
                                <td class="px-4 py-3 text-right text-xs font-semibold text-white">{{ totalUnidades }}</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-emerald-400">${{ totalConsumo.toFixed(2) }}</td>
                                <td />
                            </tr>
                        </tfoot>
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

const props = defineProps({ datos: Array, filtros: Object });

const form = reactive({
    desde:          props.filtros?.desde          ?? '',
    hasta:          props.filtros?.hasta          ?? '',
    categoria_slug: props.filtros?.categoria_slug ?? '',
});

const csvUrl = computed(() => '/admin/cart/reportes/consumo-por-categoria?' + new URLSearchParams({ ...form, format: 'csv' }));

function aplicar() {
    router.get('/admin/cart/reportes/consumo-por-categoria', form, { preserveState: true });
}

const maxConsumo = computed(() => Math.max(...(props.datos ?? []).map(r => r.total_consumido), 1));
const pct = (r) => Math.round((r.total_consumido / maxConsumo.value) * 100);

const totalItems    = computed(() => props.datos.reduce((s, r) => s + r.cantidad_items, 0));
const totalUnidades = computed(() => props.datos.reduce((s, r) => s + r.total_unidades, 0));
const totalConsumo  = computed(() => props.datos.reduce((s, r) => s + r.total_consumido, 0));
</script>
