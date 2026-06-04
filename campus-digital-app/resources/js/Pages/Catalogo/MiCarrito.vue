<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import AuthLayout from '@/Components/AuthLayout.vue'

const props = defineProps({
    carrito:      { type: Object, default: null },
    carrito_uuid: { type: String, default: null },
})

// ── Estado checkout ──────────────────────────────────────────────────────────
const checkingOut     = ref(false)
const pedidoConfirmado = ref(false)
const comprobante      = ref(null)
const loadingComp      = ref(false)
const errorMsg         = ref(null)

// ── Ítems del carrito ────────────────────────────────────────────────────────
const items = computed(() => props.carrito?.items ?? [])
const hayItems = computed(() => items.value.length > 0)

const total = computed(() => {
    if (comprobante.value?.total !== undefined) return Number(comprobante.value.total)
    return items.value.reduce((acc, it) => {
        return acc + (Number(it.precio_unitario ?? 0) * Number(it.cantidad ?? 1))
    }, 0)
})

function formatMoney(val) {
    return Number(val ?? 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })
}

function formatFecha(iso) {
    if (!iso) return '—'
    return new Date(iso).toLocaleDateString('es-MX', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

// ── Checkout ─────────────────────────────────────────────────────────────────
async function confirmarCompra() {
    if (!props.carrito_uuid || checkingOut.value) return
    checkingOut.value = true
    errorMsg.value    = null

    try {
        const res = await axios.post(
            `/catalogo/cart-proxy/carritos/${props.carrito_uuid}/checkout`
        )
        if (res.status === 200) {
            pedidoConfirmado.value = true
            // Intentar obtener comprobante
            await obtenerComprobante(props.carrito_uuid)
        } else {
            errorMsg.value = res.data?.mensaje ?? 'No se pudo confirmar la compra.'
        }
    } catch (e) {
        const msg = e.response?.data?.mensaje ?? e.response?.data?.error
        errorMsg.value = msg ?? 'Error al procesar el pago.'
    } finally {
        checkingOut.value = false
    }
}

async function obtenerComprobante(uuid) {
    loadingComp.value = true
    try {
        const res = await axios.get(`/catalogo/cart-proxy/comprobante/${uuid}`)
        if (res.status === 200) comprobante.value = res.data
    } catch { /* fallback silencioso */ } finally {
        loadingComp.value = false
    }
}
</script>

<template>
    <AuthLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

            <!-- ── Confirmación post-checkout ─────────────────────────────── -->
            <div v-if="pedidoConfirmado"
                 class="relative overflow-hidden rounded-3xl border border-emerald-500/20
                        bg-gradient-to-br from-slate-900 via-slate-800/90 to-slate-900
                        shadow-2xl shadow-emerald-500/10 p-8 sm:p-12 text-center">

                <!-- Ícono check -->
                <div class="mx-auto mb-6 w-20 h-20 rounded-full bg-emerald-500/15
                            ring-2 ring-emerald-500/40 flex items-center justify-center">
                    <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="text-3xl font-extrabold text-white mb-1">¡Pedido confirmado!</h1>
                <p class="text-slate-400 mb-8">Tu compra fue procesada exitosamente.</p>

                <!-- Comprobante -->
                <div v-if="loadingComp"
                     class="flex items-center justify-center gap-3 text-slate-400 py-6">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Obteniendo comprobante…
                </div>

                <div v-else-if="comprobante"
                     class="text-left rounded-2xl border border-slate-700/50
                            bg-slate-800/60 backdrop-blur-sm p-6 mb-8 space-y-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Folio</span>
                        <span class="font-mono text-slate-200">{{ comprobante.folio ?? comprobante.uuid ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Fecha</span>
                        <span class="text-slate-200">{{ formatFecha(comprobante.created_at ?? comprobante.fecha) }}</span>
                    </div>

                    <div v-if="comprobante.items?.length" class="border-t border-slate-700/50 pt-4 space-y-2">
                        <div v-for="(it, i) in comprobante.items" :key="i"
                             class="flex justify-between text-sm">
                            <span class="text-slate-300">
                                {{ it.nombre ?? it.referencia_externa }}
                                <span class="text-slate-500">× {{ it.cantidad }}</span>
                            </span>
                            <span class="text-slate-200">
                                {{ formatMoney(Number(it.precio_unitario) * Number(it.cantidad)) }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-slate-700/50 pt-3 flex justify-between font-bold">
                        <span class="text-slate-300">Total pagado</span>
                        <span class="text-xl text-emerald-400">{{ formatMoney(comprobante.total) }}</span>
                    </div>
                </div>

                <div v-else class="mb-8 text-slate-400 text-sm">
                    Total pagado:
                    <span class="text-xl font-bold text-emerald-400 ml-2">{{ formatMoney(total) }}</span>
                </div>

                <!-- Acciones post-checkout -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <Link :href="route('tienda.index')"
                          class="inline-flex items-center justify-center gap-2 px-6 py-2.5
                                 rounded-xl text-sm font-semibold bg-gradient-to-r
                                 from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400
                                 text-white shadow-lg shadow-blue-500/20 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Seguir comprando
                    </Link>
                    <Link :href="route('dashboard')"
                          class="inline-flex items-center justify-center gap-2 px-6 py-2.5
                                 rounded-xl text-sm font-semibold border border-slate-600
                                 text-slate-300 hover:border-slate-500 hover:text-white
                                 transition-all duration-200">
                        Ir al Dashboard
                    </Link>
                </div>
            </div>

            <!-- ── Vista normal del carrito ──────────────────────────────── -->
            <template v-else>

                <!-- Encabezado -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-100">Mi Carrito</h1>
                        <p class="mt-1 text-sm text-slate-400">
                            Revisa tus artículos y confirma tu compra
                        </p>
                    </div>
                    <Link :href="route('tienda.index')"
                          class="inline-flex items-center gap-2 text-sm text-blue-400
                                 hover:text-blue-300 font-medium transition-colors">
                        ← Seguir comprando
                    </Link>
                </div>

                <!-- Carrito vacío -->
                <div v-if="!hayItems"
                     class="flex flex-col items-center justify-center py-24 gap-6 text-center
                            rounded-2xl border border-slate-700/40 bg-slate-800/30">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800/60 border border-slate-700/40
                                flex items-center justify-center">
                        <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7A1 1 0 007 17h11M7 13L5.4 5
                                     M17 17a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-slate-300">Tu carrito está vacío</p>
                        <p class="text-sm text-slate-500 mt-1">Agrega productos desde la tienda</p>
                    </div>
                    <Link :href="route('tienda.index')"
                          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                                 text-sm font-semibold bg-blue-600/20 text-blue-400
                                 ring-1 ring-blue-500/40 hover:bg-blue-600/30 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Ir a la Tienda
                    </Link>
                </div>

                <!-- Tabla de ítems -->
                <div v-else class="rounded-2xl border border-slate-700/40
                                   bg-gradient-to-br from-slate-800/70 to-slate-900/70
                                   backdrop-blur-sm overflow-hidden">

                    <!-- Cabecera tabla -->
                    <div class="hidden sm:grid grid-cols-12 gap-4 px-6 py-3
                                border-b border-slate-700/50 text-xs font-semibold
                                uppercase tracking-wider text-slate-500">
                        <span class="col-span-6">Producto</span>
                        <span class="col-span-2 text-center">Cantidad</span>
                        <span class="col-span-2 text-right">Precio unit.</span>
                        <span class="col-span-2 text-right">Subtotal</span>
                    </div>

                    <!-- Filas -->
                    <div v-for="(item, i) in items" :key="i"
                         class="grid grid-cols-12 gap-4 items-center px-6 py-4
                                border-b border-slate-700/30 last:border-0
                                hover:bg-slate-700/20 transition-colors duration-150">

                        <!-- Nombre -->
                        <div class="col-span-12 sm:col-span-6">
                            <p class="font-semibold text-slate-100 leading-snug">
                                {{ item.nombre ?? item.referencia_externa }}
                            </p>
                            <p v-if="item.nombre && item.referencia_externa !== item.nombre"
                               class="text-xs text-slate-500 mt-0.5 font-mono">
                                {{ item.referencia_externa }}
                            </p>
                        </div>

                        <!-- Cantidad -->
                        <div class="col-span-4 sm:col-span-2 text-center">
                            <span class="text-slate-300 font-medium">{{ item.cantidad }}</span>
                        </div>

                        <!-- Precio unit -->
                        <div class="col-span-4 sm:col-span-2 text-right">
                            <span class="text-slate-400 text-sm">
                                {{ formatMoney(item.precio_unitario) }}
                            </span>
                        </div>

                        <!-- Subtotal -->
                        <div class="col-span-4 sm:col-span-2 text-right">
                            <span class="font-semibold text-emerald-400">
                                {{ formatMoney(Number(item.precio_unitario) * Number(item.cantidad)) }}
                            </span>
                        </div>
                    </div>

                    <!-- Pie de tabla: total + botón -->
                    <div class="px-6 py-5 border-t border-slate-700/50
                                bg-slate-800/50 flex flex-col sm:flex-row
                                items-start sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-sm text-slate-400">Total estimado</span>
                            <p class="text-2xl font-extrabold text-emerald-400 mt-0.5">
                                {{ formatMoney(total) }}
                            </p>
                        </div>

                        <div class="flex flex-col items-end gap-2 w-full sm:w-auto">
                            <!-- Error -->
                            <p v-if="errorMsg"
                               class="text-sm text-red-400 bg-red-500/10 ring-1 ring-red-500/25
                                      rounded-lg px-4 py-2 text-center w-full sm:w-auto">
                                {{ errorMsg }}
                            </p>

                            <button
                                id="btn-confirmar-compra"
                                @click="confirmarCompra"
                                :disabled="checkingOut"
                                class="inline-flex items-center justify-center gap-2 px-8 py-3
                                       rounded-xl text-sm font-semibold text-white
                                       bg-gradient-to-r from-emerald-600 to-emerald-500
                                       hover:from-emerald-500 hover:to-emerald-400
                                       shadow-lg shadow-emerald-500/20
                                       disabled:opacity-60 disabled:cursor-not-allowed
                                       transition-all duration-200 w-full sm:w-auto">
                                <svg v-if="checkingOut" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ checkingOut ? 'Procesando…' : 'Confirmar compra' }}
                            </button>
                        </div>
                    </div>
                </div>

            </template>
        </div>
    </AuthLayout>
</template>
