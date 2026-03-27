<template>
    <div class="crud-theme">
        <div class="crud-shell max-w-5xl">
            <h1 class="crud-title mb-1">Nuevo catalogo unificado</h1>
            <p class="crud-subtitle mb-6">Crea producto o servicio junto con precio, disponibilidad y regla base.</p>

            <div class="draft-notice">
                <span>
                    {{ draftRecovered ? 'Se recupero un borrador local.' : 'Autoguardado local activo para este formulario.' }}
                    <span v-if="lastSavedLabel" class="ml-2">Ultimo guardado: {{ lastSavedLabel }}</span>
                </span>
                <button v-if="hasDraft" type="button" @click="clearDraft(true)" class="crud-link text-xs sm:text-sm">Limpiar borrador</button>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Datos principales</h2>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="crud-label">Nombre</label>
                            <input v-model="form.nombre" placeholder="Nombre" class="crud-input" />
                            <p v-if="form.errors.nombre" class="text-red-600 text-sm mt-1">{{ form.errors.nombre }}</p>
                        </div>

                        <div>
                            <label class="crud-label">Tipo</label>
                            <select v-model="form.tipo" class="crud-select">
                                <option disabled value="">Selecciona tipo</option>
                                <option value="producto">Producto</option>
                                <option value="servicio">Servicio</option>
                            </select>
                            <p v-if="form.errors.tipo" class="text-red-600 text-sm mt-1">{{ form.errors.tipo }}</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="crud-label">Categoria</label>
                                <button type="button" @click="showCategoriaModal = true" class="crud-link text-xs">+ Nueva</button>
                            </div>
                            <select v-model="form.id_categoria" class="crud-select">
                                <option disabled value="">Selecciona categoria</option>
                                <option v-for="cat in categoriasList" :key="cat.id_categoria" :value="cat.id_categoria">
                                    {{ cat.nombre }}
                                </option>
                            </select>
                            <p v-if="form.errors.id_categoria" class="text-red-600 text-sm mt-1">{{ form.errors.id_categoria }}</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="crud-label">Areas</label>
                                <button type="button" @click="showAreaModal = true" class="crud-link text-xs">+ Nueva</button>
                            </div>
                            <select v-model="form.areas" multiple class="crud-select min-h-[102px]">
                                <option v-for="area in areasList" :key="area.id_area" :value="area.id_area">
                                    {{ area.nombre }}
                                </option>
                            </select>
                            <p class="crud-muted mt-1">Usa Ctrl/Cmd para seleccionar multiples.</p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="crud-label">Descripcion</label>
                        <textarea v-model="form.descripcion" rows="3" placeholder="Descripcion" class="crud-textarea"></textarea>
                    </div>
                </section>

                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Precio inicial (opcional)</h2>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="crud-label">Precio</label>
                            <input v-model="form.precio" type="number" step="0.01" min="0" class="crud-input" placeholder="0.00" />
                            <p v-if="form.errors.precio" class="text-red-600 text-sm mt-1">{{ form.errors.precio }}</p>
                        </div>

                        <div>
                            <label class="crud-label">Fecha inicio precio</label>
                            <input v-model="form.fecha_inicio" type="date" class="crud-input" />
                            <p v-if="form.errors.fecha_inicio" class="text-red-600 text-sm mt-1">{{ form.errors.fecha_inicio }}</p>
                        </div>
                    </div>
                </section>

                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Disponibilidad base (opcional)</h2>
                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="crud-label">Dia</label>
                            <select v-model="form.dia_semana" class="crud-select">
                                <option value="">Seleccionar</option>
                                <option v-for="dia in diasSemana" :key="dia" :value="dia">{{ dia }}</option>
                            </select>
                            <p v-if="form.errors.dia_semana" class="text-red-600 text-sm mt-1">{{ form.errors.dia_semana }}</p>
                        </div>
                        <div>
                            <label class="crud-label">Hora inicio</label>
                            <input v-model="form.hora_inicio" type="time" class="crud-input" />
                            <p v-if="form.errors.hora_inicio" class="text-red-600 text-sm mt-1">{{ form.errors.hora_inicio }}</p>
                        </div>
                        <div>
                            <label class="crud-label">Hora fin</label>
                            <input v-model="form.hora_fin" type="time" class="crud-input" />
                            <p v-if="form.errors.hora_fin" class="text-red-600 text-sm mt-1">{{ form.errors.hora_fin }}</p>
                        </div>
                    </div>
                </section>

                <section class="crud-card">
                    <h2 class="font-semibold text-lg mb-3">Regla base (opcional)</h2>
                    <div class="grid md:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="crud-label">Plantilla rapida</label>
                            <div class="flex gap-2">
                                <select v-model="selectedPlantilla" class="crud-select">
                                    <option value="">Seleccionar plantilla</option>
                                    <option v-for="tpl in reglaPlantillas" :key="tpl.key" :value="tpl.key">{{ tpl.label }}</option>
                                </select>
                                <button type="button" @click="applyReglaTemplate" class="crud-btn-secondary">Aplicar</button>
                            </div>
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="crud-label">Tipo de regla</label>
                            <input v-model="form.tipo_regla" class="crud-input" placeholder="general" />
                        </div>
                        <div>
                            <label class="crud-label">Descripcion</label>
                            <input v-model="form.regla_descripcion" class="crud-input" placeholder="Ejemplo: maximo 2 por usuario" />
                            <p v-if="form.errors.regla_descripcion" class="text-red-600 text-sm mt-1">{{ form.errors.regla_descripcion }}</p>
                        </div>
                    </div>
                </section>

                <button :disabled="form.processing" class="crud-btn-primary disabled:opacity-50">
                    {{ form.processing ? 'Guardando...' : 'Guardar todo' }}
                </button>
            </form>
        </div>

        <div v-if="showCategoriaModal" class="modal-overlay">
            <div class="modal-card">
                <h3 class="font-semibold text-lg mb-3 text-slate-100">Nueva categoria</h3>
                <input v-model="categoriaForm.nombre" type="text" class="modal-input" placeholder="Nombre" />
                <p v-if="categoriaForm.errors.nombre" class="text-sm text-red-400 mb-2">{{ categoriaForm.errors.nombre }}</p>
                <textarea v-model="categoriaForm.descripcion" rows="3" class="modal-input mb-2" placeholder="Descripcion (opcional)"></textarea>
                <p v-if="categoriaForm.errors.descripcion" class="text-sm text-red-400 mb-2">{{ categoriaForm.errors.descripcion }}</p>
                <p v-if="categoriaForm.error" class="text-sm text-red-400 mb-2">{{ categoriaForm.error }}</p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeCategoriaModal" class="px-3 py-1 border border-slate-500 text-slate-200 rounded">Cancelar</button>
                    <button type="button" @click="createCategoria" :disabled="categoriaForm.processing" class="px-3 py-1 bg-blue-600 text-white rounded disabled:opacity-50">
                        {{ categoriaForm.processing ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showAreaModal" class="modal-overlay">
            <div class="modal-card">
                <h3 class="font-semibold text-lg mb-3 text-slate-100">Nueva area</h3>
                <input v-model="areaForm.nombre" type="text" class="modal-input" placeholder="Nombre" />
                <p v-if="areaForm.errors.nombre" class="text-sm text-red-400 mb-2">{{ areaForm.errors.nombre }}</p>
                <p v-if="areaForm.error" class="text-sm text-red-400 mb-2">{{ areaForm.error }}</p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeAreaModal" class="px-3 py-1 border border-slate-500 text-slate-200 rounded">Cancelar</button>
                    <button type="button" @click="createArea" :disabled="areaForm.processing" class="px-3 py-1 bg-blue-600 text-white rounded disabled:opacity-50">
                        {{ areaForm.processing ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>

        <transition name="fade">
            <div v-if="toast.show" class="fixed right-4 top-4 z-50 px-4 py-2 rounded text-white shadow" :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'">
                {{ toast.message }}
            </div>
        </transition>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    categorias: Array,
    areas: Array
});

const diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

const form = useForm({
    nombre: '',
    descripcion: '',
    tipo: '',
    id_categoria: '',
    areas: [],
    precio: '',
    fecha_inicio: '',
    dia_semana: '',
    hora_inicio: '',
    hora_fin: '',
    regla_descripcion: '',
    tipo_regla: 'general'
});

const categoriasList = ref([...(props.categorias || [])]);
const areasList = ref([...(props.areas || [])]);

const showCategoriaModal = ref(false);
const showAreaModal = ref(false);
const categoriaForm = ref({ nombre: '', descripcion: '', processing: false, error: '', errors: {} });
const areaForm = ref({ nombre: '', processing: false, error: '', errors: {} });
const toast = ref({ show: false, message: '', type: 'success' });
const selectedPlantilla = ref('');
const hasDraft = ref(false);
const draftRecovered = ref(false);
const lastSavedAt = ref('');

const DRAFT_KEY = 'catalogo-create-draft-v1';

const reglaPlantillas = [
    {
        key: 'maximo_2',
        label: 'Maximo 2 por usuario',
        tipo: 'limite',
        descripcion: 'Maximo 2 solicitudes por usuario al dia.'
    },
    {
        key: 'reserva_anticipada',
        label: 'Reserva con anticipacion',
        tipo: 'reserva',
        descripcion: 'La reserva debe hacerse con al menos 24 horas de anticipacion.'
    },
    {
        key: 'solo_activos',
        label: 'Solo usuarios activos',
        tipo: 'acceso',
        descripcion: 'Disponible unicamente para usuarios con estatus activo.'
    }
];

function submit() {
    form.post('/catalogo', {
        onSuccess: () => {
            clearDraft();
        }
    });
}

async function createCategoria() {
    if (!categoriaForm.value.nombre) {
        categoriaForm.value.errors = { nombre: 'El nombre es obligatorio.' };
        return;
    }

    categoriaForm.value.processing = true;
    categoriaForm.value.error = '';
    categoriaForm.value.errors = {};

    try {
        const response = await axios.post('/categorias/quick-store', {
            nombre: categoriaForm.value.nombre,
            descripcion: categoriaForm.value.descripcion,
        });

        const categoria = response.data.categoria;
        categoriasList.value.push(categoria);
        form.id_categoria = categoria.id_categoria;
        closeCategoriaModal();
        showToast('Categoria creada y seleccionada.');
    } catch (error) {
        const apiErrors = error?.response?.data?.errors || {};
        categoriaForm.value.errors = mapFirstErrors(apiErrors);
        categoriaForm.value.error = error?.response?.data?.message || 'No se pudo crear la categoria.';
    } finally {
        categoriaForm.value.processing = false;
    }
}

async function createArea() {
    if (!areaForm.value.nombre) {
        areaForm.value.errors = { nombre: 'El nombre es obligatorio.' };
        return;
    }

    areaForm.value.processing = true;
    areaForm.value.error = '';
    areaForm.value.errors = {};

    try {
        const response = await axios.post('/areas/quick-store', {
            nombre: areaForm.value.nombre,
        });

        const area = response.data.area;
        areasList.value.push(area);
        form.areas = Array.from(new Set([...form.areas, area.id_area]));
        closeAreaModal();
        showToast('Area creada y agregada.');
    } catch (error) {
        const apiErrors = error?.response?.data?.errors || {};
        areaForm.value.errors = mapFirstErrors(apiErrors);
        areaForm.value.error = error?.response?.data?.message || 'No se pudo crear el area.';
    } finally {
        areaForm.value.processing = false;
    }
}

function closeCategoriaModal() {
    showCategoriaModal.value = false;
    categoriaForm.value = { nombre: '', descripcion: '', processing: false, error: '', errors: {} };
}

function closeAreaModal() {
    showAreaModal.value = false;
    areaForm.value = { nombre: '', processing: false, error: '', errors: {} };
}

function showToast(message, type = 'success') {
    toast.value = { show: true, message, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 2500);
}

function applyReglaTemplate() {
    if (!selectedPlantilla.value) {
        return;
    }

    const tpl = reglaPlantillas.find((item) => item.key === selectedPlantilla.value);
    if (!tpl) {
        return;
    }

    form.tipo_regla = tpl.tipo;
    form.regla_descripcion = tpl.descripcion;
}

function mapFirstErrors(errorsObject) {
    return Object.keys(errorsObject).reduce((acc, key) => {
        const value = errorsObject[key];
        acc[key] = Array.isArray(value) ? value[0] : value;
        return acc;
    }, {});
}

function getDraftData() {
    return {
        nombre: form.nombre,
        descripcion: form.descripcion,
        tipo: form.tipo,
        id_categoria: form.id_categoria,
        areas: form.areas,
        precio: form.precio,
        fecha_inicio: form.fecha_inicio,
        dia_semana: form.dia_semana,
        hora_inicio: form.hora_inicio,
        hora_fin: form.hora_fin,
        regla_descripcion: form.regla_descripcion,
        tipo_regla: form.tipo_regla,
        selectedPlantilla: selectedPlantilla.value,
    };
}

function loadDraft() {
    const raw = localStorage.getItem(DRAFT_KEY);
    hasDraft.value = Boolean(raw);

    if (!raw) {
        return;
    }

    try {
        const draft = JSON.parse(raw);

        const shouldRestore = confirm('Se encontro un borrador local para este formulario. Deseas restaurarlo?');
        if (!shouldRestore) {
            draftRecovered.value = false;
            return;
        }

        form.nombre = draft.nombre ?? form.nombre;
        form.descripcion = draft.descripcion ?? form.descripcion;
        form.tipo = draft.tipo ?? form.tipo;
        form.id_categoria = draft.id_categoria ?? form.id_categoria;
        form.areas = Array.isArray(draft.areas) ? draft.areas : form.areas;
        form.precio = draft.precio ?? form.precio;
        form.fecha_inicio = draft.fecha_inicio ?? form.fecha_inicio;
        form.dia_semana = draft.dia_semana ?? form.dia_semana;
        form.hora_inicio = draft.hora_inicio ?? form.hora_inicio;
        form.hora_fin = draft.hora_fin ?? form.hora_fin;
        form.regla_descripcion = draft.regla_descripcion ?? form.regla_descripcion;
        form.tipo_regla = draft.tipo_regla ?? form.tipo_regla;
        selectedPlantilla.value = draft.selectedPlantilla ?? selectedPlantilla.value;
        draftRecovered.value = true;
        showToast('Borrador restaurado correctamente.');
    } catch (error) {
        localStorage.removeItem(DRAFT_KEY);
        hasDraft.value = false;
    }
}

function clearDraft(showFeedback = false) {
    localStorage.removeItem(DRAFT_KEY);
    hasDraft.value = false;
    draftRecovered.value = false;

    if (showFeedback) {
        showToast('Borrador local eliminado.');
    }
}

function formatSavedAt() {
    if (!lastSavedAt.value) {
        return '';
    }

    return new Date(lastSavedAt.value).toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

const lastSavedLabel = ref('');

watch(
    () => getDraftData(),
    (payload) => {
        localStorage.setItem(DRAFT_KEY, JSON.stringify(payload));
        hasDraft.value = true;
        lastSavedAt.value = new Date().toISOString();
        lastSavedLabel.value = formatSavedAt();
    },
    { deep: true }
);

onMounted(() => {
    loadDraft();
});
</script>

<style scoped>
.draft-notice {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(125, 211, 252, 0.35);
    background: rgba(8, 47, 73, 0.42);
    color: #bae6fd;
    padding: 0.6rem 0.85rem;
    font-size: 0.85rem;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 40;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(2, 6, 23, 0.65);
    backdrop-filter: blur(2px);
}

.modal-card {
    width: 100%;
    max-width: 28rem;
    border-radius: 0.75rem;
    padding: 1rem;
    border: 1px solid rgba(96, 165, 250, 0.4);
    background: linear-gradient(145deg, #0f172a, #1e293b);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.42);
}

.modal-input {
    width: 100%;
    margin-bottom: 0.5rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(148, 163, 184, 0.45);
    background: rgba(15, 23, 42, 0.9);
    color: #e2e8f0;
    padding: 0.5rem 0.65rem;
}

.modal-input::placeholder {
    color: #94a3b8;
}

.fade-enter-active,
.fade-leave-active {
    transition: all 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>