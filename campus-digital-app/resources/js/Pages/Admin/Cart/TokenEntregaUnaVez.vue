<template>
  <div class="p-6 max-w-2xl">
    <h1 class="text-2xl font-bold mb-2">Entrega de Tokens — {{ solicitud.nombre_modulo }}</h1>
    <p class="text-gray-500 mb-6">Módulo: <code class="bg-gray-100 px-1 rounded">{{ modulo?.slug }}</code></p>

    <!-- Tokens ya entregados -->
    <div v-if="alreadyDelivered" class="bg-yellow-50 border border-yellow-300 rounded-xl p-6 text-center">
      <p class="text-yellow-800 font-semibold text-lg mb-2">Tokens ya entregados</p>
      <p class="text-yellow-700 text-sm mb-4">
        Estos tokens ya fueron visualizados anteriormente. Si se perdieron,
        revoca los tokens actuales y emite un par nuevo desde la pantalla del módulo.
      </p>
      <a href="/admin/cart/solicitudes" class="text-indigo-600 hover:underline text-sm">
        ← Volver a solicitudes
      </a>
    </div>

    <!-- Tokens disponibles (one-time) -->
    <template v-else>
      <!-- Banner de advertencia -->
      <div class="bg-red-50 border border-red-400 rounded-xl p-4 mb-6 flex gap-3">
        <span class="text-red-500 text-xl mt-0.5">⚠</span>
        <div>
          <p class="font-semibold text-red-700">Estos tokens NO se podrán volver a ver.</p>
          <p class="text-red-600 text-sm mt-1">
            Si los pierdes, deberás revocar estos tokens y emitir nuevos desde el panel de módulos.
            Entrégalos al equipo cliente por un canal seguro.
          </p>
        </div>
      </div>

      <!-- Access Token -->
      <div class="bg-white rounded-xl shadow p-5 mb-4">
        <h2 class="font-semibold text-gray-800 mb-3">Access Token <span class="text-xs text-gray-400">(TTL: 1 hora)</span></h2>
        <TokenField :token="accessToken" label="access_token" />
      </div>

      <!-- Refresh Token -->
      <div class="bg-white rounded-xl shadow p-5 mb-6">
        <h2 class="font-semibold text-gray-800 mb-3">Refresh Token <span class="text-xs text-gray-400">(TTL: 7 días)</span></h2>
        <TokenField :token="refreshToken" label="refresh_token" />
      </div>

      <p class="text-gray-400 text-xs text-center">
        Una vez que navegues fuera de esta página, los tokens no serán recuperables desde el sistema.
      </p>
    </template>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'

const props = defineProps({
  solicitud:        Object,
  modulo:           Object,
  accessToken:      String,
  refreshToken:     String,
  alreadyDelivered: Boolean,
})

// ── Sub-componente inline para mostrar/copiar/ocultar token ──────────────────
const TokenField = {
  props: { token: String, label: String },
  template: `
    <div>
      <div class="flex gap-2 items-center">
        <input
          :type="visible ? 'text' : 'password'"
          :value="token"
          readonly
          class="flex-1 font-mono text-xs border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 min-w-0"
        />
        <button @click="toggleVisible"
          class="px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 rounded-lg whitespace-nowrap">
          {{ visible ? 'Ocultar' : 'Mostrar 30s' }}
        </button>
        <button @click="copiar"
          class="px-3 py-2 text-xs bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded-lg whitespace-nowrap">
          {{ copiado ? '✓ Copiado' : 'Copiar' }}
        </button>
      </div>
      <p v-if="visible && segundosRestantes > 0" class="text-xs text-orange-500 mt-1">
        Se ocultará en {{ segundosRestantes }} segundo(s).
      </p>
    </div>
  `,
  setup(props) {
    const visible = ref(false)
    const copiado = ref(false)
    const segundosRestantes = ref(0)
    let timer = null

    function toggleVisible() {
      if (visible.value) {
        visible.value = false
        clearInterval(timer)
        segundosRestantes.value = 0
        return
      }
      visible.value = true
      segundosRestantes.value = 30
      timer = setInterval(() => {
        segundosRestantes.value--
        if (segundosRestantes.value <= 0) {
          visible.value = false
          clearInterval(timer)
        }
      }, 1000)
    }

    async function copiar() {
      if (!props.token) return
      await navigator.clipboard.writeText(props.token)
      copiado.value = true
      setTimeout(() => (copiado.value = false), 3000)
    }

    onUnmounted(() => clearInterval(timer))

    return { visible, copiado, segundosRestantes, toggleVisible, copiar }
  },
}
</script>
