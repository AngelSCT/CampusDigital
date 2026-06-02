<template>
    <AuthLayout>
        <div class="min-h-[70vh] flex items-center justify-center">
            <div class="w-full max-w-lg space-y-6 text-center">

                <!-- Icono de éxito animado -->
                <div class="flex justify-center">
                    <div class="w-24 h-24 rounded-full bg-emerald-500/10 border-2 border-emerald-500/30
                                flex items-center justify-center
                                animate-[pulse_2s_ease-in-out_1]">
                        <svg class="w-12 h-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Título -->
                <div>
                    <h1 class="text-3xl font-bold text-white">¡Pedido confirmado!</h1>
                    <p class="mt-2 text-slate-400 text-sm">
                        Tu compra fue procesada exitosamente.
                    </p>
                </div>

                <!-- Tarjeta resumen -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-emerald-500/20
                            rounded-2xl p-6 text-left space-y-4 shadow-xl shadow-emerald-500/5">

                    <!-- ID y método -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-700/50">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Número de pedido</p>
                            <p class="text-xl font-bold text-white font-mono">#{{ pedido?.id ?? '—' }}</p>
                        </div>
                        <span class="px-3 py-1.5 text-xs font-semibold rounded-full"
                              :class="pedidoMetodo === 'monedero'
                                  ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20'
                                  : 'bg-green-500/10 text-green-400 border border-green-500/20'">
                            {{ pedidoMetodo === 'monedero' ? 'Monedero universitario' : 'Efectivo en caja' }}
                        </span>
                    </div>

                    <!-- Detalles de productos -->
                    <div v-if="pedido?.detalles?.length" class="space-y-2">
                        <p class="text-xs text-slate-500 uppercase tracking-wider mb-3">Productos</p>
                        <div v-for="d in pedido.detalles" :key="d.id"
                             class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded bg-slate-700 flex items-center justify-center
                                             text-xs font-bold text-slate-300 flex-shrink-0">
                                    {{ d.cantidad }}
                                </span>
                                <span class="text-white truncate max-w-[200px]">{{ d.nombre_producto }}</span>
                            </div>
                            <span class="text-slate-300 font-mono flex-shrink-0 ml-4">
                                ${{ Number(d.subtotal).toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="pt-4 border-t border-slate-700/50 flex items-center justify-between">
                        <span class="text-slate-400 font-medium">Total pagado</span>
                        <span class="text-2xl font-black text-emerald-400 font-mono">
                            ${{ Number(pedidoTotal).toFixed(2) }}
                        </span>
                    </div>
                </div>

                <!-- Aviso efectivo: 40 minutos + QR -->
                <div v-if="pedidoMetodo === 'efectivo'"
                     class="bg-gradient-to-br from-amber-900/30 to-amber-800/20 border border-amber-500/30
                            rounded-2xl p-5 text-left space-y-4">

                    <!-- Cabecera -->
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-amber-500/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-amber-300 font-semibold text-sm">Pago en efectivo — tienes 40 minutos</p>
                            <p class="text-amber-400/70 text-xs mt-0.5">
                                Acude a caja y presenta este código antes de que expire el tiempo.
                            </p>
                        </div>
                    </div>

                    <!-- QR placeholder -->
                    <div class="flex flex-col items-center gap-3 py-2">
                        <div class="w-36 h-36 bg-white/5 border-2 border-dashed border-amber-500/30
                                    rounded-xl flex flex-col items-center justify-center gap-2">
                            <svg class="w-10 h-10 text-amber-500/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 3.5c0 .83-.67 1.5-1.5 1.5S15 16.33 15 15.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5zM4.5 4.5h4v4h-4v-4zm11 0h4v4h-4v-4zm-11 11h4v4h-4v-4z"/>
                            </svg>
                            <span class="text-xs text-amber-500/50">QR disponible próximamente</span>
                        </div>
                        <p v-if="pagoToken" class="text-xs font-mono text-amber-300/70 tracking-widest break-all text-center px-2">
                            {{ pagoToken.slice(0, 16).toUpperCase() }}
                        </p>
                    </div>
                </div>

                <!-- Estado del pedido -->
                <div class="flex items-center justify-center gap-3 text-sm text-slate-400">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full animate-pulse"
                             :class="pedidoMetodo === 'efectivo' ? 'bg-amber-400' : 'bg-emerald-400'"></div>
                        <span>Estado:
                            <span class="font-medium"
                                  :class="pedidoMetodo === 'efectivo' ? 'text-amber-400' : 'text-emerald-400'">
                                {{ pedidoMetodo === 'efectivo' ? 'Pendiente de Pago' : 'Pagado' }}
                            </span>
                        </span>
                    </div>
                    <span class="text-slate-600">·</span>
                    <span>{{ new Date().toLocaleDateString('es-MX', { dateStyle: 'long' }) }}</span>
                </div>

                <!-- Flash éxito (cancelación) -->
                <div
                    v-if="flashSuccess"
                    class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ flashSuccess }}
                </div>

                <!-- Flash error -->
                <div
                    v-if="flashError"
                    class="flex items-center gap-3 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ flashError }}
                </div>

                <!-- Botón período de gracia (solo regalos en_escrow, 2 min) -->
                <transition name="fade">
                    <div
                        v-if="esRegaloEscrow && segsRestantes > 0 && !cancelado"
                        class="bg-red-500/5 border border-red-500/20 rounded-2xl p-4 text-center space-y-3"
                    >
                        <p class="text-xs text-slate-400">
                            ¿Enviaste el regalo por error? Tienes
                            <span class="font-mono font-bold text-red-400">{{ segsRestantes }}s</span>
                            para deshacer el envío.
                        </p>
                        <button
                            :disabled="cancelando"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-400 border border-red-500/40 rounded-xl hover:bg-red-500/10 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150"
                            @click="cancelarRegalo"
                        >
                            <svg
                                v-if="cancelando"
                                class="animate-spin h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <span>{{ cancelando ? 'Cancelando…' : `Deshacer envío (${segsRestantes}s)` }}</span>
                        </button>
                    </div>
                </transition>

                <!-- Confirmación visual tras cancelar -->
                <div
                    v-if="cancelado"
                    class="flex items-center justify-center gap-2 text-slate-400 text-sm"
                >
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Envío cancelado — saldo reembolsado
                </div>

                <!-- Acciones -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                    <a href="/carrito"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium
                              text-slate-300 hover:text-white bg-slate-700/50 hover:bg-slate-700
                              border border-slate-600/50 rounded-xl transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Seguir comprando
                    </a>
                    <a href="/dashboard"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold
                              text-white bg-gradient-to-r from-emerald-600 to-emerald-500
                              hover:from-emerald-500 hover:to-emerald-400
                              rounded-xl shadow-lg shadow-emerald-500/20 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Ir al Dashboard
                    </a>
                </div>

            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    pedido:       { type: Object, default: null },
    pedidoTotal:  { type: Number, default: 0 },
    pedidoMetodo: { type: String, default: '' },
    pagoToken:    { type: String, default: null },
});

