<template>
    <AuthLayout>
        <div class="archivos-root">

            <div class="arch-header">
                <div class="arch-header-left">
                    <div class="arch-header-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="arch-titulo">
                            {{ esAdmin && usuarioVisto.id !== usuarioActualId
                                ? `Archivos de ${usuarioVisto.nombre} ${usuarioVisto.apellido}`
                                : 'Mis Archivos' }}
                        </h1>
                        <p class="arch-subtitulo">
                            {{ stats.total_archivos }} archivos · {{ stats.total_carpetas }} carpetas · {{ stats.tamanio_total }}
                        </p>
                    </div>
                </div>
                <div class="arch-header-actions">
                    <button @click="modalCarpeta = true" class="btn-secondary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nueva carpeta
                    </button>
                    <label class="btn-primary" :class="subiendoArchivo ? 'btn-loading' : ''">
                        <svg v-if="!subiendoArchivo" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <svg v-else class="spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"/><path fill="currentColor" d="M4 12a8 8 0 018-8v8z" opacity=".75"/></svg>
                        {{ subiendoArchivo ? 'Subiendo...' : 'Subir archivo' }}
                        <input ref="inputArchivo" type="file" class="hidden"
                            :accept="extensionesAceptadas"
                            @change="subirArchivo" :disabled="subiendoArchivo"/>
                    </label>
                </div>
            </div>

            <div v-if="esAdmin && usuariosConArchivos?.length" class="admin-bar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="admin-bar-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span class="admin-bar-label">Vista de usuario:</span>
                <div class="admin-user-list">
                    <button
                        @click="cambiarUsuario(usuarioActualId)"
                        :class="usuarioVisto.id === usuarioActualId ? 'admin-user-active' : 'admin-user-btn'"
                    >
                        Mis archivos
                    </button>
                    <button
                        v-for="u in usuariosConArchivos" :key="u.id"
                        @click="cambiarUsuario(u.id)"
                        :class="usuarioVisto.id === u.id ? 'admin-user-active' : 'admin-user-btn'"
                    >
                        {{ u.nombre }} {{ u.apellido }}
                        <span class="admin-user-count">{{ u.archivos }}</span>
                    </button>
                </div>
                <div v-if="stats.sin_ver_admin" class="admin-pendientes">
                    <span class="pendiente-dot"></span>
                    {{ stats.sin_ver_admin }} sin revisar
                </div>
            </div>

            <div class="migajas">
                <button @click="navegar(null)" class="migaja-inicio">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    Raíz
                </button>
                <template v-for="(m, i) in migajas" :key="m.id">
                    <svg class="migaja-sep" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <button
                        @click="navegar(m.id)"
                        :class="i === migajas.length - 1 ? 'migaja-actual' : 'migaja-link'"
                    >
                        {{ m.nombre }}
                    </button>
                </template>
            </div>

            <div class="arch-grid">

                <!-- Carpetas -->
                <div v-if="carpetas.length > 0" class="arch-section">
                    <div class="section-header">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Carpetas <span class="section-count">{{ carpetas.length }}</span>
                    </div>
                    <div class="carpetas-grid">
                        <div v-for="c in carpetas" :key="c.id" class="carpeta-card" @click="navegar(c.id)">
                            <div class="carpeta-icon">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                            </div>
                            <div class="carpeta-info">
                                <p class="carpeta-nombre">{{ c.nombre }}</p>
                                <p class="carpeta-meta">{{ c.archivos_count }} archivo{{ c.archivos_count !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="carpeta-actions" @click.stop>
                                <button @click="pedirEliminarCarpeta(c)" class="action-btn danger" title="Eliminar carpeta">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="archivos.length > 0" class="arch-section">
                    <div class="section-header">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Archivos <span class="section-count">{{ archivos.length }}</span>
                    </div>
                    <div class="archivos-lista">
                        <div v-for="a in archivos" :key="a.id" class="archivo-row" :class="{ 'archivo-no-visto': esAdmin && !a.visto_admin }">

                            <div :class="['archivo-ext-icon', `ext-${a.icono}`]">
                                <span>{{ a.extension.toUpperCase() }}</span>
                            </div>

                            <div class="archivo-info">
                                <p class="archivo-nombre">{{ a.nombre_original }}</p>
                                <div class="archivo-meta">
                                    <span>{{ a.tamanio_humano }}</span>
                                    <span class="meta-dot">·</span>
                                    <span>{{ formatFecha(a.created_at) }}</span>
                                    <template v-if="esAdmin && a.visto_admin">
                                        <span class="meta-dot">·</span>
                                        <span class="visto-badge">✓ Revisado por {{ a.visto_por }}</span>
                                    </template>
                                    <template v-if="esAdmin && !a.visto_admin">
                                        <span class="meta-dot">·</span>
                                        <span class="no-visto-badge">Sin revisar</span>
                                    </template>
                                </div>
                                <p v-if="a.notas_admin" class="archivo-nota">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                    {{ a.notas_admin }}
                                </p>
                            </div>

                            <div class="archivo-actions">
                                <button v-if="a.es_previsualizable" @click="previsualizarArchivo(a)" class="action-btn primary" title="Previsualizar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <a :href="route('archivos.descargar', a.id)" class="action-btn success" title="Descargar" download>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                                <button v-if="esAdmin && !a.visto_admin" @click="marcarVisto(a)" class="action-btn info" title="Marcar como revisado">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button v-if="esAdmin && a.visto_admin" @click="desmarcarVisto(a)" class="action-btn warning" title="Quitar revisión">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <button v-if="esAdmin" @click="abrirNota(a)" class="action-btn purple" title="Agregar nota">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click="pedirEliminarArchivo(a)" class="action-btn danger" title="Eliminar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="carpetas.length === 0 && archivos.length === 0" class="empty-state">
                    <div class="empty-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                    <p class="empty-titulo">Esta carpeta está vacía</p>
                    <p class="empty-sub">Sube archivos o crea subcarpetas para organizar tu contenido</p>
                    <div class="empty-actions">
                        <button @click="modalCarpeta = true" class="btn-secondary">Nueva carpeta</button>
                        <label class="btn-primary">
                            Subir archivo
                            <input type="file" class="hidden" :accept="extensionesAceptadas" @change="subirArchivo"/>
                        </label>
                    </div>
                </div>
            </div>

            <Transition name="modal">
                <div v-if="modalCarpeta" class="modal-overlay" @click.self="modalCarpeta = false">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h3>Nueva carpeta</h3>
                            <button @click="modalCarpeta = false" class="modal-close">✕</button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Nombre de la carpeta</label>
                            <input
                                ref="inputNombreCarpeta"
                                v-model="nuevaCarpetaNombre"
                                type="text"
                                placeholder="Ej: Documentos 2025"
                                class="form-input"
                                @keyup.enter="crearCarpeta"
                                maxlength="200"
                            />
                            <p v-if="errorCarpeta" class="form-error">{{ errorCarpeta }}</p>
                        </div>
                        <div class="modal-footer">
                            <button @click="modalCarpeta = false" class="btn-ghost">Cancelar</button>
                            <button @click="crearCarpeta" :disabled="!nuevaCarpetaNombre.trim()" class="btn-primary">
                                Crear carpeta
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>

            <Transition name="modal">
                <div v-if="modalNota" class="modal-overlay" @click.self="cerrarNota">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h3>Nota sobre el archivo</h3>
                            <button @click="cerrarNota" class="modal-close">✕</button>
                        </div>
                        <div class="modal-body">
                            <p class="nota-archivo-nombre">{{ archivoSeleccionado?.nombre_original }}</p>
                            <label class="form-label">Nota interna (solo visible para administradores)</label>
                            <textarea
                                v-model="notaTexto"
                                class="form-textarea"
                                rows="4"
                                placeholder="Escribe una nota sobre este archivo..."
                                maxlength="1000"
                            ></textarea>
                            <p class="nota-contador">{{ notaTexto.length }}/1000</p>
                        </div>
                        <div class="modal-footer">
                            <button @click="cerrarNota" class="btn-ghost">Cancelar</button>
                            <button @click="guardarNota" class="btn-primary">Guardar nota</button>
                        </div>
                    </div>
                </div>
            </Transition>

            <Transition name="modal">
                <div v-if="modalEliminar" class="modal-overlay" @click.self="modalEliminar = false">
                    <div class="modal-box modal-danger">
                        <div class="modal-header">
                            <h3>Confirmar eliminación</h3>
                            <button @click="modalEliminar = false" class="modal-close">✕</button>
                        </div>
                        <div class="modal-body">
                            <div class="danger-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <p class="danger-titulo">¿Eliminar "{{ itemEliminar?.nombre_original || itemEliminar?.nombre }}"?</p>
                            <p class="danger-sub">
                                {{ tipoEliminar === 'carpeta'
                                    ? 'Se eliminarán todos los archivos dentro de esta carpeta. Esta acción no se puede deshacer.'
                                    : 'El archivo será eliminado permanentemente. Esta acción no se puede deshacer.' }}
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button @click="modalEliminar = false" class="btn-ghost">Cancelar</button>
                            <button @click="confirmarEliminar" class="btn-danger">Eliminar</button>
                        </div>
                    </div>
                </div>
            </Transition>

            <Transition name="modal">
                <div v-if="modalPreview" class="modal-overlay preview-overlay" @click.self="modalPreview = false">
                    <div class="preview-box">
                        <div class="preview-header">
                            <div class="preview-title">
                                <div :class="['archivo-ext-icon', `ext-${archivoPreview?.icono}`]" style="width:32px;height:32px;font-size:9px">
                                    <span>{{ archivoPreview?.extension?.toUpperCase() }}</span>
                                </div>
                                <span>{{ archivoPreview?.nombre_original }}</span>
                            </div>
                            <div class="preview-actions">
                                <a :href="route('archivos.descargar', archivoPreview?.id)" class="btn-secondary btn-sm" download>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Descargar
                                </a>
                                <button @click="modalPreview = false" class="modal-close">✕</button>
                            </div>
                        </div>
                        <div class="preview-content">
                            <!-- PDF -->
                            <iframe v-if="archivoPreview?.extension === 'pdf'"
                                :src="route('archivos.previsualizar', archivoPreview?.id)"
                                class="preview-iframe"
                                type="application/pdf"
                            ></iframe>
                            <!-- Imagen -->
                            <img v-else-if="['png','jpg','jpeg','gif','webp','svg'].includes(archivoPreview?.extension)"
                                :src="route('archivos.previsualizar', archivoPreview?.id)"
                                class="preview-img"
                                :alt="archivoPreview?.nombre_original"
                            />
                            <!-- Texto -->
                            <div v-else-if="archivoPreview?.extension === 'txt'" class="preview-loading">
                                <p class="text-slate-400 text-sm">Cargando texto...</p>
                                <iframe :src="route('archivos.previsualizar', archivoPreview?.id)" class="preview-iframe"></iframe>
                            </div>
                            <!-- No previsualizable -->
                            <div v-else class="preview-no-disponible">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p>Previsualización no disponible para este tipo de archivo</p>
                                <a :href="route('archivos.descargar', archivoPreview?.id)" class="btn-primary btn-sm" download>Descargar para ver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>

        </div>
    </AuthLayout>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    carpetas:            { type: Array,  default: () => [] },
    archivos:            { type: Array,  default: () => [] },
    carpetaActual:       { type: Object, default: null },
    migajas:             { type: Array,  default: () => [] },
    esAdmin:             { type: Boolean, default: false },
    usuarioVisto:        { type: Object, default: () => ({}) },
    usuariosConArchivos: { type: Array,  default: null },
    stats:               { type: Object, default: () => ({}) },
    usuarioActualId:     { type: Number, default: 0 },
});

