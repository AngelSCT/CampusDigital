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

// Polling cada 20 seg si el pedido no está en estado terminal
let interval = null
const terminales = ['entregado', 'cancelado']

function iniciarPolling() {
    if (terminales.includes(estadoActual.value)) return
    interval = setInterval(async () => {
        try {
            const res = await fetch(route('pedidos.estado-json', props.pedido.id))
            const json = await res.json()
            if (json.success && json.data.estado !== estadoActual.value) {
                estadoActual.value = json.data.estado
                if (terminales.includes(json.data.estado)) {
                    clearInterval(interval)
                    router.reload({ only: ['pedido'] })
                }
            }
        } catch (_) {}
    }, 20000)
}

onMounted(iniciarPolling)
onUnmounted(() => clearInterval(interval))

function cancelar() {
    formCancelar.post(route('pedidos.cancelar', props.pedido.id), {
        onSuccess: () => { showCancelar.value = false; formCancelar.reset() }
    })
}

function formatFecha(d) {
    if (!d) return '-'
    return new Date(d).toLocaleString('es-MX', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })
}

const page = usePage()
</script>

<template>
    <AuthLayout :title="`Pedido ${pedido.numero_folio}`">
        <div class="max-w-4xl mx-auto px-4 py-8">

            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                <Link :href="route('pedidos.index')" class="hover:text-indigo-600">Mis pedidos</Link>
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
                    <span :class="['px-3 py-1.5 rounded-full text-sm font-semibold', colores[estadoActual]]">
                        {{ iconos[estadoActual] }} {{ etiquetas[estadoActual] }}
                    </span>
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

            <!-- Historial de estados -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-bold text-gray-800 mb-4">Historial de cambios</h2>
                <div class="space-y-3">
                    <div v-if="!pedido.historial?.length" class="text-gray-400 text-sm">Sin historial registrado.</div>
                    <div v-for="h in pedido.historial" :key="h.id"
                        class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0">
                            {{ iconos[h.estado_nuevo] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span v-if="h.estado_anterior"
                                    :class="['px-1.5 py-0.5 rounded text-xs font-medium', colores[h.estado_anterior]]">
                                    {{ etiquetas[h.estado_anterior] }}
                                </span>
                                <span v-if="h.estado_anterior" class="text-gray-400 text-xs">→</span>
                                <span :class="['px-1.5 py-0.5 rounded text-xs font-semibold', colores[h.estado_nuevo]]">
                                    {{ etiquetas[h.estado_nuevo] }}
                                </span>
                                <span class="text-xs text-gray-400 ml-auto">{{ formatFecha(h.created_at) }}</span>
                            </div>
                            <p v-if="h.notas" class="text-xs text-gray-500 mt-1">{{ h.notas }}</p>
                            <p v-if="h.usuario" class="text-xs text-gray-400 mt-0.5">
                                Por: {{ h.usuario.nombre }} {{ h.usuario.apellido }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>