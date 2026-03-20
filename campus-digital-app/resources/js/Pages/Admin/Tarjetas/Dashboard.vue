<template>
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        Dashboard · Tarjetas RFID/NFC
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Monitoreo en tiempo real del módulo de identificación</p>
                </div>
                <div class="flex gap-3">
                    <a :href="route('admin.tarjetas.reportes.index')"
                       class="inline-flex items-center px-4 py-2 border border-cyan-500/30 rounded-lg text-sm text-cyan-400 hover:bg-cyan-500/10 transition-all duration-200">
                        Reportes
                    </a>
                    <a :href="route('admin.tarjetas.index')"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-cyan-500/20 transition-all duration-200">
                        Gestionar Tarjetas
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Total Tarjetas</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.total_tarjetas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-green-500/20 p-5">
                    <p class="text-xs text-green-400 uppercase tracking-wider">Activas</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.activas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 p-5">
                    <p class="text-xs text-red-400 uppercase tracking-wider">Bloqueadas</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.bloqueadas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-5">
                    <p class="text-xs text-cyan-400 uppercase tracking-wider">Lecturas Hoy</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.lecturas_hoy }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-5">
                    <p class="text-xs text-blue-400 uppercase tracking-wider">Esta Semana</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.lecturas_semana }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 shadow-xl shadow-cyan-500/5 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Lecturas por Día (últimos 14 días)</h2>
                    <div class="flex items-end gap-1 h-32">
                        <div v-for="dia in lecturasPorDia" :key="dia.fecha"
                             class="flex-1 flex flex-col items-center gap-1 group">
                            <div class="relative w-full flex flex-col justify-end" style="height: 100px;">
                                <div
                                    :style="{ height: maxLecturas > 0 ? (dia.total / maxLecturas * 100) + '%' : '2px' }"
                                    class="w-full bg-gradient-to-t from-cyan-600 to-blue-500 rounded-t transition-all duration-300 group-hover:from-cyan-500 group-hover:to-blue-400 min-h-[2px]">
                                </div>
                                <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-slate-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                                    {{ dia.total }} lecturas
                                </div>
                            </div>
                            <span class="text-xs text-slate-500 w-full text-center truncate" style="font-size:9px;">
                                {{ formatDia(dia.fecha) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-4 mt-4">
                        <span class="flex items-center gap-1.5 text-xs text-slate-400">
                            <span class="w-3 h-3 rounded-full bg-gradient-to-r from-cyan-600 to-blue-500 inline-block"></span>
                            Total lecturas
                        </span>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/5 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Uso por Módulo (30 días)</h2>
                    <div v-if="lecturasPorModulo.length === 0" class="text-center py-6 text-slate-500 text-sm">Sin datos</div>
                    <div v-else class="space-y-3">
                        <div v-for="m in lecturasPorModulo" :key="m.modulo">
                            <div class="flex justify-between text-xs text-slate-400 mb-1">
                                <span class="capitalize">{{ m.modulo.replace('_', ' ') }}</span>
                                <span>{{ m.total }}</span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-1.5">
                                <div
                                    :style="{ width: maxModulo > 0 ? (m.total / maxModulo * 100) + '%' : '0%' }"
                                    class="h-1.5 rounded-full bg-gradient-to-r from-blue-600 to-cyan-500 transition-all duration-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-700">
                        <h2 class="text-base font-semibold text-white">Usuarios Más Activos (30 días)</h2>
                    </div>
                    <div class="divide-y divide-slate-700/50">
                        <div v-if="usuariosActivos.length === 0" class="px-6 py-8 text-center text-slate-500 text-sm">Sin actividad reciente</div>
                        <div v-for="(item, i) in usuariosActivos.slice(0, 5)" :key="item.id"
                             class="flex items-center gap-4 px-6 py-3 hover:bg-slate-700/20 transition-colors duration-150">
                            <span class="text-lg font-bold text-slate-500 w-6 text-center">{{ i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">
                                    {{ item.usuario?.nombre }} {{ item.usuario?.apellido }}
                                </p>
                                <p class="text-xs text-slate-400 truncate font-mono">{{ item.uid }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-cyan-400">{{ item.total_lecturas }}</p>
                                <p class="text-xs text-slate-500">lecturas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-red-500/20 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-white">Tarjetas Bloqueadas</h2>
                        <a :href="route('admin.tarjetas.index') + '?estado=bloqueada'"
                           class="text-xs text-red-400 hover:text-red-300 transition-colors duration-200">Ver todas →</a>
                    </div>
                    <div class="divide-y divide-slate-700/50">
                        <div v-if="tarjetasBloqueadas.length === 0" class="px-6 py-8 text-center text-slate-500 text-sm">
                            No hay tarjetas bloqueadas
                        </div>
                        <div v-for="t in tarjetasBloqueadas" :key="t.id"
                             class="flex items-center gap-3 px-6 py-3 hover:bg-slate-700/20 transition-colors duration-150">
                            <div class="w-2 h-2 rounded-full flex-shrink-0"
                                 :class="t.estado === 'perdida' ? 'bg-yellow-400' : 'bg-red-400'"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">
                                    {{ t.usuario?.nombre }} {{ t.usuario?.apellido }}
                                </p>
                                <p class="text-xs text-slate-400 font-mono truncate">{{ t.uid }}</p>
                            </div>
                            <div class="text-right">
                                <span :class="t.estado === 'perdida' ? 'text-yellow-400' : 'text-red-400'"
                                      class="text-xs capitalize">{{ t.estado }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h2 class="text-base font-semibold text-white">Lecturas Recientes</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Resultado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <tr v-if="lecturasRecientes.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">Sin lecturas recientes</td>
                            </tr>
                            <tr v-for="l in lecturasRecientes" :key="l.id" class="hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-6 py-3 text-sm text-slate-400 whitespace-nowrap">{{ formatDateTime(l.created_at) }}</td>
                                <td class="px-6 py-3 text-sm text-white">
                                    {{ l.tarjeta?.usuario ? `${l.tarjeta.usuario.nombre} ${l.tarjeta.usuario.apellido}` : 'Desconocido' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-300 capitalize">{{ l.modulo.replace('_', ' ') }}</td>
                                <td class="px-6 py-3 text-sm text-slate-300 capitalize">{{ l.tipo_lectura.replace('_', ' ') }}</td>
                                <td class="px-6 py-3">
                                    <span :class="l.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                                        {{ l.exito ? 'Exitoso' : 'Fallido' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    stats: Object,
    lecturasPorDia: Array,
    lecturasPorModulo: Array,
    usuariosActivos: Array,
    tarjetasBloqueadas: Array,
    lecturasRecientes: Array,
});

const maxLecturas = computed(() => Math.max(...props.lecturasPorDia.map(d => d.total), 1));
const maxModulo   = computed(() => Math.max(...props.lecturasPorModulo.map(m => m.total), 1));

function formatDia(fecha) {
    const d = new Date(fecha + 'T00:00:00');
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
}
function formatDateTime(d) {
    if (!d) return '-';
    return new Date(d).toLocaleString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}
</script>