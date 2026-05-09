<template>
    <Head title="Módulos Activos" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Módulos Activos</h2>
        </template>

        <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Módulo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tokens activos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alta</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="modulo in modulos.data" :key="modulo.id">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ modulo.nombre }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ modulo.tipo_modulo }}</td>
                            <td class="px-6 py-4 text-gray-500 font-mono text-sm">{{ modulo.slug }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                      :class="tokensActivos(modulo) > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'">
                                    {{ tokensActivos(modulo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(modulo.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('admin.cart.modulos.show', modulo.id)"
                                      class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                    Ver detalle
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="modulos.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No hay módulos registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="modulos.last_page > 1" class="mt-4 flex justify-center gap-1">
                <Link v-for="link in modulos.links" :key="link.label"
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
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    modulos: Object,
});

function tokensActivos(modulo) {
    return (modulo.tokens ?? []).filter(t => t.tipo === 'access' && t.estado === 'activo').length;
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>