const modalCarpeta      = ref(false);
const modalNota         = ref(false);
const modalEliminar     = ref(false);
const modalPreview      = ref(false);

const nuevaCarpetaNombre  = ref('');
const errorCarpeta        = ref('');
const notaTexto           = ref('');
const archivoSeleccionado = ref(null);
const archivoPreview      = ref(null);
const itemEliminar        = ref(null);
const tipoEliminar        = ref('archivo'); // 'archivo' | 'carpeta'
const subiendoArchivo     = ref(false);

const inputNombreCarpeta = ref(null);
const inputArchivo       = ref(null);

const extensionesAceptadas = '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.png,.jpg,.jpeg,.gif,.webp,.svg,.zip,.rar,.7z';

function navegar(carpetaId) {
    const params = {};
    if (carpetaId) params.carpeta = carpetaId;
    if (props.usuarioVisto.id !== props.usuarioActualId) {
        params.usuario_id = props.usuarioVisto.id;
    }
    router.get(route('archivos.index'), params, { preserveScroll: true });
}

function cambiarUsuario(usuarioId) {
    router.get(route('archivos.index'), { usuario_id: usuarioId }, { preserveScroll: false });
}

async function abrirModalCarpeta() {
    modalCarpeta.value = true;
    await nextTick();
    inputNombreCarpeta.value?.focus();
}

