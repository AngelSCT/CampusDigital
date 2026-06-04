<template>
    <button
        type="button"
        :disabled="disabled || loading"
        @click="$emit('checkout')"
        class="cart-checkout-btn w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white
               shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2
               focus-visible:outline-offset-2 focus-visible:outline-indigo-600
               disabled:cursor-not-allowed disabled:opacity-50">
        <span v-if="loading" class="inline-flex items-center gap-2">
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Procesando…
        </span>
        <span v-else>{{ label }}</span>
    </button>
</template>

<script setup>
defineProps({
    label:    { type: String, default: 'Confirmar' },
    disabled: { type: Boolean, default: false },
    loading:  { type: Boolean, default: false },
});
defineEmits(['checkout']);
</script>
