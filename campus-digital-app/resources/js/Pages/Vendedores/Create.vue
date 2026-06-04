<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Crear vendedor</h1>
                <a href="/vendedores" class="crud-btn-secondary">Volver</a>
            </div>

            <form @submit.prevent="submit" class="crud-card space-y-3">
                <div>
                    <label class="crud-label">Nombre</label>
                    <input v-model="form.nombre" class="crud-input" placeholder="Nombre del vendedor" />
                </div>

                <div>
                    <label class="crud-label">Email</label>
                    <input v-model="form.email" type="email" class="crud-input" placeholder="correo@ejemplo.com" />
                </div>

                <div>
                    <label class="crud-label">Telefono</label>
                    <input v-model="form.telefono" class="crud-input" placeholder="Telefono" />
                </div>

                <div>
                    <label class="crud-label">Descripcion</label>
                    <textarea v-model="form.descripcion" class="crud-textarea" rows="3" placeholder="Descripcion"></textarea>
                </div>

                <label class="inline-flex items-center gap-2 text-slate-200">
                    <input v-model="form.activo" type="checkbox" />
                    Activo
                </label>

                <button class="crud-btn-primary">Guardar</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const form = reactive({
    nombre: '',
    email: '',
    telefono: '',
    descripcion: '',
    activo: true,
});

function submit() {
    router.post('/vendedores', form);
}
</script>
