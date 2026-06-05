<template>
    <Head title="Admin — Detalle reserva" />
    <AuthLayout>
        <div class="space-y-6 max-w-3xl">
            <div>
                <a href="/admin/reservas" class="text-sm text-cyan-400 hover:text-cyan-300">← Volver a reservas</a>
                <div class="flex items-center gap-3 mt-2">
                    <h1 class="text-3xl font-bold text-white">Reserva #{{ reserva.id_reserva }}</h1>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold"
                        :class="{
                            'bg-green-500/20 text-green-300':   reserva.estado === 'confirmada',
                            'bg-yellow-500/20 text-yellow-300': reserva.estado === 'pendiente',
                            'bg-red-500/20 text-red-300':       reserva.estado === 'cancelada',
                            'bg-slate-500/20 text-slate-300':   ['completada','no_show'].includes(reserva.estado),
                        }">
                        {{ reserva.estado }}
                    </span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-400">Usuario</p>
                    <p class="text-white font-semibold">{{ reserva.usuario?.nombre }} {{ reserva.usuario?.apellido }}</p>
                    <p class="text-xs text-slate-500">{{ reserva.usuario?.email }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Recurso</p>
                    <p class="text-white font-semibold">{{ reserva.recurso?.nombre }}</p>
                    <p class="text-xs text-slate-500">{{ reserva.recurso?.ubicacion?.edificio ?? 'N/A' }}</p>
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
                    <p class="text-slate-400">Cobro</p>
                    <p class="text-white font-semibold">${{ reserva.monto_cobrado }}</p>
                </div>
                <div v-if="reserva.motivo_cancelacion">
                    <p class="text-slate-400">Cancelada por</p>
                    <p class="text-white font-semibold">{{ reserva.usuario_cancela?.nombre ?? 'N/A' }}</p>
                </div>
            </div>

            <div v-if="reserva.proposito" class="bg-slate-800/50 rounded-xl border border-slate-700 p-4">
                <p class="text-sm text-slate-400">Propósito</p>
                <p class="text-sm text-white mt-1">{{ reserva.proposito }}</p>
            </div>

            <div v-if="reserva.motivo_cancelacion" class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                <p class="text-sm text-red-300">{{ reserva.motivo_cancelacion }}</p>
            </div>

            <!-- Acciones admin -->
            <div v-if="canChange" class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-6">
                <h3 class="text-sm font-semibold text-white mb-3">Cambiar estado</h3>
                <form @submit.prevent="submit" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Nuevo estado</label>
                        <select v-model="form.estado" class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm">
                            <option value="confirmada">Confirmada</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="completada">Completada</option>
                            <option value="no_show">No Show</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div v-if="form.estado === 'cancelada'">
                        <label class="block text-sm font-medium text-white mb-2">Motivo</label>
                        <input v-model="form.motivo_cancelacion" type="text" maxlength="500"
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white text-sm" />
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-700 text-white text-sm font-medium rounded-lg disabled:opacity-50">
                        {{ form.processing ? 'Actualizando...' : 'Actualizar reserva' }}
                    </button>
                </form>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    reserva: Object,
});

const form = useForm({
    estado:              props.reserva.estado,
    motivo_cancelacion:  '',
});

const canChange = computed(() => !['completada'].includes(props.reserva.estado));

const formatDate = (d) => new Date(d).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });

const submit = () => form.patch(`/admin/reservas/${props.reserva.id_reserva}`, { preserveScroll: true });
</script>
