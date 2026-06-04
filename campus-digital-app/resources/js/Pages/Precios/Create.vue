<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Nuevo precio</h1>
                <a href="/precios" class="crud-btn-secondary">Volver</a>
            </div>

            <form @submit.prevent="submit" class="crud-card space-y-3">
                <div>
                    <label class="crud-label">Producto</label>
                    <select v-model="form.id_catalogo" class="crud-select">
                        <option value="">Selecciona producto</option>
                        <option v-for="c in catalogo" :key="c.id_catalogo" :value="c.id_catalogo">
                            {{ c.nombre }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="crud-label">Precio</label>
                    <input type="number" v-model="form.precio" placeholder="Precio" class="crud-input" />
                </div>

                <div>
                    <label class="crud-label">Fecha inicio</label>
                    <input type="date" v-model="form.fecha_inicio" class="crud-input" />
                </div>

                <button class="crud-btn-primary">Guardar</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

defineProps({
    catalogo: Array
});

const form = reactive({
    id_catalogo: '',
    precio: '',
    fecha_inicio: ''
});

function submit() {
    router.post('/precios', form);
}
</script>