function crearCarpeta() {
    if (!nuevaCarpetaNombre.value.trim()) return;
    errorCarpeta.value = '';

    router.post(route('archivos.carpeta.crear'), {
        nombre:   nuevaCarpetaNombre.value.trim(),
        padre_id: props.carpetaActual?.id ?? null,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            modalCarpeta.value = false;
            nuevaCarpetaNombre.value = '';
        },
        onError: (errors) => {
            errorCarpeta.value = errors.nombre ?? 'Error al crear la carpeta.';
        },
    });
}

function subirArchivo(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    subiendoArchivo.value = true;

    const form = useForm({ archivo: file, carpeta_id: props.carpetaActual?.id ?? null });
    form.post(route('archivos.subir'), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            subiendoArchivo.value = false;
            if (inputArchivo.value) inputArchivo.value.value = '';
        },
    });
}

function previsualizarArchivo(archivo) {
    archivoPreview.value = archivo;
    modalPreview.value   = true;
}

function pedirEliminarArchivo(archivo) {
    itemEliminar.value  = archivo;
    tipoEliminar.value  = 'archivo';
    modalEliminar.value = true;
}

function pedirEliminarCarpeta(carpeta) {
    itemEliminar.value  = carpeta;
    tipoEliminar.value  = 'carpeta';
    modalEliminar.value = true;
}

