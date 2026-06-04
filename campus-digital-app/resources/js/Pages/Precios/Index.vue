<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Precios</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/precios/create" class="crud-btn-primary">Nuevo precio</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <div class="grid md:grid-cols-2 gap-2">
                    <input v-model="filters.search" type="text" class="crud-input" placeholder="Buscar por producto" />
                    <select v-model="filters.estado" class="crud-select">
                        <option value="">Todos los estados</option>
                        <option value="actual">Actual</option>
                        <option value="historico">Historico</option>
                    </select>
                </div>
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in filteredPrecios" :key="p.id_precio">
                                <td>{{ p.catalogo.nombre }}</td>
                                <td>${{ p.precio }}</td>
                                <td>{{ p.fecha_inicio }}</td>
                                <td>{{ p.fecha_fin || 'Actual' }}</td>
                            </tr>
                            <tr v-if="filteredPrecios.length === 0">
                                <td colspan="4" class="text-center text-slate-400">No hay resultados para los filtros seleccionados.</td>
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
    precios: Array
});

const filters = ref({
    search: '',
    estado: ''
});

const filteredPrecios = computed(() => {
    return (props.precios || []).filter((item) => {
        const bySearch = !filters.value.search || (item.catalogo?.nombre || '').toLowerCase().includes(filters.value.search.toLowerCase());
        const isActual = !item.fecha_fin;
        const byEstado = !filters.value.estado
            || (filters.value.estado === 'actual' ? isActual : !isActual);

        return bySearch && byEstado;
    });
});
</script>