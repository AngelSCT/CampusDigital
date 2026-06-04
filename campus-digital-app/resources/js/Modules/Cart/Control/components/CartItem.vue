<template>
    <li class="cart-item flex flex-col gap-2 py-3">

        <!-- Fila principal: info del ítem + precio + quitar -->
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="truncate text-sm font-medium text-gray-800">{{ item.nombre }}</p>
                <p class="text-xs text-gray-400">{{ item.referencia_externa }}</p>
                <p class="text-xs text-gray-400">Cantidad: {{ item.cantidad ?? 1 }}</p>
                <p v-if="item.duracion_dias" class="text-xs text-gray-400">{{ item.duracion_dias }} día(s)</p>

                <!-- Badge de regalo cuando está activo -->
                <span v-if="item.metadata?.es_regalo"
                      class="inline-flex items-center gap-1 mt-1 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                    🎁 Es regalo ✓
                    <button @click="quitarRegalo"
                            class="ml-1 text-emerald-400 hover:text-red-500 transition-colors text-xs leading-none"
                            title="Quitar regalo">✕</button>
                </span>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <div class="text-right">
                    <p class="text-xs text-gray-400">{{ formatMoney(item.precio_unitario) }} / ud.</p>
                    <p class="text-sm font-semibold text-gray-700">
                        {{ formatMoney((item.precio_unitario ?? 0) * (item.cantidad ?? 1)) }}
                    </p>
                </div>
                <slot name="item-extra" :item="item" />
                <button @click="$emit('remove', item.id)"
                        class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors"
                        title="Quitar">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Botón "Enviar como regalo" (solo cuando no es regalo y el form no está abierto) -->
        <div v-if="!item.metadata?.es_regalo && !mostrarFormRegalo">
            <button @click="mostrarFormRegalo = true"
                    class="text-xs text-violet-600 hover:text-violet-800 border border-violet-200 hover:border-violet-400 rounded-full px-3 py-1 transition-all">
                🎁 Enviar como regalo
            </button>
        </div>

        <!-- Formulario inline de regalo -->
        <div v-if="mostrarFormRegalo && !item.metadata?.es_regalo"
             class="mt-1 rounded-lg border border-violet-200 bg-violet-50 p-3 space-y-2 text-sm">

            <p class="text-xs font-semibold text-violet-700 uppercase tracking-wide">Datos del destinatario</p>

            <!-- Matrícula -->
            <div>
                <label class="block text-xs text-gray-600 mb-1">Matrícula</label>
                <input v-model="matricula"
                       type="text"
                       placeholder="Ej. A01234567"
                       class="w-full rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-1 focus:ring-violet-400"
                       @input="resetVerificacion" />
            </div>

            <!-- Primer nombre -->
            <div>
                <label class="block text-xs text-gray-600 mb-1">Primer nombre</label>
                <input v-model="primerNombre"
                       type="text"
                       placeholder="Ej. Juan"
                       class="w-full rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-1 focus:ring-violet-400"
                       @input="resetVerificacion" />
            </div>

            <!-- Mensaje opcional -->
            <div>
                <label class="block text-xs text-gray-600 mb-1">Mensaje (opcional)</label>
                <textarea v-model="mensaje"
                          placeholder="Escribe un mensaje..."
                          rows="2"
                          class="w-full rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-1 focus:ring-violet-400 resize-none" />
            </div>

            <!-- Feedback de verificación -->
            <p v-if="destinatarioVerificado" class="text-xs text-emerald-600 font-medium">
                ✓ {{ nombreDestinatario }} verificado
            </p>
            <p v-if="errorDestinatario" class="text-xs text-red-500">
                {{ errorDestinatario }}
            </p>

            <!-- Botones -->
            <div class="flex flex-wrap gap-2 pt-1">
                <button @click="verificarDestinatario"
                        :disabled="verificando || !matricula || !primerNombre"
                        class="text-xs bg-violet-600 hover:bg-violet-700 disabled:opacity-50 text-white rounded-md px-3 py-1.5 transition-colors">
                    {{ verificando ? 'Verificando…' : 'Verificar destinatario' }}
                </button>

                <button @click="confirmarRegalo"
                        :disabled="!destinatarioVerificado"
                        class="text-xs bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-md px-3 py-1.5 transition-colors">
                    Confirmar regalo
                </button>

                <button @click="cancelarForm"
                        class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-md px-3 py-1.5 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>

    </li>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    item: { type: Object, required: true },
    apiBaseUrl: { type: String, default: '/catalogo/cart-proxy' },
});

const emit = defineEmits(['remove', 'toggle-regalo']);

// ── Estado del formulario de regalo ──────────────────────────────────────────

const mostrarFormRegalo       = ref(false);
const matricula               = ref('');
const primerNombre            = ref('');
const mensaje                 = ref('');
const destinatarioVerificado  = ref(false);
const destinatarioId          = ref(null);
const nombreDestinatario      = ref('');
const destinatarioMatricula   = ref('');
const errorDestinatario       = ref('');
const verificando             = ref(false);

// ── Helpers ──────────────────────────────────────────────────────────────────

function formatMoney(val) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val ?? 0);
}

function resetVerificacion() {
    destinatarioVerificado.value = false;
    destinatarioId.value = null;
    nombreDestinatario.value = '';
    errorDestinatario.value = '';
}

function cancelarForm() {
    mostrarFormRegalo.value = false;
    matricula.value = '';
    primerNombre.value = '';
    mensaje.value = '';
    resetVerificacion();
}

// ── Verificar destinatario ────────────────────────────────────────────────────

async function verificarDestinatario() {
    if (!matricula.value || !primerNombre.value) return;
    verificando.value = true;
    errorDestinatario.value = '';

    try {
        const res = await fetch(`${props.apiBaseUrl}/validar-destinatario`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({
                matricula:    matricula.value.trim(),
                primer_nombre: primerNombre.value.trim(),
            }),
        });

        const data = await res.json();

        if (res.ok && data.valido) {
            destinatarioVerificado.value = true;
            destinatarioId.value = data.destinatario_id;
            nombreDestinatario.value = data.nombre_completo;
            destinatarioMatricula.value = matricula.value.trim();
        } else {
            errorDestinatario.value = data.mensaje ?? 'No se pudo verificar el destinatario.';
        }
    } catch {
        errorDestinatario.value = 'Error de conexión. Intenta de nuevo.';
    } finally {
        verificando.value = false;
    }
}

// ── Confirmar regalo ─────────────────────────────────────────────────────────

function confirmarRegalo() {
    if (!destinatarioVerificado.value) return;

    emit('toggle-regalo', {
        itemId: props.item.id,
        datosRegalo: {
            es_regalo:               true,
            destinatario_id:         destinatarioId.value,
            destinatario_matricula:  destinatarioMatricula.value,
            mensaje_dedicatorio:     mensaje.value.trim(),
        },
    });

    mostrarFormRegalo.value = false;
    matricula.value = '';
    primerNombre.value = '';
    mensaje.value = '';
}

// ── Quitar regalo ─────────────────────────────────────────────────────────────

function quitarRegalo() {
    emit('toggle-regalo', {
        itemId: props.item.id,
        datosRegalo: { es_regalo: false },
    });
}
</script>
