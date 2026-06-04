<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h1 class="crud-title">Catalogo</h1>

                <div class="flex items-center gap-2">
                    <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard</a>
                    <a href="/catalogo/create" class="crud-btn-primary">
                        Nuevo
                    </a>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Acciones masivas</h2>

                <div class="flex flex-wrap items-center gap-2">
                    <select v-model="bulk.action" class="crud-select min-w-[210px]">
                        <option value="">Selecciona accion</option>
                        <option value="activar">Activar seleccionados</option>
                        <option value="desactivar">Desactivar seleccionados</option>
                        <option value="cambiar_categoria">Cambiar categoria</option>
                        <option value="eliminar">Eliminar seleccionados</option>
                    </select>

                    <select
                        v-if="bulk.action === 'cambiar_categoria'"
                        v-model="bulk.id_categoria"
                        class="crud-select min-w-[220px]"
                    >
                        <option value="">Selecciona categoria destino</option>
                        <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
                            {{ cat.nombre }}
                        </option>
                    </select>

                    <button
                        @click="applyBulk"
                        :disabled="bulkProcessing || selectedIds.length === 0 || !bulk.action"
                        class="crud-btn-primary disabled:opacity-50"
                    >
                        {{ bulkProcessing ? 'Aplicando...' : `Aplicar (${selectedIds.length})` }}
                    </button>
                </div>
            </div>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Filtros</h2>

                <div class="grid md:grid-cols-4 gap-2">
                    <input
                        v-model="filters.search"
                        type="text"
                        class="crud-input"
                        placeholder="Buscar por nombre"
                    />

                    <select v-model="filters.tipo" class="crud-select">
                        <option value="">Todos los tipos</option>
                        <option value="producto">Producto</option>
                        <option value="servicio">Servicio</option>
                    </select>

                    <select v-model="filters.estado" class="crud-select">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>

                    <select v-model="filters.id_categoria" class="crud-select">
                        <option value="">Todas las categorias</option>
                        <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
                            {{ cat.nombre }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="crud-card">
                <div class="crud-table-wrap">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th class="p-2 text-left w-[50px]">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    @change="toggleAll($event)"
                                />
                            </th>
                            <th class="p-2 text-left">Nombre</th>
                            <th class="p-2 text-left">Tipo</th>
                            <th class="p-2 text-left">Categoria</th>
                            <th class="p-2 text-left">Estado</th>
                            <th class="p-2 text-left">Precio actual</th>
                            <th class="p-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in filteredRows" :key="item.id_catalogo" class="align-top">
                            <td class="p-2">
                                <input
                                    type="checkbox"
                                    :value="item.id_catalogo"
                                    v-model="selectedIds"
                                />
                            </td>
                            <td class="p-2 font-medium text-slate-100">{{ item.nombre }}</td>
                            <td class="p-2">{{ item.tipo }}</td>
                            <td class="p-2">
                                {{ item.categoria ? item.categoria.nombre : 'Sin categoria' }}
                            </td>
                            <td class="p-2">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" v-model="item.activo" />
                                    <span class="text-sm text-slate-300">{{ item.activo ? 'Activo' : 'Inactivo' }}</span>
                                </label>
                            </td>
                            <td class="p-2">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    v-model="item.precio_actual"
                                    class="crud-input w-28"
                                />
                            </td>
                            <td class="p-2">
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        @click="saveQuick(item)"
                                        :disabled="quickSavingId === item.id_catalogo"
                                        class="crud-btn-primary text-sm disabled:opacity-50"
                                    >
                                        {{ quickSavingId === item.id_catalogo ? 'Guardando...' : 'Guardar rapido' }}
                                    </button>

                                    <a :href="`/catalogo/${item.id_catalogo}/edit`" class="crud-link text-sm">
                                        Editar
                                    </a>
                                    <button @click="eliminar(item.id_catalogo)" class="crud-btn-danger text-sm">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredRows.length === 0">
                            <td colspan="7" class="p-4 text-center text-slate-400">
                                No hay resultados para los filtros seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <transition name="fade">
            <div
                v-if="toast.show"
                class="fixed right-4 top-4 text-white px-4 py-2 rounded shadow-lg z-50"
                :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'"
            >
                {{ toast.message }}
            </div>
        </transition>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    catalogo: Array,
    categorias: Array
});

