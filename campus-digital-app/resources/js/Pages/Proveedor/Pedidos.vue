<template>
    <AuthLayout>
        <div class="space-y-6">

            <!-- Encabezado -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-orange-400 to-amber-400 bg-clip-text text-transparent">
                        Panel Proveedor
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Pedidos activos de
                        <span class="text-orange-400 font-semibold">{{ tienda }}</span>
                    </p>
                </div>
                <span class="px-3 py-1.5 text-sm font-medium bg-orange-500/10 border border-orange-500/30 text-orange-400 rounded-full">
                    {{ pedidos.length }} pedido{{ pedidos.length !== 1 ? 's' : '' }}
                </span>
            </div>

            <!-- Alerta global -->
            <transition name="fade">
                <div v-if="mensajeGlobal"
                     class="flex items-center gap-3 p-4 rounded-xl text-sm font-medium"
                     :class="mensajeGlobal.tipo === 'ok'
                         ? 'bg-green-500/10 border border-green-500/20 text-green-400'
                         : 'bg-red-500/10 border border-red-500/20 text-red-400'">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="mensajeGlobal.tipo === 'ok'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ mensajeGlobal.texto }}
                    <button @click="mensajeGlobal = null" class="ml-auto opacity-60 hover:opacity-100">✕</button>
                </div>
            </transition>

            <!-- Sin pedidos -->
            <div v-if="pedidos.length === 0"
                 class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 rounded-full bg-slate-800/80 border border-slate-700/50 flex items-center justify-center mb-5">
                    <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-1">Sin pedidos activos</h3>
                <p class="text-slate-400 text-sm">Los nuevos pedidos de tu tienda aparecerán aquí.</p>
            </div>

            <!-- Lista de pedidos -->
            <div v-else class="space-y-4">
                <div v-for="pedido in pedidos" :key="pedido.id"
                     class="bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700/40
                            hover:border-orange-500/20 rounded-2xl p-5 transition-all duration-200">

                    <!-- Cabecera del pedido -->
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-white font-bold text-lg font-mono">#{{ pedido.id }}</span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full"
                                      :class="estadoClass(pedido.estado)">
                                    {{ pedido.estado.replace('_', ' ') }}
                                </span>
                                <span v-if="pedido.destinatario_id"
                                      class="px-2 py-0.5 text-xs font-medium bg-pink-500/10 border border-pink-500/30 text-pink-400 rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"/>
                                    </svg>
                                    Escrow
                                </span>
                            </div>
                            <p class="text-slate-400 text-xs mt-1">
                                {{ formatFecha(pedido.created_at) }} · {{ pedido.metodo_pago }}
                            </p>
                        </div>
                        <span class="text-2xl font-black text-orange-400 font-mono flex-shrink-0">
                            ${{ Number(pedido.total).toFixed(2) }}
                        </span>
                    </div>

                    <!-- Productos del pedido (de esta tienda) -->
                    <div class="space-y-2 mb-4">
                        <div v-for="detalle in pedido.detalles" :key="detalle.id"
                             class="flex items-center justify-between text-sm bg-slate-700/30 rounded-lg px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded bg-orange-500/20 flex items-center justify-center
                                             text-xs font-bold text-orange-400 flex-shrink-0">
                                    {{ detalle.cantidad }}
                                </span>
                                <span class="text-slate-200">{{ detalle.nombre_producto }}</span>
                            </div>
                            <span class="text-slate-400 font-mono text-xs">
                                ${{ Number(detalle.subtotal).toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Panel de cancelación -->
                    <div class="border-t border-slate-700/50 pt-4">

                        <!-- Checkbox merma -->
                        <div class="flex flex-wrap gap-3 mb-3">
                            <label v-for="detalle in pedido.detalles" :key="'merma-' + detalle.id"
                                   class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox"
                                       :value="detalle.producto_id"
                                       v-model="mermaSeleccionada[pedido.id]"
                                       class="w-4 h-4 rounded accent-amber-500 cursor-pointer" />
                                <span class="text-xs text-slate-400 group-hover:text-amber-400 transition-colors">
                                    ¿Dañado? <span class="font-medium">{{ detalle.nombre_producto }}</span>
                                    <span class="text-amber-400/70 ml-1">(registrar merma)</span>
                                </span>
                            </label>
                        </div>

                        <!-- Botón cancelar -->
                        <button @click="cancelarPedido(pedido)"
                                :disabled="!!cancelando[pedido.id]"
                                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold
                                       text-red-400 bg-red-500/10 border border-red-500/30
                                       hover:bg-red-500/20 hover:border-red-500/50
                                       rounded-xl transition-all duration-200
                                       disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="cancelando[pedido.id]" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ cancelando[pedido.id] ? 'Cancelando...' : 'Cancelar por falta de stock' }}
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    pedidos: { type: Array,  default: () => [] },
    tienda:  { type: String, default: '' },
});

const pedidos         = ref([...props.pedidos]);
const cancelando      = reactive({});
const mermaSeleccionada = reactive({});
const mensajeGlobal   = ref(null);

function estadoClass(estado) {
    const map = {
        pagado:          'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400',
        esperando_pago:  'bg-amber-500/10 border border-amber-500/30 text-amber-400',
        en_escrow:       'bg-purple-500/10 border border-purple-500/30 text-purple-400',
        cancelado:       'bg-red-500/10 border border-red-500/30 text-red-400',
    };
    return map[estado] ?? 'bg-slate-600/20 border border-slate-600/30 text-slate-400';
}

function formatFecha(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('es-MX', { dateStyle: 'medium' });
}

function csrfToken() {
    return document.cookie.split('; ')
        .find(r => r.startsWith('XSRF-TOKEN='))
        ?.split('=')[1]
        ? decodeURIComponent(document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN=')).split('=')[1])
        : document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function cancelarPedido(pedido) {
    if (cancelando[pedido.id]) return;
    cancelando[pedido.id] = true;
    mensajeGlobal.value   = null;

    try {
        const res = await fetch(`/proveedor/pedidos/${pedido.id}/cancelar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-XSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                productos_danados: mermaSeleccionada[pedido.id] ?? [],
            }),
        });

        const data = await res.json();

        if (!res.ok) {
            mensajeGlobal.value = { tipo: 'error', texto: data.mensaje ?? `Error ${res.status}` };
            return;
        }

        // Quitar el pedido de la lista local
        pedidos.value = pedidos.value.filter(p => p.id !== pedido.id);
        mensajeGlobal.value = { tipo: 'ok', texto: data.mensaje };
    } catch {
        mensajeGlobal.value = { tipo: 'error', texto: 'Error de red al cancelar el pedido.' };
    } finally {
        cancelando[pedido.id] = false;
    }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>
