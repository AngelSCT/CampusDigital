<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Categorias</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/categorias/create" class="crud-btn-primary">Nueva categoria</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <input v-model="filters.search" type="text" class="crud-input" placeholder="Buscar por nombre o descripcion" />
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripcion</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cat in filteredCategorias" :key="cat.id_categoria">
                                <td>{{ cat.nombre }}</td>
                                <td>{{ cat.descripcion || '-' }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <a :href="`/categorias/${cat.id_categoria}/edit`" class="crud-link">Editar</a>
                                        <button @click="eliminar(cat.id_categoria)" class="crud-btn-danger">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredCategorias.length === 0">
                                <td colspan="3" class="text-center text-slate-400">No hay resultados para los filtros seleccionados.</td>
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
    categorias: Array
});

const filters = ref({
    search: ''
});

const filteredCategorias = computed(() => {
    return (props.categorias || []).filter((item) => {
        if (!filters.value.search) {
            return true;
        }

        const term = filters.value.search.toLowerCase();
        return (item.nombre || '').toLowerCase().includes(term)
            || (item.descripcion || '').toLowerCase().includes(term);
    });
});

function eliminar(id) {
    if (confirm('¿Seguro que quieres eliminar esta categoría?')) {
        router.delete(`/categorias/${id}`);
    }
}
</script>