const page          = usePage()
const cancelado     = ref(false)
const cancelando    = ref(false)
const segsRestantes = ref(0)
let intervalo       = null

const flashSuccess = computed(() => page.props.flash?.success ?? '')
const flashError   = computed(() => page.props.errors?.mensaje ?? '')

// Solo muestra el botón si el pedido es un regalo en_escrow
const esRegaloEscrow = computed(() =>
    !!props.pedido?.destinatario_id && props.pedido?.estado === 'en_escrow'
)

function calcularSegs() {
    if (!props.pedido?.created_at) return 0
    const creado   = new Date(props.pedido.created_at).getTime()
    const elapsed  = Math.floor((Date.now() - creado) / 1000)
    return Math.max(0, 120 - elapsed)
}

onMounted(() => {
    if (!esRegaloEscrow.value) return
    segsRestantes.value = calcularSegs()
    intervalo = setInterval(() => {
        segsRestantes.value = calcularSegs()
        if (segsRestantes.value <= 0) clearInterval(intervalo)
    }, 1000)
})

onUnmounted(() => clearInterval(intervalo))

function cancelarRegalo() {
    if (!props.pedido?.id || cancelando.value) return
    cancelando.value = true
    router.post(
        `/carrito/regalos/${props.pedido.id}/cancelar-remitente`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => { cancelado.value = true },
            onFinish:  () => { cancelando.value = false },
        }
    )
}
</script>
