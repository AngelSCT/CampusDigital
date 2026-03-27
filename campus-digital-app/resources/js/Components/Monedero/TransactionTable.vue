<template>
    <div class="rounded-2xl border border-blue-500/20 bg-slate-800/80 overflow-hidden">
        <!-- Encabezado con filtros -->
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-700/60">
            <h2 class="text-base font-semibold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ title }}
            </h2>

            <!-- Filtros -->
            <div class="flex gap-1.5" v-if="showFilters">
                <button
                    v-for="filtro in filtros"
                    :key="filtro.value"
                    @click="$emit('filter', filtro.value)"
                    type="button"
                    class="px-3 py-1 text-xs font-medium rounded-lg border transition-all duration-200"
                    :class="activeFilter === filtro.value
                        ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-300'
                        : 'bg-slate-700/60 border-slate-600 text-slate-400 hover:border-slate-500 hover:text-slate-300'"
                >
                    {{ filtro.label }}
                </button>
            </div>
        </div>

        <!-- Tabla vacía -->
        <div v-if="!rows || rows.length === 0" class="flex flex-col items-center justify-center py-14 px-6 text-center">
            <svg class="w-12 h-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <p class="text-slate-500 text-sm">{{ emptyMessage }}</p>
        </div>

        <!-- Filas -->
        <div v-else class="divide-y divide-slate-700/40">
            <div
                v-for="row in rows"
                :key="row.id"
                class="group flex items-center gap-4 px-6 py-4 transition-colors duration-150 hover:bg-slate-700/20"
            >
                <!-- Ícono de estado -->
                <div
                    class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform duration-200 group-hover:scale-105"
                    :class="estadoIconBg(row.estado)"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            :d="estadoIconPath(row.estado)"
                        />
                    </svg>
                </div>

                <!-- Información principal -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-medium text-white truncate">
                            {{ row.descripcion }}
                        </p>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize"
                            :class="estadoBadgeClass(row.estado)"
                        >
                            {{ row.estado }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">{{ row.fecha }}</p>
                    <p v-if="row.razonFallo" class="text-xs text-red-400 mt-0.5">{{ row.razonFallo }}</p>
                </div>

                <!-- Monto -->
                <p
                    class="text-sm font-bold font-mono whitespace-nowrap"
                    :class="esExitoso(row.estado) ? 'text-green-400' : 'text-slate-500'"
                >
                    {{ esExitoso(row.estado) ? '+' : '' }}{{ row.monto }}
                </p>

                <!-- Acciones -->
                <div class="flex gap-1 flex-shrink-0">
                    <slot name="actions" :row="row" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: 'Historial',
    },
    rows: {
        type: Array,
        default: () => [],
    },
    activeFilter: {
        type: String,
        default: 'todos',
    },
    showFilters: {
        type: Boolean,
        default: true,
    },
    emptyMessage: {
        type: String,
        default: 'Sin registros disponibles',
    },
    filtros: {
        type: Array,
        default: () => [
            { value: 'todos', label: 'Todos' },
            { value: 'exitoso', label: 'Exitosos' },
            { value: 'fallido', label: 'Fallidos' },
        ],
    },
});

defineEmits(['filter']);

function esExitoso(estado) {
    return estado === 'exitoso' || estado === 'exitosa';
}

function estadoIconBg(estado) {
    if (esExitoso(estado)) return 'bg-green-500/20 text-green-400';
    if (estado === 'fallido' || estado === 'fallida') return 'bg-red-500/20 text-red-400';
    return 'bg-yellow-500/20 text-yellow-400';
}

function estadoIconPath(estado) {
    if (esExitoso(estado)) return 'M5 13l4 4L19 7';
    if (estado === 'fallido' || estado === 'fallida') return 'M6 18L18 6M6 6l12 12';
    return 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
}

function estadoBadgeClass(estado) {
    if (esExitoso(estado)) return 'bg-green-500/20 text-green-400 border-green-500/30';
    if (estado === 'fallido' || estado === 'fallida') return 'bg-red-500/20 text-red-400 border-red-500/30';
    return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
}
</script>
