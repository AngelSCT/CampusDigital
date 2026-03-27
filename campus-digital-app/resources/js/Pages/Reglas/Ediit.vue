<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Editar regla</h1>
                <a href="/reglas" class="crud-btn-secondary">Volver</a>
            </div>

            <form @submit.prevent="submit" class="crud-card space-y-3">
                <div>
                    <label class="crud-label">Producto</label>
                    <select v-model="form.id_catalogo" class="crud-select">
                        <option v-for="c in catalogo" :key="c.id_catalogo" :value="c.id_catalogo">
                            {{ c.nombre }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="crud-label">Descripcion</label>
                    <input v-model="form.descripcion" class="crud-input" />
                </div>

                <div>
                    <label class="crud-label">Tipo de regla</label>
                    <input v-model="form.tipo_regla" class="crud-input" />
                </div>

                <button class="crud-btn-primary">Actualizar</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    regla: Object,
    catalogo: Array
});

const form = reactive({
    id_catalogo: props.regla.id_catalogo,
    descripcion: props.regla.descripcion,
    tipo_regla: props.regla.tipo_regla
});

function submit() {
    router.put(`/reglas/${props.regla.id_regla}`, form);
}
</script>