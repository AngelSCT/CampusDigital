<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Areas</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/areas/create" class="crud-btn-primary">Nueva area</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <input v-model="filters.search" type="text" class="crud-input" placeholder="Buscar por nombre" />
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in filteredAreas" :key="a.id_area">
                                <td>{{ a.nombre }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <a :href="`/areas/${a.id_area}/edit`" class="crud-link">Editar</a>
                                        <button @click="eliminar(a.id_area)" class="crud-btn-danger">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredAreas.length === 0">
                                <td colspan="2" class="text-center text-slate-400">No hay resultados para los filtros seleccionados.</td>
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
import { router } from '@inertiajs/vue3';

const props = defineProps({
    areas: Array
});

const filters = ref({
    search: ''
});

const filteredAreas = computed(() => {
    return (props.areas || []).filter((item) => {
        return !filters.value.search || item.nombre?.toLowerCase().includes(filters.value.search.toLowerCase());
    });
});

function eliminar(id) {
    if (confirm('¿Eliminar área?')) {
        router.delete(`/areas/${id}`);
    }
}
</script>