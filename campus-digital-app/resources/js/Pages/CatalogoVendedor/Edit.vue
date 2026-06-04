<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-5xl">
            <h1 class="crud-title mb-1">Editar catalogo de vendedor</h1>
            <p class="crud-subtitle mb-6">Actualiza datos base y configuraciones del registro.</p>

            <form @submit.prevent="submit" class="space-y-6">
                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Datos principales</h2>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="crud-label">Vendedor</label>
                            <select v-model="form.id_vendedor" class="crud-select">
                                <option disabled value="">Selecciona vendedor</option>
                                <option v-for="item in vendedores" :key="item.id_vendedor" :value="item.id_vendedor">{{ item.nombre }}</option>
                            </select>
                            <p v-if="form.errors.id_vendedor" class="text-red-600 text-sm mt-1">{{ form.errors.id_vendedor }}</p>
                        </div>

                        <div>
                            <label class="crud-label">Catalogo base (opcional)</label>
                            <select v-model="form.id_catalogo_base" class="crud-select">
                                <option :value="null">Sin referencia</option>
                                <option v-for="item in catalogoBase" :key="item.id_catalogo" :value="item.id_catalogo">{{ item.nombre }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="crud-label">Nombre personalizado</label>
                            <input v-model="form.nombre_personalizado" class="crud-input" />
                            <p v-if="form.errors.nombre_personalizado" class="text-red-600 text-sm mt-1">{{ form.errors.nombre_personalizado }}</p>
                        </div>

                        <div>
                            <label class="crud-label">Tipo</label>
                            <select v-model="form.tipo" class="crud-select">
                                <option value="producto">Producto</option>
                                <option value="servicio">Servicio</option>
                            </select>
                        </div>

                        <div>
                            <label class="crud-label">Categoria</label>
                            <select v-model="form.id_categoria" class="crud-select">
                                <option :value="null">Sin categoria</option>
                                <option v-for="item in categorias" :key="item.id_categoria" :value="item.id_categoria">{{ item.nombre }}</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-slate-200">
                                <input v-model="form.activo" type="checkbox" />
                                Activo
                            </label>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="crud-label">Descripcion</label>
                        <textarea v-model="form.descripcion_personalizada" rows="3" class="crud-textarea"></textarea>
                    </div>
                </section>

                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Precio actual</h2>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="crud-label">Precio</label>
                            <input v-model="form.precio" type="number" step="0.01" min="0" class="crud-input" />
                        </div>
                        <div>
                            <label class="crud-label">Fecha inicio precio</label>
                            <input v-model="form.fecha_inicio" type="date" class="crud-input" />
                        </div>
                    </div>
                </section>

                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Disponibilidad base</h2>
                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="crud-label">Dia</label>
                            <select v-model="form.dia_semana" class="crud-select">
                                <option value="">Seleccionar</option>
                                <option v-for="dia in diasSemana" :key="dia" :value="dia">{{ dia }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="crud-label">Hora inicio</label>
                            <input v-model="form.hora_inicio" type="time" class="crud-input" />
                        </div>
                        <div>
                            <label class="crud-label">Hora fin</label>
                            <input v-model="form.hora_fin" type="time" class="crud-input" />
                        </div>
                    </div>
                </section>

                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Regla base</h2>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="crud-label">Tipo de regla</label>
                            <input v-model="form.tipo_regla" class="crud-input" />
                        </div>
                        <div>
                            <label class="crud-label">Descripcion</label>
                            <input v-model="form.regla_descripcion" class="crud-input" />
                        </div>
                    </div>
                </section>

                <button :disabled="form.processing" class="crud-btn-primary disabled:opacity-50">
                    {{ form.processing ? 'Actualizando...' : 'Actualizar todo' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    catalogo: Object,
    vendedores: Array,
    categorias: Array,
    catalogoBase: Array,
    precioActual: Object,
    disponibilidad: Object,
    regla: Object,
});

const diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

const form = useForm({
    id_vendedor: props.catalogo.id_vendedor,
    id_catalogo_base: props.catalogo.id_catalogo_base,
    nombre_personalizado: props.catalogo.nombre_personalizado,
    descripcion_personalizada: props.catalogo.descripcion_personalizada,
    tipo: props.catalogo.tipo,
    id_categoria: props.catalogo.id_categoria,
    activo: !!props.catalogo.activo,
    precio: props.precioActual?.precio ?? '',
    fecha_inicio: props.precioActual?.fecha_inicio ?? '',
    dia_semana: props.disponibilidad?.dia_semana ?? '',
    hora_inicio: props.disponibilidad?.hora_inicio ?? '',
    hora_fin: props.disponibilidad?.hora_fin ?? '',
    regla_descripcion: props.regla?.descripcion ?? '',
    tipo_regla: props.regla?.tipo_regla ?? 'general',
});

function submit() {
    form.put(`/catalogo-vendedor/${props.catalogo.id_cv}`);
}
</script>
