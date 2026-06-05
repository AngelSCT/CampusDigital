<template>
    <Head title="Turnos" />
    <AuthLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">
                    Sistema de Turnos
                </h1>
                <p class="mt-1 text-sm text-slate-400">Solicita un turno para atención o recolección</p>
            </div>

            <!-- Mi turno activo -->
            <div v-if="miTurno" class="bg-gradient-to-br from-emerald-900/40 to-teal-800/30 rounded-xl border border-emerald-500/30 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-xs text-emerald-300 uppercase tracking-wider">Tu turno</p>
                        <p class="text-5xl font-bold text-white mt-1">{{ miTurno.numero_turno }}</p>
                        <p class="text-sm text-slate-300 mt-1">
                            {{ tipos[miTurno.tipo_turno] }} · Posición #{{ miTurno.posicion }}
                        </p>
                        <p class="text-xs mt-2" :class="miTurno.estado === 'llamado' ? 'text-amber-300 font-semibold' : 'text-slate-400'">
                            {{ miTurno.estado === 'llamado' ? '🔔 ¡Te están llamando!' : 'En espera...' }}
                        </p>
                    </div>
                    <button @click="cancelarTurno" :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 bg-red-600/20 border border-red-500/40 text-red-300 hover:bg-red-600/30 text-sm font-medium rounded-lg">
                        Cancelar turno
                    </button>
                </div>
            </div>

            <!-- Form para pedir turno -->
            <div v-else class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-emerald-500/20 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Solicitar un turno</h2>
                <form @submit.prevent="pedirTurno" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Tipo de turno</label>
                        <select v-model="form.tipo_turno" required
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/50 sm:text-sm">
                            <option value="">Selecciona un tipo</option>
                            <option v-for="(label, key) in tipos" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Notas (opcional)</label>
                        <input v-model="form.notas" type="text" maxlength="500" placeholder="Ej. Recoger pedido #12345"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/50 sm:text-sm" />
                    </div>
                    <div v-if="$page.props.flash?.error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3">
                        <p class="text-sm text-red-300">{{ $page.props.flash.error }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing || !form.tipo_turno"
                        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-br from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white text-sm font-medium rounded-lg transition-all disabled:opacity-50">
                        {{ form.processing ? 'Generando...' : 'Solicitar turno' }}
                    </button>
                </form>
            </div>

            <!-- Estadísticas por tipo de turno -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="(stats, tipo) in estadisticasPorTipo" :key="tipo"
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-4">
                    <h3 class="text-sm font-semibold text-slate-300">{{ tipos[tipo] }}</h3>
                    <div class="grid grid-cols-3 gap-2 mt-3 text-xs">
                        <div>
                            <p class="text-slate-400">En cola</p>
                            <p class="text-2xl font-bold text-amber-400">{{ stats.esperando }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400">Atendidos</p>
                            <p class="text-2xl font-bold text-emerald-400">{{ stats.atendidos }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400">No-show</p>
                            <p class="text-2xl font-bold text-red-400">{{ stats.no_show }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    miTurno:             Object,
    tipos:               Object,
    estadisticasPorTipo: Object,
});

const form = useForm({
    tipo_turno: '',
    notas: '',
});

const pedirTurno = () => {
    form.post('/turnos', { preserveScroll: true });
};

const cancelarTurno = () => {
    if (confirm('¿Cancelar tu turno?')) {
        router.delete(`/turnos/${props.miTurno.id_turno}`, { preserveScroll: true });
    }
};
</script>
