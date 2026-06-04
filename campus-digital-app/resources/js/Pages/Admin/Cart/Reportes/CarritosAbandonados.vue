<template>
    <Head title="Reporte — Carritos Abandonados" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <a href="/admin/cart/dashboard" class="text-slate-400 hover:text-slate-300 text-sm transition-colors">← Dashboard</a>
                <span class="text-slate-600">/</span>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-red-400 to-orange-400 bg-clip-text text-transparent">
                    Carritos Abandonados
                </h1>
            </div>

            <!-- Filtros -->
            <form @submit.prevent="aplicar" class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 p-5 flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Desde</label>
                    <input v-model="form.desde" type="date" class="bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm [color-scheme:dark] focus:outline-none" />
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Hasta</label>
                    <input v-model="form.hasta" type="date" class="bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm [color-scheme:dark] focus:outline-none" />
                </div>
                <button type="submit" class="px-4 py-2 bg-gradient-to-br from-red-600 to-orange-600 hover:from-red-500 hover:to-orange-500 text-white text-sm font-medium rounded-lg transition-all">
                    Filtrar
                </button>
                <a :href="csvUrl" class="px-4 py-2 bg-slate-700/50 border border-slate-600 text-slate-300 hover:text-white text-sm rounded-lg transition-colors">
                    Exportar CSV
                </a>
            </form>

            <!-- KPIs -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 p-5">
                    <p class="text-xs text-slate-400 uppercase mb-1">Abandonados (período)</p>
                    <p class="text-3xl font-bold text-red-400">{{ datos.total }}</p>
                    <p class="text-xs text-slate-500 mt-1">cancelados + expirados</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-orange-500/20 p-5">
                    <p class="text-xs text-slate-400 uppercase mb-1">Abiertos vencidos</p>
                    <p class="text-3xl font-bold text-orange-400">{{ datos.abiertos_vencidos.length }}</p>
                    <p class="text-xs text-slate-500 mt-1">expira_at pasado, aún abiertos</p>
                </div>
            </div>

            <!-- Tabla abandonados -->
            <TablaCelda titulo="Abandonados" :filas="datos.lista" />

            <!-- Tabla abiertos vencidos -->
            <TablaCelda titulo="Abiertos vencidos" :filas="datos.abiertos_vencidos" variant="orange" />
        </div>
    </AuthLayout>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({ datos: Object, filtros: Object });

const form = reactive({ desde: props.filtros?.desde ?? '', hasta: props.filtros?.hasta ?? '' });

const csvUrl = computed(() => '/admin/cart/reportes/carritos-abandonados?' + new URLSearchParams({ ...form, format: 'csv' }));

function aplicar() {
    router.get('/admin/cart/reportes/carritos-abandonados', form, { preserveState: true });
}

const TablaCelda = {
    props: { titulo: String, filas: Array, variant: { type: String, default: 'red' } },
    template: `
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700/40 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between">
                <h2 class="text-sm font-semibold text-white">{{ titulo }} ({{ filas.length }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">UUID</th>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">Módulo</th>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">Estado</th>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 uppercase">Creado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in filas" :key="r.carrito_uuid" class="border-t border-slate-700/40 hover:bg-slate-700/20">
                            <td class="px-4 py-3 font-mono text-xs text-slate-400">{{ r.carrito_uuid.substring(0,8) }}…</td>
                            <td class="px-4 py-3 text-slate-300">{{ r.usuario_ref }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ r.modulo }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs bg-red-500/20 text-red-400 border border-red-500/30">{{ r.estado }}</span></td>
                            <td class="px-4 py-3 text-white">{{ '$' + r.total.toFixed(2) }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ r.created_at ? new Date(r.created_at).toLocaleString('es-MX') : '—' }}</td>
                        </tr>
                        <tr v-if="!filas.length">
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500 text-sm">Sin registros.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `,
};
</script>
