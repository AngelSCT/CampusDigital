<script setup>
import { ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
    pedidos: Array,
    estados: Array,
    modulos: Array,
    moduloActual: String,
})

const colores = {
    creado:     'border-blue-500/40 bg-blue-950/30',
    aceptado:   'border-yellow-500/40 bg-yellow-950/30',
    en_proceso: 'border-orange-500/40 bg-orange-950/30',
    listo:      'border-purple-500/40 bg-purple-950/30',
    entregado:  'border-green-500/40 bg-green-950/30',
    cancelado:  'border-red-500/40 bg-red-950/30',
}
const badgeColores = {
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

const transiciones = {
    creado: [
        { val: 'aceptado', label: '✅ Aceptar', color: 'bg-yellow-600 hover:bg-yellow-500' },
    ],
    aceptado: [
        { val: 'en_proceso', label: '⚙️ En proceso', color: 'bg-orange-600 hover:bg-orange-500' },
        { val: 'cancelado',  label: '❌ Cancelar',   color: 'bg-red-700 hover:bg-red-600' },
    ],
    en_proceso: [
        { val: 'listo',     label: '🎉 Marcar listo', color: 'bg-purple-600 hover:bg-purple-500' },
        { val: 'cancelado', label: '❌ Cancelar',     color: 'bg-red-700 hover:bg-red-600' },
    ],
    listo: [
        { val: 'entregado', label: '📦 Entregar',  color: 'bg-green-600 hover:bg-green-500' },
        { val: 'cancelado', label: '❌ Cancelar',  color: 'bg-red-700 hover:bg-red-600' },
    ],
}

const pedidoActivo = ref(null)
const formEstado   = useForm({ estado: '', notas: '' })

function abrirModal(pedido, nuevoEstado) {
    pedidoActivo.value = pedido
    formEstado.estado  = nuevoEstado
    formEstado.notas   = ''
}

function confirmarCambio() {
    formEstado.post(`/pedidos/${pedidoActivo.value.id}/estado`, {
        onSuccess: () => {
            pedidoActivo.value = null
            formEstado.reset()
        }
    })
}

function filtrarModulo(m) {
    router.get('/pedidos/panel/operador', { modulo: m === 'todos' ? '' : m }, { preserveState: true })
}

function formatHora(d) {
    if (!d) return '-'
    return new Date(d).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
}

const page = usePage()
</script>

<template>
    <AuthLayout title="Panel Operador">
        <div class="max-w-7xl mx-auto px-4 py-8">

            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-white">Panel de Operador</h1>
                    <p class="text-sm text-gray-400 mt-1">Gestión de pedidos en tiempo real</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <!-- Navegación entre vistas -->
                    <a href="/pedidos"
                        class="px-4 py-2 bg-[#2d2d3f] text-gray-300 rounded-lg text-sm font-medium hover:bg-[#3d3d4f] transition">
                        📋 Lista
                    </a>
                    <a href="/pedidos/panel/dashboard"
                        class="px-4 py-2 bg-blue-700 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition">
                        📊 Dashboard
                    </a>
                    <a href="/pedidos/panel/reportes"
                        class="px-4 py-2 bg-[#2d2d3f] text-gray-300 rounded-lg text-sm font-medium hover:bg-[#3d3d4f] transition">
                        📑 Reportes
                    </a>
                    <button @click="router.reload()"
                        class="px-4 py-2 bg-purple-700 text-white rounded-lg text-sm font-medium hover:bg-purple-600 transition flex items-center gap-2">
                        🔄 Actualizar
                    </button>
                </div>
            </div>

            <!-- Flash -->
            <div v-if="page.props.flash?.success"
                class="mb-4 p-3 bg-green-900/40 border border-green-700 text-green-300 rounded-lg text-sm">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.errors?.error"
                class="mb-4 p-3 bg-red-900/40 border border-red-700 text-red-300 rounded-lg text-sm">
                {{ page.props.errors.error }}
            </div>

            <!-- Contador por estado -->
            <div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-6">
                <div v-for="e in estados" :key="e"
                    class="bg-[#1e1e2e] border border-[#2d2d3f] rounded-xl p-3 text-center">
                    <p class="text-xl font-bold text-white">
                        {{ pedidos.filter(p => p.estado === e).length }}
                    </p>
                    <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold mt-1 inline-block', badgeColores[e]]">
                        {{ etiquetas[e] }}
                    </span>
                </div>
            </div>

            <!-- Filtro módulo -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button v-for="m in ['todos', ...modulos]" :key="m"
                    @click="filtrarModulo(m)"
                    :class="['px-4 py-2 rounded-full text-sm font-medium capitalize transition',
                        moduloActual === m
                            ? 'bg-purple-600 text-white'
                            : 'bg-[#1e1e2e] border border-[#2d2d3f] text-gray-400 hover:bg-[#2d2d3f]']">
                    {{ m }}
                </button>
            </div>

            <!-- Sin pedidos -->
            <div v-if="pedidos.length === 0"
                class="text-center py-16 text-gray-500 bg-[#1e1e2e] rounded-xl border border-[#2d2d3f]">
                <p class="text-4xl mb-3">📭</p>
                <p class="font-medium">No hay pedidos activos en este momento</p>
                <button @click="router.reload()" class="mt-4 px-4 py-2 bg-purple-700 text-white rounded-lg text-sm hover:bg-purple-600">
                    🔄 Recargar
                </button>
            </div>

            <!-- Grid de pedidos -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <div v-for="p in pedidos" :key="p.id"
                    :class="['rounded-xl border-2 p-4 transition hover:shadow-lg hover:shadow-black/20', colores[p.estado]]">

                    <!-- Header tarjeta -->
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="font-mono text-xs font-bold text-purple-400">{{ p.numero_folio }}</p>
                            <p class="text-sm font-semibold text-white mt-0.5 capitalize">{{ p.modulo }}</p>
                        </div>
                        <span :class="['px-2 py-1 rounded-full text-xs font-semibold', badgeColores[p.estado]]">
                            {{ etiquetas[p.estado] }}
                        </span>
                    </div>

                    <!-- Usuario -->
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-full bg-purple-700 flex items-center justify-center text-xs font-bold text-white shrink-0">
                            {{ (p.usuario?.nombre?.[0] ?? '?') + (p.usuario?.apellido?.[0] ?? '') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-200 truncate">
                                {{ p.usuario?.nombre }} {{ p.usuario?.apellido }}
                            </p>
                            <p class="text-xs text-gray-500">{{ formatHora(p.created_at) }}</p>
                        </div>
                        <p class="font-bold text-white text-sm shrink-0">${{ Number(p.total).toFixed(2) }}</p>
                    </div>

                    <!-- Descripción -->
                    <p v-if="p.descripcion" class="text-xs text-gray-400 mb-3 line-clamp-2 bg-[#16213e] rounded-lg px-2 py-1.5">
                        {{ p.descripcion }}
                    </p>

                    <!-- Acciones -->
                    <div class="flex flex-wrap gap-2" v-if="transiciones[p.estado]?.length">
                        <button v-for="t in transiciones[p.estado]" :key="t.val"
                            @click="abrirModal(p, t.val)"
                            :class="['px-3 py-1.5 text-white text-xs font-semibold rounded-lg transition', t.color]">
                            {{ t.label }}
                        </button>
                    </div>
                    <p v-else class="text-xs text-gray-500 italic">Sin acciones disponibles</p>
                </div>
            </div>
        </div>

        <!-- Modal confirmación -->
        <Teleport to="body">
            <div v-if="pedidoActivo"
                class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 px-4">
                <div class="bg-[#1e1e2e] border border-[#2d2d3f] rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="font-bold text-white text-lg mb-1">Confirmar cambio de estado</h3>
                    <p class="text-sm text-gray-400 mb-4">
                        Pedido
                        <span class="font-mono font-semibold text-purple-400">{{ pedidoActivo.numero_folio }}</span>
                        — <span class="text-gray-300">{{ etiquetas[pedidoActivo.estado] }}</span>
                        →
                        <span :class="['font-semibold', formEstado.estado === 'cancelado' ? 'text-red-400' : 'text-green-400']">
                            {{ etiquetas[formEstado.estado] }}
                        </span>
                    </p>
                    <textarea v-model="formEstado.notas"
                        rows="3"
                        placeholder="Notas opcionales..."
                        class="w-full bg-[#16213e] border border-[#2d2d3f] rounded-lg px-3 py-2 text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-4" />
                    <div class="flex gap-3">
                        <button @click="confirmarCambio" :disabled="formEstado.processing"
                            :class="['flex-1 py-2.5 rounded-lg text-white font-semibold text-sm transition disabled:opacity-50',
                                formEstado.estado === 'cancelado'
                                    ? 'bg-red-700 hover:bg-red-600'
                                    : 'bg-purple-600 hover:bg-purple-500']">
                            {{ formEstado.processing ? 'Procesando...' : 'Confirmar' }}
                        </button>
                        <button @click="pedidoActivo = null"
                            class="flex-1 py-2.5 rounded-lg border border-[#2d2d3f] text-gray-300 font-semibold text-sm hover:bg-[#2d2d3f] transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthLayout>
</template>