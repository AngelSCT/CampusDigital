<template>
    <div class="cart-summary border-t border-gray-100 pt-3">
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">{{ items.length }} artículo(s)</span>
            <span class="font-bold text-gray-900">{{ formatMoney(total) }}</span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    items: { type: Array, required: true },
    total: { type: [Number, String], default: 0 },
});

function formatMoney(val) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val ?? 0);
}
</script>
