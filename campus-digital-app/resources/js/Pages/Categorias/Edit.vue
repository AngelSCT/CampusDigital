<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Editar categoria</h1>
                <a href="/categorias" class="crud-btn-secondary">Volver</a>
            </div>

            <form @submit.prevent="submit" class="crud-card space-y-3">
                <div>
                    <label class="crud-label">Nombre</label>
                    <input v-model="form.nombre" class="crud-input" />
                </div>
                <div>
                    <label class="crud-label">Descripcion</label>
                    <input v-model="form.descripcion" class="crud-input" />
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
    categoria: Object
});

const form = reactive({
    nombre: props.categoria.nombre,
    descripcion: props.categoria.descripcion
});

function submit() {
    router.put(`/categorias/${props.categoria.id_categoria}`, form);
}
</script>