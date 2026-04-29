<template>
    <Head :title="`Módulo — ${modulo.nombre}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">{{ modulo.nombre }}</h2>
                <Link :href="route('admin.cart.modulos.index')"
                      class="text-sm text-indigo-600 hover:text-indigo-900">
                    ← Volver a módulos
                </Link>
            </div>
        </template>

        <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Info general -->
            <div class="bg-white shadow rounded-lg p-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-500">Tipo:</span>
                    <span class="ml-2 text-gray-800">{{ modulo.tipo_modulo }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-500">Slug:</span>
                    <code class="ml-2 bg-gray-100 px-1 rounded">{{ modulo.slug }}</code>
                </div>
                <div v-if="modulo.solicitud">
                    <span class="font-medium text-gray-500">Folio solicitud:</span>
                    <span class="ml-2 text-gray-800">{{ modulo.solicitud.folio }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-500">Alta:</span>
                    <span class="ml-2 text-gray-800">{{ formatDate(modulo.created_at) }}</span>
                </div>
            </div>

            <!-- Flash success -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ $page.props.flash.success }}
            </div>

            <!-- Acciones -->
            <div class="bg-white shadow rounded-lg p-6 flex flex-wrap gap-3">
                <form @submit.prevent="revocar">
                    <div class="flex gap-2 items-center">
                        <input v-model="motivoRevocar" type="text" placeholder="Motivo (opcional)"
                               class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <button type="submit"
                                class="px-4 py-1.5 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                            Revocar tokens
                        </button>
                    </div>
                </form>

                <form @submit.prevent="forzarRefresh">
                    <button type="submit"
                            class="px-4 py-1.5 bg-yellow-500 text-white text-sm rounded hover:bg-yellow-600">
                        Forzar refresh
                    </button>
                </form>
            </div>

            <!-- Historial de tokens -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-700">Historial de tokens</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">JTI</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Tipo</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Estado</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Motivo revocación</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Emitido</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Entregado</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase text-xs">Expira</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="token in modulo.tokens" :key="token.id">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                {{ token.jti.substring(0, 8) }}…
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium"
                                      :class="token.tipo === 'access' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'">
                                    {{ token.tipo }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium"
                                      :class="token.estado === 'activo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                    {{ token.estado }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ token.motivo_revocacion ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ formatDate(token.created_at) }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ token.entregado_at ? formatDate(token.entregado_at) : '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ formatDate(token.expires_at) }}</td>
                        </tr>
                        <tr v-if="!modulo.tokens?.length">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Sin tokens registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    modulo: Object,
});

const motivoRevocar = ref('');

const revocarForm = useForm({});
function revocar() {
    revocarForm.transform(() => ({ motivo: motivoRevocar.value || undefined }))
        .post(route('admin.cart.modulos.revocar', props.modulo.id));
}

const refreshForm = useForm({});
function forzarRefresh() {
    refreshForm.post(route('admin.cart.modulos.forzar-refresh', props.modulo.id));
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}
</script>
