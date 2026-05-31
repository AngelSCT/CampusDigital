<template>
    <Head :title="`Tokens — ${solicitud.nombre_modulo}`" />
    <AuthLayout>
        <div class="max-w-2xl mx-auto space-y-6">

            <!-- Header -->
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="/admin/cart/modulos" class="text-slate-400 hover:text-slate-300 text-sm transition-colors">← Módulos</a>
                    <span class="text-slate-600">/</span>
                    <span class="text-slate-400 text-sm">Entrega de tokens</span>
                </div>
                <h1 class="text-2xl font-bold text-white">{{ solicitud.nombre_modulo }}</h1>
                <p class="text-sm text-slate-400 mt-1">
                    Módulo:
                    <code class="text-violet-300 bg-violet-500/10 px-1.5 py-0.5 rounded text-xs">{{ modulo?.slug }}</code>
                </p>
            </div>

            <!-- Ya entregados -->
            <div v-if="alreadyDelivered"
                 class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-yellow-500/30 shadow-xl p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-yellow-500/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-yellow-400 font-semibold text-lg mb-2">Tokens ya entregados</p>
                <p class="text-slate-400 text-sm mb-5">
                    Estos tokens ya fueron visualizados anteriormente. Si se perdieron,
                    revoca los tokens actuales y emite un par nuevo desde la pantalla del módulo.
                </p>
                <a href="/admin/cart/modulos"
                   class="inline-flex items-center text-violet-400 hover:text-violet-300 text-sm transition-colors">
                    ← Volver a módulos
                </a>
            </div>

            <!-- Tokens disponibles (one-time) -->
            <template v-else>
                <!-- Banner de advertencia -->
                <div class="bg-red-500/10 border border-red-500/40 rounded-xl p-4 flex gap-3">
                    <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-red-400 text-sm">Estos tokens NO se podrán volver a ver.</p>
                        <p class="text-red-400/80 text-xs mt-1">
                            Si los pierdes, deberás revocar estos tokens y emitir nuevos desde el panel de módulos.
                            Entrégalos al equipo cliente por un canal seguro.
                        </p>
                    </div>
                </div>

                <!-- Access Token -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/5 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-white text-sm">Access Token</h2>
                        <span class="text-xs text-slate-500 bg-slate-700/50 px-2 py-0.5 rounded">TTL: 1 hora</span>
                    </div>
                    <TokenField :token="accessToken" label="access_token" />
                </div>

                <!-- Refresh Token -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-purple-500/20 shadow-xl shadow-purple-500/5 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-white text-sm">Refresh Token</h2>
                        <span class="text-xs text-slate-500 bg-slate-700/50 px-2 py-0.5 rounded">TTL: 7 días</span>
                    </div>
                    <TokenField :token="refreshToken" label="refresh_token" />
                </div>

                <p class="text-slate-600 text-xs text-center">
                    Una vez que navegues fuera de esta página, los tokens no serán recuperables desde el sistema.
                </p>
            </template>
        </div>
    </AuthLayout>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    solicitud:        Object,
    modulo:           Object,
    accessToken:      String,
    refreshToken:     String,
    alreadyDelivered: Boolean,
});

// Sub-componente inline: mostrar/ocultar/copiar token con auto-hide en 30s
const TokenField = {
    props: { token: String, label: String },
    template: `
        <div>
            <div class="flex gap-2 items-center">
                <input
                    :type="visible ? 'text' : 'password'"
                    :value="token"
                    readonly
                    class="flex-1 font-mono text-xs bg-slate-700/50 border border-slate-600 text-white rounded-lg px-3 py-2 min-w-0 focus:outline-none"
                />
                <button @click="toggleVisible" type="button"
                        class="px-3 py-2 text-xs bg-slate-600/50 hover:bg-slate-500/50 text-slate-300 hover:text-white border border-slate-600 rounded-lg whitespace-nowrap transition-colors">
                    {{ visible ? 'Ocultar' : 'Mostrar 30s' }}
                </button>
                <button @click="copiar" type="button"
                        class="px-3 py-2 text-xs bg-violet-500/20 hover:bg-violet-500/30 text-violet-300 border border-violet-500/30 rounded-lg whitespace-nowrap transition-colors">
                    {{ copiado ? '✓ Copiado' : 'Copiar' }}
                </button>
            </div>
            <p v-if="visible && segundosRestantes > 0" class="text-xs text-amber-400 mt-1.5">
                Se ocultará en {{ segundosRestantes }}s.
            </p>
        </div>
    `,
    setup(props) {
        const visible           = ref(false);
        const copiado           = ref(false);
        const segundosRestantes = ref(0);
        let timer = null;

        function toggleVisible() {
            if (visible.value) {
                visible.value = false;
                clearInterval(timer);
                segundosRestantes.value = 0;
                return;
            }
            visible.value = true;
            segundosRestantes.value = 30;
            timer = setInterval(() => {
                segundosRestantes.value--;
                if (segundosRestantes.value <= 0) {
                    visible.value = false;
                    clearInterval(timer);
                }
            }, 1000);
        }

        async function copiar() {
            if (!props.token) return;
            await navigator.clipboard.writeText(props.token);
            copiado.value = true;
            setTimeout(() => (copiado.value = false), 3000);
        }

        onUnmounted(() => clearInterval(timer));

        return { visible, copiado, segundosRestantes, toggleVisible, copiar };
    },
};
</script>
