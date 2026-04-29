<script setup>
import { ref } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
    pedidos: Object, estados: Array, modulos: Array,
    esAdmin: Boolean, esOperador: Boolean, filtros: Object,
})

const filtros = ref({ ...props.filtros })

const colores = {
    creado:     'bg-blue-900/40 text-blue-300 border border-blue-700',
    aceptado:   'bg-yellow-900/40 text-yellow-300 border border-yellow-700',
    en_proceso: 'bg-orange-900/40 text-orange-300 border border-orange-700',
    listo:      'bg-purple-900/40 text-purple-300 border border-purple-700',
    entregado:  'bg-green-900/40 text-green-300 border border-green-700',
    cancelado:  'bg-red-900/40 text-red-300 border border-red-700',
}
const etiquetas = {
    creado:'Creado', aceptado:'Aceptado', en_proceso:'En proceso',
    listo:'Listo', entregado:'Entregado', cancelado:'Cancelado',
}

function buscar() {
    router.get(route('pedidos.index'), filtros.value, { preserveState: true, replace: true })
}
function limpiar() {
    filtros.value = {}
    router.get(route('pedidos.index'))
}
function formatFecha(d) {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('es-MX', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })
}
const page = usePage()
</script>

<template>
    <AuthLayout title="Pedidos">
        <div class="max-w-7xl mx-auto px-4 py-8">

            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-white">
                        {{ esAdmin ? 'Todos los pedidos' : 'Mis pedidos' }}
                    </h1>
                    <p class="text-sm text-gray-400 mt-1">Historial y seguimiento de pedidos</p>
                </div>
                <div class="flex gap-2">
                    <Link v-if="esOperador" href="/pedidos/panel/operador"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition">
                        Panel Operador
                    </Link>
                    <Link v-if="esAdmin" href="/pedidos/panel/dashboard"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                        Dashboard
                    </Link>
                    <Link v-if="esAdmin" href="/pedidos/panel/reportes"
                        class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-600 transition">
                        Reportes
                    </Link>
                </div>
            </div>

            <!-- Flash -->
            <div v-if="page.props.flash?.success"
                class="mb-4 p-3 bg-green-900/40 border border-green-700 text-green-300 rounded-lg text-sm">
                {{ page.props.flash.success }}
            </div>

            <!-- Filtros -->
            <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] p-4 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    <select v-model="filtros.estado" class="input-dark">
                        <option value="">Todos los estados</option>
                        <option v-for="e in estados" :key="e" :value="e">{{ etiquetas[e] }}</option>
                    </select>
                    <select v-model="filtros.modulo" class="input-dark">
                        <option value="">Todos los módulos</option>
                        <option v-for="m in modulos" :key="m" :value="m" class="capitalize">{{ m }}</option>
                    </select>
                    <input v-model="filtros.fecha_desde" type="date" class="input-dark" />
                    <input v-model="filtros.fecha_hasta" type="date" class="input-dark" />
                    <input v-model="filtros.buscar" type="text" class="input-dark" placeholder="Buscar folio..." />
                </div>
                <div class="flex gap-2 mt-3">
                    <button @click="buscar" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">
                        Buscar
                    </button>
                    <button @click="limpiar" class="px-4 py-2 bg-[#2d2d3f] text-gray-300 rounded-lg text-sm font-medium hover:bg-[#3d3d4f]">
                        Limpiar
                    </button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-[#1e1e2e] rounded-xl border border-[#2d2d3f] overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-[#16213e] border-b border-[#2d2d3f]">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-400 uppercase text-xs tracking-wide">Folio</th>
                            <th v-if="esAdmin" class="text-left px-4 py-3 font-semibold text-gray-400 uppercase text-xs tracking-wide">Usuario</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-400 uppercase text-xs tracking-wide">Módulo</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-400 uppercase text-xs tracking-wide">Estado</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-400 uppercase text-xs tracking-wide">Total</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-400 uppercase text-xs tracking-wide">Fecha</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-400 uppercase text-xs tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="pedidos.data.length === 0">
                            <td :colspan="esAdmin ? 7 : 6" class="text-center py-12 text-gray-500">
                                No hay pedidos que mostrar.
                            </td>
                        </tr>
                        <tr v-for="p in pedidos.data" :key="p.id"
                            class="border-b border-[#2d2d3f] hover:bg-[#16213e] transition">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-purple-400">{{ p.numero_folio }}</td>
                            <td v-if="esAdmin" class="px-4 py-3 text-gray-300">
                                {{ p.usuario?.nombre }} {{ p.usuario?.apellido }}
                            </td>
                            <td class="px-4 py-3 capitalize text-gray-400">{{ p.modulo }}</td>
                            <td class="px-4 py-3">
                                <span :class="['px-2 py-1 rounded-full text-xs font-semibold', colores[p.estado]]">
                                    {{ etiquetas[p.estado] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-white">
                                ${{ Number(p.total).toFixed(2) }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ formatFecha(p.created_at) }}</td>
                            <td class="px-4 py-3 text-center">
                                <a :href="`/pedidos/${p.id}`"
                                    class="text-purple-400 hover:text-purple-300 font-medium text-xs">
                                    Ver detalle →
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="pedidos.last_page > 1" class="flex justify-between items-center px-4 py-3 border-t border-[#2d2d3f] bg-[#16213e]">
                    <span class="text-xs text-gray-500">
                        Mostrando {{ pedidos.from }}–{{ pedidos.to }} de {{ pedidos.total }} pedidos
                    </span>
                    <div class="flex gap-1">
                        <Link v-for="link in pedidos.links" :key="link.label"
                            :href="link.url ?? '#'"
                            :class="['px-3 py-1 text-xs rounded border transition',
                                link.active ? 'bg-purple-600 text-white border-purple-600' : 'bg-[#1e1e2e] text-gray-400 border-[#2d2d3f] hover:bg-[#2d2d3f]',
                                !link.url ? 'opacity-40 pointer-events-none' : '']"
                            v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.input-dark {
    @apply w-full bg-[#16213e] border border-[#2d2d3f] rounded-lg px-3 py-2 text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500;
}
</style>