function confirmarEliminar() {
    if (tipoEliminar.value === 'archivo') {
        router.delete(route('archivos.eliminar', itemEliminar.value.id), {
            preserveScroll: true,
            onFinish: () => { modalEliminar.value = false; },
        });
    } else {
        router.delete(route('archivos.carpeta.eliminar', itemEliminar.value.id), {
            preserveScroll: true,
            onFinish: () => { modalEliminar.value = false; },
        });
    }
}

function marcarVisto(archivo) {
    router.post(route('archivos.marcar-visto', archivo.id), {}, { preserveScroll: true });
}

function desmarcarVisto(archivo) {
    router.post(route('archivos.desmarcar-visto', archivo.id), {}, { preserveScroll: true });
}

function abrirNota(archivo) {
    archivoSeleccionado.value = archivo;
    notaTexto.value           = archivo.notas_admin ?? '';
    modalNota.value           = true;
}

function cerrarNota() {
    modalNota.value           = false;
    archivoSeleccionado.value = null;
    notaTexto.value           = '';
}

function guardarNota() {
    router.post(route('archivos.nota', archivoSeleccionado.value.id), {
        notas_admin: notaTexto.value,
    }, {
        preserveScroll: true,
        onSuccess: () => cerrarNota(),
    });
}

function formatFecha(fecha) {
    if (!fecha) return '';
    return new Date(fecha).toLocaleDateString('es-MX', {
        day:   '2-digit',
        month: 'short',
        year:  'numeric',
    });
}
</script>

