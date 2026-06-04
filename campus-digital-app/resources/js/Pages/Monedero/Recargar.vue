<template>
    <AuthLayout>
        <div class="max-w-5xl mx-auto space-y-6">

            <!-- Encabezado -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    Recargar Saldo
                </h1>
                <p class="mt-1 text-sm text-slate-400">Agrega saldo a tu monedero universitario</p>
            </div>

            <!-- Alertas -->
            <div v-if="$page.props.flash?.success"
                 class="flex items-center gap-3 p-4 bg-green-500/10 border border-green-500/20 rounded-xl text-green-400">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-sm font-medium">{{ $page.props.flash.success }}</span>
            </div>

            <div v-if="$page.props.flash?.error"
                 class="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium">{{ $page.props.flash.error }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Columna izquierda: saldo, métodos de pago y formulario -->
                <div class="lg:col-span-1 space-y-4">

                    <!-- Saldo actual -->
                    <div class="bg-gradient-to-br from-cyan-900/40 to-blue-900/40 border border-cyan-500/20 rounded-xl p-5 text-center">
                        <p class="text-xs text-cyan-400 uppercase tracking-wider mb-1">
                            Saldo Disponible
                        </p>
                        <p class="text-4xl font-bold text-white font-mono">
                            ${{ formatMonto(monedero?.saldo_disponible ?? 0) }}
                        </p>
                    </div>

                    <!-- ⭐ MÉTODOS DE PAGO + WIDGET DE RECARGA RÁPIDA ⭐ -->
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-700">
                            <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Métodos de Pago
                            </h2>
                        </div>

                        <div class="p-5 space-y-3">

                            <!-- Métodos disponibles -->
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="mp in metodosPago" :key="mp.value"
                                        @click="form.metodo_pago = mp.value" type="button"
                                        class="py-2 flex flex-col items-center gap-1 rounded-lg border text-xs font-medium transition-all duration-200"
                                        :class="form.metodo_pago === mp.value
                                            ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-300'
                                            : 'bg-slate-700/50 border-slate-600 text-slate-400 hover:border-slate-500'">
                                    <span class="text-base">{{ mp.icon }}</span>
                                    {{ mp.label }}
                                </button>
                            </div>

                            <!-- Separador -->
                            <div class="border-t border-slate-700/60 pt-3">
                                <p class="text-xs text-slate-500 mb-2 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Recarga rápida
                                </p>

                                <!-- Montos rápidos -->
                                <div class="grid grid-cols-3 gap-1.5 mb-3">
                                    <button v-for="m in montosRapidos" :key="m"
                                            @click="form.monto = m" type="button"
                                            class="py-1.5 text-xs font-medium rounded-lg border transition-all duration-200"
                                            :class="form.monto == m
                                                ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-300'
                                                : 'bg-slate-700/50 border-slate-600 text-slate-400 hover:border-slate-500 hover:text-slate-300'">
                                        ${{ m }}
                                    </button>
                                </div>

                                <!-- Input monto personalizado -->
                                <div class="relative mb-3">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">$</span>
                                    <input v-model="form.monto" type="number" min="1" max="5000" placeholder="Otro monto..."
                                           class="w-full pl-7 pr-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                                           :class="{ 'border-red-500': errors.monto }"/>
                                </div>
                                <p v-if="errors.monto" class="text-xs text-red-400 -mt-2 mb-2">{{ errors.monto }}</p>

                                <!-- ⭐ BOTÓN ENVIAR RECARGA AL MÓDULO 2 ⭐ -->
                                <button @click="submit" :disabled="procesando"
                                        class="w-full py-2.5 bg-gradient-to-br from-cyan-600 to-blue-700 border border-transparent rounded-lg text-sm font-semibold text-white hover:from-cyan-500 hover:to-blue-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-cyan-500/20 transition-all duration-200 flex items-center justify-center gap-2">
                                    <svg v-if="procesando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ procesando ? 'Procesando...' : 'Recargar Saldo' }}
                                </button>

                                <!-- Nota informativa -->
                                <p class="text-xs text-slate-500 text-center mt-2">
                                    El saldo se acredita inmediatamente
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: historial -->
                <div class="lg:col-span-2">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Historial de Recargas
                            </h2>
                            <span class="text-xs text-slate-400">{{ recargas.length }} registros</span>
                        </div>

                        <div v-if="recargas.length === 0" class="px-6 py-12 text-center">
                            <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <p class="text-slate-500 text-sm">Sin recargas registradas</p>
                        </div>

                        <div v-else class="divide-y divide-slate-700/50">
                            <div v-for="r in recargas" :key="r.id"
                                 class="flex items-center gap-4 px-6 py-4 hover:bg-slate-700/20 transition-colors duration-150">

                                <!-- Ícono estado -->
                                <div :class="r.estado === 'exitosa' || r.estado === 'exitoso'
                                         ? 'bg-green-500/20 text-green-400'
                                         : r.estado === 'fallida' || r.estado === 'fallido'
                                             ? 'bg-red-500/20 text-red-400'
                                             : 'bg-yellow-500/20 text-yellow-400'"
                                     class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="r.estado === 'exitosa' || r.estado === 'exitoso'"
                                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        <path v-else-if="r.estado === 'fallida' || r.estado === 'fallido'"
                                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-white">
                                            Recarga vía {{ metodoLabel(r.metodo_pago) }}
                                        </p>
                                        <span :class="badgeClass(r.estado)"
                                              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border capitalize">
                                            {{ r.estado }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ formatDateTime(r.created_at) }}</p>
                                    <p v-if="r.referencia" class="text-xs text-slate-500 mt-0.5 font-mono">{{ r.referencia }}</p>
                                    <p v-if="r.razon_fallo" class="text-xs text-red-400 mt-0.5">{{ r.razon_fallo }}</p>
                                </div>

                                <!-- Monto -->
                                <p :class="(r.estado === 'exitosa' || r.estado === 'exitoso') ? 'text-green-400' : 'text-slate-500'"
                                   class="text-sm font-bold font-mono whitespace-nowrap">
                                    {{ (r.estado === 'exitosa' || r.estado === 'exitoso') ? '+' : '' }}${{ formatMonto(r.monto) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    monedero: { type: Object, default: null },
    recargas: { type: Array,  default: () => [] },
    limites:  { type: Object, default: () => ({}) },
});

