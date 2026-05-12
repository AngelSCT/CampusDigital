<template>
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        Reportes · Tarjetas RFID/NFC
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Accesos, lecturas por módulo e incidentes</p>
                </div>
                <a :href="route('admin.tarjetas.dashboard')"
                   class="inline-flex items-center px-4 py-2 border border-slate-600 rounded-lg text-sm text-white hover:bg-slate-700/50 transition-all duration-200">
                    ← Dashboard
                </a>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Filtros de búsqueda</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Desde</label>
                        <input type="date" v-model="filtros.desde"
                               class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20" />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Hasta</label>
                        <input type="date" v-model="filtros.hasta"
                               class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20" />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Módulo</label>
                        <select v-model="filtros.modulo"
                                class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm px-3 py-2 focus:border-cyan-500">
                            <option value="">Todos</option>
                            <option v-for="m in modulos" :key="m" :value="m" class="capitalize">{{ m }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Tipo</label>
                        <select v-model="filtros.tipo"
                                class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm px-3 py-2 focus:border-cyan-500">
                            <option value="">Todos</option>
                            <option v-for="t in tipos" :key="t" :value="t" class="capitalize">{{ t.replace('_', ' ') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Resultado</label>
                        <select v-model="filtros.exito"
                                class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm px-3 py-2 focus:border-cyan-500">
                            <option value="">Todos</option>
                            <option value="true">Exitosos</option>
                            <option value="false">Fallidos</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-4">
                    <button @click="aplicarFiltros"
                            class="px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-cyan-500/20 transition-all duration-200">
                        Aplicar Filtros
                    </button>
                    <button @click="limpiarFiltros"
                            class="px-4 py-2 border border-slate-600 rounded-lg text-sm text-white hover:bg-slate-700/50 transition-all duration-200">
                        Limpiar
                    </button>
                    <a :href="exportUrl"
                       class="ml-auto px-4 py-2 bg-gradient-to-br from-green-600 to-emerald-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-green-500/20 transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Exportar CSV
                    </a>
                    <a :href="route('admin.tarjetas.reportes.export-incidentes')"
                       class="px-4 py-2 bg-gradient-to-br from-red-600/80 to-red-700/80 rounded-lg text-sm font-medium text-white transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Exportar Incidentes
                    </a>
                </div>
            </div>

            <div class="flex gap-1 bg-slate-800/50 rounded-xl p-1 border border-slate-700">
                <button v-for="tab in tabs" :key="tab.id" @click="tabActivo = tab.id"
                        :class="tabActivo === tab.id
                            ? 'bg-gradient-to-br from-cyan-600 to-blue-600 text-white shadow-lg shadow-cyan-500/20'
                            : 'text-slate-400 hover:text-white'"
                        class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200">
                    {{ tab.label }}
                </button>
            </div>

            <div v-if="tabActivo === 'lecturas'"
                 class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-white">Accesos / Lecturas por Usuario</h2>
                    <span class="text-xs text-slate-400">{{ lecturas.total }} registros</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">UID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Resultado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <tr v-if="lecturas.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-slate-500 text-sm">Sin registros para los filtros aplicados</td>
                            </tr>
                            <tr v-for="l in lecturas.data" :key="l.id" class="hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-6 py-3 text-sm text-slate-400 whitespace-nowrap">{{ formatDateTime(l.created_at) }}</td>
                                <td class="px-6 py-3 text-sm text-white font-mono">{{ l.uid_leido }}</td>
                                <td class="px-6 py-3">
                                    <div v-if="l.tarjeta?.usuario">
                                        <p class="text-sm text-white">{{ l.tarjeta.usuario.nombre }} {{ l.tarjeta.usuario.apellido }}</p>
                                        <p class="text-xs text-slate-400">{{ l.tarjeta.usuario.email }}</p>
                                    </div>
                                    <span v-else class="text-sm text-slate-500 italic">Tarjeta no registrada</span>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-300 capitalize">{{ l.modulo.replace('_', ' ') }}</td>
                                <td class="px-6 py-3 text-sm text-slate-300 capitalize">{{ l.tipo_lectura.replace('_', ' ') }}</td>
                                <td class="px-6 py-3">
                                    <span :class="l.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                                        {{ l.exito ? 'Exitoso' : 'Fallido' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-400 max-w-xs truncate">{{ l.detalle }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="lecturas.last_page > 1" class="px-6 py-4 border-t border-slate-700 flex justify-between items-center">
                    <p class="text-sm text-slate-400">{{ lecturas.from }} - {{ lecturas.to }} de {{ lecturas.total }}</p>
                    <div class="flex gap-2">
                        <a v-if="lecturas.prev_page_url" :href="lecturas.prev_page_url"
                           class="px-3 py-1 rounded-lg border border-slate-600 text-sm text-white hover:bg-slate-700/50">Anterior</a>
                        <a v-if="lecturas.next_page_url" :href="lecturas.next_page_url"
                           class="px-3 py-1 rounded-lg border border-slate-600 text-sm text-white hover:bg-slate-700/50">Siguiente</a>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-700 flex flex-wrap gap-3 bg-slate-900/30">
                    <span class="text-xs text-slate-500 self-center mr-1">Exportar esta tabla:</span>
                    <a :href="exportUrl"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-green-600 to-emerald-600 rounded-lg text-xs font-medium text-white hover:from-green-500 hover:to-emerald-500 shadow-lg shadow-green-500/20 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        CSV — Lecturas
                    </a>
                    <a :href="exportPdfUrl('lecturas')"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-xs font-medium text-white hover:from-red-500 hover:to-rose-500 shadow-lg shadow-red-500/20 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        PDF — Lecturas
                    </a>
                </div>
            </div>

            <div v-if="tabActivo === 'modulo'"
                 class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-6">
                <h2 class="text-base font-semibold text-white mb-4">Uso por Módulo</h2>
                <div v-if="usoModulo.length === 0" class="text-center py-8 text-slate-500 text-sm">Sin datos</div>
                <div v-else class="space-y-4">
                    <div v-for="m in usoModulo" :key="m.modulo" class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-white capitalize font-medium">{{ m.modulo.replace('_', ' ') }}</span>
                            <div class="flex gap-4">
                                <span class="text-slate-400">Total: <span class="text-white font-medium">{{ m.total }}</span></span>
                                <span class="text-green-400">Exitosas: {{ m.exitosas }}</span>
                            </div>
                        </div>
                        <div class="w-full bg-slate-700 rounded-full h-2">
                            <div
                                :style="{ width: maxModulo > 0 ? (m.total / maxModulo * 100) + '%' : '0%' }"
                                class="h-2 rounded-full bg-gradient-to-r from-blue-600 to-cyan-500 transition-all duration-500">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-700 flex flex-wrap gap-3">
                    <span class="text-xs text-slate-500 self-center mr-1">Exportar esta tabla:</span>
                    <a :href="exportPdfUrl('modulo', 'csv')"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg text-xs font-medium text-white hover:from-blue-500 hover:to-indigo-500 shadow-lg shadow-blue-500/20 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        CSV — Módulos
                    </a>
                    <a :href="exportPdfUrl('modulo', 'pdf')"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-xs font-medium text-white hover:from-red-500 hover:to-rose-500 shadow-lg shadow-red-500/20 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        PDF — Módulos
                    </a>
                </div>
            </div>

            <div v-if="tabActivo === 'incidentes'"
                 class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-red-500/20 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-white">Incidentes y Auditoría de Bloqueos</h2>
                    <span class="text-xs text-slate-400">{{ incidentes.total }} registros</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tarjeta (UID)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Motivo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Bloqueado por</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <tr v-if="incidentes.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-slate-500 text-sm">Sin incidentes registrados 🎉</td>
                            </tr>
                            <tr v-for="t in incidentes.data" :key="t.id" class="hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-6 py-3 text-sm font-mono text-white">{{ t.uid }}</td>
                                <td class="px-6 py-3">
                                    <p class="text-sm text-white">{{ t.usuario?.nombre }} {{ t.usuario?.apellido }}</p>
                                    <p class="text-xs text-slate-400">{{ t.usuario?.email }}</p>
                                </td>
                                <td class="px-6 py-3">
                                    <span :class="badgeClass(t.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                                        {{ t.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-400 max-w-xs truncate">{{ t.motivo_bloqueo ?? '-' }}</td>
                                <td class="px-6 py-3 text-sm text-slate-300">
                                    {{ t.bloqueado_por ? `${t.bloqueado_por.nombre} ${t.bloqueado_por.apellido}` : '-' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-400 whitespace-nowrap">{{ formatDate(t.bloqueado_at) }}</td>
                                <td class="px-6 py-3 text-right">
                                    <a :href="route('admin.tarjetas.show', t.id)"
                                       class="text-cyan-400 hover:text-cyan-300 text-sm transition-colors duration-150">Ver</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-red-500/20 flex flex-wrap gap-3 bg-slate-900/30">
                    <span class="text-xs text-slate-500 self-center mr-1">Exportar esta tabla:</span>
                    <a :href="route('admin.tarjetas.reportes.export-incidentes')"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-green-600 to-emerald-600 rounded-lg text-xs font-medium text-white hover:from-green-500 hover:to-emerald-500 shadow-lg shadow-green-500/20 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        CSV — Incidentes
                    </a>
                    <a :href="exportPdfUrl('incidentes', 'pdf')"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-xs font-medium text-white hover:from-red-500 hover:to-rose-500 shadow-lg shadow-red-500/20 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        PDF — Incidentes
                    </a>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    lecturas: Object,
    usoModulo: Array,
    incidentes: Object,
    filters: Object,
    modulos: Array,
    tipos: Array,
});

const tabActivo = ref('lecturas');
const tabs = [
    { id: 'lecturas',   label: 'Accesos / Lecturas' },
    { id: 'modulo',     label: 'Uso por Módulo' },
    { id: 'incidentes', label: 'Incidentes' },
];

const filtros = reactive({
    desde:  props.filters?.desde  ?? '',
    hasta:  props.filters?.hasta  ?? '',
    modulo: props.filters?.modulo ?? '',
    tipo:   props.filters?.tipo   ?? '',
    exito:  props.filters?.exito  ?? '',
});

function aplicarFiltros() {
    router.get(route('admin.tarjetas.reportes.index'), filtros, { preserveState: true });
}

function limpiarFiltros() {
    Object.assign(filtros, { desde: '', hasta: '', modulo: '', tipo: '', exito: '' });
    aplicarFiltros();
}

const exportUrl = computed(() => {
    const params = new URLSearchParams(
        Object.fromEntries(Object.entries(filtros).filter(([, v]) => v))
    );
    return route('admin.tarjetas.reportes.export-csv') + (params.toString() ? '?' + params.toString() : '');
});


function exportPdfUrl(seccion, formato = 'pdf') {
    const params = new URLSearchParams(
        Object.fromEntries(Object.entries(filtros).filter(([, v]) => v))
    );
    const base = `/admin/tarjetas/reportes/export-${seccion}-${formato}`;
    return base + (params.toString() ? '?' + params.toString() : '');
}

const maxModulo = computed(() => Math.max(...(props.usoModulo?.map(m => m.total) ?? []), 1));

function badgeClass(estado) {
    const map = {
        activa:    'bg-green-500/20 text-green-400',
        bloqueada: 'bg-red-500/20 text-red-400',
        perdida:   'bg-yellow-500/20 text-yellow-400',
        cancelada: 'bg-slate-500/20 text-slate-400',
    };
    return map[estado] ?? map.cancelada;
}

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
}
function formatDateTime(d) {
    return d ? new Date(d).toLocaleString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-';
}
</script>