<style scoped>
.archivos-root {
    font-family: 'Inter', sans-serif;
    padding: 1.5rem;
    min-height: 100vh;
    color: #e2e8f0;
}

.arch-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.arch-header-left { display: flex; align-items: center; gap: 1rem; }
.arch-header-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    border-radius: 0.875rem;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 20px rgba(59,130,246,0.35);
    flex-shrink: 0;
}
.arch-header-icon svg { width: 24px; height: 24px; color: #fff; }
.arch-titulo { font-size: 1.5rem; font-weight: 800; color: #fff; line-height: 1.2; }
.arch-subtitulo { font-size: 0.75rem; color: #64748b; margin-top: 2px; }
.arch-header-actions { display: flex; gap: 0.625rem; flex-wrap: wrap; }

.btn-primary {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: #fff; font-size: 0.8125rem; font-weight: 600;
    border: none; border-radius: 0.625rem; cursor: pointer;
    box-shadow: 0 4px 12px rgba(59,130,246,0.3);
    transition: all 0.2s;
}
.btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
.btn-primary svg { width: 14px; height: 14px; }
.btn-primary.btn-loading { opacity: 0.7; cursor: not-allowed; }

.btn-secondary {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(30,41,59,0.8);
    border: 1px solid rgba(59,130,246,0.25);
    color: #94a3b8; font-size: 0.8125rem; font-weight: 500;
    border-radius: 0.625rem; cursor: pointer;
    transition: all 0.2s; text-decoration: none;
}
.btn-secondary:hover { border-color: rgba(59,130,246,0.5); color: #fff; background: rgba(30,58,138,0.3); }
.btn-secondary svg { width: 14px; height: 14px; }

.btn-ghost {
    padding: 0.5rem 1rem;
    background: transparent;
    border: 1px solid rgba(100,116,139,0.3);
    color: #94a3b8; font-size: 0.8125rem; font-weight: 500;
    border-radius: 0.625rem; cursor: pointer;
    transition: all 0.2s;
}
.btn-ghost:hover { background: rgba(100,116,139,0.1); color: #fff; }

.btn-danger {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #b91c1c, #ef4444);
    color: #fff; font-size: 0.8125rem; font-weight: 600;
    border: none; border-radius: 0.625rem; cursor: pointer;
    box-shadow: 0 4px 12px rgba(239,68,68,0.3);
    transition: all 0.2s;
}
.btn-danger:hover { opacity: 0.9; }

.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
.hidden { display: none; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.admin-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    background: rgba(30,58,138,0.15);
    border: 1px solid rgba(59,130,246,0.2);
    border-radius: 0.875rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
}
.admin-bar-icon { width: 16px; height: 16px; color: #3b82f6; flex-shrink: 0; }
.admin-bar-label { font-size: 0.75rem; font-weight: 600; color: #64748b; white-space: nowrap; }
.admin-user-list { display: flex; gap: 0.375rem; flex-wrap: wrap; flex: 1; }
.admin-user-btn {
    padding: 0.25rem 0.75rem;
    background: rgba(30,41,59,0.6);
    border: 1px solid rgba(71,85,105,0.4);
    color: #94a3b8; font-size: 0.75rem; font-weight: 500;
    border-radius: 999px; cursor: pointer; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 0.375rem;
}
.admin-user-btn:hover { border-color: rgba(59,130,246,0.4); color: #fff; }
.admin-user-active {
    padding: 0.25rem 0.75rem;
    background: rgba(30,64,175,0.3);
    border: 1px solid rgba(59,130,246,0.5);
    color: #93c5fd; font-size: 0.75rem; font-weight: 600;
    border-radius: 999px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 0.375rem;
}
.admin-user-count {
    background: rgba(59,130,246,0.2);
    color: #60a5fa;
    padding: 0 0.375rem;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 700;
}
.admin-pendientes {
    display: flex; align-items: center; gap: 0.375rem;
    font-size: 0.75rem; color: #f87171; font-weight: 600;
    margin-left: auto;
}
.pendiente-dot {
    width: 8px; height: 8px;
    background: #ef4444;
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

.migajas {
    display: flex; align-items: center; gap: 0.25rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.migaja-inicio, .migaja-link {
    display: inline-flex; align-items: center; gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    background: rgba(30,41,59,0.6);
    border: 1px solid rgba(71,85,105,0.3);
    color: #94a3b8; font-size: 0.75rem;
    border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;
}
.migaja-inicio svg { width: 12px; height: 12px; }
.migaja-inicio:hover, .migaja-link:hover { color: #60a5fa; border-color: rgba(59,130,246,0.4); }
.migaja-actual {
    padding: 0.25rem 0.625rem;
    background: rgba(30,64,175,0.2);
    border: 1px solid rgba(59,130,246,0.35);
    color: #93c5fd; font-size: 0.75rem; font-weight: 600;
    border-radius: 0.5rem;
}
.migaja-sep { width: 12px; height: 12px; color: #334155; }

.arch-grid { display: flex; flex-direction: column; gap: 1.5rem; }
.arch-section {}
.section-header {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.75rem; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.08em;
    margin-bottom: 0.75rem;
}
.section-header svg { width: 14px; height: 14px; }
.section-count {
    padding: 0.1rem 0.5rem;
    background: rgba(30,41,59,0.8);
    border: 1px solid rgba(71,85,105,0.3);
    border-radius: 999px;
    font-size: 0.65rem; color: #94a3b8;
}

.carpetas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
}
.carpeta-card {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: rgba(30,41,59,0.7);
    border: 1px solid rgba(71,85,105,0.25);
    border-radius: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}
.carpeta-card:hover {
    border-color: rgba(59,130,246,0.4);
    background: rgba(30,58,138,0.15);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}
.carpeta-icon { width: 36px; height: 36px; color: #f59e0b; flex-shrink: 0; }
.carpeta-icon svg { width: 100%; height: 100%; }
.carpeta-info { flex: 1; min-width: 0; }
.carpeta-nombre { font-size: 0.8125rem; font-weight: 600; color: #e2e8f0; truncate: true; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.carpeta-meta { font-size: 0.7rem; color: #64748b; margin-top: 1px; }
.carpeta-actions { opacity: 0; transition: opacity 0.2s; }
.carpeta-card:hover .carpeta-actions { opacity: 1; }

.action-btn {
    width: 30px; height: 30px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 0.5rem; border: 1px solid; cursor: pointer;
    transition: all 0.15s; text-decoration: none;
}
.action-btn svg { width: 14px; height: 14px; }
.action-btn.primary { background: rgba(59,130,246,0.15); border-color: rgba(59,130,246,0.3); color: #60a5fa; }
.action-btn.primary:hover { background: rgba(59,130,246,0.3); }
.action-btn.success { background: rgba(34,197,94,0.15); border-color: rgba(34,197,94,0.3); color: #4ade80; }
.action-btn.success:hover { background: rgba(34,197,94,0.3); }
.action-btn.info { background: rgba(6,182,212,0.15); border-color: rgba(6,182,212,0.3); color: #22d3ee; }
.action-btn.info:hover { background: rgba(6,182,212,0.3); }
.action-btn.warning { background: rgba(245,158,11,0.15); border-color: rgba(245,158,11,0.3); color: #fbbf24; }
.action-btn.warning:hover { background: rgba(245,158,11,0.3); }
.action-btn.purple { background: rgba(168,85,247,0.15); border-color: rgba(168,85,247,0.3); color: #c084fc; }
.action-btn.purple:hover { background: rgba(168,85,247,0.3); }
.action-btn.danger { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.3); color: #f87171; }
.action-btn.danger:hover { background: rgba(239,68,68,0.3); }

.archivos-lista { display: flex; flex-direction: column; gap: 0.5rem; }
.archivo-row {
    display: flex; align-items: center; gap: 1rem;
    padding: 0.875rem 1rem;
    background: rgba(30,41,59,0.5);
    border: 1px solid rgba(71,85,105,0.2);
    border-radius: 0.875rem;
    transition: all 0.2s;
}
.archivo-row:hover {
    border-color: rgba(59,130,246,0.3);
    background: rgba(30,58,138,0.1);
}
.archivo-no-visto {
    border-left: 3px solid rgba(239,68,68,0.5) !important;
}

.archivo-ext-icon {
    width: 42px; height: 42px;
    border-radius: 0.625rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 10px; font-weight: 800; font-family: monospace;
    letter-spacing: -0.5px;
}
.ext-pdf      { background: rgba(239,68,68,0.2);   color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
.ext-word     { background: rgba(37,99,235,0.2);   color: #60a5fa; border: 1px solid rgba(37,99,235,0.3); }
.ext-excel    { background: rgba(34,197,94,0.2);   color: #4ade80; border: 1px solid rgba(34,197,94,0.3); }
.ext-powerpoint { background: rgba(249,115,22,0.2); color: #fb923c; border: 1px solid rgba(249,115,22,0.3); }
.ext-text     { background: rgba(100,116,139,0.2); color: #94a3b8; border: 1px solid rgba(100,116,139,0.3); }
.ext-image    { background: rgba(168,85,247,0.2);  color: #c084fc; border: 1px solid rgba(168,85,247,0.3); }
.ext-archive  { background: rgba(245,158,11,0.2);  color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
.ext-generic  { background: rgba(71,85,105,0.2);   color: #94a3b8; border: 1px solid rgba(71,85,105,0.3); }

.archivo-info { flex: 1; min-width: 0; }
.archivo-nombre { font-size: 0.875rem; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.archivo-meta { display: flex; align-items: center; gap: 0.375rem; font-size: 0.7rem; color: #64748b; margin-top: 2px; flex-wrap: wrap; }
.meta-dot { color: #334155; }
.visto-badge { color: #4ade80; font-weight: 600; }
.no-visto-badge { color: #f87171; font-weight: 600; }
.archivo-nota {
    display: flex; align-items: flex-start; gap: 0.375rem;
    font-size: 0.7rem; color: #c084fc; margin-top: 4px;
    background: rgba(168,85,247,0.08); border-radius: 0.375rem; padding: 0.25rem 0.5rem;
}
.archivo-nota svg { width: 12px; height: 12px; flex-shrink: 0; margin-top: 1px; }
.archivo-actions { display: flex; align-items: center; gap: 0.375rem; }

.empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 4rem 2rem; text-align: center;
    background: rgba(15,23,42,0.4);
    border: 1px dashed rgba(71,85,105,0.3);
    border-radius: 1.25rem;
}
.empty-icon {
    width: 80px; height: 80px;
    background: rgba(30,41,59,0.6);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 1rem;
}
.empty-icon svg { width: 40px; height: 40px; color: #334155; }
.empty-titulo { font-size: 1.125rem; font-weight: 700; color: #94a3b8; margin-bottom: 0.375rem; }
.empty-sub { font-size: 0.8125rem; color: #475569; margin-bottom: 1.5rem; }
.empty-actions { display: flex; gap: 0.625rem; }

.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    z-index: 100; padding: 1rem;
}
.modal-box {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border: 1px solid rgba(59,130,246,0.25);
    border-radius: 1.25rem;
    width: 100%; max-width: 460px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    overflow: hidden;
}
.modal-box.modal-danger { border-color: rgba(239,68,68,0.3); }
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(71,85,105,0.2);
}
.modal-header h3 { font-size: 1rem; font-weight: 700; color: #fff; }
.modal-close {
    width: 28px; height: 28px;
    background: rgba(71,85,105,0.3); border: none;
    color: #94a3b8; border-radius: 0.375rem; cursor: pointer;
    font-size: 0.75rem; transition: all 0.15s;
}
.modal-close:hover { background: rgba(71,85,105,0.5); color: #fff; }
.modal-body { padding: 1.5rem; }
.modal-footer {
    display: flex; justify-content: flex-end; gap: 0.625rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(71,85,105,0.2);
}

.form-label { display: block; font-size: 0.8125rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.5rem; }
.form-input {
    width: 100%; background: rgba(15,23,42,0.8);
    border: 1px solid rgba(71,85,105,0.4);
    border-radius: 0.625rem; color: #fff;
    padding: 0.625rem 0.875rem; font-size: 0.875rem;
    transition: all 0.2s; box-sizing: border-box;
}
.form-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
.form-textarea {
    width: 100%; background: rgba(15,23,42,0.8);
    border: 1px solid rgba(71,85,105,0.4);
    border-radius: 0.625rem; color: #fff;
    padding: 0.625rem 0.875rem; font-size: 0.875rem;
    resize: vertical; transition: all 0.2s; box-sizing: border-box;
}
.form-textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
.form-error { font-size: 0.75rem; color: #f87171; margin-top: 0.375rem; }
.nota-archivo-nombre { font-size: 0.8125rem; color: #60a5fa; font-weight: 600; margin-bottom: 0.875rem; }
.nota-contador { font-size: 0.65rem; color: #475569; text-align: right; margin-top: 0.25rem; }

.danger-icon {
    width: 56px; height: 56px;
    background: rgba(239,68,68,0.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
}
.danger-icon svg { width: 28px; height: 28px; color: #f87171; }
.danger-titulo { font-size: 0.9375rem; font-weight: 700; color: #fff; text-align: center; margin-bottom: 0.5rem; }
.danger-sub { font-size: 0.8125rem; color: #94a3b8; text-align: center; line-height: 1.5; }

.preview-overlay { padding: 0.75rem; }
.preview-box {
    background: #0f172a;
    border: 1px solid rgba(59,130,246,0.2);
    border-radius: 1.25rem;
    width: 100%; max-width: 1000px;
    height: 90vh;
    display: flex; flex-direction: column;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,0.7);
}
.preview-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(71,85,105,0.2);
    flex-shrink: 0;
}
.preview-title {
    display: flex; align-items: center; gap: 0.75rem;
    font-size: 0.875rem; font-weight: 600; color: #e2e8f0;
    overflow: hidden; min-width: 0;
}
.preview-title span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.preview-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.preview-content { flex: 1; overflow: hidden; display: flex; }
.preview-iframe { width: 100%; height: 100%; border: none; background: #fff; }
.preview-img {
    max-width: 100%; max-height: 100%;
    object-fit: contain;
    margin: auto; display: block; padding: 1rem;
}
.preview-no-disponible {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 1rem; color: #64748b; width: 100%;
}
.preview-no-disponible svg { width: 64px; height: 64px; }
.preview-no-disponible p { font-size: 0.9375rem; }

.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }

@media (max-width: 768px) {
    .archivos-root { padding: 1rem; }
    .arch-header { flex-direction: column; align-items: flex-start; }
    .carpetas-grid { grid-template-columns: 1fr 1fr; }
    .archivo-row { flex-wrap: wrap; }
    .archivo-actions { flex-wrap: wrap; }
    .preview-box { max-width: 100%; height: 95vh; border-radius: 0.875rem; }
}
@media (max-width: 480px) {
    .carpetas-grid { grid-template-columns: 1fr; }
    .arch-header-actions { width: 100%; }
    .btn-primary, .btn-secondary { flex: 1; justify-content: center; }
}
</style>