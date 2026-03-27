<template>
    <AuthLayout>
        <div class="max-w-6xl mx-auto space-y-6">

            <!-- Encabezado -->
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    Recargar Saldo
                </h1>
                <p class="mt-1 text-sm text-slate-400">Carga dinero a tu monedero universitario</p>
            </div>

            <!-- Alertas -->
            <div v-if="alertSuccess" class="flex items-center gap-3 p-4 bg-green-500/10 border border-green-500/20 rounded-xl text-green-400">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-sm font-medium">{{ alertSuccess }}</span>
            </div>

            <div v-if="alertError" class="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium">{{ alertError }}</span>
            </div>

            <div v-if="alertWarning" class="flex items-center gap-3 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-xl text-yellow-400">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium">{{ alertWarning }}</span>
            </div>

            <!-- Grid Principal -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Columna Izquierda: Formulario y Saldo -->
                <div class="lg:col-span-1 space-y-4">

                    <!-- Saldo Actual -->
                    <div class="bg-gradient-to-br from-cyan-900/40 to-blue-900/40 border border-cyan-500/20 rounded-xl p-6 text-center">
                        <p class="text-xs text-cyan-400 uppercase tracking-wider mb-2">Saldo Disponible</p>
                        <p class="text-4xl font-bold text-white font-mono">
                            ${{ formatMonto(monedero?.saldo_disponible ?? 0) }}
                        </p>
                        <p class="text-xs text-slate-400 mt-2">Actualizado: {{ formatDateTime(new Date()) }}</p>
                    </div>

                    <!-- Formulario de Recarga -->
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-700">
                            <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Nueva Recarga
                            </h2>
                        </div>

                        <div class="p-5 space-y-4">

                            <!-- Monto -->
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Monto a recargar</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                    <input 
                                        v-model.number="form.monto" 
                                        type="number" 
                                        min="1" 
                                        max="5000" 
                                        placeholder="0.00"
                                        class="w-full pl-7 pr-4 py-2.5 rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all duration-200"
                                        :class="{ 'border-red-500': errors.monto }"
                                    />
                                </div>
                                <p v-if="errors.monto" class="text-xs text-red-400 mt-1">{{ errors.monto }}</p>

                                <!-- Montos Rápidos -->
                                <div class="grid grid-cols-3 gap-2 mt-2">
                                    <button 
                                        v-for="m in montosRapidos" 
                                        :key="m"
                                        @click="form.monto = m" 
                                        type="button"
                                        class="py-1.5 text-xs font-medium rounded-lg border transition-all duration-200"
                                        :class="form.monto == m
                                            ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-300'
                                            : 'bg-slate-700/50 border-slate-600 text-slate-400 hover:border-slate-500 hover:text-slate-300'"
                                    >
                                        ${{ m }}
                                    </button>
                                </div>
                            </div>

                            <!-- Método de Pago -->
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Método de pago</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button 
                                        v-for="mp in metodosPago" 
                                        :key="mp.value"
                                        @click="form.metodo_pago = mp.value" 
                                        type="button"
                                        class="py-2.5 flex flex-col items-center gap-1 rounded-lg border text-xs font-medium transition-all duration-200"
                                        :class="form.metodo_pago === mp.value
                                            ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-300'
                                            : 'bg-slate-700/50 border-slate-600 text-slate-400 hover:border-slate-500'"
                                    >
                                        <span class="text-lg">{{ mp.icon }}</span>
                                        {{ mp.label }}
                                    </button>
                                </div>
                                <p v-if="errors.metodo_pago" class="text-xs text-red-400 mt-1">{{ errors.metodo_pago }}</p>
                            </div>

                            <!-- Botón Recargar -->
                            <button 
                                @click="procesarRecarga" 
                                :disabled="procesando"
                                class="w-full py-2.5 bg-gradient-to-br from-cyan-600 to-blue-700 border border-transparent rounded-lg text-sm font-semibold text-white hover:from-cyan-500 hover:to-blue-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-cyan-500/20 transition-all duration-200 flex items-center justify-center gap-2"
                            >
                                <svg v-if="procesando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ procesando ? 'Procesando...' : 'Realizar Recarga' }}
                            </button>
                        </div>
                    </div>

                    <!-- Información de Límites -->
                    <div class="bg-slate-900/50 border border-slate-700 rounded-xl p-4 space-y-2">
                        <p class="text-xs font-semibold text-slate-300">Límites de Recarga</p>
                        <div class="space-y-1 text-xs text-slate-400">
                            <p>✓ Monto: $1 - $5,000</p>
                            <p>✓ Máximo: 3 recargas/día</p>
                            <p>✓ Intervalo: 5 minutos</p>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Historial -->
                <div class="lg:col-span-2">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Historial de Recargas
                            </h2>
                            <div class="flex gap-2">
                                <button
                                    v-for="estado in ['todos', 'exitosa', 'fallida']"
                                    :key="estado"
                                    @click="filtroEstado = estado"
                                    type="button"
                                    class="px-3 py-1 text-xs font-medium rounded-lg border transition-all duration-200"
                                    :class="filtroEstado === estado
                                        ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-300'
                                        : 'bg-slate-700/50 border-slate-600 text-slate-400 hover:border-slate-500'"
                                >
                                    {{ estado.charAt(0).toUpperCase() + estado.slice(1) }}
                                </button>
                            </div>
                        </div>

                        <div v-if="recargasFiltradas.length === 0" class="px-6 py-12 text-center">
                            <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <p class="text-slate-500 text-sm">Sin recargas registradas</p>
                        </div>

                        <div v-else class="divide-y divide-slate-700/50">
                            <div 
                                v-for="r in recargasFiltradas" 
                                :key="r.id"
                                class="flex items-center gap-4 px-6 py-4 hover:bg-slate-700/20 transition-colors duration-150"
                            >
                                <!-- Ícono Estado -->
                                <div 
                                    :class="r.estado === 'exitosa'
                                        ? 'bg-green-500/20 text-green-400'
                                        : r.estado === 'fallida'
                                            ? 'bg-red-500/20 text-red-400'
                                            : 'bg-yellow-500/20 text-yellow-400'"
                                    class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="r.estado === 'exitosa'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        <path v-else-if="r.estado === 'fallida'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-white">
                                            Recarga vía {{ metodoLabel(r.metodo_pago) }}
                                        </p>
                                        <span 
                                            :class="badgeClass(r.estado)"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border capitalize"
                                        >
                                            {{ r.estado }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ formatDateTime(r.created_at) }}</p>
                                    <p v-if="r.razon_fallo" class="text-xs text-red-400 mt-0.5">{{ r.razon_fallo }}</p>
                                </div>

                                <!-- Monto -->
                                <p 
                                    :class="r.estado === 'exitosa' ? 'text-green-400' : 'text-slate-500'"
                                    class="text-sm font-bold font-mono whitespace-nowrap"
                                >
                                    {{ r.estado === 'exitosa' ? '+' : '' }}${{ formatMonto(r.monto) }}
                                </p>

                                <!-- Acciones -->
                                <button 
                                    v-if="r.estado === 'exitosa'"
                                    @click="descargarComprobante(r.id)"
                                    type="button"
                                    class="p-2 text-slate-400 hover:text-cyan-400 hover:bg-slate-700/50 rounded-lg transition-all duration-200"
                                    title="Descargar comprobante"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </button>

                                <!-- Botón Reintentar (US3) -->
                                <button
                                    v-if="r.estado === 'fallida'"
                                    @click="reintentar(r.id)"
                                    :disabled="reintentando === r.id"
                                    type="button"
                                    class="p-2 text-slate-400 hover:text-yellow-400 hover:bg-slate-700/50 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    title="Reintentar pago"
                                >
                                    <svg v-if="reintentando === r.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    monedero: { type: Object, default: null },
    recargas: { type: Array, default: () => [] },
    limites: { type: Object, default: () => ({
        monto_minimo: 1,
        monto_maximo: 5000,
        max_recargas_dia: 3,
        intervalo_minutos: 5
    }) }
});

