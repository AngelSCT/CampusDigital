<template>
    <div
        class="relative rounded-2xl border overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl cursor-default"
        :class="cardClasses"
    >
        <!-- Barra de color superior -->
        <div class="absolute top-0 inset-x-0 h-0.5" :class="barClass"></div>

        <!-- Destello de fondo -->
        <div class="absolute inset-0 opacity-[0.04] pointer-events-none rounded-full scale-150" :class="glowClass"></div>

        <div class="relative p-5">
            <!-- Encabezado: icono + etiqueta -->
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-12 h-12 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                    :class="iconBoxClass"
                >
                    <span>{{ icon }}</span>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-medium leading-tight">{{ label }}</p>
                    <p v-if="subtitle" class="text-xs text-slate-500 mt-0.5">{{ subtitle }}</p>
                </div>
            </div>

            <!-- Valor principal -->
            <div class="flex items-end justify-between gap-2">
                <div>
                    <p class="text-3xl font-extrabold text-white tabular-nums leading-none" :class="valueClass">
                        {{ prefix }}{{ displayValue }}{{ suffix }}
                    </p>

                    <!-- Tendencia vs mes anterior -->
                    <p v-if="trend !== null" class="text-xs mt-1.5 flex items-center gap-1">
                        <span :class="trend >= 0 ? 'text-green-400' : 'text-red-400'">
                            {{ trend >= 0 ? '▲' : '▼' }} {{ Math.abs(trend) }}%
                        </span>
                        <span class="text-slate-500">vs mes anterior</span>
                    </p>
                </div>

                <!-- Slot para acción extra (ej: botón) -->
                <slot name="action" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
    label:    { type: String,          required: true },
    subtitle: { type: String,          default: null },
    value:    { type: [Number, String], default: 0 },
    prefix:   { type: String,          default: '' },
    suffix:   { type: String,          default: '' },
    icon:     { type: String,          default: '📊' },
    variant:  {
        type:      String,
        default:   'primary',
        validator: (v) => ['primary', 'success', 'danger', 'warning'].includes(v),
    },
    trend:    { type: Number,   default: null },
    animated: { type: Boolean,  default: true },
    decimals: { type: Number,   default: 0 },
});

// ── Animación count-up ──────────────────────────────────────────
const displayValue = ref('0');

function formatNum(n) {
    if (typeof n !== 'number') return String(n);
    return props.decimals > 0
        ? n.toFixed(props.decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
        : Math.round(n).toLocaleString('es-MX');
}

function animateTo(target) {
    const num = typeof target === 'number' ? target : parseFloat(target) || 0;
    if (!props.animated) {
        displayValue.value = formatNum(num);
        return;
    }
    const duration  = 900;
    const startTime = performance.now();
    function step(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        const eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        displayValue.value = formatNum(num * eased);
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

onMounted(() => animateTo(props.value));
watch(() => props.value, (v) => animateTo(v));

// ── Estilos según variante ──────────────────────────────────────
const variants = {
    primary: {
        card:    'bg-slate-800/70 border-blue-500/30 hover:border-blue-400/60 hover:shadow-blue-500/20',
        iconBox: 'bg-gradient-to-br from-blue-700 to-blue-500 shadow-lg shadow-blue-500/30',
        bar:     'bg-gradient-to-r from-blue-500 to-blue-400',
        glow:    'bg-blue-500',
        value:   '',
    },
    success: {
        card:    'bg-slate-800/70 border-green-500/30 hover:border-green-400/60 hover:shadow-green-500/20',
        iconBox: 'bg-gradient-to-br from-green-700 to-green-500 shadow-lg shadow-green-500/30',
        bar:     'bg-gradient-to-r from-green-500 to-emerald-400',
        glow:    'bg-green-500',
        value:   'text-green-300',
    },
    danger: {
        card:    'bg-slate-800/70 border-red-500/30 hover:border-red-400/60 hover:shadow-red-500/20',
        iconBox: 'bg-gradient-to-br from-red-700 to-red-500 shadow-lg shadow-red-500/30',
        bar:     'bg-gradient-to-r from-red-500 to-red-400',
        glow:    'bg-red-500',
        value:   'text-red-300',
    },
    warning: {
        card:    'bg-slate-800/70 border-yellow-500/30 hover:border-yellow-400/60 hover:shadow-yellow-500/20',
        iconBox: 'bg-gradient-to-br from-yellow-700 to-yellow-500 shadow-lg shadow-yellow-500/30',
        bar:     'bg-gradient-to-r from-yellow-500 to-amber-400',
        glow:    'bg-yellow-500',
        value:   'text-yellow-300',
    },
};

const v           = computed(() => variants[props.variant] || variants.primary);
const cardClasses = computed(() => v.value.card);
const iconBoxClass = computed(() => v.value.iconBox);
const barClass    = computed(() => v.value.bar);
const glowClass   = computed(() => v.value.glow);
const valueClass  = computed(() => v.value.value);
</script>
