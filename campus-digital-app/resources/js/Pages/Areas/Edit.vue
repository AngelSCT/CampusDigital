<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Editar area</h1>
                <a href="/areas" class="crud-btn-secondary">Volver</a>
            </div>

            <form @submit.prevent="submit" class="crud-card space-y-3">
                <div>
                    <label class="crud-label">Nombre</label>
                    <input
                        type="text"
                        v-model="form.nombre"
                        class="crud-input"
                    />
                </div>

                <button class="crud-btn-primary">Actualizar</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    area: Object
});

const form = reactive({
    nombre: props.area.nombre
});

function submit() {
    router.put(`/areas/${props.area.id_area}`, form);
}
</script>