const montosRapidos = [50, 100, 200, 300, 500, 1000];

const metodosPago = [
    { value: 'tarjeta', label: 'Tarjeta', icon: '💳' },
    { value: 'transferencia', label: 'Transferencia', icon: '🏦' },
    { value: 'efectivo', label: 'Efectivo', icon: '💵' },
    { value: 'billetera_digital', label: 'Billetera', icon: '📱' },
];

const form = reactive({
    monto: '',
    metodo_pago: 'tarjeta',
});

const procesando = ref(false);
const reintentando = ref(null);
const errors = reactive({});
const filtroEstado = ref('todos');
const alertSuccess = ref('');
const alertError = ref('');
const alertWarning = ref('');

const recargasFiltradas = computed(() => {
    if (filtroEstado.value === 'todos') return props.recargas || [];
    return (props.recargas || []).filter(r => r.estado === filtroEstado.value);
});

onMounted(() => {
    validarLimites();
});

function validarLimites() {
    const hoy = new Date();
    const recargasHoy = (props.recargas || []).filter(r => {
        const fechaRecarga = new Date(r.created_at);
        return fechaRecarga.toDateString() === hoy.toDateString() && r.estado === 'exitosa';
    });

    if (recargasHoy.length >= props.limites.max_recargas_dia) {
        alertWarning.value = `Has alcanzado el límite de ${props.limites.max_recargas_dia} recargas por día.`;
    }

    const ultimaRecarga = (props.recargas || []).filter(r => r.estado === 'exitosa')[0];
    if (ultimaRecarga) {
        const fechaUltima = new Date(ultimaRecarga.created_at);
        const ahora = new Date();
        const minutosDiferencia = Math.floor((ahora - fechaUltima) / 60000);
        
        if (minutosDiferencia < props.limites.intervalo_minutos) {
            alertWarning.value = `Espera ${props.limites.intervalo_minutos - minutosDiferencia} minuto(s) antes de recargar nuevamente.`;
        }
    }
}

