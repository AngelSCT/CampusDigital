<template>
    <div class="crud-theme">
        <div class="crud-shell">
            <div class="crud-topbar">
                <div>
                    <h1 class="crud-title">Dashboard de Catalogo de Usuario (Demo)</h1>
                    <p class="crud-subtitle">Simula la vista de un usuario que administra su propio catalogo.</p>
                </div>
                <a href="/catalogo-dashboard" class="crud-btn-secondary">Volver al dashboard admin</a>
            </div>

            <div class="crud-card mb-4">
                <div class="flex gap-2">
                    <a :href="`/catalogo-vendedor/create?vendedor_id=${vendedorActual}`" class="crud-btn-secondary">Nuevo item</a>
                    <a href="/promociones" class="crud-btn-secondary">Mis promociones</a>
                </div>
            </div>

            <section class="vendor-management-section">
                <div class="vendor-management-head">
                    <h2 class="vendor-management-title">Gestión de tu negocio</h2>
                    <p class="vendor-management-subtitle">Acceso rápido a tu configuración</p>
                </div>

                <div class="vendor-links-grid">
                    <a href="/catalogo-vendedor" class="vendor-link-card">
                        <p class="vendor-link-title">Mis Catálogos</p>
                        <p class="vendor-link-desc">Personaliza tus productos, precios y ofertas</p>
                    </a>
                    <a href="/precios" class="vendor-link-card">
                        <p class="vendor-link-title">Precios</p>
                        <p class="vendor-link-desc">Gestiona los precios vigentes en el sistema</p>
                    </a>
                    <a href="/disponibilidad" class="vendor-link-card">
                        <p class="vendor-link-title">Disponibilidad</p>
                        <p class="vendor-link-desc">Configura tus horarios y disponibilidad</p>
                    </a>
                    <a href="/reglas" class="vendor-link-card">
                        <p class="vendor-link-title">Reglas</p>
                        <p class="vendor-link-desc">Define reglas personalizadas para tu negocio</p>
                    </a>
                </div>
            </section>

            <section class="grid md:grid-cols-4 gap-3 mb-4">
                <article class="crud-card">
                    <p class="crud-muted">Total de items</p>
                    <p class="text-2xl font-bold text-slate-100">{{ safeStats.total_catalogo }}</p>
                </article>
                <article class="crud-card">
                    <p class="crud-muted">Activos</p>
                    <p class="text-2xl font-bold text-emerald-300">{{ safeStats.total_activos }}</p>
                </article>
                <article class="crud-card">
                    <p class="crud-muted">Inactivos</p>
                    <p class="text-2xl font-bold text-rose-300">{{ safeStats.total_inactivos }}</p>
                </article>
                <article class="crud-card">
                    <p class="crud-muted">Promociones activas</p>
                    <p class="text-2xl font-bold text-sky-300">{{ safeStats.promociones_activas }}</p>
                </article>
            </section>

            <section class="grid md:grid-cols-3 gap-3 mb-4">
                <article class="crud-card">
                    <p class="crud-muted">Sin precio actual</p>
                    <p class="text-2xl font-bold text-amber-300">{{ safeStats.sin_precio_actual }}</p>
                </article>
                <article class="crud-card">
                    <p class="crud-muted">Sin disponibilidad</p>
                    <p class="text-2xl font-bold text-amber-300">{{ safeStats.sin_disponibilidad }}</p>
                </article>
                <article class="crud-card">
                    <p class="crud-muted">Sin regla</p>
                    <p class="text-2xl font-bold text-amber-300">{{ safeStats.sin_regla }}</p>
                </article>
            </section>

            <div class="crud-card mb-4">
                <h2 class="font-semibold mb-2 text-slate-100">Mi catalogo</h2>
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Precio actual</th>
                                <th>Estado</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in catalogo" :key="item.id_cv">
                                <td>{{ item.nombre_personalizado }}</td>
                                <td>{{ item.tipo }}</td>
                                <td>{{ formatCurrency(item.precio_actual) }}</td>
                                <td>{{ item.activo ? 'Activo' : 'Inactivo' }}</td>
                                <td>
                                    <a :href="`/catalogo-vendedor/${item.id_cv}/edit`" class="crud-link">Editar</a>
                                </td>
                            </tr>
                            <tr v-if="catalogo.length === 0">
                                <td colspan="5" class="text-center text-slate-400">No hay items para este vendedor.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="crud-card">
                <h2 class="font-semibold mb-2 text-slate-100">Pendientes de configuracion</h2>
                <div class="crud-table-wrap">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Estado</th>
                                <th>Pendiente</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in pendientes" :key="item.id_cv">
                                <td>{{ item.nombre_personalizado }}</td>
                                <td>{{ item.activo ? 'Activo' : 'Inactivo' }}</td>
                                <td>{{ pendingLabel(item) }}</td>
                                <td>
                                    <a :href="`/catalogo-vendedor/${item.id_cv}/edit`" class="crud-link">Completar</a>
                                </td>
                            </tr>
                            <tr v-if="pendientes.length === 0">
                                <td colspan="4" class="text-center text-slate-400">Sin pendientes. Todo en orden.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    vendedorActual: {
        type: Number,
        default: null,
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    catalogo: {
        type: Array,
        default: () => [],
    },
    pendientes: {
        type: Array,
        default: () => [],
    },
});

