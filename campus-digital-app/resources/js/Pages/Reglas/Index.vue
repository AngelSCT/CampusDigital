<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Reglas</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/reglas/create" class="crud-btn-primary">Nueva regla</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <div class="grid md:grid-cols-2 gap-2">
                    <input v-model="filters.search" type="text" class="crud-input" placeholder="Buscar por producto o descripcion" />
                    <input v-model="filters.tipo" type="text" class="crud-input" placeholder="Filtrar por tipo" />
                </div>
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Descripcion</th>
                                <th>Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in filteredReglas" :key="r.id_regla">
                                <td>{{ r.catalogo?.nombre }}</td>
                                <td>{{ r.descripcion }}</td>
                                <td>{{ r.tipo_regla || 'N/A' }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <a :href="`/reglas/${r.id_regla}/edit`" class="crud-link">Editar</a>
                                        <button @click="eliminar(r.id_regla)" class="crud-btn-danger">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredReglas.length === 0">
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
import { router } from '@inertiajs/vue3';

const props = defineProps({
    reglas: Array
});

const filters = ref({
    search: '',
    tipo: ''
});

const filteredReglas = computed(() => {
    return (props.reglas || []).filter((item) => {
        const term = filters.value.search.toLowerCase();
        const bySearch = !term
            || (item.catalogo?.nombre || '').toLowerCase().includes(term)
            || (item.descripcion || '').toLowerCase().includes(term);
        const byTipo = !filters.value.tipo || (item.tipo_regla || '').toLowerCase().includes(filters.value.tipo.toLowerCase());

        return bySearch && byTipo;
    });
});

function eliminar(id) {
    if (confirm('¿Eliminar regla?')) {
        router.delete(`/reglas/${id}`);
    }
}
</script>