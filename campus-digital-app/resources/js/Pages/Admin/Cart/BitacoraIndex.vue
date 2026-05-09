<template>
    <Head title="Bitácora" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Bitácora de eventos</h2>
        </template>

        <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <!-- Filtros -->
            <form @submit.prevent="aplicarFiltros"
                  class="bg-white shadow rounded-lg p-4 flex flex-wrap gap-3 items-end">

                <!-- Multi-select de acciones -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Acciones</label>
                    <select v-model="form.accion" multiple
                            class="border border-gray-300 rounded px-2 py-1 text-sm h-24 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option v-for="a in accionesDisponibles" :key="a" :value="a">{{ a }}</option>
                    </select>
                </div>

                <!-- Módulo -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Módulo</label>
                    <select v-model="form.modulo_id"
                            class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Todos</option>
                        <option v-for="m in modulos" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                    </select>
                </div>

                <!-- Desde -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                    <input v-model="form.desde" type="date"
                           class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>

                <!-- Hasta -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                    <input v-model="form.hasta" type="date"
                           class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-1.5 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                        Filtrar
                    </button>
                    <button type="button" @click="limpiar"
                            class="px-4 py-1.5 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                        Limpiar
                    </button>
                </div>
            </form>

            <!-- Tabla -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Acción</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Módulo ID</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Usuario</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">IP</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="entrada in bitacoras.data" :key="entrada.id">
                            <td class="px-4 py-3">
                                <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">{{ entrada.accion }}</code>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ entrada.modulo_id ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ entrada.user_id ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ entrada.ip_address ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ formatDate(entrada.created_at) }}</td>
                        </tr>
                        <tr v-if="bitacoras.data.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                                Sin registros con los filtros aplicados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="bitacoras.last_page > 1" class="flex justify-center gap-1">
                <Link v-for="link in bitacoras.links" :key="link.label"
                      :href="link.url || '#'"
                      :class="['px-3 py-1 rounded text-sm border',
                               link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                               !link.url ? 'opacity-40 pointer-events-none' : '']"
                      v-html="link.label" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    bitacoras:           Object,
    filtros:             Object,
    accionesDisponibles: Array,
    modulos:             Array,
});

const form = reactive({
    accion:    props.filtros?.accion    ?? [],
    modulo_id: props.filtros?.modulo_id ?? '',
    desde:     props.filtros?.desde     ?? '',
    hasta:     props.filtros?.hasta     ?? '',
    user_id:   props.filtros?.user_id   ?? '',
});

function aplicarFiltros() {
    const params = {};
    if (form.accion?.length)  params.accion    = form.accion;
    if (form.modulo_id)       params.modulo_id = form.modulo_id;
    if (form.desde)           params.desde     = form.desde;
    if (form.hasta)           params.hasta     = form.hasta;
    if (form.user_id)         params.user_id   = form.user_id;

    router.get(route('admin.cart.bitacora.index'), params, { preserveState: true });
}

function limpiar() {
    form.accion    = [];
    form.modulo_id = '';
    form.desde     = '';
    form.hasta     = '';
    form.user_id   = '';
    router.get(route('admin.cart.bitacora.index'));
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}
</script>
