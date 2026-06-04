<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-5xl">
            <h1 class="crud-title mb-1">Crear promocion</h1>
            <p class="crud-subtitle mb-6">Define una promocion y asignala al catalogo global y/o de vendedores.</p>

            <form @submit.prevent="submit" class="space-y-6">
                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Datos principales</h2>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="crud-label">Nombre</label>
                            <input v-model="form.nombre" class="crud-input" placeholder="Nombre" />
                            <p v-if="form.errors.nombre" class="text-red-600 text-sm mt-1">{{ form.errors.nombre }}</p>
                        </div>
                        <div>
                            <label class="crud-label">Tipo</label>
                            <input v-model="form.tipo" class="crud-input" placeholder="porcentaje, monto fijo, etc." />
                        </div>
                        <div>
                            <label class="crud-label">Valor</label>
                            <input v-model="form.valor" type="number" min="0" step="0.01" class="crud-input" placeholder="0.00" />
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-slate-200">
                                <input v-model="form.activa" type="checkbox" />
                                Activa
                            </label>
                        </div>
                        <div>
                            <label class="crud-label">Fecha inicio</label>
                            <input v-model="form.fecha_inicio" type="date" class="crud-input" />
                        </div>
                        <div>
                            <label class="crud-label">Fecha fin</label>
                            <input v-model="form.fecha_fin" type="date" class="crud-input" />
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="crud-label">Descripcion</label>
                        <textarea v-model="form.descripcion" rows="3" class="crud-textarea" placeholder="Descripcion"></textarea>
                    </div>
                </section>

                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Asignaciones (opcionales)</h2>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="crud-label">Catalogo global</label>
                            <select v-model="form.catalogo_ids" multiple class="crud-select min-h-[180px]">
                                <option v-for="item in catalogo" :key="item.id_catalogo" :value="item.id_catalogo">{{ item.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="crud-label">Catalogo vendedor</label>
                            <select v-model="form.catalogo_vendedor_ids" multiple class="crud-select min-h-[180px]">
                                <option v-for="item in catalogoVendedor" :key="item.id_cv" :value="item.id_cv">{{ item.nombre_personalizado }}</option>
                            </select>
                        </div>
                    </div>
                </section>

                <button :disabled="form.processing" class="crud-btn-primary disabled:opacity-50">
                    {{ form.processing ? 'Guardando...' : 'Guardar promocion' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    catalogo: Array,
    catalogoVendedor: Array,
});

const form = useForm({
    nombre: '',
    descripcion: '',
    tipo: '',
    valor: '',
    fecha_inicio: '',
    fecha_fin: '',
    activa: true,
    catalogo_ids: [],
    catalogo_vendedor_ids: [],
});

function submit() {
    form.post('/promociones');
}
</script>
