<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Inventario</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/inventario/agregar-stock" class="crud-btn-primary">Agregar stock</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <input v-model="filters.search" type="text" class="crud-input" placeholder="Buscar por producto" />
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Stock actual</th>
                                <th>Stock minimo</th>
                                <th>Estado</th>
                                <th>Ultima actualizacion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in filteredInventario" :key="item.id_inventario">
                                <td>{{ item.catalogo?.nombre || 'Sin producto' }}</td>
                                <td>{{ item.stock_actual }}</td>
                                <td>{{ item.stock_minimo }}</td>
                                <td>
                                    <span :class="estadoClass(item)">{{ estadoLabel(item) }}</span>
                                </td>
                                <td>{{ formatDate(item.fecha_actualizacion) }}</td>
                            </tr>
                            <tr v-if="filteredInventario.length === 0">
                                <td colspan="5" class="text-center text-slate-400">No hay registros de inventario.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    inventario: {
        type: Array,
        default: () => [],
    },
});

const filters = ref({
    search: '',
});

const filteredInventario = computed(() => {
    return (props.inventario || []).filter((item) => {
        if (!filters.value.search) {
            return true;
        }

        return (item.catalogo?.nombre || '').toLowerCase().includes(filters.value.search.toLowerCase());
    });
});

function estadoLabel(item) {
    if ((item.stock_actual ?? 0) <= 0) {
        return 'Agotado';
    }

    if ((item.stock_actual ?? 0) <= (item.stock_minimo ?? 0)) {
        return 'Bajo';
    }

    return 'Disponible';
}

function estadoClass(item) {
    const estado = estadoLabel(item);
    if (estado === 'Agotado') {
        return 'text-rose-300';
    }

    if (estado === 'Bajo') {
        return 'text-amber-300';
    }

    return 'text-emerald-300';
}

function formatDate(value) {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>
