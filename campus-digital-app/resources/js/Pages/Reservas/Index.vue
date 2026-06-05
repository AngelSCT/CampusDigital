<template>
    <Head title="Reservas" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
                        Reservas de Recursos
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Reserva salas, laboratorios y equipos</p>
                </div>
            </div>

            <!-- Mis reservas activas -->
            <div v-if="misReservas && misReservas.length > 0" class="bg-gradient-to-br from-amber-900/30 to-amber-800/20 rounded-xl border border-amber-500/30 p-4">
                <h2 class="text-sm font-semibold text-amber-300 mb-3">Mis reservas activas</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div v-for="r in misReservas" :key="r.id_reserva" class="bg-slate-800/50 rounded-lg p-3 border border-amber-500/20">
                        <p class="font-semibold text-white">{{ r.recurso?.nombre }}</p>
                        <p class="text-xs text-slate-300">{{ formatDate(r.fecha_inicio) }} - {{ formatDate(r.fecha_fin) }}</p>
                        <Link :href="`/reservas/${r.id_reserva}`" class="text-xs text-cyan-400 hover:text-cyan-300 mt-1 inline-block">Ver detalle →</Link>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-4">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Buscar</label>
                        <input v-model="filterForm.search" type="text" placeholder="Nombre del recurso..."
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/50 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Tipo</label>
                        <select v-model="filterForm.tipo"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/50 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="sala">Salas</option>
                            <option value="laboratorio">Laboratorios</option>
                            <option value="equipo">Equipos</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Grid de recursos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="r in recursos.data" :key="r.id_recurso"
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 shadow-lg shadow-cyan-500/5 p-5 hover:border-cyan-500/50 transition-all">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">{{ r.nombre }}</h3>
                            <p class="text-xs text-slate-400 mt-1">{{ tipoLabel(r.tipo) }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-cyan-500/20 text-cyan-300">
                            Cap. {{ r.capacidad }}
                        </span>
                    </div>
                    <p v-if="r.descripcion" class="text-sm text-slate-300 mt-2 line-clamp-2">{{ r.descripcion }}</p>
                    <p v-if="r.ubicacion" class="text-xs text-slate-400 mt-2">📍 {{ r.ubicacion.edificio }} - {{ r.ubicacion.aula_departamento }}</p>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-700">
                        <span class="text-sm font-bold text-cyan-400">
                            {{ r.costo_por_hora > 0 ? `$${r.costo_por_hora}/h` : 'Gratis' }}
                        </span>
                        <Link :href="`/reservas/crear/${r.id_recurso}`"
                            class="inline-flex items-center px-3 py-1.5 bg-gradient-to-br from-cyan-600 to-blue-700 hover:from-cyan-500 hover:to-blue-600 text-white text-sm font-medium rounded-lg transition-all">
                            Reservar
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="recursos.data && recursos.data.length === 0" class="text-center py-12 text-slate-400">
                No hay recursos disponibles con esos filtros.
            </div>

            <!-- Paginación -->
            <nav v-if="recursos.links" class="flex justify-center gap-1">
                <Link v-for="link in recursos.links" :key="link.label" :href="link.url || '#'"
                    :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-cyan-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700']"
                    v-html="link.label" />
            </nav>
        </div>
    </AuthLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    recursos:    Object,
    misReservas: Array,
    filters:     Object,
});

const filterForm = useForm({
    search: props.filters?.search ?? '',
    tipo:   props.filters?.tipo ?? '',
});

const applyFilters = () => filterForm.get('/reservas', { preserveState: true });

const tipoLabel = (tipo) => ({
    sala: 'Sala',
    laboratorio: 'Laboratorio',
    equipo: 'Equipo',
})[tipo] ?? tipo;

const formatDate = (d) => new Date(d).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
</script>
