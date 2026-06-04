<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Agregar stock a inventario</h1>
                <a href="/inventario" class="crud-btn-secondary">Volver</a>
            </div>

            <form @submit.prevent="submit" class="crud-card space-y-3">
                <div>
                    <label class="crud-label">Producto</label>
                    <select v-model="form.id_catalogo" class="crud-select">
                        <option disabled value="">Selecciona producto</option>
                        <option v-for="item in catalogo" :key="item.id_catalogo" :value="item.id_catalogo">
                            {{ item.nombre }}
                        </option>
                    </select>
                    <p v-if="form.errors.id_catalogo" class="text-red-600 text-sm mt-1">{{ form.errors.id_catalogo }}</p>
                </div>

                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <label class="crud-label">Cantidad a agregar</label>
                        <input type="number" min="1" step="1" v-model="form.cantidad" class="crud-input" placeholder="1" />
                        <p v-if="form.errors.cantidad" class="text-red-600 text-sm mt-1">{{ form.errors.cantidad }}</p>
                    </div>

                    <div>
                        <label class="crud-label">Stock minimo (opcional)</label>
                        <input type="number" min="0" step="1" v-model="form.stock_minimo" class="crud-input" placeholder="0" />
                        <p v-if="form.errors.stock_minimo" class="text-red-600 text-sm mt-1">{{ form.errors.stock_minimo }}</p>
                    </div>
                </div>

                <p class="crud-muted">Esta accion registra una entrada positiva en movimientos y actualiza el inventario.</p>

                <button :disabled="form.processing" class="crud-btn-primary disabled:opacity-50">
                    {{ form.processing ? 'Guardando...' : 'Agregar stock' }}
                </button>
            </form>

            <div class="crud-card mt-4" v-if="form.id_catalogo">
                <h2 class="font-semibold mb-2 text-slate-100">Referencia actual</h2>
                <p class="crud-muted">
                    Stock actual: {{ selectedStock.stock_actual }} | Stock minimo: {{ selectedStock.stock_minimo }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    catalogo: {
        type: Array,
        default: () => [],
    },
    inventarioActual: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    id_catalogo: '',
    cantidad: 1,
    stock_minimo: '',
});

const selectedStock = computed(() => {
    if (!form.id_catalogo) {
        return {
            stock_actual: 0,
            stock_minimo: 0,
        };
    }

    const item = (props.inventarioActual || []).find((row) => Number(row.id_catalogo) === Number(form.id_catalogo));
    if (!item) {
        return {
            stock_actual: 0,
            stock_minimo: 0,
        };
    }

    return {
        stock_actual: item.stock_actual ?? 0,
        stock_minimo: item.stock_minimo ?? 0,
    };
});

function submit() {
    form.post('/inventario/agregar-stock');
}
</script>
