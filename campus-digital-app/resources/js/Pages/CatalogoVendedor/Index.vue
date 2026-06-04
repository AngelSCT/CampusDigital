<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Catalogo por vendedor</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/catalogo-vendedor/create" class="crud-btn-primary">Nuevo registro</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <input v-model="filters.search" type="text" class="crud-input" placeholder="Buscar por nombre, vendedor o categoria" />
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Vendedor</th>
                                <th>Tipo</th>
                                <th>Categoria</th>
                                <th>Precio actual</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in filteredCatalogo" :key="item.id_cv">
                                <td>{{ item.nombre_personalizado }}</td>
                                <td>{{ item.vendedor?.nombre || '-' }}</td>
                                <td>{{ item.tipo }}</td>
                                <td>{{ item.categoria?.nombre || '-' }}</td>
                                <td>{{ formatCurrency(item.precio_actual) }}</td>
                                <td>{{ item.activo ? 'Activo' : 'Inactivo' }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <a :href="`/catalogo-vendedor/${item.id_cv}/edit`" class="crud-link">Editar</a>
                                        <button @click="eliminar(item.id_cv)" class="crud-btn-danger">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredCatalogo.length === 0">
                                <td colspan="7" class="text-center text-slate-400">No hay resultados para los filtros seleccionados.</td>
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
    catalogo: Array,
});

const filters = ref({
    search: '',
});

const filteredCatalogo = computed(() => {
    return (props.catalogo || []).filter((item) => {
        if (!filters.value.search) {
            return true;
        }

        const term = filters.value.search.toLowerCase();

        return (item.nombre_personalizado || '').toLowerCase().includes(term)
            || (item.vendedor?.nombre || '').toLowerCase().includes(term)
            || (item.categoria?.nombre || '').toLowerCase().includes(term);
    });
});

function eliminar(id) {
    if (confirm('¿Seguro que quieres eliminar este registro?')) {
        router.delete(`/catalogo-vendedor/${id}`);
    }
}

function formatCurrency(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    const number = Number(value);
    if (Number.isNaN(number)) {
        return '-';
    }

    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
    }).format(number);
}
</script>
