<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router, Link, useForm, usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
    pedido: Object,
    estados: Array,
    puedeCancelar: Boolean,
})

const colores = {
    creado: 'bg-blue-100 text-blue-800', aceptado: 'bg-yellow-100 text-yellow-800',
    en_proceso: 'bg-orange-100 text-orange-800', listo: 'bg-purple-100 text-purple-800',
    entregado: 'bg-green-100 text-green-800', cancelado: 'bg-red-100 text-red-800',
}
const etiquetas = {
    creado: 'Creado', aceptado: 'Aceptado', en_proceso: 'En proceso',
    listo: 'Listo', entregado: 'Entregado', cancelado: 'Cancelado',
}
const iconos = {
    creado: '📋', aceptado: '✅', en_proceso: '⚙️',
    listo: '🎉', entregado: '📦', cancelado: '❌',
}

const estadoActual = ref(props.pedido.estado)
const showCancelar = ref(false)
const formCancelar = useForm({ motivo: '' })

// ── Polling cada 10 seg si el pedido NO está en estado terminal ──
let interval = null
const terminales = ['entregado', 'cancelado']
const polling = ref(false)
const flashCambio = ref(false)

function iniciarPolling() {
    if (terminales.includes(estadoActual.value)) return
    polling.value = true
    interval = setInterval(async () => {
        try {
            // ⬇️ FIX: URL directa en lugar de route() — más confiable
            const res = await fetch(`/pedidos/${props.pedido.id}/estado-json`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
            if (!res.ok) return
            const json = await res.json()

            if (json.success && json.data.estado !== estadoActual.value) {
                // Animación flash al detectar cambio
                flashCambio.value = true
                setTimeout(() => { flashCambio.value = false }, 1500)

                estadoActual.value = json.data.estado

                // Si el cambio fue al estado terminal, recargar para traer el historial nuevo
                if (terminales.includes(json.data.estado)) {
                    clearInterval(interval)
                    polling.value = false
                }
                // En cualquier cambio, recargar el pedido para traer el historial actualizado
                router.reload({ only: ['pedido'] })
            }
        } catch (err) {
            console.error('Polling error:', err)
        }
    }, 10000)
}

onMounted(iniciarPolling)
onUnmounted(() => clearInterval(interval))

function cancelar() {
    formCancelar.post(`/pedidos/${props.pedido.id}/cancelar`, {
        onSuccess: () => { showCancelar.value = false; formCancelar.reset() }
    })
}

function formatFecha(d) {
    if (!d) return '-'
    return new Date(d).toLocaleString('es-MX', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })
}

function tiempoTranscurrido(desde, hasta) {
    if (!desde || !hasta) return null
    const diff = new Date(hasta) - new Date(desde)
    const minutos = Math.floor(diff / 60000)
    if (minutos < 1) return null
    if (minutos < 60) return `${minutos} min`
    const horas = Math.floor(minutos / 60)
    if (horas < 24) return `${horas}h ${minutos % 60}min`
    const dias = Math.floor(horas / 24)
    return `${dias}d ${horas % 24}h`
}

const page = usePage()
</script>

