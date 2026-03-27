<template>
    <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-6">
        <h2 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Acciones rápidas
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            <!-- Recargar saldo -->
            <a
                href="/modulo_8/recargar"
                class="group flex items-center gap-3 p-4 bg-slate-700/40 border border-green-500/20 rounded-xl hover:border-green-500/50 hover:bg-green-500/10 transition-all duration-200"
            >
                <div class="w-10 h-10 rounded-xl bg-green-500/15 flex items-center justify-center flex-shrink-0 group-hover:bg-green-500/25 transition-colors">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white group-hover:text-green-300 transition-colors">Recargar saldo</p>
                    <p class="text-xs text-slate-400 truncate">Agrega fondos a tu monedero</p>
                </div>
                <svg class="w-4 h-4 text-slate-500 group-hover:text-green-400 group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <!-- Realizar pago -->
            <button
                type="button"
                @click="$emit('pagar')"
                class="group flex items-center gap-3 p-4 bg-slate-700/40 border border-blue-500/20 rounded-xl hover:border-blue-500/50 hover:bg-blue-500/10 transition-all duration-200 w-full text-left"
            >
                <div class="w-10 h-10 rounded-xl bg-blue-500/15 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-500/25 transition-colors">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white group-hover:text-blue-300 transition-colors">Realizar pago</p>
                    <p class="text-xs text-slate-400 truncate">Usa tu saldo disponible</p>
                </div>
                <svg class="w-4 h-4 text-slate-500 group-hover:text-blue-400 group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <!-- Ver historial -->
            <a
                href="#historial"
                class="group flex items-center gap-3 p-4 bg-slate-700/40 border border-slate-600/40 rounded-xl hover:border-slate-500/60 hover:bg-slate-700/60 transition-all duration-200"
            >
                <div class="w-10 h-10 rounded-xl bg-slate-700 flex items-center justify-center flex-shrink-0 group-hover:bg-slate-600 transition-colors">
                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white group-hover:text-slate-200 transition-colors">Ver historial</p>
                    <p class="text-xs text-slate-400 truncate">Revisa tus movimientos</p>
                </div>
                <svg class="w-4 h-4 text-slate-500 group-hover:text-slate-300 group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <!-- Actualizar datos -->
            <button
                type="button"
                @click="$emit('actualizar')"
                :disabled="loading"
                class="group flex items-center gap-3 p-4 bg-slate-700/40 border border-slate-600/40 rounded-xl hover:border-slate-500/60 hover:bg-slate-700/60 transition-all duration-200 w-full text-left disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <div class="w-10 h-10 rounded-xl bg-slate-700 flex items-center justify-center flex-shrink-0 group-hover:bg-slate-600 transition-colors">
                    <svg
                        class="w-5 h-5 text-slate-300 transition-transform duration-500"
                        :class="loading ? 'animate-spin' : 'group-hover:rotate-180'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white group-hover:text-slate-200 transition-colors">Actualizar</p>
                    <p class="text-xs text-slate-400 truncate">{{ loading ? 'Actualizando...' : 'Refrescar datos' }}</p>
                </div>
            </button>

        </div>
    </div>
</template>

<script setup>
defineProps({
    loading: { type: Boolean, default: false },
});

defineEmits(['pagar', 'actualizar']);
</script>
