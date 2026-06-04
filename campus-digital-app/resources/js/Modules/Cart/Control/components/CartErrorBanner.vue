<template>
    <div class="cart-error-banner flex items-start gap-2 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
        <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <div class="flex-1">
            <p class="font-medium">{{ error.mensaje }}</p>
            <p v-if="error.code" class="mt-0.5 text-xs opacity-70">Código: {{ error.code }}</p>
        </div>
        <button v-if="retryable" @click="$emit('retry')"
                class="text-xs font-medium underline hover:no-underline">
            Reintentar
        </button>
    </div>
</template>

<script setup>
defineProps({
    error:     { type: Object, required: true },
    retryable: { type: Boolean, default: false },
});
defineEmits(['retry']);
</script>
