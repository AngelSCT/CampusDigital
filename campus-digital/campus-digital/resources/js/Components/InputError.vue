<template>
    <div v-show="message" class="text-sm text-error mt-1">
        {{ message }}
    </div>
</template>

<script setup>
defineProps({
    message: String,
});
</script>