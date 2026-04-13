<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
  pedidos: { type: Array, default: () => [] },
})

const page       = usePage()
const procesando = ref(null)

const flashSuccess = computed(() => page.props.flash?.success ?? '')
const flashError   = computed(() => page.props.errors?.mensaje ?? '')

function aceptarRegalo(pedido) {
  procesando.value = `aceptar-${pedido.id}`

  router.post(
    `/carrito/regalos/${pedido.id}/aceptar`,
    {},
    {
      preserveScroll: true,
      onFinish: () => { procesando.value = null },
    }
  )
}

function rechazarRegalo(pedido) {
  procesando.value = `rechazar-${pedido.id}`

  router.post(
    `/carrito/regalos/${pedido.id}/rechazar`,
    {},
    {
      preserveScroll: true,
      onFinish: () => { procesando.value = null },
    }
  )
}

const estadoBadge = {
  en_escrow: { label: 'Pendiente de reclamar', cls: 'bg-amber-500/20 text-amber-400 ring-1 ring-amber-500/30' },
  pendiente:  { label: 'Pendiente',             cls: 'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30' },
  entregado:  { label: 'Reclamado',             cls: 'bg-emerald-500/20 text-emerald-400 ring-1 ring-emerald-500/30' },
  rechazado:  { label: 'Rechazado',             cls: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30' },
  cancelado:  { label: 'Cancelado',             cls: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30' },
}
</script>

<template>
  <AuthLayout>
    <div class="max-w-4xl mx-auto py-8 px-4">

      <!-- Encabezado de página -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Mis Regalos Recibidos</h1>
        <p class="text-slate-400 text-sm mt-1">Aquí aparecen los regalos que tus amigos te han enviado.</p>
      </div>

      <!-- Mensaje de éxito -->
      <div
        v-if="flashSuccess"
        class="mb-6 flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm"
      >
        <span class="text-xl">🎉</span>
        {{ flashSuccess }}
      </div>

      <!-- Mensaje de error -->
      <div
        v-if="flashError"
        class="mb-6 flex items-center gap-3 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm"
      >
        <span class="text-xl">⚠️</span>
        {{ flashError }}
      </div>

      <!-- Empty State -->
      <div
        v-if="!pedidos.length"
        class="flex flex-col items-center justify-center py-20 text-center"
      >
        <div class="w-20 h-20 rounded-full bg-slate-800 flex items-center justify-center mb-5 text-4xl">
          🎁
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">Aún no tienes regalos pendientes</h3>
        <p class="text-slate-400 text-sm max-w-xs">
          ¡Dile a tus amigos que te inviten un café!
        </p>
      </div>

      <!-- Lista de regalos -->
      <div v-else class="space-y-4">
        <div
          v-for="pedido in pedidos"
          :key="pedido.id"
          class="bg-slate-900 border border-slate-800 rounded-xl p-5 transition hover:border-slate-700"
        >
          <!-- Header tarjeta -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <!-- Avatar placeholder remitente -->
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ (pedido.usuario?.nombre ?? '?')[0].toUpperCase() }}
              </div>
              <div>
                <p class="text-white font-semibold leading-tight">
                  {{ pedido.usuario?.nombre ?? 'Alguien especial' }}
                </p>
                <p class="text-slate-500 text-xs">te envió un regalo · Pedido #{{ pedido.id }}</p>
              </div>
            </div>
            <!-- Badge estado -->
            <span
              class="text-xs font-semibold px-2.5 py-1 rounded-full"
              :class="estadoBadge[pedido.estado]?.cls ?? 'bg-slate-700 text-slate-300'"
            >
              {{ estadoBadge[pedido.estado]?.label ?? pedido.estado }}
            </span>
          </div>

          <!-- Productos -->
          <div class="bg-slate-800/60 rounded-lg divide-y divide-slate-700/50 mb-4">
            <div
              v-for="detalle in pedido.detalles"
              :key="detalle.id"
              class="flex items-center justify-between px-4 py-2.5"
            >
              <div class="flex items-center gap-2">
                <span class="text-slate-300 font-medium text-sm">{{ detalle.producto?.nombre ?? 'Producto' }}</span>
                <span class="text-xs text-slate-500 bg-slate-700 px-1.5 py-0.5 rounded-md">×{{ detalle.cantidad }}</span>
              </div>
              <span class="text-slate-400 text-sm">${{ Number(detalle.precio_unitario ?? 0).toFixed(2) }}</span>
            </div>
          </div>

          <!-- Footer: total + botón -->
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 uppercase tracking-wide">Total del regalo</p>
              <p class="text-white font-bold text-lg">${{ Number(pedido.total).toFixed(2) }}</p>
            </div>

            <!-- Acciones en_escrow: Reclamar + Rechazar -->
            <div v-if="pedido.estado === 'en_escrow'" class="flex items-center gap-3">

              <!-- Botón rechazar (peligro) -->
              <button
                :disabled="procesando !== null"
                class="flex items-center gap-2 px-4 py-3 border border-red-500/60 text-red-400 font-semibold rounded-xl hover:bg-red-500/10 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150"
                @click="rechazarRegalo(pedido)"
              >
                <svg
                  v-if="procesando === `rechazar-${pedido.id}`"
                  class="animate-spin h-4 w-4 text-red-400"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                <span>{{ procesando === `rechazar-${pedido.id}` ? 'Procesando…' : 'Rechazar' }}</span>
              </button>

              <!-- Botón reclamar -->
              <button
                :disabled="procesando !== null"
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-900/40 hover:from-emerald-400 hover:to-teal-500 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150"
                @click="aceptarRegalo(pedido)"
              >
                <svg
                  v-if="procesando === `aceptar-${pedido.id}`"
                  class="animate-spin h-4 w-4 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                <span>{{ procesando === `aceptar-${pedido.id}` ? 'Procesando…' : '🎁 Reclamar Regalo' }}</span>
              </button>

            </div>

            <!-- Confirmación entregado -->
            <div
              v-else-if="pedido.estado === 'entregado'"
              class="flex items-center gap-2 text-emerald-400 font-semibold text-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Regalo reclamado
            </div>
          </div>
        </div>
      </div>

    </div>
  </AuthLayout>
</template>
