<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Promociones</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/promociones/create" class="crud-btn-primary">Nueva promocion</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <input v-model="filters.search" type="text" class="crud-input" placeholder="Buscar por nombre o tipo" />
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Estado</th>
                                <th>Catalogo</th>
                                <th>Catalogo vendedor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in filteredPromociones" :key="item.id_promocion">
                                <td>{{ item.nombre }}</td>
                                <td>{{ item.tipo || '-' }}</td>
                                <td>{{ formatCurrency(item.valor) }}</td>
                                <td>{{ item.activa ? 'Activa' : 'Inactiva' }}</td>
                                <td>{{ item.catalogo?.length || 0 }}</td>
                                <td>{{ item.catalogo_vendedor?.length || 0 }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <a :href="`/promociones/${item.id_promocion}/edit`" class="crud-link">Editar</a>
                                        <button @click="eliminar(item.id_promocion)" class="crud-btn-danger">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredPromociones.length === 0">
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
    promociones: Array,
});

const filters = ref({
    search: '',
});

const filteredPromociones = computed(() => {
    return (props.promociones || []).filter((item) => {
        if (!filters.value.search) {
            return true;
        }

        const term = filters.value.search.toLowerCase();

        return (item.nombre || '').toLowerCase().includes(term)
            || (item.tipo || '').toLowerCase().includes(term);
    });
});

function eliminar(id) {
    if (confirm('¿Seguro que quieres eliminar esta promocion?')) {
        router.delete(`/promociones/${id}`);
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
