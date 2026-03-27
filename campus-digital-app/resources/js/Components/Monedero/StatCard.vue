<template>
    <div
        class="group stat-card relative overflow-hidden rounded-2xl border p-6 transition-all duration-300 cursor-default select-none"
        :class="variantClasses"
    >
        <!-- Glow de fondo -->
        <div class="card-glow absolute inset-0 pointer-events-none" :class="glowClass" />

        <!-- Contenido -->
        <div class="relative z-10">
            <!-- Encabezado -->
            <div class="flex items-center gap-4 mb-5">
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-2xl text-2xl flex-shrink-0 shadow-lg transition-transform duration-300 group-hover:scale-110"
                    :class="iconBoxClass"
                >
                    <slot name="icon">{{ icon }}</slot>
                </div>
                <span class="text-sm font-medium text-slate-300">{{ label }}</span>
            </div>

            <!-- Valor -->
            <div class="stat-value text-4xl font-extrabold text-white leading-none tracking-tight">
                {{ value }}
            </div>

            <!-- Subtítulo opcional -->
            <p v-if="subtitle" class="text-xs text-slate-400 mt-2">{{ subtitle }}</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    /** 'primary' | 'success' | 'error' | 'warning' */
    variant: {
        type: String,
        default: 'primary',
    },
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        default: '📊',
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const variantMap = {
    primary: {
        card: 'border-blue-500/30 bg-slate-800/80 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-900/30 hover:border-blue-500/50',
        glow: 'bg-gradient-radial-primary opacity-5',
        iconBox: 'bg-gradient-to-br from-blue-700 to-blue-500 shadow-blue-500/30',
    },
    success: {
        card: 'border-green-500/30 bg-slate-800/80 hover:-translate-y-2 hover:shadow-2xl hover:shadow-green-900/30 hover:border-green-500/50',
        glow: 'bg-gradient-radial-success opacity-5',
        iconBox: 'bg-gradient-to-br from-green-700 to-green-500 shadow-green-500/30',
    },
    error: {
        card: 'border-red-500/30 bg-slate-800/80 hover:-translate-y-2 hover:shadow-2xl hover:shadow-red-900/30 hover:border-red-500/50',
        glow: 'bg-gradient-radial-error opacity-5',
        iconBox: 'bg-gradient-to-br from-red-700 to-red-500 shadow-red-500/30',
    },
    warning: {
        card: 'border-yellow-500/30 bg-slate-800/80 hover:-translate-y-2 hover:shadow-2xl hover:shadow-yellow-900/30 hover:border-yellow-500/50',
        glow: 'bg-gradient-radial-warning opacity-5',
        iconBox: 'bg-gradient-to-br from-yellow-600 to-yellow-400 shadow-yellow-500/30',
    },
};

const variantClasses = computed(() => variantMap[props.variant]?.card ?? variantMap.primary.card);
const glowClass = computed(() => variantMap[props.variant]?.glow ?? variantMap.primary.glow);
const iconBoxClass = computed(() => variantMap[props.variant]?.iconBox ?? variantMap.primary.iconBox);
</script>

<style scoped>
.stat-card {
    backdrop-filter: blur(8px);
}

.stat-card::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    border-radius: 9999px 9999px 0 0;
    background: currentColor;
    opacity: 0.2;
}

.card-glow {
    background: radial-gradient(ellipse at 50% 0%, currentColor 0%, transparent 70%);
}

.stat-value {
    animation: countUp 0.6s ease-out;
}

@keyframes countUp {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
