<template>
    <li class="cart-item flex items-start justify-between gap-2 py-2">
        <div class="flex-1 min-w-0">
            <p class="truncate text-sm font-medium text-gray-800">{{ item.nombre }}</p>
            <p class="text-xs text-gray-400">{{ item.referencia_externa }}</p>
            <p class="text-xs text-gray-400">Cantidad: {{ item.cantidad ?? 1 }}</p>
            <p v-if="item.duracion_dias" class="text-xs text-gray-400">{{ item.duracion_dias }} día(s)</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="text-right">
                <p class="text-xs text-gray-400">{{ formatMoney(item.precio_unitario) }} / ud.</p>
                <p class="text-sm font-semibold text-gray-700">
                    {{ formatMoney((item.precio_unitario ?? 0) * (item.cantidad ?? 1)) }}
                </p>
            </div>
            <slot name="item-extra" :item="item" />
            <button @click="$emit('remove', item.id)"
                    class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors"
                    title="Quitar">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </li>
</template>

<script setup>
defineProps({ item: { type: Object, required: true } });
defineEmits(['remove']);

function formatMoney(val) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val ?? 0);
}
</script>
