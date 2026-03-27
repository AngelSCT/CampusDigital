<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Crear regla</h1>
                <a href="/reglas" class="crud-btn-secondary">Volver</a>
            </div>

            <form @submit.prevent="submit" class="crud-card space-y-3">
                <div>
                    <label class="crud-label">Producto</label>
                    <select v-model="form.id_catalogo" class="crud-select">
                        <option disabled value="">Selecciona producto</option>
                        <option v-for="c in catalogo" :key="c.id_catalogo" :value="c.id_catalogo">
                            {{ c.nombre }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="crud-label">Descripcion</label>
                    <input v-model="form.descripcion" placeholder="Descripcion" class="crud-input" />
                </div>

                <div>
                    <label class="crud-label">Tipo de regla</label>
                    <input v-model="form.tipo_regla" placeholder="Tipo de regla (opcional)" class="crud-input" />
                </div>

                <button class="crud-btn-primary">Guardar</button>
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
    descripcion: '',
    tipo_regla: ''
});

function submit() {
    router.post('/reglas', form);
}
</script>