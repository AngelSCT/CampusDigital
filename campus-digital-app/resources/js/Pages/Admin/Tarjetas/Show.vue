<template>
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a :href="route('admin.tarjetas.index')"
                       class="inline-flex items-center px-3 py-2 border border-slate-600 rounded-lg text-sm text-white hover:bg-slate-700/50 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-white font-mono">{{ tarjeta.uid }}</h1>
                        <p class="text-sm text-slate-400">Detalle de tarjeta universitaria</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a :href="route('admin.tarjetas.edit', tarjeta.id)"
                       class="inline-flex items-center px-4 py-2 border border-slate-600 rounded-lg text-sm text-white hover:bg-slate-700/50 transition-all duration-200">
                        Editar
                    </a>
                    <button @click="abrirBloqueo"
                            :class="tarjeta.estado === 'activa'
                                ? 'from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 shadow-red-500/20'
                                : 'from-green-600 to-green-700 hover:from-green-500 hover:to-green-600 shadow-green-500/20'"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-br border border-transparent rounded-lg text-sm font-medium text-white shadow-lg transition-all duration-200">
                        {{ tarjeta.estado === 'activa' ? 'Bloquear Tarjeta' : 'Reactivar Tarjeta' }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 shadow-xl shadow-cyan-500/5 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-white">Información de Tarjeta</h2>
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-600 to-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-slate-400">UID</p>
                            <p class="text-sm font-mono text-white">{{ tarjeta.uid }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Estado</p>
                            <span :class="badgeClass(tarjeta.estado)"
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize mt-1">
                                {{ tarjeta.estado }}
                            </span>
                        </div>
                        <div v-if="tarjeta.motivo_bloqueo">
                            <p class="text-xs text-slate-400">Motivo de bloqueo</p>
                            <p class="text-sm text-red-400">{{ tarjeta.motivo_bloqueo }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Registrada</p>
                            <p class="text-sm text-white">{{ formatDate(tarjeta.created_at) }}</p>
                        </div>
                        <div v-if="tarjeta.registrado_por">
                            <p class="text-xs text-slate-400">Registrada por</p>
                            <p class="text-sm text-white">{{ tarjeta.registrado_por.nombre }} {{ tarjeta.registrado_por.apellido }}</p>
                        </div>
                        <div v-if="tarjeta.bloqueado_at">
                            <p class="text-xs text-slate-400">Bloqueada</p>
                            <p class="text-sm text-white">{{ formatDate(tarjeta.bloqueado_at) }}</p>
                        </div>
                        <div v-if="tarjeta.bloqueado_por">
                            <p class="text-xs text-slate-400">Bloqueada por</p>
                            <p class="text-sm text-white">{{ tarjeta.bloqueado_por.nombre }} {{ tarjeta.bloqueado_por.apellido }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/5 p-6 space-y-4">
                    <h2 class="text-base font-semibold text-white">Usuario Asociado</h2>
                    <div v-if="tarjeta.usuario" class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ tarjeta.usuario.nombre[0] }}{{ tarjeta.usuario.apellido[0] }}
                            </div>
                            <div>
                                <p class="font-semibold text-white">{{ tarjeta.usuario.nombre }} {{ tarjeta.usuario.apellido }}</p>
                                <p class="text-sm text-slate-400">{{ tarjeta.usuario.email }}</p>
                            </div>
                        </div>
                        <div v-if="tarjeta.usuario.telefono">
                            <p class="text-xs text-slate-400">Teléfono</p>
                            <p class="text-sm text-white">{{ tarjeta.usuario.telefono }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-500">Sin usuario asociado</p>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-6 space-y-4">
                    <h2 class="text-base font-semibold text-white">Estadísticas de Uso</h2>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-700/30 rounded-lg p-3">
                            <p class="text-xs text-slate-400">Total lecturas</p>
                            <p class="text-2xl font-bold text-white">{{ statsLecturas.total }}</p>
                        </div>
                        <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-3">
                            <p class="text-xs text-green-400">Exitosas</p>
                            <p class="text-2xl font-bold text-white">{{ statsLecturas.exitosas }}</p>
                        </div>
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-3">
                            <p class="text-xs text-red-400">Fallidas</p>
                            <p class="text-2xl font-bold text-white">{{ statsLecturas.fallidas }}</p>
                        </div>
                        <div class="bg-cyan-500/10 border border-cyan-500/20 rounded-lg p-3">
                            <p class="text-xs text-cyan-400">Hoy</p>
                            <p class="text-2xl font-bold text-white">{{ statsLecturas.hoy }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-white">Historial de Lecturas</h2>
                    <span class="text-xs text-slate-400">{{ lecturas.total }} registros</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Resultado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Detalle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Operador</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <tr v-if="lecturas.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500 text-sm">Sin lecturas registradas</td>
                            </tr>
                            <tr v-for="l in lecturas.data" :key="l.id" class="hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-6 py-3 text-sm text-slate-300 whitespace-nowrap">{{ formatDateTime(l.created_at) }}</td>
                                <td class="px-6 py-3">
                                    <span class="text-sm text-white capitalize">{{ l.modulo.replace('_', ' ') }}</span>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-300 capitalize">{{ l.tipo_lectura.replace('_', ' ') }}</td>
                                <td class="px-6 py-3">
                                    <span :class="l.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                                        {{ l.exito ? 'Exitoso' : 'Fallido' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-400 max-w-xs truncate">{{ l.detalle }}</td>
                                <td class="px-6 py-3 text-sm text-slate-400">
                                    {{ l.operador ? `${l.operador.nombre} ${l.operador.apellido}` : '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="lecturas.last_page > 1" class="px-6 py-4 border-t border-slate-700 flex justify-between items-center">
                    <p class="text-sm text-slate-400">{{ lecturas.from }} - {{ lecturas.to }} de {{ lecturas.total }}</p>
                    <div class="flex gap-2">
                        <a v-if="lecturas.prev_page_url" :href="lecturas.prev_page_url"
                           class="px-3 py-1 rounded-lg border border-slate-600 text-sm text-white hover:bg-slate-700/50 transition-all duration-200">Anterior</a>
                        <a v-if="lecturas.next_page_url" :href="lecturas.next_page_url"
                           class="px-3 py-1 rounded-lg border border-slate-600 text-sm text-white hover:bg-slate-700/50 transition-all duration-200">Siguiente</a>
                    </div>
                </div>
            </div>

            <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/30 shadow-2xl w-full max-w-md">
                    <div class="p-6 space-y-4">
                        <h3 class="text-lg font-bold text-white">
                            {{ tarjeta.estado === 'activa' ? 'Bloquear Tarjeta' : 'Reactivar Tarjeta' }}
                        </h3>
                        <div v-if="tarjeta.estado === 'activa'" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Tipo de bloqueo</label>
                                <select v-model="modal.estado"
                                        class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white sm:text-sm px-3 py-2">
                                    <option value="bloqueada">Bloqueada (temporal)</option>
                                    <option value="perdida">Reportada como perdida</option>
                                    <option value="cancelada">Cancelada definitivamente</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Motivo <span class="text-red-400">*</span></label>
                                <textarea v-model="modal.motivo" rows="3"
                                          class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 sm:text-sm px-3 py-2"
                                          placeholder="Motivo del bloqueo..."></textarea>
                            </div>
                        </div>
                        <p v-else class="text-slate-300 text-sm">¿Confirmas que deseas reactivar esta tarjeta?</p>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-700 flex justify-end gap-3">
                        <button @click="modal.show = false"
                                class="px-4 py-2 border border-slate-600 rounded-lg text-sm text-white hover:bg-slate-700/50">Cancelar</button>
                        <button @click="confirmarBloqueo" :disabled="modal.procesando"
                                :class="tarjeta.estado === 'activa' ? 'from-red-600 to-red-700' : 'from-green-600 to-green-700'"
                                class="px-4 py-2 bg-gradient-to-br border border-transparent rounded-lg text-sm font-medium text-white disabled:opacity-50">
                            {{ modal.procesando ? 'Procesando...' : 'Confirmar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    tarjeta: Object,
    lecturas: Object,
    statsLecturas: Object,
});

const modal = reactive({ show: false, motivo: '', estado: 'bloqueada', procesando: false });

function abrirBloqueo() {
    modal.motivo = ''; modal.estado = 'bloqueada'; modal.procesando = false; modal.show = true;
}

function confirmarBloqueo() {
    if (props.tarjeta.estado === 'activa' && !modal.motivo.trim()) return;
    modal.procesando = true;
    router.post(route('admin.tarjetas.toggle-block', props.tarjeta.id),
        { motivo: modal.motivo, estado: modal.estado },
        { onSuccess: () => { modal.show = false; }, onFinish: () => { modal.procesando = false; } }
    );
}

function badgeClass(estado) {
    const map = { activa: 'bg-green-500/20 text-green-400', bloqueada: 'bg-red-500/20 text-red-400', perdida: 'bg-yellow-500/20 text-yellow-400', cancelada: 'bg-slate-500/20 text-slate-400' };
    return map[estado] ?? map.cancelada;
}

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}
function formatDateTime(d) {
    if (!d) return '-';
    return new Date(d).toLocaleString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}
</script>