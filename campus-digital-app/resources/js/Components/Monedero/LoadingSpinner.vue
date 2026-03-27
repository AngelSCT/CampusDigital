<template>
    <div
        class="loading-spinner"
        :class="sizeClass"
        role="status"
        aria-label="Cargando..."
    >
        <svg
            :class="['animate-spin', svgClass]"
            fill="none"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v8z"
            />
        </svg>
        <span v-if="label" class="sr-only">{{ label }}</span>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    /** 'sm' | 'md' | 'lg' | 'xl' */
    size: {
        type: String,
        default: 'md',
    },
    label: {
        type: String,
        default: 'Cargando...',
    },
});

const sizeMap = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-12 h-12',
};

const sizeClass = computed(() => 'inline-flex items-center justify-center');
const svgClass = computed(() => sizeMap[props.size] || sizeMap.md);
</script>
