<template>
    <Head :title="`Módulo — ${modulo.nombre}`" />
    <AuthLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <Link href="/admin/cart/modulos"
                          class="text-slate-400 hover:text-slate-300 transition-colors text-sm">
                        ← Módulos
                    </Link>
                    <span class="text-slate-600">/</span>
                    <h1 class="text-2xl font-bold text-white">{{ modulo.nombre }}</h1>
                    <span :class="modulo.activo
                        ? 'bg-green-500/20 text-green-400 border-green-500/30'
                        : 'bg-slate-700/50 text-slate-400 border-slate-600/40'"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border">
                        {{ modulo.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $page.props.flash.success }}
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Info general -->
                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-xl shadow-violet-500/5 p-6">
                    <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Información del módulo</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs text-slate-500 mb-1">Tipo</dt>
                            <dd class="text-white font-medium">{{ modulo.tipo_modulo }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 mb-1">Slug</dt>
                            <dd><code class="text-sm text-violet-300 bg-violet-500/10 px-2 py-0.5 rounded">{{ modulo.slug }}</code></dd>
                        </div>
                        <div v-if="modulo.solicitud">
                            <dt class="text-xs text-slate-500 mb-1">Folio solicitud</dt>
                            <dd>
                                <a :href="`/admin/cart/solicitudes/${modulo.solicitud.id}`"
                                   class="text-blue-400 hover:text-blue-300 font-mono text-sm transition-colors">
                                    {{ modulo.solicitud.folio }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 mb-1">Fecha de alta</dt>
                            <dd class="text-slate-300 text-sm">{{ formatDate(modulo.created_at) }}</dd>
                        </div>
                        <div v-if="modulo.categorias_autorizadas?.length" class="col-span-2">
                            <dt class="text-xs text-slate-500 mb-2">Categorías autorizadas</dt>
                            <dd class="flex flex-wrap gap-1">
                                <span v-for="cat in modulo.categorias_autorizadas" :key="cat"
                                      class="px-2 py-0.5 bg-blue-500/20 text-blue-300 border border-blue-500/30 rounded text-xs font-medium">
                                    {{ cat }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Acciones -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700/40 shadow-xl p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Acciones</h3>

                    <!-- Revocar tokens -->
                    <form @submit.prevent="revocar" class="space-y-2">
                        <label class="text-xs text-slate-400 block">Motivo (opcional)</label>
                        <input v-model="motivoRevocar" type="text" placeholder="Ej: compromiso de seguridad"
                               class="w-full bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500/50 focus:ring-1 focus:ring-red-500/30 transition-colors" />
                        <button type="submit"
                                class="w-full px-4 py-2 bg-gradient-to-br from-red-700 to-red-800 hover:from-red-600 hover:to-red-700 text-white text-sm font-medium rounded-lg shadow-lg shadow-red-500/10 transition-all duration-200">
                            Revocar tokens activos
                        </button>
                    </form>

                    <div class="border-t border-slate-700/50 pt-4">
                        <form @submit.prevent="forzarRefresh">
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-gradient-to-br from-amber-600 to-amber-700 hover:from-amber-500 hover:to-amber-600 text-white text-sm font-medium rounded-lg shadow-lg shadow-amber-500/10 transition-all duration-200">
                                Forzar refresh (emitir par nuevo)
                            </button>
                        </form>
                        <p class="text-xs text-slate-500 mt-2 text-center">
                            Revoca el par actual y emite tokens nuevos.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Historial de tokens -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-xl shadow-violet-500/5 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700/50 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-white">Historial de tokens</h3>
                    <span class="text-xs text-slate-500">JTI parcial — el token completo nunca se muestra</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">JTI</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Motivo revocación</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Emitido</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Entregado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Expira</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="token in modulo.tokens" :key="token.id"
                                class="border-t border-slate-700/40 hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-4 py-3 font-mono text-xs text-slate-400">
                                    {{ token.jti.substring(0, 8) }}…
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="token.tipo === 'access'
                                        ? 'bg-blue-500/20 text-blue-400 border-blue-500/30'
                                        : 'bg-purple-500/20 text-purple-400 border-purple-500/30'"
                                          class="inline-flex px-2 py-0.5 rounded text-xs font-medium border">
                                        {{ token.tipo }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="token.estado === 'activo'
                                        ? 'bg-green-500/20 text-green-400 border-green-500/30'
                                        : 'bg-slate-700/50 text-slate-400 border-slate-600/40'"
                                          class="inline-flex px-2 py-0.5 rounded text-xs font-medium border">
                                        {{ token.estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ token.motivo_revocacion ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ formatDateTime(token.created_at) }}</td>
                                <td class="px-4 py-3 text-xs">
                                    <span :class="token.entregado_at ? 'text-green-400' : 'text-slate-500'">
                                        {{ token.entregado_at ? formatDateTime(token.entregado_at) : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ formatDateTime(token.expires_at) }}</td>
                            </tr>
                            <tr v-if="!modulo.tokens?.length">
                                <td colspan="7" class="px-4 py-12 text-center text-slate-500 text-sm">
                                    Sin tokens registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({ modulo: Object });

const motivoRevocar = ref('');

const revocarForm = useForm({});
function revocar() {
    revocarForm.transform(() => ({ motivo: motivoRevocar.value || undefined }))
        .post(`/admin/cart/modulos/${props.modulo.id}/revocar`);
}

const refreshForm = useForm({});
function forzarRefresh() {
    refreshForm.post(`/admin/cart/modulos/${props.modulo.id}/forzar-refresh`);
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTime(val) {
    if (!val) return '—';
    return new Date(val).toLocaleString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}
</script>
