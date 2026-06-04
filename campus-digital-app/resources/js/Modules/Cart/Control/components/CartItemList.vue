<template>
    <ul class="cart-item-list divide-y divide-gray-100">
        <CartItem
            v-for="item in items"
            :key="item.id"
            :item="item"
            :api-base-url="apiBaseUrl"
            @remove="$emit('remove', $event)"
            @toggle-regalo="$emit('toggle-regalo', $event)">
            <template #item-extra="{ item: i }">
                <slot name="item-extra" :item="i" />
            </template>
        </CartItem>
    </ul>
</template>

<script setup>
import CartItem from './CartItem.vue';
defineProps({
    items:      { type: Array, required: true },
    apiBaseUrl: { type: String, default: '/catalogo/cart-proxy' },
});
defineEmits(['remove', 'toggle-regalo']);
</script>
