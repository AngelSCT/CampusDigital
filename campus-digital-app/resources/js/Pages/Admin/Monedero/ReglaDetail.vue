<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle de Regla de Saldo
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Regla #{{ regla.id }}
                        </h3>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-medium"
                            :class="regla.activo
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800'"
                        >
                            {{ regla.activo ? '✓ Activo' : '✗ Inactivo' }}
                        </span>
                    </div>

                    <div class="border-t border-gray-200">
                        <dl class="divide-y divide-gray-200">
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Usuario</dt>
                                <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">
                                    {{ regla.usuario?.nombre || '-' }}
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Tipo de Límite</dt>
                                <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        {{ formatTipoLimite(regla.tipo_limite) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Monto Límite</dt>
                                <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900 font-semibold">
                                    ${{ formatCurrency(regla.monto_limite) }}
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Módulo</dt>
                                <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                    <span v-if="regla.modulo"
                                          class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        {{ regla.modulo }}
                                    </span>
                                    <span v-else class="text-sm text-gray-500">Todos los módulos</span>
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">
                                    {{ regla.descripcion || 'Sin descripción' }}
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Creada</dt>
                                <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">
                                    {{ formatDate(regla.created_at) }}
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Actualizada</dt>
                                <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">
                                    {{ formatDate(regla.updated_at) }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-8 flex gap-4">
                        <a :href="`/admin/monedero/reglas/${regla.id}/edit`"
                           class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Editar Regla
                        </a>
                        <a href="/admin/monedero/reglas"
                           class="px-6 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">
                            Volver al listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    regla: {
        type: Object,
        required: true,
    },
});

function formatTipoLimite(tipo) {
    const tipos = { diario: 'Diario', semanal: 'Semanal', mensual: 'Mensual' };
    return tipos[tipo] || tipo;
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
    }).format(value || 0);
}

function formatDate(date) {
    return new Date(date).toLocaleString('es-AR');
}
</script>
