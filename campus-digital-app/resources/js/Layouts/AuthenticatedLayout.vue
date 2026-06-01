<template>
    <AuthLayout>
        <slot />
    </AuthLayout>
</template>

<script setup>
import AuthLayout from './AuthLayout.vue';
</script>

<!-- Este archivo existe para compatibilidad con imports que usan -->
<!-- "@/Layouts/AuthenticatedLayout.vue" en lugar del nombre real "AuthLayout.vue" -->
