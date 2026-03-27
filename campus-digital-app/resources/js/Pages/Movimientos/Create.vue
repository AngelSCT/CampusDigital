<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Registrar consumo</h1>
                <a href="/catalogo-dashboard" class="crud-btn-secondary">Dashboard</a>
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
                </div>

                <div>
                    <label class="crud-label">Cantidad</label>
                    <input type="number" v-model="form.cantidad" placeholder="Cantidad" class="crud-input" />
                </div>

                <button class="crud-btn-primary">Registrar</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    catalogo: Array
});

const form = reactive({
    id_catalogo: '',
    cantidad: 1
});

function submit() {
    router.post('/movimientos', form);
}
</script>