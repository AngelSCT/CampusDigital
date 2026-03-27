<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Nueva area</h1>
                <a href="/areas" class="crud-btn-secondary">Volver</a>
            </div>

            <form @submit.prevent="submit" class="crud-card space-y-3">
                <div>
                    <label class="crud-label">Nombre del area</label>
                    <input
                        type="text"
                        v-model="form.nombre"
                        placeholder="Nombre del area"
                        class="crud-input"
                    />
                </div>

                <button class="crud-btn-primary">Guardar</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const form = reactive({
    nombre: ''
});

function submit() {
    router.post('/areas', form);
}
</script>