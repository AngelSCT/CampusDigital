<template>
    <Head title="Cart Admin — Módulos" />
    <AuthLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-violet-400 to-blue-400 bg-clip-text text-transparent">
                        Cart Admin
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Gestión de módulos clientes del API de Carrito</p>
                </div>
                <div class="flex gap-2">
                    <a href="/admin/cart/solicitudes"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-slate-700 to-slate-800 border border-slate-600 text-white text-sm font-medium rounded-lg hover:from-slate-600 hover:to-slate-700 transition-all duration-200">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Solicitudes
                        <span v-if="stats.solicitudes_pendientes"
                              class="ml-1.5 px-1.5 py-0.5 rounded-full bg-yellow-500/30 text-yellow-300 text-xs font-semibold">
                            {{ stats.solicitudes_pendientes }}
                        </span>
                    </a>
                    <a href="/admin/cart/bitacora"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-slate-700 to-slate-800 border border-slate-600 text-white text-sm font-medium rounded-lg hover:from-slate-600 hover:to-slate-700 transition-all duration-200">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                        </svg>
                        Bitácora
                    </a>
                </div>
            </div>

            <!-- KPI cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-lg shadow-violet-500/5 p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Total módulos</p>
                    <p class="text-3xl font-bold text-white">{{ stats.total }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-green-500/20 shadow-lg shadow-green-500/5 p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Activos</p>
                    <p class="text-3xl font-bold text-green-400">{{ stats.activos }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-lg shadow-blue-500/5 p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tokens activos</p>
                    <p class="text-3xl font-bold text-blue-400">{{ stats.tokens_activos }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-yellow-500/20 shadow-lg shadow-yellow-500/5 p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Solicitudes pendientes</p>
                    <p class="text-3xl font-bold text-yellow-400">{{ stats.solicitudes_pendientes }}</p>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-xl shadow-violet-500/5 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700/50">
                    <h2 class="text-base font-semibold text-white">Módulos registrados</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Slug</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tokens activos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Alta</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="modulo in modulos.data" :key="modulo.id"
                                class="border-t border-slate-700/40 hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-6 py-4 font-medium text-white">{{ modulo.nombre }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-violet-500/20 text-violet-300 border border-violet-500/30">
                                        {{ modulo.tipo_modulo }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-xs text-slate-400 bg-slate-700/50 px-2 py-0.5 rounded">{{ modulo.slug }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="modulo.activo
                                        ? 'bg-green-500/20 text-green-400 border-green-500/30'
                                        : 'bg-slate-700/50 text-slate-400 border-slate-600/40'"
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border">
                                        {{ modulo.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="tokensActivos(modulo) > 0
                                        ? 'bg-blue-500/20 text-blue-400 border-blue-500/30'
                                        : 'bg-slate-700/50 text-slate-400 border-slate-600/40'"
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border">
                                        {{ tokensActivos(modulo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-400">{{ formatDate(modulo.created_at) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="`/admin/cart/modulos/${modulo.id}`"
                                          class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-violet-400 hover:text-violet-300 bg-violet-500/10 hover:bg-violet-500/20 border border-violet-500/30 rounded-lg transition-colors duration-150">
                                        Ver detalle →
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="modulos.data.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <p class="text-slate-400 text-sm">No hay módulos registrados.</p>
                                    <p class="text-slate-500 text-xs mt-1">Las solicitudes aprobadas aparecerán aquí.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="modulos.last_page > 1" class="px-6 py-4 border-t border-slate-700/50 flex justify-center gap-1">
                    <Link v-for="link in modulos.links" :key="link.label"
                          :href="link.url || '#'"
                          :class="['px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors',
                                   link.active
                                       ? 'bg-violet-600 text-white border-violet-600'
                                       : 'bg-slate-700/50 text-slate-300 border-slate-600 hover:bg-slate-600/50',
                                   !link.url ? 'opacity-40 pointer-events-none' : '']"
                          v-html="link.label" />
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    modulos: Object,
    stats:   Object,
});

function tokensActivos(modulo) {
    return (modulo.tokens ?? []).filter(t => t.tipo === 'access' && t.estado === 'activo').length;
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>
