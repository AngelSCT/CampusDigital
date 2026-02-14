<template>
    <AuthLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Bitácora de Accesos
                    </h1>
                    <p class="mt-1 text-sm text-white">Registro de intentos de acceso al sistema</p>
                </div>
                <div class="relative" v-click-away="() => showExportMenu = false">
                    <button @click="toggleExportMenu" type="button" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg shadow-blue-500/30">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Menú desplegable -->
                    <div v-if="showExportMenu" class="absolute right-0 mt-2 w-72 rounded-lg shadow-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-blue-500/20 z-10">
                        <div class="py-1">
                            <!-- Exportar todos - CSV -->
                            <a :href="route('admin.bitacora.export-accesos')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div>
                                    <div class="font-medium">Todos los accesos (CSV)</div>
                                    <div class="text-xs text-slate-400">Exportar registro completo</div>
                                </div>
                            </a>

                            <!-- Exportar todos - PDF -->
                            <a :href="route('admin.bitacora.export-accesos-pdf')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200 border-t border-slate-700">
                                <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <div class="font-medium">Todos los accesos (PDF)</div>
                                    <div class="text-xs text-slate-400">Documento completo</div>
                                </div>
                            </a>

                            <!-- Separador -->
                            <div class="border-t border-slate-700 my-1"></div>
                            <div class="px-4 py-2">
                                <p class="text-xs font-semibold text-slate-400 uppercase">Reportes por Período</p>
                            </div>

                            <!-- Por período - CSV -->
                            <button @click="showPeriodModal('csv')" type="button" class="w-full flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="text-left">
                                    <div class="font-medium">Accesos por período (CSV)</div>
                                    <div class="text-xs text-slate-400">Seleccionar fechas</div>
                                </div>
                            </button>

                            <!-- Por período - PDF -->
                            <button @click="showPeriodModal('pdf')" type="button" class="w-full flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200 border-t border-slate-700">
                                <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="text-left">
                                    <div class="font-medium">Accesos por período (PDF)</div>
                                    <div class="text-xs text-slate-400">Reporte detallado</div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl p-6 border border-blue-500/20">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Buscar Email</label>
                        <input 
                            v-model="form.search" 
                            type="text" 
                            placeholder="Buscar por email..."
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-slate-400 transition-all duration-200"
                        />
                    </div>

                    <!-- Evento -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Evento</label>
                        <select 
                            v-model="form.evento"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        >
                            <option value="">Todos</option>
                            <option v-for="evento in eventos" :key="evento" :value="evento">{{ evento }}</option>
                        </select>
                    </div>

                    <!-- Éxito -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Estado</label>
                        <select 
                            v-model="form.exito"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        >
                            <option value="">Todos</option>
                            <option value="si">Exitosos</option>
                            <option value="no">Fallidos</option>
                        </select>
                    </div>

                    <!-- Fecha Desde -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Desde</label>
                        <input 
                            v-model="form.fecha_desde" 
                            type="date"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        />
                    </div>

                    <!-- Fecha Hasta -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Hasta</label>
                        <input 
                            v-model="form.fecha_hasta" 
                            type="date"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        />
                    </div>

                    <!-- Botones -->
                    <div class="lg:col-span-5 flex gap-2">
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg shadow-blue-500/30"
                        >
                            Aplicar Filtros
                        </button>
                        <button 
                            type="button"
                            @click="clearFilters"
                            class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all duration-200"
                        >
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl overflow-hidden border border-blue-500/20">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Evento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">IP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="acceso in accesos.data" :key="acceso.id" class="hover:bg-slate-700/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    {{ acceso.id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    {{ acceso.email_intentado || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                    {{ acceso.evento }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="acceso.exito ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'bg-red-500/20 text-red-400 border-red-500/30'" 
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                    >
                                        {{ acceso.exito ? 'Éxito' : 'Fallido' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                    {{ acceso.ip || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                    {{ formatDate(acceso.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <button 
                                        @click="showDetails(acceso)"
                                        class="text-blue-400 hover:text-blue-300 transition-colors duration-200"
                                    >
                                        Ver detalle
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="accesos.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 mb-4 rounded-full bg-slate-700/50 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-white">No se encontraron registros</p>
                                        <p class="text-xs text-slate-400 mt-1">Intenta ajustar los filtros de búsqueda</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="accesos.data.length > 0" class="bg-slate-900 px-4 py-3 border-t border-slate-700 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-white">
                            Mostrando 
                            <span class="font-medium text-blue-400">{{ accesos.from }}</span>
                            a 
                            <span class="font-medium text-blue-400">{{ accesos.to }}</span>
                            de 
                            <span class="font-medium text-blue-400">{{ accesos.total }}</span>
                            resultados
                        </div>
                        <div class="flex gap-2">
                            <a 
                                v-for="link in accesos.links" 
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm rounded-lg transition-all duration-200',
                                    link.active 
                                        ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg shadow-blue-500/30' 
                                        : 'bg-slate-700 text-white hover:bg-slate-600',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Detalles -->
        <div v-if="showModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50" @click="showModal = false">
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-6 max-w-2xl w-full mx-4 border border-blue-500/20 shadow-2xl shadow-blue-500/20" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-white">Detalles del Acceso</h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-white transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div v-if="selectedAcceso" class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-400">ID</p>
                            <p class="text-sm text-white">{{ selectedAcceso.id }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-400">Email</p>
                            <p class="text-sm text-white">{{ selectedAcceso.email_intentado || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-400">Evento</p>
                            <p class="text-sm text-white">{{ selectedAcceso.evento }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-400">Estado</p>
                            <span 
                                :class="selectedAcceso.exito ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'bg-red-500/20 text-red-400 border-red-500/30'" 
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border"
                            >
                                {{ selectedAcceso.exito ? 'Éxito' : 'Fallido' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-400">IP</p>
                            <p class="text-sm text-white">{{ selectedAcceso.ip || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-400">Fecha</p>
                            <p class="text-sm text-white">{{ formatDate(selectedAcceso.created_at) }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm font-medium text-slate-400">User Agent</p>
                            <p class="text-sm text-white break-all">{{ selectedAcceso.user_agent || 'N/A' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm font-medium text-slate-400">Detalle</p>
                            <p class="text-sm text-white">{{ selectedAcceso.detalle || 'Sin detalles' }}</p>
                        </div>
                        <div v-if="selectedAcceso.meta_json" class="col-span-2">
                            <p class="text-sm font-medium text-slate-400 mb-2">Metadata</p>
                            <pre class="text-xs text-white bg-slate-900 p-3 rounded-lg border border-slate-700 overflow-x-auto">{{ JSON.stringify(selectedAcceso.meta_json, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal de Selección de Período -->
<div v-if="showPeriodModalFlag" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50" @click="closePeriodModal">
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-6 max-w-md w-full mx-4 border border-blue-500/20 shadow-2xl shadow-blue-500/20" @click.stop>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-white">Seleccionar Período</h3>
            <button @click="closePeriodModal" class="text-slate-400 hover:text-white transition-colors duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form @submit.prevent="exportByPeriod" class="space-y-4">
            <!-- Fecha Desde -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">Fecha Desde *</label>
                <input 
                    v-model="periodForm.fecha_desde" 
                    type="date"
                    required
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                />
            </div>

            <!-- Fecha Hasta -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">Fecha Hasta *</label>
                <input 
                    v-model="periodForm.fecha_hasta" 
                    type="date"
                    required
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                />
            </div>

            <!-- Filtros opcionales -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">Evento (Opcional)</label>
                <select 
                    v-model="periodForm.evento"
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                >
                    <option value="">Todos los eventos</option>
                    <option v-for="evento in eventos" :key="evento" :value="evento">{{ evento }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-white mb-2">Estado (Opcional)</label>
                <select 
                    v-model="periodForm.exito"
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                >
                    <option value="">Todos</option>
                    <option value="si">Solo exitosos</option>
                    <option value="no">Solo fallidos</option>
                </select>
            </div>

            <!-- Botones -->
            <div class="flex gap-2 pt-4">
                <button 
                    type="button"
                    @click="closePeriodModal"
                    class="flex-1 px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all duration-200"
                >
                    Cancelar
                </button>
                <button 
                    type="submit"
                    class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg shadow-blue-500/30"
                >
                    Exportar {{ exportType.toUpperCase() }}
                </button>
            </div>
        </form>
    </div>
</div>
    </AuthLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    accesos: Object,
    eventos: Array,
    filters: Object,
});

const form = reactive({
    search: props.filters?.search || '',
    evento: props.filters?.evento || '',
    exito: props.filters?.exito || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || '',
});

const showModal = ref(false);
const selectedAcceso = ref(null);
const showExportMenu = ref(false);
const showPeriodModalFlag = ref(false);
const exportType = ref('csv');

const periodForm = reactive({
    fecha_desde: '',
    fecha_hasta: '',
    evento: '',
    exito: '',
});

function toggleExportMenu() {
    showExportMenu.value = !showExportMenu.value;
}

function showPeriodModal(type) {
    exportType.value = type;
    showPeriodModalFlag.value = true;
    showExportMenu.value = false;
}

function closePeriodModal() {
    showPeriodModalFlag.value = false;
    periodForm.fecha_desde = '';
    periodForm.fecha_hasta = '';
    periodForm.evento = '';
    periodForm.exito = '';
}

function exportByPeriod() {
    const params = new URLSearchParams();
    params.append('fecha_desde', periodForm.fecha_desde);
    params.append('fecha_hasta', periodForm.fecha_hasta);
    if (periodForm.evento) params.append('evento', periodForm.evento);
    if (periodForm.exito) params.append('exito', periodForm.exito);

    const route = exportType.value === 'csv' 
        ? 'admin.bitacora.export-accesos-periodo'
        : 'admin.bitacora.export-accesos-periodo-pdf';
    
    window.location.href = window.route(route) + '?' + params.toString();
    closePeriodModal();
}

function applyFilters() {
    router.get(route('admin.bitacora.accesos'), form, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    form.search = '';
    form.evento = '';
    form.exito = '';
    form.fecha_desde = '';
    form.fecha_hasta = '';
    applyFilters();
}

function formatDate(date) {
    return new Date(date).toLocaleString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function showDetails(acceso) {
    selectedAcceso.value = acceso;
    showModal.value = true;
}
</script>