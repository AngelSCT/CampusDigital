<template>
    <Head title="Admin — Turnos" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">
                        Cola de Turnos
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Llamar, atender y marcar no-show</p>
                </div>
                <a href="/admin/reservas" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg">
                    ← Volver a reservas
                </a>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div v-for="(stats, tipo) in estadisticasPorTipo" :key="tipo"
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-emerald-500/20 p-4">
                    <h3 class="text-xs font-semibold text-slate-300 uppercase">{{ tipos[tipo] }}</h3>
                    <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                        <div>
                            <p class="text-2xl font-bold text-amber-400">{{ stats.esperando }}</p>
                            <p class="text-xs text-slate-400">En cola</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-emerald-400">{{ stats.atendidos }}</p>
                            <p class="text-xs text-slate-400">Atendidos</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-red-400">{{ stats.no_show }}</p>
                            <p class="text-xs text-slate-400">No-show</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-emerald-500/20 p-4">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Tipo</label>
                        <select v-model="filterForm.tipo_turno" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm">
                            <option value="">Todos</option>
                            <option v-for="(label, key) in tipos" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Estado</label>
                        <select v-model="filterForm.estado" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm">
                            <option value="">Todos</option>
                            <option v-for="(label, key) in estados" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Fecha</label>
                        <input v-model="filterForm.fecha" type="date" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm" />
                    </div>
                </form>
            </div>

            <!-- Lista de turnos -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-emerald-500/20 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Pos.</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="t in turnos.data" :key="t.id_turno" class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 text-cyan-300 font-mono">{{ t.numero_turno }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ t.posicion }}</td>
                            <td class="px-4 py-3 text-white">{{ tipos[t.tipo_turno] }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ t.usuario?.nombre }} {{ t.usuario?.apellido }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-amber-500/20 text-amber-300':   t.estado === 'esperando',
                                        'bg-yellow-500/20 text-yellow-300': t.estado === 'llamado',
                                        'bg-emerald-500/20 text-emerald-300': t.estado === 'atendido',
                                        'bg-red-500/20 text-red-300':       ['no_show','cancelado'].includes(t.estado),
                                    }">
                                    {{ t.estado }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div v-if="t.estado === 'esperando'" class="flex justify-end gap-2">
                                    <button @click="llamar(t)" class="px-2 py-1 bg-yellow-600/20 border border-yellow-500/40 text-yellow-300 text-xs rounded">Llamar</button>
                                </div>
                                <div v-else-if="t.estado === 'llamado'" class="flex justify-end gap-2">
                                    <button @click="atender(t)" class="px-2 py-1 bg-emerald-600/20 border border-emerald-500/40 text-emerald-300 text-xs rounded">Atender</button>
                                    <button @click="noShow(t)" class="px-2 py-1 bg-red-600/20 border border-red-500/40 text-red-300 text-xs rounded">No-show</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="!turnos.data || turnos.data.length === 0" class="text-center py-8 text-slate-400">
                    No hay turnos en cola hoy.
                </div>
            </div>

            <nav v-if="turnos.links" class="flex justify-center gap-1">
                <a v-for="link in turnos.links" :key="link.label" :href="link.url || '#'"
                    :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700']"
                    v-html="link.label" />
            </nav>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    turnos:              Object,
    tipos:               Object,
    estados:             Object,
    estadisticasPorTipo: Object,
    filters:             Object,
});

const filterForm = useForm({
    tipo_turno: props.filters?.tipo_turno ?? '',
    estado:     props.filters?.estado     ?? '',
    fecha:      props.filters?.fecha      ?? new Date().toISOString().split('T')[0],
});

const applyFilters = () => filterForm.get('/admin/reservas/turnos', { preserveState: true });

const llamar  = (t) => router.post(`/admin/reservas/turnos/${t.id_turno}/llamar`);
const atender = (t) => router.post(`/admin/reservas/turnos/${t.id_turno}/atender`);
const noShow  = (t) => router.post(`/admin/reservas/turnos/${t.id_turno}/no-show`);
</script>
