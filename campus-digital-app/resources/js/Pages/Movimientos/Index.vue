<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Movimientos</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/movimientos/create" class="crud-btn-primary">Nuevo movimiento</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <div class="grid md:grid-cols-2 gap-2">
                    <input v-model="filters.search" type="text" class="crud-input" placeholder="Buscar por producto" />
                    <input v-model="filters.fecha" type="date" class="crud-input" />
                </div>
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="mov in filteredMovimientos" :key="mov.id_movimiento">
                                <td>{{ mov.catalogo?.nombre || 'Sin producto' }}</td>
                                <td>{{ mov.cantidad }}</td>
                                <td>{{ formatDate(mov.fecha) }}</td>
                            </tr>
                            <tr v-if="filteredMovimientos.length === 0">
                                <td colspan="3" class="text-center text-slate-400">No hay movimientos registrados.</td>
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
    movimientos: {
        type: Array,
        default: () => []
    }
});

const filters = ref({
    search: '',
    fecha: ''
});

const filteredMovimientos = computed(() => {
    return (props.movimientos || []).filter((item) => {
        const bySearch = !filters.value.search || (item.catalogo?.nombre || '').toLowerCase().includes(filters.value.search.toLowerCase());

        if (!filters.value.fecha) {
            return bySearch;
        }

        const itemDate = item.fecha ? new Date(item.fecha).toISOString().slice(0, 10) : '';
        return bySearch && itemDate === filters.value.fecha;
    });
});

function formatDate(value) {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>