const safeStats = computed(() => ({
    total_catalogo: props.stats?.total_catalogo ?? 0,
    total_activos: props.stats?.total_activos ?? 0,
    total_inactivos: props.stats?.total_inactivos ?? 0,
    sin_precio_actual: props.stats?.sin_precio_actual ?? 0,
    sin_disponibilidad: props.stats?.sin_disponibilidad ?? 0,
    sin_regla: props.stats?.sin_regla ?? 0,
    promociones_activas: props.stats?.promociones_activas ?? 0,
}));

function formatCurrency(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    const number = Number(value);
    if (Number.isNaN(number)) {
        return '-';
    }

    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
    }).format(number);
}

function pendingLabel(item) {
    const pending = [];

    if (!item.activo) {
        pending.push('activar');
    }
    if (Number(item.falta_precio) === 1) {
        pending.push('precio');
    }
    if (Number(item.falta_disponibilidad) === 1) {
        pending.push('disponibilidad');
    }
    if (Number(item.falta_regla) === 1) {
        pending.push('regla');
    }

    return pending.join(', ');
}
</script>

<style scoped>
.vendor-management-section {
    margin-bottom: 1.5rem;
}

.vendor-management-head {
    margin-bottom: 0.75rem;
}

.vendor-management-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #e2e8f0;
}

.vendor-management-subtitle {
    margin: 0.25rem 0 0;
    font-size: 0.85rem;
    color: #94a3b8;
}

.vendor-links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
}

.vendor-link-card {
    position: relative;
    text-decoration: none;
    color: inherit;
    border: 1px solid rgba(51, 65, 85, 0.8);
    border-radius: 0.6rem;
    padding: 0.85rem 0.75rem;
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.5), rgba(15, 23, 42, 0.3));
    transition: all 0.2s ease;
    overflow: hidden;
}

.vendor-link-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.08), transparent);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.vendor-link-card:hover {
    border-color: rgba(96, 165, 250, 0.5);
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.5));
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(56, 189, 248, 0.1);
}

.vendor-link-card:hover::before {
    opacity: 1;
}

.vendor-link-title {
    margin: 0 0 0.25rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: #7dd3fc;
    position: relative;
    z-index: 1;
}

.vendor-link-desc {
    margin: 0;
    font-size: 0.78rem;
    color: #b0b9c6;
    position: relative;
    z-index: 1;
    line-height: 1.3;
}

@media (max-width: 768px) {
    .vendor-links-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}

@media (max-width: 480px) {
    .vendor-links-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
