<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Solicitudes de Alta — Módulo Carrito</h1>

    <!-- Filtro por estado -->
    <div class="flex gap-3 mb-6">
      <a
        v-for="opcion in estadoOpciones"
        :key="opcion.value"
        :href="opcion.url"
        :class="[
          'px-4 py-2 rounded-full text-sm font-medium transition',
          filtroEstado === opcion.value
            ? 'bg-indigo-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
        ]"
      >
        {{ opcion.label }}
      </a>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Módulo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="s in solicitudes.data" :key="s.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-mono text-sm">{{ s.folio }}</td>
            <td class="px-6 py-4">{{ s.nombre_modulo }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ s.tipo_modulo }}</td>
            <td class="px-6 py-4">
              <span :class="badgeClass(s.estado)" class="px-2 py-1 rounded-full text-xs font-semibold">
                {{ s.estado }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(s.created_at) }}</td>
            <td class="px-6 py-4 text-right">
              <Link :href="`/admin/cart/solicitudes/${s.id}`" class="text-indigo-600 hover:underline text-sm">
                Ver →
              </Link>
            </td>
          </tr>
          <tr v-if="!solicitudes.data.length">
            <td colspan="6" class="px-6 py-10 text-center text-gray-400">No hay solicitudes.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4 flex gap-2">
      <a
        v-for="link in solicitudes.links"
        :key="link.label"
        :href="link.url"
        v-html="link.label"
        :class="[
          'px-3 py-1 rounded text-sm',
          link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
          !link.url && 'opacity-40 cursor-default pointer-events-none',
        ]"
      />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  solicitudes: Object,
  filtroEstado: String,
})

const estadoOpciones = [
  { value: null,         label: 'Todas',    url: '/admin/cart/solicitudes' },
  { value: 'pendiente',  label: 'Pendientes', url: '/admin/cart/solicitudes?estado=pendiente' },
  { value: 'aprobada',   label: 'Aprobadas',  url: '/admin/cart/solicitudes?estado=aprobada' },
  { value: 'rechazada',  label: 'Rechazadas', url: '/admin/cart/solicitudes?estado=rechazada' },
]

function badgeClass(estado) {
  return {
    'bg-yellow-100 text-yellow-800': estado === 'pendiente',
    'bg-green-100 text-green-800':   estado === 'aprobada',
    'bg-red-100 text-red-800':       estado === 'rechazada',
  }
}

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('es-MX') : '—'
}
</script>
