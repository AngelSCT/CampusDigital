<template>
    <input
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
        :class="error ? 'border-error' : ''"
    />
</template>

<script setup>
defineProps({
    modelValue: String,
    type: {
        type: String,
        default: 'text',
    },
    error: String,
});

defineEmits(['update:modelValue']);
</script>