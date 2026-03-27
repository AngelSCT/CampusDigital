<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Vendedores</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/vendedores/create" class="crud-btn-primary">Nuevo vendedor</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <input
                    v-model="filters.search"
                    type="text"
                    class="crud-input"
                    placeholder="Buscar por nombre, email o telefono"
                />
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Telefono</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="vendedor in filteredVendedores" :key="vendedor.id_vendedor">
                                <td>{{ vendedor.nombre }}</td>
                                <td>{{ vendedor.email }}</td>
                                <td>{{ vendedor.telefono || '-' }}</td>
                                <td>{{ vendedor.activo ? 'Activo' : 'Inactivo' }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <a :href="`/vendedores/${vendedor.id_vendedor}/edit`" class="crud-link">Editar</a>
                                        <button @click="eliminar(vendedor.id_vendedor)" class="crud-btn-danger">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredVendedores.length === 0">
                                <td colspan="5" class="text-center text-slate-400">No hay resultados para los filtros seleccionados.</td>
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
    vendedores: Array,
});

const filters = ref({
    search: '',
});

const filteredVendedores = computed(() => {
    return (props.vendedores || []).filter((item) => {
        if (!filters.value.search) {
            return true;
        }

        const term = filters.value.search.toLowerCase();

        return (item.nombre || '').toLowerCase().includes(term)
            || (item.email || '').toLowerCase().includes(term)
            || (item.telefono || '').toLowerCase().includes(term);
    });
});

function eliminar(id) {
    if (confirm('¿Seguro que quieres eliminar este vendedor?')) {
        router.delete(`/vendedores/${id}`);
    }
}
</script>
