<template>
    <div class="bg-slate-800/70 border border-slate-700 rounded-2xl overflow-hidden">

        <!-- ── Encabezado y filtros ── -->
        <div class="px-6 py-4 border-b border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <h2 class="text-base font-semibold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Historial de movimientos
                </h2>
                <span class="text-xs text-slate-400 whitespace-nowrap">
                    {{ totalFiltrados }} resultado{{ totalFiltrados !== 1 ? 's' : '' }}
                </span>
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Búsqueda -->
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input
                        v-model="filtrosLocales.busqueda"
                        @input="emitFiltro('busqueda', $event.target.value)"
                        type="text"
                        placeholder="Buscar..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-sm text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/50 transition-all"
                    />
                </div>

                <!-- Tipo -->
                <select
                    v-model="filtrosLocales.tipo"
                    @change="emitFiltro('tipo', $event.target.value)"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-sm text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/50 transition-all"
                >
                    <option value="">Todos los tipos</option>
                    <option value="recarga">Recargas</option>
                    <option value="pago">Pagos</option>
                    <option value="transferencia">Transferencias</option>
                </select>

                <!-- Estado -->
                <select
                    v-model="filtrosLocales.estado"
                    @change="emitFiltro('estado', $event.target.value)"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-sm text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/50 transition-all"
                >
                    <option value="">Todos los estados</option>
                    <option value="exitoso">Exitosos</option>
                    <option value="fallido">Fallidos</option>
                    <option value="pendiente">Pendientes</option>
                </select>

                <!-- Limpiar filtros -->
                <button
                    @click="limpiar"
                    class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-sm text-slate-300 hover:bg-slate-600/50 hover:text-white transition-all"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Limpiar
                </button>
            </div>
        </div>

        <!-- ── Cuerpo: loading / vacío / tabla ── -->
        <div class="overflow-x-auto">

            <!-- Loading -->
            <div v-if="loading" class="flex items-center justify-center py-16 gap-3">
                <svg class="w-7 h-7 text-cyan-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <span class="text-sm text-slate-400">Cargando movimientos...</span>
            </div>

            <!-- Sin resultados -->
            <div v-else-if="!movimientos || movimientos.length === 0"
                 class="flex flex-col items-center justify-center py-16 text-slate-400">
                <svg class="w-12 h-12 mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm font-medium">Sin movimientos</p>
                <p class="text-xs text-slate-500 mt-1">
                    {{ hayFiltros ? 'No hay resultados para los filtros aplicados' : 'Aún no tienes movimientos registrados' }}
                </p>
            </div>

            <!-- Tabla -->
            <table v-else class="w-full">
                <thead>
                    <tr class="bg-slate-900/40">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40">
                    <tr
                        v-for="mov in movimientos"
                        :key="mov.id"
                        class="hover:bg-slate-700/20 transition-colors duration-150"
                    >
                        <!-- Tipo -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm flex-shrink-0"
                                     :class="tipoIconClass(mov.tipo)">
                                    {{ tipoIconEmoji(mov.tipo) }}
                                </div>
                                <span class="text-sm text-white font-medium">{{ formatMovementType(mov.tipo) }}</span>
                            </div>
                        </td>

                        <!-- Monto -->
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold font-mono" :class="montoClass(mov)">
                                {{ montoPrefix(mov) }}${{ formatAmount(mov.monto) }}
                            </span>
                        </td>

                        <!-- Estado -->
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                  :class="estadoClass(mov.estado)">
                                <span class="w-1.5 h-1.5 rounded-full" :class="estadoDot(mov.estado)"></span>
                                {{ formatStatus(mov.estado) }}
                            </span>
                        </td>

                        <!-- Fecha -->
                        <td class="px-6 py-4">
                            <span class="text-xs text-slate-400">{{ formatDateTime(mov.created_at) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── Paginación ── -->
        <div v-if="totalPaginas > 1" class="px-6 py-4 border-t border-slate-700 flex items-center justify-between gap-4 flex-wrap">
            <p class="text-xs text-slate-400">
                Página {{ paginaActual }} de {{ totalPaginas }}
            </p>
            <div class="flex gap-1.5 flex-wrap">
                <button
                    @click="$emit('cambiar-pagina', paginaActual - 1)"
                    :disabled="paginaActual <= 1"
                    class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-600 hover:bg-slate-700/50 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                >← Anterior</button>

                <button
                    v-for="p in paginasVisibles"
                    :key="p"
                    @click="$emit('cambiar-pagina', p)"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-medium transition-all',
                        p === paginaActual
                            ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40'
                            : 'text-slate-300 border border-slate-600 hover:bg-slate-700/50'
                    ]"
                >{{ p }}</button>

                <button
                    @click="$emit('cambiar-pagina', paginaActual + 1)"
                    :disabled="paginaActual >= totalPaginas"
                    class="px-3 py-1.5 rounded-lg text-xs text-slate-300 border border-slate-600 hover:bg-slate-700/50 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                >Siguiente →</button>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { formatAmount, formatDateTime, formatMovementType, formatStatus } from '@/utils/formatters.js';

const props = defineProps({
    movimientos:    { type: Array,  default: () => [] },
    loading:        { type: Boolean, default: false },
    totalFiltrados: { type: Number,  default: 0 },
    paginaActual:   { type: Number,  default: 1 },
    totalPaginas:   { type: Number,  default: 1 },
});

const emit = defineEmits(['cambiar-pagina', 'filtrar', 'limpiar-filtros']);

const filtrosLocales = ref({ busqueda: '', tipo: '', estado: '' });

const hayFiltros = computed(() =>
    filtrosLocales.value.busqueda || filtrosLocales.value.tipo || filtrosLocales.value.estado
);

const paginasVisibles = computed(() => {
    const total = props.totalPaginas;
    const actual = props.paginaActual;
    const range = [];
    for (let i = Math.max(1, actual - 2); i <= Math.min(total, actual + 2); i++) {
        range.push(i);
    }
    return range;
});

function emitFiltro(key, value) {
    emit('filtrar', { key, value });
}

function limpiar() {
    filtrosLocales.value = { busqueda: '', tipo: '', estado: '' };
    emit('limpiar-filtros');
}

// ── Helpers de estilos ───────────────────────────────────────────
const TIPO_EMOJI = { recarga: '⬆️', pago: '⬇️', transferencia: '↔️' };
const TIPO_CLASS = { recarga: 'bg-green-500/15', pago: 'bg-blue-500/15', transferencia: 'bg-purple-500/15' };
const ESTADO_CLASS = {
    exitosa:   'bg-green-500/15 text-green-400 border border-green-500/25',
    exitoso:   'bg-green-500/15 text-green-400 border border-green-500/25',
    fallida:   'bg-red-500/15 text-red-400 border border-red-500/25',
    fallido:   'bg-red-500/15 text-red-400 border border-red-500/25',
    pendiente: 'bg-yellow-500/15 text-yellow-400 border border-yellow-500/25',
};
const ESTADO_DOT = {
    exitosa: 'bg-green-400', exitoso: 'bg-green-400',
    fallida: 'bg-red-400',  fallido: 'bg-red-400',
    pendiente: 'bg-yellow-400',
};

function tipoIconEmoji(tipo) {
    return TIPO_EMOJI[tipo] || '📋';
}

function tipoIconClass(tipo) {
    return TIPO_CLASS[tipo] || 'bg-slate-700';
}

function montoClass(mov) {
    if (!['exitosa', 'exitoso'].includes(mov.estado)) return 'text-slate-500';
    return mov.tipo === 'recarga' ? 'text-green-400' : 'text-red-400';
}

function montoPrefix(mov) {
    if (!['exitosa', 'exitoso'].includes(mov.estado)) return '';
    return mov.tipo === 'recarga' ? '+' : '-';
}

function estadoClass(estado) {
    return ESTADO_CLASS[estado] || 'bg-slate-700 text-slate-400 border border-slate-600';
}

function estadoDot(estado) {
    return ESTADO_DOT[estado] || 'bg-slate-400';
}
</script>