function procesarRecarga() {
    errors.monto = '';
    errors.metodo_pago = '';
    alertSuccess.value = '';
    alertError.value = '';
    alertWarning.value = '';

    // Validaciones
    if (!form.monto || form.monto < props.limites.monto_minimo) {
        errors.monto = `El monto debe ser mayor a $${props.limites.monto_minimo}`;
        return;
    }

    if (form.monto > props.limites.monto_maximo) {
        errors.monto = `El monto no puede exceder $${props.limites.monto_maximo}`;
        return;
    }

    if (!form.metodo_pago) {
        errors.metodo_pago = 'Selecciona un método de pago';
        return;
    }

    // Validar límites
    const hoy = new Date();
    const recargasHoy = (props.recargas || []).filter(r => {
        const fechaRecarga = new Date(r.created_at);
        return fechaRecarga.toDateString() === hoy.toDateString() && r.estado === 'exitosa';
    });

    if (recargasHoy.length >= props.limites.max_recargas_dia) {
        alertError.value = `Has alcanzado el límite de ${props.limites.max_recargas_dia} recargas por día.`;
        return;
    }

    const ultimaRecarga = (props.recargas || []).filter(r => r.estado === 'exitosa')[0];
    if (ultimaRecarga) {
        const fechaUltima = new Date(ultimaRecarga.created_at);
        const ahora = new Date();
        const minutosDiferencia = Math.floor((ahora - fechaUltima) / 60000);
        
        if (minutosDiferencia < props.limites.intervalo_minutos) {
            alertError.value = `Debes esperar ${props.limites.intervalo_minutos} minutos entre recargas.`;
            return;
        }
    }

    // Procesar - ESTA ES LA PARTE CORRECTA
    procesando.value = true;
    
    const datosForm = {
        monto: form.monto,
        metodo_pago: form.metodo_pago
    };

    router.post('/modulo_8/recargar', datosForm, {
        onSuccess: () => {
            alertSuccess.value = `Recarga de $${form.monto} exitosa!`;
            form.monto = '';
            form.metodo_pago = 'tarjeta';
            setTimeout(() => {
                router.reload();
            }, 2000);
        },
        onError: (err) => {
            console.error('Error:', err);
            alertError.value = Object.values(err)[0] || 'Error al procesar la recarga.';
        },
        onFinish: () => {
            procesando.value = false;
        }
    });
}

// US3 - Reintentar pago fallido
function reintentar(id) {
    reintentando.value = id;
    alertSuccess.value = '';
    alertError.value = '';

    router.post(`/modulo_8/recargar/${id}/reintentar`, {}, {
        onSuccess: () => {
            alertSuccess.value = 'Reintento procesado. Revisa el estado de tu recarga.';
            router.reload();
        },
        onError: () => {
            alertError.value = 'No se pudo reintentar el pago. Intenta más tarde.';
        },
        onFinish: () => {
            reintentando.value = null;
        }
    });
}

function descargarComprobante(id) {
    window.location.href = route('modulo_8.comprobante', { id });
}

function formatMonto(v) {
    return Number(v).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDateTime(d) {
    if (!d) return '-';
    return new Date(d).toLocaleString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function metodoLabel(m) {
    const labels = {
        tarjeta: 'Tarjeta',
        transferencia: 'Transferencia',
        efectivo: 'Efectivo',
        billetera_digital: 'Billetera Digital'
    };
    return labels[m] || m;
}

function badgeClass(estado) {
    if (estado === 'exitosa') return 'bg-green-500/20 text-green-400 border-green-500/20';
    if (estado === 'fallida') return 'bg-red-500/20 text-red-400 border-red-500/20';
    return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/20';
}
</script>