<template>
    <Head title="Admin — Reservas" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
                        Gestión de Reservas
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Módulo 4.7 — Reservas y Turnos</p>
                </div>
                <div class="flex gap-2">
                    <a href="/admin/reservas/dashboard"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-slate-700 to-slate-800 border border-slate-600 text-white text-sm font-medium rounded-lg hover:from-slate-600 hover:to-slate-700">
                        Dashboard
                    </a>
                    <a href="/admin/reservas/recursos"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-slate-700 to-slate-800 border border-slate-600 text-white text-sm font-medium rounded-lg hover:from-slate-600 hover:to-slate-700">
                        Recursos
                    </a>
                    <a href="/admin/reservas/turnos"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-slate-700 to-slate-800 border border-slate-600 text-white text-sm font-medium rounded-lg hover:from-slate-600 hover:to-slate-700">
                        Turnos
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-4">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Buscar</label>
                        <input v-model="filterForm.search" type="text" placeholder="Usuario..."
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Estado</label>
                        <select v-model="filterForm.estado" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="cancelada">Cancelada</option>
                            <option value="no_show">No Show</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Recurso</label>
                        <select v-model="filterForm.recurso" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm">
                            <option value="">Todos</option>
                            <option v-for="r in recursos" :key="r.id_recurso" :value="r.id_recurso">{{ r.nombre }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Desde</label>
                        <input v-model="filterForm.desde" type="date" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Hasta</label>
                        <input v-model="filterForm.hasta" type="date" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm" />
                    </div>
                </form>
            </div>

            <!-- Tabla de reservas -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Recurso</th>
                            <th class="px-4 py-3">Inicio</th>
                            <th class="px-4 py-3">Fin</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="r in reservas.data" :key="r.id_reserva" class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 text-slate-400">#{{ r.id_reserva }}</td>
                            <td class="px-4 py-3 text-white">{{ r.usuario?.nombre }} {{ r.usuario?.apellido }}</td>
                            <td class="px-4 py-3 text-cyan-300">{{ r.recurso?.nombre }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ formatDate(r.fecha_inicio) }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ formatDate(r.fecha_fin) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-green-500/20 text-green-300':   r.estado === 'confirmada',
                                        'bg-yellow-500/20 text-yellow-300': r.estado === 'pendiente',
                                        'bg-red-500/20 text-red-300':       r.estado === 'cancelada',
                                        'bg-slate-500/20 text-slate-300':   ['completada','no_show'].includes(r.estado),
                                    }">
                                    {{ r.estado }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a :href="`/admin/reservas/${r.id_reserva}`" class="text-cyan-400 hover:text-cyan-300 text-sm">Ver</a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!reservas.data || reservas.data.length === 0" class="text-center py-8 text-slate-400">
                    No hay reservas con esos filtros.
                </div>
            </div>

            <!-- Paginación -->
            <nav v-if="reservas.links" class="flex justify-center gap-1">
                <a v-for="link in reservas.links" :key="link.label" :href="link.url || '#'"
                    :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-cyan-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700']"
                    v-html="link.label" />
            </nav>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    reservas: Object,
    recursos: Array,
    filters:  Object,
});

const filterForm = useForm({
    search:  props.filters?.search  ?? '',
    estado:  props.filters?.estado  ?? '',
    recurso: props.filters?.recurso ?? '',
    desde:   props.filters?.desde   ?? '',
    hasta:   props.filters?.hasta   ?? '',
});

const applyFilters = () => filterForm.get('/admin/reservas', { preserveState: true });

const formatDate = (d) => new Date(d).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
</script>
