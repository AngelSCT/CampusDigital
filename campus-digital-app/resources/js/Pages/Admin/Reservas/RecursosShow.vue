<template>
    <Head title="Detalle recurso" />
    <AuthLayout>
        <div class="space-y-6 max-w-4xl">
            <div>
                <a href="/admin/reservas/recursos" class="text-sm text-cyan-400 hover:text-cyan-300">← Volver a recursos</a>
                <h1 class="text-3xl font-bold text-white mt-2">{{ recurso.nombre }}</h1>
                <p class="text-sm text-slate-400 capitalize">{{ recurso.tipo }} · {{ recurso.estado }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-slate-800/50 rounded-xl border border-slate-700 p-4 text-center">
                    <p class="text-xs text-slate-400 uppercase">Capacidad</p>
                    <p class="text-2xl font-bold text-cyan-400 mt-1">{{ recurso.capacidad }}</p>
                </div>
                <div class="bg-slate-800/50 rounded-xl border border-slate-700 p-4 text-center">
                    <p class="text-xs text-slate-400 uppercase">Costo</p>
                    <p class="text-2xl font-bold text-cyan-400 mt-1">{{ recurso.costo_por_hora > 0 ? `$${recurso.costo_por_hora}` : 'Gratis' }}</p>
                </div>
                <div class="bg-slate-800/50 rounded-xl border border-slate-700 p-4 text-center">
                    <p class="text-xs text-slate-400 uppercase">Reservas</p>
                    <p class="text-2xl font-bold text-emerald-400 mt-1">{{ recurso.reservas_count ?? 0 }}</p>
                </div>
                <div class="bg-slate-800/50 rounded-xl border border-slate-700 p-4 text-center">
                    <p class="text-xs text-slate-400 uppercase">Turnos</p>
                    <p class="text-2xl font-bold text-amber-400 mt-1">{{ recurso.turnos_count ?? 0 }}</p>
                </div>
            </div>

            <div v-if="recurso.descripcion" class="bg-slate-800/50 rounded-xl border border-slate-700 p-4">
                <p class="text-sm text-slate-400">Descripción</p>
                <p class="text-sm text-white mt-1">{{ recurso.descripcion }}</p>
            </div>

            <div v-if="recurso.ubicacion" class="bg-slate-800/50 rounded-xl border border-slate-700 p-4 text-sm">
                <p class="text-slate-400">Ubicación</p>
                <p class="text-white font-semibold mt-1">{{ recurso.ubicacion.edificio }} - {{ recurso.ubicacion.aula_departamento }}</p>
            </div>

            <div class="bg-slate-800/50 rounded-xl border border-slate-700 p-4">
                <h3 class="text-sm font-semibold text-white mb-3">Reservas recientes</h3>
                <div v-if="reservasRecientes && reservasRecientes.length > 0" class="space-y-2">
                    <div v-for="r in reservasRecientes" :key="r.id_reserva" class="flex items-center justify-between text-sm">
                        <div>
                            <p class="text-white">{{ r.usuario?.nombre }} {{ r.usuario?.apellido }}</p>
                            <p class="text-xs text-slate-400">{{ formatDate(r.fecha_inicio) }} → {{ formatDate(r.fecha_fin) }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-700 text-slate-300">{{ r.estado }}</span>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-400">Sin reservas aún.</p>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    recurso:           Object,
    reservasRecientes: Array,
});

const formatDate = (d) => new Date(d).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
</script>
