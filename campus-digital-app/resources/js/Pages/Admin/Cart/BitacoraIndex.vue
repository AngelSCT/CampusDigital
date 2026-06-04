<template>
    <Head title="Cart Admin — Bitácora" />
    <AuthLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <a href="/admin/cart/modulos" class="text-slate-400 hover:text-slate-300 text-sm transition-colors">← Módulos</a>
                        <span class="text-slate-600">/</span>
                        <span class="text-slate-400 text-sm">Bitácora</span>
                    </div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-violet-400 to-blue-400 bg-clip-text text-transparent">
                        Bitácora de eventos
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Registro de acciones del módulo Carrito</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-xl shadow-violet-500/5 p-5">
                <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

                    <!-- Acciones multi-select -->
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Acciones</label>
                        <select v-model="form.accion" multiple
                                class="w-full bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm h-24 focus:outline-none focus:border-violet-500/50 focus:ring-1 focus:ring-violet-500/30 transition-colors">
                            <option v-for="a in accionesDisponibles" :key="a" :value="a"
                                    class="bg-slate-800">{{ a }}</option>
                        </select>
                    </div>

                    <!-- Módulo -->
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Módulo</label>
                        <select v-model="form.modulo_id"
                                class="w-full bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-violet-500/50 focus:ring-1 focus:ring-violet-500/30 transition-colors">
                            <option value="" class="bg-slate-800">Todos</option>
                            <option v-for="m in modulos" :key="m.id" :value="m.id" class="bg-slate-800">{{ m.nombre }}</option>
                        </select>
                    </div>

                    <!-- Rango de fechas -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Desde</label>
                            <input v-model="form.desde" type="date"
                                   class="w-full bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-violet-500/50 focus:ring-1 focus:ring-violet-500/30 transition-colors [color-scheme:dark]" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Hasta</label>
                            <input v-model="form.hasta" type="date"
                                   class="w-full bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-violet-500/50 focus:ring-1 focus:ring-violet-500/30 transition-colors [color-scheme:dark]" />
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-gradient-to-br from-violet-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 text-white text-sm font-medium rounded-lg shadow-lg shadow-violet-500/20 transition-all duration-200">
                            Filtrar
                        </button>
                        <button type="button" @click="limpiar"
                                class="px-4 py-2 bg-slate-700/50 border border-slate-600 text-slate-300 hover:text-white hover:bg-slate-600/50 text-sm font-medium rounded-lg transition-colors">
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-xl shadow-violet-500/5 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Acción</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Módulo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">IP</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="entrada in bitacoras.data" :key="entrada.id"
                                class="border-t border-slate-700/40 hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-4 py-3">
                                    <span :class="accionBadgeClass(entrada.accion)"
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border">
                                        {{ entrada.accion }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs">
                                    {{ moduloNombre(entrada.modulo_id) ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ entrada.user_id ?? '—' }}</td>
                                <td class="px-4 py-3 font-mono text-slate-500 text-xs">{{ entrada.ip_address ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ formatDate(entrada.created_at) }}</td>
                            </tr>
                            <tr v-if="bitacoras.data.length === 0">
                                <td colspan="5" class="px-4 py-16 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="text-slate-400 text-sm">Sin registros con los filtros aplicados.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="bitacoras.last_page > 1" class="px-6 py-4 border-t border-slate-700/50 flex justify-center gap-1">
                    <Link v-for="link in bitacoras.links" :key="link.label"
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
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    bitacoras:           Object,
    filtros:             Object,
    accionesDisponibles: Array,
    modulos:             Array,
});

const form = reactive({
    accion:    props.filtros?.accion    ?? [],
    modulo_id: props.filtros?.modulo_id ?? '',
    desde:     props.filtros?.desde     ?? '',
    hasta:     props.filtros?.hasta     ?? '',
});

function aplicarFiltros() {
    const params = {};
    if (form.accion?.length)  params.accion    = form.accion;
    if (form.modulo_id)       params.modulo_id = form.modulo_id;
    if (form.desde)           params.desde     = form.desde;
    if (form.hasta)           params.hasta     = form.hasta;
    router.get('/admin/cart/bitacora', params, { preserveState: true });
}

function limpiar() {
    form.accion    = [];
    form.modulo_id = '';
    form.desde     = '';
    form.hasta     = '';
    router.get('/admin/cart/bitacora');
}

function moduloNombre(id) {
    if (!id) return null;
    return props.modulos?.find(m => m.id === id)?.nombre ?? `#${id}`;
}

// Colores por tipo de acción
function accionBadgeClass(accion) {
    if (!accion) return 'bg-slate-700/50 text-slate-400 border-slate-600/40';
    if (accion.includes('carrito.creado'))    return 'bg-cyan-500/20   text-cyan-400   border-cyan-500/30';
    if (accion.includes('item.agregado'))     return 'bg-green-500/20  text-green-400  border-green-500/30';
    if (accion.includes('item.removido'))     return 'bg-orange-500/20 text-orange-400 border-orange-500/30';
    if (accion.includes('item.devuelto'))     return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
    if (accion.includes('checkout'))          return 'bg-blue-500/20   text-blue-400   border-blue-500/30';
    if (accion.includes('revertido'))         return 'bg-red-500/20    text-red-400    border-red-500/30';
    if (accion.includes('cancelado'))         return 'bg-red-500/20    text-red-400    border-red-500/30';
    if (accion.includes('pedido.creado'))     return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
    if (accion.includes('pedido.cancelado'))  return 'bg-red-500/20    text-red-400    border-red-500/30';
    if (accion.includes('token') && accion.includes('emis')) return 'bg-violet-500/20 text-violet-400 border-violet-500/30';
    if (accion.includes('token') && accion.includes('revoc'))return 'bg-red-500/20    text-red-400    border-red-500/30';
    if (accion.includes('token'))             return 'bg-purple-500/20 text-purple-400 border-purple-500/30';
    if (accion.includes('conciliacion'))      return 'bg-amber-500/20  text-amber-400  border-amber-500/30';
    if (accion.includes('saldo'))             return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
    return 'bg-slate-700/50 text-slate-400 border-slate-600/40';
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}
</script>