// Montos rápidos para recarga
const montosRapidos = [50, 100, 200, 300, 500, 1000];

// Métodos de pago disponibles
const metodosPago = [
    { value: 'tarjeta',       label: 'Tarjeta',      icon: '💳' },
    { value: 'transferencia', label: 'Transfer.',     icon: '🏦' },
    { value: 'efectivo',      label: 'Efectivo',      icon: '💵' },
];

// Formulario reactivo
const form = useForm({
    monto:       '',
    metodo_pago: 'tarjeta',
});

const procesando = ref(false);
const errors     = reactive({});

// ⭐ Submit — envía la recarga al Módulo 2 a través del backend
function submit() {
    errors.monto       = '';
    errors.metodo_pago = '';

    if (!form.monto || Number(form.monto) < 1) {
        errors.monto = 'El monto debe ser mayor a $1';
        return;
    }

    if (Number(form.monto) > 5000) {
        errors.monto = 'El monto máximo es $5,000';
        return;
    }

    procesando.value = true;
    form.post(route('monedero.recargas.store'), {
        onFinish: () => { procesando.value = false; },
        onError:  (e) => { Object.assign(errors, e); },
    });
}

// Formateo
function formatMonto(v) {
    return Number(v ?? 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDateTime(d) {
    return d ? new Date(d).toLocaleString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }) : '-';
}

function metodoLabel(m) {
    const l = { tarjeta: 'Tarjeta', transferencia: 'Transferencia', efectivo: 'Efectivo' };
    return l[m] ?? m;
}

function badgeClass(estado) {
    if (estado === 'exitosa' || estado === 'exitoso')  return 'bg-green-500/20 text-green-400 border-green-500/20';
    if (estado === 'fallida' || estado === 'fallido')  return 'bg-red-500/20 text-red-400 border-red-500/20';
    return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/20';
}
</script>
