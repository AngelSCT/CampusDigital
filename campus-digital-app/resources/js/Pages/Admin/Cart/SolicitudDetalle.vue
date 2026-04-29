<template>
  <div class="p-6 max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
      <Link href="/admin/cart/solicitudes" class="text-gray-500 hover:text-gray-700">← Volver</Link>
      <h1 class="text-2xl font-bold">Solicitud {{ solicitud.folio }}</h1>
      <span :class="badgeClass(solicitud.estado)" class="px-2 py-1 rounded-full text-xs font-semibold">
        {{ solicitud.estado }}
      </span>
    </div>

    <!-- Datos de la solicitud -->
    <div class="bg-white rounded-lg shadow p-6 mb-6 grid grid-cols-2 gap-4">
      <div><p class="text-xs text-gray-500">Nombre del módulo</p><p class="font-medium">{{ solicitud.nombre_modulo }}</p></div>
      <div><p class="text-xs text-gray-500">Tipo de módulo</p><p class="font-medium">{{ solicitud.tipo_modulo }}</p></div>
      <div><p class="text-xs text-gray-500">Contacto</p><p>{{ solicitud.contacto_nombre }} · {{ solicitud.contacto_email }}</p></div>
      <div>
        <p class="text-xs text-gray-500">Categorías solicitadas</p>
        <div class="flex flex-wrap gap-1 mt-1">
          <span v-for="cat in solicitud.categorias_solicitadas" :key="cat"
            class="px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded text-xs">{{ cat }}</span>
        </div>
      </div>
      <div v-if="solicitud.descripcion" class="col-span-2">
        <p class="text-xs text-gray-500">Descripción</p>
        <p class="text-sm">{{ solicitud.descripcion }}</p>
      </div>
      <div v-if="solicitud.motivo_rechazo" class="col-span-2 bg-red-50 p-3 rounded">
        <p class="text-xs text-red-600 font-semibold mb-1">Motivo de rechazo</p>
        <p class="text-sm text-red-700">{{ solicitud.motivo_rechazo }}</p>
      </div>
    </div>

    <!-- Acciones solo para solicitudes pendientes -->
    <div v-if="solicitud.estado === 'pendiente'" class="flex gap-4">
      <button @click="modalAprobar = true"
        class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
        Aprobar
      </button>
      <button @click="modalRechazar = true"
        class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
        Rechazar
      </button>
    </div>

    <!-- Modal Aprobar -->
    <div v-if="modalAprobar" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <h2 class="text-lg font-bold mb-3">¿Aprobar solicitud?</h2>
        <p class="text-sm text-gray-600 mb-5">
          Se creará el módulo <strong>{{ solicitud.nombre_modulo }}</strong> y se emitirá un par de
          tokens JWT. Los tokens se mostrarán <strong>una sola vez</strong> en la siguiente pantalla.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="modalAprobar = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
          <form :action="`/admin/cart/solicitudes/${solicitud.id}/aprobar`" method="POST">
            <input type="hidden" name="_token" :value="csrf" />
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
              Confirmar aprobación
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Rechazar -->
    <div v-if="modalRechazar" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <h2 class="text-lg font-bold mb-3">Rechazar solicitud</h2>
        <form :action="`/admin/cart/solicitudes/${solicitud.id}/rechazar`" method="POST">
          <input type="hidden" name="_token" :value="csrf" />
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Motivo de rechazo <span class="text-red-500">*</span>
          </label>
          <textarea v-model="motivoRechazo" name="motivo_rechazo" rows="3" required minlength="10"
            class="w-full border border-gray-300 rounded-lg p-2 text-sm mb-4"
            placeholder="Describa el motivo (mínimo 10 caracteres)..."></textarea>
          <div class="flex gap-3 justify-end">
            <button type="button" @click="modalRechazar = false" class="px-4 py-2 text-gray-600">Cancelar</button>
            <button type="submit" :disabled="motivoRechazo.length < 10"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-40">
              Confirmar rechazo
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({ solicitud: Object })
const modalAprobar = ref(false)
const modalRechazar = ref(false)
const motivoRechazo = ref('')

const csrf = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? ''

function badgeClass(estado) {
  return {
    'bg-yellow-100 text-yellow-800': estado === 'pendiente',
    'bg-green-100 text-green-800':   estado === 'aprobada',
    'bg-red-100 text-red-800':       estado === 'rechazada',
  }
}
</script>
