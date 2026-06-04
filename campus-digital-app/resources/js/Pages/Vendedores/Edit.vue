<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Editar vendedor</h1>
                <a href="/vendedores" class="crud-btn-secondary">Volver</a>
            </div>

            <form @submit.prevent="submit" class="crud-card space-y-3">
                <div>
                    <label class="crud-label">Nombre</label>
                    <input v-model="form.nombre" class="crud-input" />
                </div>

                <div>
                    <label class="crud-label">Email</label>
                    <input v-model="form.email" type="email" class="crud-input" />
                </div>

                <div>
                    <label class="crud-label">Telefono</label>
                    <input v-model="form.telefono" class="crud-input" />
                </div>

                <div>
                    <label class="crud-label">Descripcion</label>
                    <textarea v-model="form.descripcion" class="crud-textarea" rows="3"></textarea>
                </div>

                <label class="inline-flex items-center gap-2 text-slate-200">
                    <input v-model="form.activo" type="checkbox" />
                    Activo
                </label>

                <button class="crud-btn-primary">Actualizar</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    vendedor: Object,
});

const form = reactive({
    nombre: props.vendedor.nombre,
    email: props.vendedor.email,
    telefono: props.vendedor.telefono,
    descripcion: props.vendedor.descripcion,
    activo: !!props.vendedor.activo,
});

function submit() {
    router.put(`/vendedores/${props.vendedor.id_vendedor}`, form);
}
</script>
