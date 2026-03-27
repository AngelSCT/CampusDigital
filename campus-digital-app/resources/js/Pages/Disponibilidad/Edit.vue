<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-3xl">
            <div class="crud-topbar">
                <h1 class="crud-title">Editar disponibilidad</h1>
                <a href="/disponibilidad" class="crud-btn-secondary">Volver</a>
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
                    <label class="crud-label">Dia</label>
                    <select v-model="form.dia_semana" class="crud-select">
                        <option>Lunes</option>
                        <option>Martes</option>
                        <option>Miercoles</option>
                        <option>Jueves</option>
                        <option>Viernes</option>
                        <option>Sabado</option>
                        <option>Domingo</option>
                    </select>
                </div>

                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <label class="crud-label">Hora inicio</label>
                        <input type="time" v-model="form.hora_inicio" class="crud-input" />
                    </div>
                    <div>
                        <label class="crud-label">Hora fin</label>
                        <input type="time" v-model="form.hora_fin" class="crud-input" />
                    </div>
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
    item: Object,
    catalogo: Array
});

const form = reactive({
    id_catalogo: props.item.id_catalogo,
    dia_semana: props.item.dia_semana,
    hora_inicio: props.item.hora_inicio,
    hora_fin: props.item.hora_fin
});

function submit() {
    router.put(`/disponibilidad/${props.item.id_disponibilidad}`, form);
}
</script>
