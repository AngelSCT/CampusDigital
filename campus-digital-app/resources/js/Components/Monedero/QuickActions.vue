<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <a
            v-for="action in actions"
            :key="action.key"
            :href="action.href"
            class="quick-action-btn flex items-center gap-4 px-5 py-4 rounded-xl border font-semibold text-sm text-white transition-all duration-300 no-underline"
            :class="actionClasses(action.variant)"
        >
            <span
                class="text-2xl flex-shrink-0 transition-transform duration-300 action-icon"
            >{{ action.icon }}</span>
            <span>{{ action.label }}</span>
        </a>
    </div>
</template>

<script setup>
const variantMap = {
    success: 'border-green-500/30 hover:border-green-400/60 hover:bg-green-500/10 hover:translate-x-1 hover:shadow-lg hover:shadow-green-900/20',
    primary: 'border-blue-500/30 hover:border-blue-400/60 hover:bg-blue-500/10 hover:translate-x-1 hover:shadow-lg hover:shadow-blue-900/20',
    warning: 'border-yellow-500/30 hover:border-yellow-400/60 hover:bg-yellow-500/10 hover:translate-x-1 hover:shadow-lg hover:shadow-yellow-900/20',
    secondary: 'border-slate-500/30 hover:border-slate-400/60 hover:bg-slate-500/10 hover:translate-x-1 hover:shadow-lg hover:shadow-slate-900/20',
};

defineProps({
    actions: {
        type: Array,
        default: () => [
            { key: 'recargar', label: 'Recargar Saldo', icon: '➕', href: '/modulo_8/recargar', variant: 'success' },
            { key: 'movimientos', label: 'Ver Movimientos', icon: '📋', href: '/modulo_8/movimientos', variant: 'primary' },
        ],
    },
});

function actionClasses(variant) {
    return variantMap[variant] || variantMap.primary;
}
</script>

<style scoped>
.quick-action-btn {
    background: rgba(30, 41, 59, 0.8);
    backdrop-filter: blur(8px);
}

.quick-action-btn:hover .action-icon {
    transform: scale(1.15);
}
</style>
