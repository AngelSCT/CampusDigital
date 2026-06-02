<template>
    <Head :title="`Solicitud ${solicitud.folio}`" />
    <AuthLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="/admin/cart/solicitudes"
                   class="text-slate-400 hover:text-slate-300 transition-colors text-sm">
                    ← Solicitudes
                </a>
                <span class="text-slate-600">/</span>
                <span class="font-mono text-slate-300 text-sm">{{ solicitud.folio }}</span>
                <span :class="badgeClass(solicitud.estado)"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border">
                    {{ solicitud.estado }}
                </span>
            </div>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error"
                 class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $page.props.flash.error }}
            </div>

            <!-- Datos de la solicitud -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-violet-500/20 shadow-xl shadow-violet-500/5 p-6">
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Datos de la solicitud</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Nombre del módulo</p>
                        <p class="font-medium text-white">{{ solicitud.nombre_modulo }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Tipo de módulo</p>
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-violet-500/20 text-violet-300 border border-violet-500/30">
                            {{ solicitud.tipo_modulo }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Contacto</p>
                        <p class="text-slate-300">{{ solicitud.contacto_nombre }}</p>
                        <p class="text-slate-500 text-xs">{{ solicitud.contacto_email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-2">Categorías solicitadas</p>
                        <div class="flex flex-wrap gap-1">
                            <span v-for="cat in solicitud.categorias_solicitadas" :key="cat"
                                  class="px-2 py-0.5 bg-blue-500/20 text-blue-300 border border-blue-500/30 rounded text-xs font-medium">
                                {{ cat }}
                            </span>
                        </div>
                    </div>
                    <div v-if="solicitud.descripcion" class="col-span-2">
                        <p class="text-xs text-slate-500 mb-1">Descripción</p>
                        <p class="text-sm text-slate-300">{{ solicitud.descripcion }}</p>
                    </div>
                    <div v-if="solicitud.motivo_rechazo" class="col-span-2 bg-red-500/10 border border-red-500/20 p-4 rounded-lg">
                        <p class="text-xs font-semibold text-red-400 mb-1">Motivo de rechazo</p>
                        <p class="text-sm text-red-300">{{ solicitud.motivo_rechazo }}</p>
                    </div>
                </div>
            </div>

            <!-- Acciones para solicitudes pendientes -->
            <div v-if="solicitud.estado === 'pendiente'"
                 class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700/40 shadow-xl p-6">
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Acciones</h3>
                <div class="flex flex-wrap gap-3">
                    <button @click="modalAprobar = true"
                            class="inline-flex items-center px-5 py-2.5 bg-gradient-to-br from-green-600 to-green-700 hover:from-green-500 hover:to-green-600 text-white font-medium text-sm rounded-lg shadow-lg shadow-green-500/20 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Aprobar solicitud
                    </button>
                    <button @click="modalRechazar = true"
                            class="inline-flex items-center px-5 py-2.5 bg-gradient-to-br from-red-700 to-red-800 hover:from-red-600 hover:to-red-700 text-white font-medium text-sm rounded-lg shadow-lg shadow-red-500/20 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Rechazar solicitud
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Aprobar -->
        <div v-if="modalAprobar" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 px-4">
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-green-500/30 rounded-2xl p-6 w-full max-w-md shadow-2xl shadow-green-500/10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-white">¿Aprobar solicitud?</h2>
                </div>
                <p class="text-sm text-slate-300 mb-5">
                    Se creará el módulo <strong class="text-white">{{ solicitud.nombre_modulo }}</strong> y se emitirá un par de
                    tokens JWT. Los tokens se mostrarán <strong class="text-yellow-400">una sola vez</strong>.
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="modalAprobar = false"
                            class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200 transition-colors">
                        Cancelar
                    </button>
                    <form :action="`/admin/cart/solicitudes/${solicitud.id}/aprobar`" method="POST">
                        <input type="hidden" name="_token" :value="csrf" />
                        <button type="submit"
                                class="px-5 py-2 bg-gradient-to-br from-green-600 to-green-700 hover:from-green-500 hover:to-green-600 text-white text-sm font-medium rounded-lg shadow-lg shadow-green-500/20 transition-all duration-200">
                            Confirmar aprobación
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Rechazar -->
        <div v-if="modalRechazar" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 px-4">
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-red-500/30 rounded-2xl p-6 w-full max-w-md shadow-2xl shadow-red-500/10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-white">Rechazar solicitud</h2>
                </div>
                <form :action="`/admin/cart/solicitudes/${solicitud.id}/rechazar`" method="POST">
                    <input type="hidden" name="_token" :value="csrf" />
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Motivo de rechazo <span class="text-red-400">*</span>
                    </label>
                    <textarea v-model="motivoRechazo" name="motivo_rechazo" rows="3" required minlength="10"
                              class="w-full bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-red-500/50 focus:ring-1 focus:ring-red-500/30 transition-colors"
                              placeholder="Describa el motivo (mínimo 10 caracteres)..." />
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="modalRechazar = false"
                                class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="motivoRechazo.length < 10"
                                class="px-5 py-2 bg-gradient-to-br from-red-700 to-red-800 hover:from-red-600 hover:to-red-700 text-white text-sm font-medium rounded-lg shadow-lg shadow-red-500/20 transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed">
                            Confirmar rechazo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({ solicitud: Object });

const modalAprobar  = ref(false);
const modalRechazar = ref(false);
const motivoRechazo = ref('');

const csrf = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '';

function badgeClass(estado) {
    const map = {
        pendiente: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
        aprobada:  'bg-green-500/20  text-green-400  border-green-500/30',
        rechazada: 'bg-red-500/20    text-red-400    border-red-500/30',
    };
    return map[estado] ?? 'bg-slate-700/50 text-slate-400 border-slate-600/40';
}
</script>
