<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Components/AuthLayout.vue'
import CartControl from '@/Modules/Cart/Control/CartControl.vue'

const props = defineProps({
    producto:       Object,
    categoria:      String,
    precio_vigente: String,
})

const page    = usePage()
const userRef = page.props.auth?.user?.matricula
    ?? String(page.props.auth?.user?.id ?? '')

const cartRef            = ref(null)
const pedidoConfirmado   = ref(false)
const checkoutData       = ref(null)

function agregarAlCarrito() {
    cartRef.value?.addItem({
        referencia_externa: 'CAT-' + props.producto.id_catalogo,
        cantidad: 1,
    })
}

function onCheckoutSuccess(data) {
    pedidoConfirmado.value = true
    checkoutData.value     = data ?? null
}

const totalMostrado = () => {
    const t = checkoutData.value?.total ?? checkoutData.value?.carrito?.total
    if (t !== undefined && t !== null) return Number(t).toFixed(2)
    return props.precio_vigente ?? null
}
</script>

<template>
    <AuthLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <!-- ── Panel de confirmación (post-checkout) ───────────────────── -->
            <div v-if="pedidoConfirmado"
                 class="bg-gradient-to-br from-slate-800/80 to-slate-900/80 backdrop-blur-sm
                        border border-emerald-500/20 rounded-2xl shadow-xl shadow-emerald-500/5
                        p-10 flex flex-col items-center gap-6 text-center">

                <!-- Ícono ✅ -->
                <div class="w-20 h-20 rounded-full bg-emerald-500/15 border border-emerald-500/30
                            flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <!-- Título -->
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold text-slate-100">¡Pedido confirmado!</h1>
                    <p class="text-slate-400">Tu compra fue procesada exitosamente</p>
                </div>

                <!-- Total pagado -->
                <div v-if="totalMostrado()"
                     class="bg-slate-800/60 border border-slate-700/50 rounded-xl px-8 py-4">
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total pagado</p>
                    <p class="text-3xl font-bold text-emerald-400">${{ totalMostrado() }}</p>
                </div>

                <!-- Acciones -->
                <div class="flex flex-col sm:flex-row gap-3 w-full max-w-sm">
                    <button
                        id="btn-confirmar-seguir-comprando"
                        @click="$inertia.visit(route('tienda.index'))"
                        class="flex-1 flex items-center justify-center gap-2 px-5 py-2.5
                               rounded-xl text-sm font-semibold transition-all duration-200
                               bg-gradient-to-r from-blue-600 to-blue-500
                               hover:from-blue-500 hover:to-blue-400
                               text-white shadow-lg shadow-blue-500/25"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Seguir comprando
                    </button>
                    <button
                        id="btn-confirmar-dashboard"
                        @click="$inertia.visit(route('dashboard'))"
                        class="flex-1 flex items-center justify-center gap-2 px-5 py-2.5
                               rounded-xl text-sm font-semibold transition-all duration-200
                               bg-slate-800/50 text-slate-300 border border-slate-700/60
                               hover:bg-slate-700/60 hover:text-blue-400 hover:border-blue-500/40"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Ir al Dashboard
                    </button>
                </div>
            </div>

            <!-- ── Vista normal del producto (oculta tras confirmar) ────────── -->
            <template v-else>

                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-slate-400">
                    <a :href="route('tienda.index')"
                       class="hover:text-blue-400 transition-colors duration-200">
                        Tienda
                    </a>
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-slate-300 truncate max-w-xs">{{ producto.nombre }}</span>
                </nav>

                <!-- Tarjeta del producto -->
                <div class="bg-gradient-to-br from-slate-800/80 to-slate-900/80 backdrop-blur-sm
                            border border-blue-500/10 rounded-2xl shadow-xl shadow-blue-500/5 p-6 space-y-4">

                    <!-- Badges -->
                    <div class="flex flex-wrap gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full"
                              :class="producto.tipo === 'servicio'
                                  ? 'bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30'
                                  : 'bg-blue-500/15 text-blue-400 ring-1 ring-blue-500/30'">
                            {{ producto.tipo === 'servicio' ? 'Servicio' : 'Producto' }}
                        </span>
                        <span v-if="categoria"
                              class="text-xs font-medium px-2.5 py-1 rounded-full bg-slate-700/60
                                     text-slate-300 ring-1 ring-slate-600/50">
                            {{ categoria }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-100 leading-tight">
                        {{ producto.nombre }}
                    </h1>

                    <p class="text-slate-400 leading-relaxed">{{ producto.descripcion }}</p>

                    <div class="flex items-center gap-3 pt-1">
                        <span v-if="precio_vigente" class="text-3xl font-bold text-emerald-400">
                            ${{ precio_vigente }}
                        </span>
                        <span v-else class="text-lg text-slate-500 italic">Sin precio disponible</span>
                    </div>

                    <!-- Badge indisponible -->
                    <div v-if="!producto.cart_disponible"
                         class="flex items-center gap-2 text-sm font-medium text-red-400
                                bg-red-500/10 ring-1 ring-red-500/30 rounded-xl px-4 py-2.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-10.75a.75.75 0 011.5 0v4.5a.75.75 0 01-1.5 0v-4.5zm.75 7a.75.75 0 100-1.5.75.75 0 000 1.5z"
                                  clip-rule="evenodd"/>
                        </svg>
                        {{ producto.motivo_no_disponible }}
                    </div>

                    <!-- Botón agregar -->
                    <button
                        id="btn-agregar-carrito"
                        :disabled="!producto.cart_disponible"
                        @click="agregarAlCarrito"
                        class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl
                               font-semibold text-sm transition-all duration-200
                               bg-gradient-to-r from-blue-600 to-blue-500
                               hover:from-blue-500 hover:to-blue-400
                               text-white shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40
                               disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none
                               disabled:from-slate-700 disabled:to-slate-600"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7A1 1 0 007 17h11M7 13L5.4 5M17 17a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        Agregar al carrito
                    </button>
                </div>

                <!-- CartControl -->
                <div class="bg-gradient-to-br from-slate-800/60 to-slate-900/60 backdrop-blur-sm
                            border border-blue-500/10 rounded-2xl shadow-xl shadow-blue-500/5 overflow-hidden">
                    <CartControl
                        ref="cartRef"
                        api-base-url="/catalogo/cart-proxy"
                        :user-ref="userRef"
                        :config="{ requiereSaldo: false, expiraEnMinutos: 120 }"
                        @checkout-success="onCheckoutSuccess"
                    />
                </div>

                <!-- Botón secundario: seguir comprando -->
                <div class="flex justify-center">
                    <a
                        id="btn-seguir-comprando"
                        :href="route('tienda.index')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium
                               text-slate-300 bg-slate-800/50 border border-slate-700/60
                               hover:bg-slate-700/60 hover:text-blue-400 hover:border-blue-500/40
                               transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Seguir comprando
                    </a>
                </div>

            </template>
        </div>
    </AuthLayout>
</template>