const rows = ref([]);
const selectedIds = ref([]);
const quickSavingId = ref(null);
const bulkProcessing = ref(false);
const bulk = ref({
    action: '',
    id_categoria: ''
});
const filters = ref({
    search: '',
    tipo: '',
    estado: '',
    id_categoria: ''
});
const toast = ref({
    show: false,
    message: '',
    type: 'success'
});

watch(
    () => props.catalogo,
    (value) => {
        rows.value = (value || []).map((item) => ({
            ...item,
            activo: Boolean(item.activo),
            precio_actual: item.precio_actual ?? ''
        }));
    },
    { immediate: true }
);

const allSelected = computed(() => {
    return filteredRows.value.length > 0 && filteredRows.value.every((item) => selectedIds.value.includes(item.id_catalogo));
});

const filteredRows = computed(() => {
    return rows.value.filter((item) => {
        const bySearch = !filters.value.search || item.nombre.toLowerCase().includes(filters.value.search.toLowerCase());
        const byTipo = !filters.value.tipo || item.tipo === filters.value.tipo;
        const byEstado = !filters.value.estado || (filters.value.estado === 'activo' ? Boolean(item.activo) : !Boolean(item.activo));
        const byCategoria = !filters.value.id_categoria || Number(item.id_categoria) === Number(filters.value.id_categoria);

        return bySearch && byTipo && byEstado && byCategoria;
    });
});

function toggleAll(event) {
    if (event.target.checked) {
        const visibleIds = filteredRows.value.map((item) => item.id_catalogo);
        selectedIds.value = Array.from(new Set([...selectedIds.value, ...visibleIds]));
        return;
    }

    const visibleIds = filteredRows.value.map((item) => item.id_catalogo);
    selectedIds.value = selectedIds.value.filter((id) => !visibleIds.includes(id));
}

function saveQuick(item) {
    quickSavingId.value = item.id_catalogo;

    router.patch(
        `/catalogo/${item.id_catalogo}/quick-update`,
        {
            activo: item.activo,
            precio: item.precio_actual === '' ? null : item.precio_actual,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => showToast('Registro actualizado correctamente.'),
            onError: () => showToast('No se pudo actualizar el registro.', 'error'),
            onFinish: () => {
                quickSavingId.value = null;
            }
        }
    );
}

function applyBulk() {
    if (selectedIds.value.length === 0 || !bulk.value.action) {
        return;
    }

    if (bulk.value.action === 'eliminar' && !confirm('Se eliminaran los elementos seleccionados. Deseas continuar?')) {
        return;
    }

    bulkProcessing.value = true;

    router.post(
        '/catalogo/bulk-update',
        {
            ids: selectedIds.value,
            action: bulk.value.action,
            id_categoria: bulk.value.id_categoria || null,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => showToast('Accion masiva aplicada correctamente.'),
            onError: () => showToast('No se pudo aplicar la accion masiva.', 'error'),
            onFinish: () => {
                bulkProcessing.value = false;
                selectedIds.value = [];
                bulk.value.action = '';
                bulk.value.id_categoria = '';
            }
        }
    );
}

function eliminar(id) {
    if (confirm('Eliminar este registro?')) {
        router.delete(`/catalogo/${id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => showToast('Registro eliminado.'),
            onError: () => showToast('No se pudo eliminar el registro.', 'error')
        });
    }
}

function showToast(message, type = 'success') {
    toast.value = {
        show: true,
        message,
        type,
    };

    setTimeout(() => {
        toast.value.show = false;
    }, 2600);
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>