<template>
    <ul class="cart-item-list divide-y divide-gray-100">
        <CartItem
            v-for="item in items"
            :key="item.id"
            :item="item"
            @remove="$emit('remove', $event)">
            <template #item-extra="{ item: i }">
                <slot name="item-extra" :item="i" />
            </template>
        </CartItem>
    </ul>
</template>

<script setup>
import CartItem from './CartItem.vue';
defineProps({ items: { type: Array, required: true } });
defineEmits(['remove']);
</script>
