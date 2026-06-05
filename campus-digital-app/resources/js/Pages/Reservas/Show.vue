<template>
    <Head title="Detalle de reserva" />
    <AuthLayout>
        <div class="space-y-6 max-w-3xl">
            <div>
                <Link href="/reservas" class="text-sm text-cyan-400 hover:text-cyan-300">← Volver a reservas</Link>
                <div class="flex items-center gap-3 mt-2">
                    <h1 class="text-3xl font-bold text-white">Reserva #{{ reserva.id_reserva }}</h1>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold"
                        :class="{
                            'bg-green-500/20 text-green-300':   reserva.estado === 'confirmada',
                            'bg-yellow-500/20 text-yellow-300': reserva.estado === 'pendiente',
                            'bg-red-500/20 text-red-300':       reserva.estado === 'cancelada',
                            'bg-slate-500/20 text-slate-300':   reserva.estado === 'completada' || reserva.estado === 'no_show',
                        }">
                        {{ reserva.estado }}
                    </span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-slate-400">Recurso</p>
                        <p class="text-white font-semibold">{{ reserva.recurso?.nombre }}</p>
                        <p class="text-xs text-slate-500 capitalize">{{ reserva.recurso?.tipo }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400">Ubicación</p>
                        <p class="text-white font-semibold">{{ reserva.recurso?.ubicacion?.edificio ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-500">{{ reserva.recurso?.ubicacion?.aula_departamento ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400">Inicio</p>
                        <p class="text-white font-semibold">{{ formatDate(reserva.fecha_inicio) }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400">Fin</p>
                        <p class="text-white font-semibold">{{ formatDate(reserva.fecha_fin) }}</p>
                    </div>
                    <div v-if="reserva.cobro_saldo">
                        <p class="text-slate-400">Cargo aplicado</p>
                        <p class="text-white font-semibold">${{ reserva.monto_cobrado }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400">Duración</p>
                        <p class="text-white font-semibold">{{ duracionHoras(reserva) }} horas</p>
                    </div>
                </div>

                <div v-if="reserva.proposito" class="pt-3 border-t border-slate-700">
                    <p class="text-sm text-slate-400">Propósito</p>
                    <p class="text-sm text-white mt-1">{{ reserva.proposito }}</p>
                </div>

                <div v-if="reserva.motivo_cancelacion" class="pt-3 border-t border-slate-700">
                    <p class="text-sm text-slate-400">Motivo de cancelación</p>
                    <p class="text-sm text-red-300 mt-1">{{ reserva.motivo_cancelacion }}</p>
                </div>
            </div>

            <div v-if="canCancel" class="flex gap-3">
                <button @click="cancelar" :disabled="form.processing"
                    class="inline-flex items-center px-4 py-2 bg-red-600/20 border border-red-500/40 text-red-300 hover:bg-red-600/30 text-sm font-medium rounded-lg transition-all">
                    {{ form.processing ? 'Cancelando...' : 'Cancelar reserva' }}
                </button>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    reserva: Object,
});

const form = useForm({});

const canCancel = computed(() => {
    return ['pendiente', 'confirmada'].includes(props.reserva.estado);
});

const formatDate = (d) => new Date(d).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });

const duracionHoras = (r) => {
    const seg = (new Date(r.fecha_fin) - new Date(r.fecha_inicio)) / 1000;
    return (seg / 3600).toFixed(2);
};

const cancelar = () => {
    if (confirm('¿Cancelar esta reserva?')) {
        form.delete(`/reservas/${props.reserva.id_reserva}`, { preserveScroll: true });
    }
};
</script>
