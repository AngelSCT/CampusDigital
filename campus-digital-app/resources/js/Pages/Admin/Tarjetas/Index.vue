<template>
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        Tarjetas Universitarias
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Gestión de tarjetas RFID/NFC del campus</p>
                </div>
                <div class="flex gap-3">
                    <a :href="route('admin.tarjetas.dashboard')"
                       class="inline-flex items-center px-4 py-2 border border-cyan-500/30 rounded-lg text-sm font-medium text-cyan-400 hover:bg-cyan-500/10 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Dashboard
                    </a>
                    <a :href="route('admin.tarjetas.create')"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:from-cyan-500 hover:to-blue-500 shadow-lg shadow-cyan-500/20 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Registrar Tarjeta
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Total</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.total_tarjetas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-green-500/20 p-4">
                    <p class="text-xs text-green-400 uppercase tracking-wider">Activas</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.activas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 p-4">
                    <p class="text-xs text-red-400 uppercase tracking-wider">Bloqueadas</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.bloqueadas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-yellow-500/20 p-4">
                    <p class="text-xs text-yellow-400 uppercase tracking-wider">Perdidas</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.perdidas }}</p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <input
                        v-model="filtros.search"
                        type="text"
                        placeholder="Buscar por UID, nombre o email..."
                        @input="buscar"
                        class="flex-1 rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 sm:text-sm px-3 py-2 transition-all duration-200"
                    />
                    <select
                        v-model="filtros.estado"
                        @change="buscar"
                        class="rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 sm:text-sm px-3 py-2 transition-all duration-200"
                    >
                        <option value="">Todos los estados</option>
                        <option value="activa">Activa</option>
                        <option value="bloqueada">Bloqueada</option>
                        <option value="perdida">Perdida</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-cyan-500/5 rounded-xl border border-cyan-500/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tarjeta / UID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Lecturas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Registrada</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <tr v-if="tarjetas.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-700/50 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    No se encontraron tarjetas
                                </td>
                            </tr>
                            <tr v-for="tarjeta in tarjetas.data" :key="tarjeta.id"
                                class="hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-600 to-blue-600 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                        </div>
                                        <span class="text-sm font-mono text-white">{{ tarjeta.uid }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="tarjeta.usuario">
                                        <p class="text-sm font-medium text-white">{{ tarjeta.usuario.nombre }} {{ tarjeta.usuario.apellido }}</p>
                                        <p class="text-xs text-slate-400">{{ tarjeta.usuario.email }}</p>
                                    </div>
                                    <span v-else class="text-sm text-slate-500">Sin usuario</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="badgeClass(tarjeta.estado)"
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                                        {{ tarjeta.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-300">{{ tarjeta.lecturas_count }}</td>
                                <td class="px-6 py-4 text-sm text-slate-400">{{ formatDate(tarjeta.created_at) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a :href="route('admin.tarjetas.show', tarjeta.id)"
                                           class="text-cyan-400 hover:text-cyan-300 transition-colors duration-150 text-sm">Ver</a>
                                        <a :href="route('admin.tarjetas.edit', tarjeta.id)"
                                           class="text-blue-400 hover:text-blue-300 transition-colors duration-150 text-sm">Editar</a>
                                        <button @click="abrirBloqueo(tarjeta)"
                                                :class="tarjeta.estado === 'activa' ? 'text-red-400 hover:text-red-300' : 'text-green-400 hover:text-green-300'"
                                                class="transition-colors duration-150 text-sm">
                                            {{ tarjeta.estado === 'activa' ? 'Bloquear' : 'Activar' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="tarjetas.last_page > 1" class="px-6 py-4 border-t border-slate-700 flex justify-between items-center">
                    <p class="text-sm text-slate-400">
                        Mostrando {{ tarjetas.from }} - {{ tarjetas.to }} de {{ tarjetas.total }}
                    </p>
                    <div class="flex gap-2">
                        <a v-if="tarjetas.prev_page_url" :href="tarjetas.prev_page_url"
                           class="px-3 py-1 rounded-lg border border-slate-600 text-sm text-white hover:bg-slate-700/50 transition-all duration-200">
                            Anterior
                        </a>
                        <a v-if="tarjetas.next_page_url" :href="tarjetas.next_page_url"
                           class="px-3 py-1 rounded-lg border border-slate-600 text-sm text-white hover:bg-slate-700/50 transition-all duration-200">
                            Siguiente
                        </a>
                    </div>
                </div>
            </div>

            <div v-if="modalBloqueo.show"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/30 shadow-2xl w-full max-w-md">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-white mb-1">
                            {{ modalBloqueo.tarjeta?.estado === 'activa' ? 'Bloquear Tarjeta' : 'Reactivar Tarjeta' }}
                        </h3>
                        <p class="text-sm text-slate-400 mb-4">UID: <span class="font-mono text-white">{{ modalBloqueo.tarjeta?.uid }}</span></p>

                        <!-- Bloquear -->
                        <div v-if="modalBloqueo.tarjeta?.estado === 'activa'" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Tipo de bloqueo <span class="text-red-400">*</span></label>
                                <select v-model="modalBloqueo.estado"
                                        class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20 sm:text-sm px-3 py-2">
                                    <option value="bloqueada">Bloqueada (temporal)</option>
                                    <option value="perdida">Reportada como perdida</option>
                                    <option value="cancelada">Cancelada definitivamente</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Motivo <span class="text-red-400">*</span></label>
                                <textarea v-model="modalBloqueo.motivo" rows="3"
                                          class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 sm:text-sm px-3 py-2"
                                          placeholder="Explica el motivo del bloqueo..."></textarea>
                            </div>
                        </div>

                        <p v-else class="text-sm text-slate-300 mb-4">¿Confirmas que deseas reactivar esta tarjeta?</p>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-700 flex justify-end gap-3">
                        <button @click="modalBloqueo.show = false"
                                class="px-4 py-2 border border-slate-600 rounded-lg text-sm text-white hover:bg-slate-700/50 transition-all duration-200">
                            Cancelar
                        </button>
                        <button @click="confirmarBloqueo"
                                :disabled="modalBloqueo.procesando"
                                :class="modalBloqueo.tarjeta?.estado === 'activa' ? 'from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 shadow-red-500/20' : 'from-green-600 to-green-700 hover:from-green-500 hover:to-green-600 shadow-green-500/20'"
                                class="px-4 py-2 bg-gradient-to-br border border-transparent rounded-lg text-sm font-medium text-white shadow-lg disabled:opacity-50 transition-all duration-200">
                            {{ modalBloqueo.procesando ? 'Procesando...' : (modalBloqueo.tarjeta?.estado === 'activa' ? 'Bloquear' : 'Reactivar') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    tarjetas: Object,
    filters: Object,
    stats: Object,
});

const filtros = reactive({
    search: props.filters?.search ?? '',
    estado: props.filters?.estado ?? '',
});

let timeout = null;
function buscar() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.tarjetas.index'), filtros, { preserveState: true, replace: true });
    }, 400);
}

const modalBloqueo = reactive({
    show: false,
    tarjeta: null,
    motivo: '',
    estado: 'bloqueada',
    procesando: false,
});

function abrirBloqueo(tarjeta) {
    modalBloqueo.tarjeta   = tarjeta;
    modalBloqueo.motivo    = '';
    modalBloqueo.estado    = 'bloqueada';
    modalBloqueo.procesando = false;
    modalBloqueo.show      = true;
}

function confirmarBloqueo() {
    if (modalBloqueo.tarjeta.estado === 'activa' && !modalBloqueo.motivo.trim()) return;

    modalBloqueo.procesando = true;
    router.post(
        route('admin.tarjetas.toggle-block', modalBloqueo.tarjeta.id),
        { motivo: modalBloqueo.motivo, estado: modalBloqueo.estado },
        {
            onSuccess: () => { modalBloqueo.show = false; },
            onFinish: () => { modalBloqueo.procesando = false; },
        }
    );
}

function badgeClass(estado) {
    const map = {
        activa:    'bg-green-500/20 text-green-400 border border-green-500/30',
        bloqueada: 'bg-red-500/20 text-red-400 border border-red-500/30',
        perdida:   'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30',
        cancelada: 'bg-slate-500/20 text-slate-400 border border-slate-500/30',
    };
    return map[estado] ?? map.cancelada;
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>