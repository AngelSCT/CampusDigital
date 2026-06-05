<template>
    <Head title="Nueva reserva" />
    <AuthLayout>
        <div class="space-y-6">
            <div>
                <Link href="/reservas" class="text-sm text-cyan-400 hover:text-cyan-300">← Volver a recursos</Link>
                <h1 class="text-3xl font-bold text-white mt-2">Reservar: {{ recurso.nombre }}</h1>
                <p class="text-sm text-slate-400">{{ recurso.descripcion }}</p>
            </div>

            <!-- Info del recurso -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-slate-400">Tipo</p>
                    <p class="text-white font-semibold capitalize">{{ recurso.tipo }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Capacidad</p>
                    <p class="text-white font-semibold">{{ recurso.capacidad }} personas</p>
                </div>
                <div>
                    <p class="text-slate-400">Ubicación</p>
                    <p class="text-white font-semibold">{{ recurso.ubicacion?.edificio ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Costo</p>
                    <p class="text-white font-semibold">{{ recurso.costo_por_hora > 0 ? `$${recurso.costo_por_hora}/h` : 'Gratis' }}</p>
                </div>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Fecha y hora de inicio</label>
                        <input v-model="form.fecha_inicio" type="datetime-local" required
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/50 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Fecha y hora de fin</label>
                        <input v-model="form.fecha_fin" type="datetime-local" required
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/50 sm:text-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-white mb-2">Propósito (opcional)</label>
                    <textarea v-model="form.proposito" rows="3" maxlength="500"
                        placeholder="Describe el motivo de la reserva..."
                        class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/50 sm:text-sm"></textarea>
                </div>

                <div v-if="errors.error || Object.keys(errors).length > 0" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3">
                    <p v-for="(e, k) in errors" :key="k" class="text-sm text-red-300">{{ typeof e === 'string' ? e : Object.values(e).join(', ') }}</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-br from-cyan-600 to-blue-700 hover:from-cyan-500 hover:to-blue-600 text-white text-sm font-medium rounded-lg transition-all disabled:opacity-50">
                        {{ form.processing ? 'Reservando...' : 'Confirmar reserva' }}
                    </button>
                    <Link href="/reservas"
                        class="inline-flex items-center px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium rounded-lg transition-all">
                        Cancelar
                    </Link>
                </div>
            </form>

            <!-- Horarios ocupados del día -->
            <div v-if="ocupados && ocupados.length > 0" class="bg-slate-800/50 rounded-xl border border-slate-700 p-4">
                <h3 class="text-sm font-semibold text-white mb-3">Horarios ya reservados</h3>
                <div class="space-y-2">
                    <div v-for="o in ocupados" :key="o.id_reserva" class="flex items-center gap-3 text-sm">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold"
                            :class="o.estado === 'confirmada' ? 'bg-green-500/20 text-green-300' : 'bg-yellow-500/20 text-yellow-300'">
                            {{ o.estado }}
                        </span>
                        <span class="text-slate-300">{{ formatDate(o.fecha_inicio) }} → {{ formatDate(o.fecha_fin) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    recurso:  Object,
    fecha:    String,
    ocupados: Array,
    errors:   Object,
});

const form = useForm({
    fecha_inicio: '',
    fecha_fin:    '',
    proposito:    '',
});

const submit = () => {
    form.post('/reservas', { preserveScroll: true });
};

const formatDate = (d) => new Date(d).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
</script>
