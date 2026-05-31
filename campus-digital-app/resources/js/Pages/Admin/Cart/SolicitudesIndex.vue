<template>
    <Head title="Cart Admin — Solicitudes" />
    <AuthLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <a href="/admin/cart/modulos" class="text-slate-400 hover:text-slate-300 text-sm transition-colors">← Módulos</a>
                        <span class="text-slate-600">/</span>
                        <span class="text-slate-400 text-sm">Solicitudes</span>
                    </div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-violet-400 to-blue-400 bg-clip-text text-transparent">
                        Solicitudes de Alta
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Módulos que solicitan acceso al API de Carrito</p>
                </div>
            </div>

            <!-- Filtros / tabs -->
            <div class="flex flex-wrap gap-2">
                <a v-for="opcion in estadoOpciones"
                   :key="opcion.value ?? 'todas'"
                   :href="opcion.url"
                   :class="[
                       'px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-200',
                       filtroEstado === opcion.value
                           ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/20'
                           : 'bg-slate-700/50 text-slate-300 border border-slate-600/50 hover:bg-slate-600/50',
                   ]">
                    {{ opcion.label }}
                </a>
            </div>

            <!-- Tabla -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-xl shadow-violet-500/5 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Folio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in solicitudes.data" :key="s.id"
                                class="border-t border-slate-700/40 hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-6 py-4 font-mono text-sm text-slate-300">{{ s.folio }}</td>
                                <td class="px-6 py-4 font-medium text-white">{{ s.nombre_modulo }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-violet-500/20 text-violet-300 border border-violet-500/30">
                                        {{ s.tipo_modulo }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="badgeClass(s.estado)"
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border">
                                        {{ s.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-400">{{ formatDate(s.created_at) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="`/admin/cart/solicitudes/${s.id}`"
                                          class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-violet-400 hover:text-violet-300 bg-violet-500/10 hover:bg-violet-500/20 border border-violet-500/30 rounded-lg transition-colors duration-150">
                                        Ver →
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!solicitudes.data.length">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="text-slate-400 text-sm">
                                        {{ filtroEstado ? `No hay solicitudes ${filtroEstado}s.` : 'No hay solicitudes.' }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="solicitudes.last_page > 1" class="px-6 py-4 border-t border-slate-700/50 flex justify-center gap-1">
                    <a v-for="link in solicitudes.links"
                       :key="link.label"
                       :href="link.url"
                       v-html="link.label"
                       :class="[
                           'px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors',
                           link.active
                               ? 'bg-violet-600 text-white border-violet-600'
                               : 'bg-slate-700/50 text-slate-300 border-slate-600 hover:bg-slate-600/50',
                           !link.url && 'opacity-40 cursor-default pointer-events-none',
                       ]" />
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    solicitudes:  Object,
    filtroEstado: String,
});

const estadoOpciones = [
    { value: null,        label: 'Todas',      url: '/admin/cart/solicitudes' },
    { value: 'pendiente', label: 'Pendientes', url: '/admin/cart/solicitudes?estado=pendiente' },
    { value: 'aprobada',  label: 'Aprobadas',  url: '/admin/cart/solicitudes?estado=aprobada' },
    { value: 'rechazada', label: 'Rechazadas', url: '/admin/cart/solicitudes?estado=rechazada' },
];

function badgeClass(estado) {
    const map = {
        pendiente: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
        aprobada:  'bg-green-500/20  text-green-400  border-green-500/30',
        rechazada: 'bg-red-500/20    text-red-400    border-red-500/30',
    };
    return map[estado] ?? 'bg-slate-700/50 text-slate-400 border-slate-600/40';
}

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
}
</script>
