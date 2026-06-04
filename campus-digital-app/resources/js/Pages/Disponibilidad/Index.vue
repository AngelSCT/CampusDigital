<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <h1 class="crud-title">Disponibilidad</h1>
                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/disponibilidad/create" class="crud-btn-primary">Nueva disponibilidad</a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>
                <div class="grid md:grid-cols-2 gap-2">
                    <input v-model="filters.search" type="text" class="crud-input" placeholder="Buscar por producto" />
                    <select v-model="filters.dia" class="crud-select">
                        <option value="">Todos los dias</option>
                        <option value="lunes">Lunes</option>
                        <option value="martes">Martes</option>
                        <option value="miercoles">Miercoles</option>
                        <option value="jueves">Jueves</option>
                        <option value="viernes">Viernes</option>
                        <option value="sabado">Sabado</option>
                        <option value="domingo">Domingo</option>
                    </select>
                </div>
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Dia</th>
                                <th>Hora inicio</th>
                                <th>Hora fin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in filteredDisponibilidad" :key="d.id_disponibilidad">
                                <td>{{ d.catalogo?.nombre }}</td>
                                <td>{{ d.dia_semana }}</td>
                                <td>{{ d.hora_inicio }}</td>
                                <td>{{ d.hora_fin }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <a :href="`/disponibilidad/${d.id_disponibilidad}/edit`" class="crud-link">Editar</a>
                                        <button @click="eliminar(d.id_disponibilidad)" class="crud-btn-danger">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredDisponibilidad.length === 0">
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
    disponibilidad: Array
});

const filters = ref({
    search: '',
    dia: ''
});

const filteredDisponibilidad = computed(() => {
    return (props.disponibilidad || []).filter((item) => {
        const bySearch = !filters.value.search || (item.catalogo?.nombre || '').toLowerCase().includes(filters.value.search.toLowerCase());
        const byDia = !filters.value.dia || (item.dia_semana || '').toLowerCase() === filters.value.dia;
        return bySearch && byDia;
    });
});

function eliminar(id) {
    if (confirm('¿Eliminar registro?')) {
        router.delete(`/disponibilidad/${id}`);
    }
}
</script>