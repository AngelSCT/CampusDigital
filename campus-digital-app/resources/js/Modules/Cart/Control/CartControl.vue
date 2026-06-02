<template>
    <div class="cart-control">

        <!-- Loading skeleton -->
        <div v-if="loading" class="space-y-2 animate-pulse p-4">
            <div class="h-4 w-2/3 rounded bg-gray-200" />
            <div class="h-4 w-1/2 rounded bg-gray-200" />
            <div class="h-8 w-full rounded bg-gray-200" />
        </div>

        <!-- Confirmed state -->
        <div v-else-if="confirmed" class="p-4 space-y-2">
            <div class="rounded-md bg-green-50 border border-green-200 p-3 text-sm text-green-700 font-medium">
                ¡Confirmado correctamente!
            </div>
            <div v-if="pendingCharge"
                 class="rounded-md bg-yellow-50 border border-yellow-200 p-3 text-sm text-yellow-700">
                Cargo pendiente de procesamiento. Recibirás una notificación cuando se aplique.
            </div>
        </div>

        <!-- Active cart -->
        <div v-else class="cart-control__body p-4 space-y-3">

            <!-- Error banner (overrideable via slot) -->
            <template v-if="error">
                <slot name="error-banner" :error="error">
                    <CartErrorBanner :error="error" :retryable="true" @retry="cartStore.init()" />
                </slot>
            </template>

            <!-- Empty state (overrideable via slot) -->
            <template v-if="!error && items.length === 0">
                <slot name="empty-state">
                    <CartEmptyState />
                </slot>
            </template>

            <!-- Item list -->
            <template v-if="items.length > 0">
                <CartItemList
                    :items="items"
                    :api-base-url="props.apiBaseUrl"
                    @remove="cartStore.removeItem($event)"
                    @toggle-regalo="({ itemId, datosRegalo }) => cartStore.toggleRegalo(itemId, datosRegalo)">
                    <template #item-extra="{ item }">
                        <slot name="item-extra" :item="item" />
                    </template>
                </CartItemList>

                <CartSummary :items="items" :total="total" />
            </template>

            <!-- Checkout button -->
            <CartCheckoutButton
                v-if="items.length > 0"
                :label="config.etiquetaBotonCheckout ?? 'Confirmar'"
                :disabled="items.length === 0"
                :loading="checkingOut"
                @checkout="cartStore.doCheckout()" />

            <!-- Slot bajo el botón -->
            <slot name="checkout-footer" />
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useCart }              from './composables/useCart.js';
import CartItemList             from './components/CartItemList.vue';
import CartSummary              from './components/CartSummary.vue';
import CartCheckoutButton       from './components/CartCheckoutButton.vue';
import CartEmptyState           from './components/CartEmptyState.vue';
import CartErrorBanner          from './components/CartErrorBanner.vue';

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps({
    /** Referencia opaca al usuario final (matrícula, UUID, etc.). */
    userRef: { type: String, required: true },

    /**
     * Configuración del carrito:
     * {
     *   categoriaSlug: String,
     *   requiereSaldo: Boolean,
     *   expiraEnMinutos: Number,
     *   permitirMultiplesItems: Boolean,
     *   etiquetaBotonCheckout: String,
     *   metadataInicial: Object,
     * }
     */
    config: { type: Object, required: true },

    /**
     * URL base del proxy del módulo cliente.
     * El módulo cliente define sus propias rutas proxy que internamente
     * llaman al Cart API con el JWT de módulo (nunca llega al browser).
     */
    apiBaseUrl: { type: String, default: '/cart-proxy' },
});

// ─── Emits ────────────────────────────────────────────────────────────────────

const emit = defineEmits([
    'cart-ready',
    'cart-updated',
    'item-added',
    'item-removed',
    'checkout-success',
    'checkout-error',
    'cart-error',
]);

// ─── State ────────────────────────────────────────────────────────────────────

const cartStore = useCart(props.userRef, props.config, props.apiBaseUrl, emit);
const { items, total, loading, checkingOut, error, confirmed, pendingCharge } = cartStore;

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(() => cartStore.init());

// ─── Public API (defineExpose) ────────────────────────────────────────────────

/**
 * addItem(item) — Llamado desde el módulo cliente para agregar un ítem
 * desde fuera del componente (ej. al hacer click en "Agregar" en el catálogo).
 *
 * Ejemplo desde el módulo cliente:
 *   <CartControl ref="cartRef" ... />
 *   cartRef.value.addItem({ referencia_externa: isbn, nombre: titulo, ... })
 */
defineExpose({
    addItem: (item) => cartStore.addItem(item),
});
</script>
