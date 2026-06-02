<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import CartControl from '@/Modules/Cart/Control/CartControl.vue'

const props = defineProps({
    producto:       Object,
    categoria:      String,
    precio_vigente: String,
})

const page    = usePage()
const userRef = page.props.auth?.user?.matricula
    ?? String(page.props.auth?.user?.id ?? '')

const cartRef = ref(null)

function agregarAlCarrito() {
    cartRef.value?.addItem({
        referencia_externa: 'CAT-' + props.producto.id_catalogo,
        cantidad: 1,
        // SOLO referencia_externa + cantidad. Nunca precio ni nombre.
    })
}
</script>

<template>
    <div class="max-w-2xl mx-auto p-6">

        <!-- Info del producto -->
        <div class="mb-6">
            <span class="text-sm text-gray-500">{{ categoria }}</span>
            <h1 class="text-2xl font-bold mt-1">{{ producto.nombre }}</h1>
            <p class="text-gray-600 mt-2">{{ producto.descripcion }}</p>

            <div class="mt-4 text-xl font-semibold">
                <span v-if="precio_vigente">${{ precio_vigente }}</span>
                <span v-else class="text-red-500">Sin precio disponible</span>
            </div>

            <div v-if="!producto.cart_disponible" class="mt-2 text-sm text-red-500">
                {{ producto.motivo_no_disponible }}
            </div>
        </div>

        <!-- Botón agregar -->
        <button
            :disabled="!producto.cart_disponible"
            @click="agregarAlCarrito"
            class="mb-6 px-6 py-2 bg-blue-600 text-white rounded disabled:opacity-50 disabled:cursor-not-allowed"
        >
            Agregar al carrito
        </button>

        <!-- Carrito embebido -->
        <CartControl
            ref="cartRef"
            api-base-url="/catalogo/cart-proxy"
            :user-ref="userRef"
            :config="{ requiereSaldo: false, expiraEnMinutos: 120 }"
            @checkout-success="() => $inertia.visit('/catalogo')"
        />
    </div>
</template>