<template>
    <AuthLayout :title="`Pedido ${pedido.numero_folio}`">
        <div class="max-w-4xl mx-auto px-4 py-8">

            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                <a href="/pedidos" class="hover:text-indigo-600">Mis pedidos</a>
                <span>/</span>
                <span class="text-gray-800 font-medium">{{ pedido.numero_folio }}</span>
            </div>

            <!-- Flash -->
            <div v-if="page.props.flash?.success"
                class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                {{ page.props.flash.success }}
            </div>

            <!-- Card principal -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ pedido.numero_folio }}</h1>
                        <p class="text-sm text-gray-500 mt-1 capitalize">{{ pedido.modulo }} · {{ formatFecha(pedido.created_at) }}</p>
                    </div>

                    <!-- Badge de estado + indicador de polling en vivo -->
                    <div class="flex flex-col items-end gap-1.5">
                        <span :class="[
                            'px-3 py-1.5 rounded-full text-sm font-semibold transition-all duration-500',
                            colores[estadoActual],
                            flashCambio ? 'ring-4 ring-purple-400 scale-110' : ''
                        ]">
                            {{ iconos[estadoActual] }} {{ etiquetas[estadoActual] }}
                        </span>
                        <div v-if="polling" class="flex items-center gap-1.5 text-xs text-gray-500">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            Actualizando en vivo
                        </div>
                    </div>
                </div>

                <!-- Stepper de estados -->
                <div class="flex items-center gap-0 mb-8 overflow-x-auto pb-2">
                    <template v-for="(e, i) in estados.filter(s => s !== 'cancelado')" :key="e">
                        <div class="flex flex-col items-center min-w-[80px]">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all',
                                estadoActual === e ? 'bg-indigo-600 text-white ring-4 ring-indigo-200' :
                                estados.indexOf(estadoActual) > estados.indexOf(e) ? 'bg-green-500 text-white' :
                                'bg-gray-200 text-gray-400']">
                                {{ estadoActual === e || estados.indexOf(estadoActual) > estados.indexOf(e) ? '✓' : i + 1 }}
                            </div>
                            <span class="text-xs mt-1 text-gray-500 text-center">{{ etiquetas[e] }}</span>
                        </div>
                        <div v-if="i < estados.filter(s => s !== 'cancelado').length - 1"
                            :class="['flex-1 h-1 min-w-[20px] transition-all',
                                estados.indexOf(estadoActual) > i ? 'bg-green-400' : 'bg-gray-200']" />
                    </template>
                </div>

                <!-- Datos del pedido -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Usuario</p>
                        <p class="font-semibold text-gray-800 mt-1">{{ pedido.usuario?.nombre }} {{ pedido.usuario?.apellido }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
                        <p class="font-bold text-gray-800 text-lg mt-1">${{ Number(pedido.total).toFixed(2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Operador</p>
                        <p class="font-semibold text-gray-800 mt-1">
                            {{ pedido.operador ? `${pedido.operador.nombre} ${pedido.operador.apellido}` : 'Sin asignar' }}
                        </p>
                    </div>
                    <div v-if="pedido.descripcion" class="bg-gray-50 rounded-lg p-3 col-span-2 md:col-span-3">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Descripción</p>
                        <p class="text-gray-800 mt-1">{{ pedido.descripcion }}</p>
                    </div>
                    <div v-if="pedido.notas" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 col-span-2 md:col-span-3">
                        <p class="text-xs text-yellow-700 uppercase tracking-wide font-semibold">Notas</p>
                        <p class="text-gray-800 mt-1">{{ pedido.notas }}</p>
                    </div>
                </div>

                <!-- Botón descargar ticket PDF -->
                <div class="flex justify-end gap-2 mb-4">
                    <a :href="`/pedidos/${pedido.id}/ticket-pdf`"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition"
                       target="_blank">
                        📄 Descargar Ticket PDF
                    </a>
                </div>
                <!-- Botón cancelar -->
                <div v-if="puedeCancelar && !showCancelar" class="flex justify-end">
                    <button @click="showCancelar = true"
                        class="px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                        Cancelar pedido
                    </button>
                </div>

                <!-- Form cancelación -->
                <div v-if="showCancelar" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="font-semibold text-red-800 mb-3">¿Seguro que deseas cancelar este pedido?</p>
                    <textarea v-model="formCancelar.motivo"
                        rows="3" placeholder="Indica el motivo de cancelación (requerido)..."
                        class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 bg-white" />
                    <p v-if="formCancelar.errors.motivo" class="text-red-600 text-xs mt-1">{{ formCancelar.errors.motivo }}</p>
                    <div class="flex gap-2 mt-3">
                        <button @click="cancelar" :disabled="formCancelar.processing"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50">
                            Confirmar cancelación
                        </button>
                        <button @click="showCancelar = false"
                            class="px-4 py-2 bg-white text-gray-600 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">
                            Volver
                        </button>
                    </div>
                </div>
            </div>

            <!-- Timeline del historial -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-bold text-gray-800 mb-6">Historial del pedido</h2>

                <div v-if="!pedido.historial?.length" class="text-gray-400 text-sm text-center py-4">
                    Sin historial registrado.
                </div>

                <ol v-else class="relative border-s-2 border-gray-200 ml-3">
                    <li
                        v-for="(h, i) in pedido.historial"
                        :key="h.id"
                        class="mb-6 ms-6 last:mb-0"
                    >
                        <span
                            :class="[
                                'absolute -start-4 flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-white text-sm shadow-sm',
                                h.estado_nuevo === pedido.estado
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-100 text-gray-600'
                            ]"
                        >
                            {{ iconos[h.estado_nuevo] }}
                        </span>

                        <div class="bg-gray-50 border border-gray-100 rounded-lg p-3">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span
                                    v-if="h.estado_anterior"
                                    :class="['px-1.5 py-0.5 rounded text-xs font-medium', colores[h.estado_anterior]]"
                                >
                                    {{ etiquetas[h.estado_anterior] }}
                                </span>
                                <span v-if="h.estado_anterior" class="text-gray-400 text-xs">→</span>
                                <span
                                    :class="['px-1.5 py-0.5 rounded text-xs font-semibold', colores[h.estado_nuevo]]"
                                >
                                    {{ etiquetas[h.estado_nuevo] }}
                                </span>
                                <span class="text-xs text-gray-400 ml-auto">
                                    {{ formatFecha(h.created_at) }}
                                </span>
                            </div>

                            <p
                                v-if="i > 0 && tiempoTranscurrido(pedido.historial[i-1].created_at, h.created_at)"
                                class="text-xs text-indigo-600 italic"
                            >
                                ⏱ {{ tiempoTranscurrido(pedido.historial[i-1].created_at, h.created_at) }}
                                en {{ etiquetas[pedido.historial[i-1].estado_nuevo] }}
                            </p>

                            <p v-if="h.notas" class="text-sm text-gray-700 mt-1.5">
                                {{ h.notas }}
                            </p>

                            <p v-if="h.usuario" class="text-xs text-gray-500 mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                {{ h.usuario.nombre }} {{ h.usuario.apellido }}
                            </p>
                        </div>
                    </li>

                    <li
                        v-if="!terminales.includes(pedido.estado)"
                        class="ms-6 relative"
                    >
                        <span class="absolute -start-4 flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-white bg-white border-2 border-dashed border-gray-300 text-xs text-gray-400">
                            ⏳
                        </span>
                        <p class="text-xs text-gray-400 italic pt-1.5">
                            Esperando siguiente cambio...
                        </p>
                    </li>
                </ol>
            </div>
        </div>
    </AuthLayout>
</template>