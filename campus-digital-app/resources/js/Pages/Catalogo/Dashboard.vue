<template>
    <div class="catalogo-dashboard">
        <div class="dashboard-pattern"></div>

        <main class="dashboard-shell">
            <header class="hero">
                <p class="hero-tag">Panel de Inventario</p>
                <h1 class="hero-title">Dashboard de Catalogo</h1>
                <p class="hero-subtitle">Resumen operativo de productos, categorias y movimiento de consumo.</p>
                <div class="hero-actions">
                    <a href="/catalogo-usuario-demo" class="hero-btn">Abrir demo de usuario</a>
                </div>
            </header>

            <section class="stats-grid">
                <article class="stat-card">
                    <p class="stat-label">Productos</p>
                    <p class="stat-value">{{ safeStats.total_catalogo }}</p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Categorias</p>
                    <p class="stat-value">{{ safeStats.total_categorias }}</p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Areas</p>
                    <p class="stat-value">{{ safeStats.total_areas }}</p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Vendedores</p>
                    <p class="stat-value">{{ safeStats.total_vendedores }}</p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Promociones</p>
                    <p class="stat-value">{{ safeStats.total_promociones }}</p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Movimientos</p>
                    <p class="stat-value">{{ safeStats.total_movimientos }}</p>
                </article>
            </section>

            <section class="stats-grid">
                <article class="stat-card stat-card-soft">
                    <p class="stat-label">Catalogo activo</p>
                    <p class="stat-value">{{ safeStats.total_activos }}</p>
                </article>
                <article class="stat-card stat-card-soft">
                    <p class="stat-label">Catalogo inactivo</p>
                    <p class="stat-value">{{ safeStats.total_inactivos }}</p>
                </article>
                <article class="stat-card stat-card-alert">
                    <p class="stat-label">Sin precio actual</p>
                    <p class="stat-value">{{ safeStats.sin_precio_actual }}</p>
                </article>
                <article class="stat-card stat-card-alert">
                    <p class="stat-label">Sin disponibilidad o regla</p>
                    <p class="stat-value">{{ safeStats.sin_disponibilidad + safeStats.sin_regla }}</p>
                </article>
            </section>

            <section class="panel-card mb-panel">
                <div class="panel-head">
                    <h2>Control de CRUDs</h2>
                    <span>Acceso rapido por modulo</span>
                </div>

                <div class="crud-grid">
                    <a v-for="item in crudSummary" :key="item.title" :href="item.href" class="crud-card">
                        <p class="crud-title">{{ item.title }}</p>
                        <p class="crud-count">{{ item.count }}</p>
                        <p class="crud-cta">{{ item.cta }}</p>
                    </a>
                </div>
            </section>

            <section class="content-grid mb-panel">
                <article class="panel-card">
                    <div class="panel-head">
                        <h2>Grafica de consumo top</h2>
                        <span>Base: tabla movimientos</span>
                    </div>

                    <div v-if="top?.length" class="bar-chart">
                        <div v-for="item in top" :key="`bar-${item.nombre}`" class="bar-row">
                            <div class="bar-label">{{ item.nombre }}</div>
                            <div class="bar-track">
                                <div class="bar-fill" :style="{ width: topWidth(item.total) }"></div>
                            </div>
                            <div class="bar-value">{{ item.total }}</div>
                        </div>
                    </div>
                    <p v-else class="empty-state">Sin datos para graficar.</p>
                </article>

                <article class="panel-card">
                    <div class="panel-head">
                        <h2>Estado de catalogo</h2>
                        <span>Base: tabla catalogo</span>
                    </div>

                    <div class="donut-wrap">
                        <div class="donut-chart" :style="{ background: activeDonutGradient }">
                            <div class="donut-center">
                                <strong>{{ safeStats.total_catalogo }}</strong>
                                <span>Total</span>
                            </div>
                        </div>

                        <div class="donut-legend">
                            <p><span class="legend-dot legend-active"></span> Activos: {{ safeStats.total_activos }}</p>
                            <p><span class="legend-dot legend-inactive"></span> Inactivos: {{ safeStats.total_inactivos }}</p>
                        </div>
                    </div>

                    <div class="coverage-list">
                        <div class="coverage-row">
                            <div class="coverage-head">
                                <span>Cobertura de precio</span>
                                <span>{{ percentLabel(priceCoverage) }}</span>
                            </div>
                            <div class="coverage-track">
                                <div class="coverage-fill" :style="{ width: `${priceCoverage}%` }"></div>
                            </div>
                        </div>
                        <div class="coverage-row">
                            <div class="coverage-head">
                                <span>Cobertura de disponibilidad</span>
                                <span>{{ percentLabel(disponibilidadCoverage) }}</span>
                            </div>
                            <div class="coverage-track">
                                <div class="coverage-fill" :style="{ width: `${disponibilidadCoverage}%` }"></div>
                            </div>
                        </div>
                        <div class="coverage-row">
                            <div class="coverage-head">
                                <span>Cobertura de reglas</span>
                                <span>{{ percentLabel(reglaCoverage) }}</span>
                            </div>
                            <div class="coverage-track">
                                <div class="coverage-fill" :style="{ width: `${reglaCoverage}%` }"></div>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <section class="content-grid">
                <article class="panel-card">
                    <div class="panel-head">
                        <h2>Top de consumo</h2>
                        <span>Top 5</span>
                    </div>

                    <table class="ranking-table" v-if="top?.length">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Consumo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in top" :key="item.nombre">
                                <td>{{ item.nombre }}</td>
                                <td>{{ item.total }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="empty-state">Aun no hay consumos registrados.</p>
                </article>

                <article class="panel-card">
                    <div class="panel-head">
                        <h2>Consumo por categoria</h2>
                        <span>Ranking</span>
                    </div>

                    <div v-if="categorias?.length" class="category-list">
                        <div class="category-row" v-for="item in categorias" :key="item.nombre">
                            <div class="category-title">{{ item.nombre }}</div>
                            <div class="category-track">
                                <div class="category-fill" :style="{ width: categoryWidth(item.total) }"></div>
                            </div>
                            <div class="category-total">{{ item.total }}</div>
                        </div>
                    </div>
                    <p v-else class="empty-state">Aun no hay categorias con consumo.</p>
                </article>
            </section>

            <section class="panel-card mt-panel">
                <div class="panel-head">
                    <h2>Elementos por atender</h2>
                    <span>Configuracion pendiente en CRUD</span>
                </div>

                <table class="ranking-table" v-if="pendientes?.length">
                    <thead>
                        <tr>
                            <th>Catalogo</th>
                            <th>Estado</th>
                            <th>Pendiente</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in pendientes" :key="item.id_catalogo">
                            <td>{{ item.nombre }}</td>
                            <td>{{ item.activo ? 'Activo' : 'Inactivo' }}</td>
                            <td>{{ pendingLabel(item) }}</td>
                            <td>
                                <a :href="`/catalogo/${item.id_catalogo}/edit`" class="action-link">Completar</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="empty-state">No hay pendientes en catalogo. Buen trabajo.</p>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({})
    },
    top: {
        type: Array,
        default: () => []
    },
    categorias: {
        type: Array,
        default: () => []
    },
    pendientes: {
        type: Array,
        default: () => []
    },
    crudSummary: {
        type: Array,
        default: () => []
    }
});

const safeStats = computed(() => ({
    total_catalogo: props.stats?.total_catalogo ?? 0,
    total_categorias: props.stats?.total_categorias ?? 0,
    total_areas: props.stats?.total_areas ?? 0,
    total_vendedores: props.stats?.total_vendedores ?? 0,
    total_promociones: props.stats?.total_promociones ?? 0,
    total_movimientos: props.stats?.total_movimientos ?? 0,
    total_activos: props.stats?.total_activos ?? 0,
    total_inactivos: props.stats?.total_inactivos ?? 0,
    sin_precio_actual: props.stats?.sin_precio_actual ?? 0,
    sin_disponibilidad: props.stats?.sin_disponibilidad ?? 0,
    sin_regla: props.stats?.sin_regla ?? 0
}));

const maxCategoria = computed(() => {
    if (!props.categorias.length) {
        return 1;
    }

    return Math.max(...props.categorias.map((item) => Number(item.total) || 0), 1);
});

const maxTop = computed(() => {
    if (!props.top.length) {
        return 1;
    }

    return Math.max(...props.top.map((item) => Number(item.total) || 0), 1);
});

const activeDonutGradient = computed(() => {
    const total = safeStats.value.total_catalogo || 1;
    const activePercent = Math.round((safeStats.value.total_activos / total) * 100);
    return `conic-gradient(#22c55e 0% ${activePercent}%, #ef4444 ${activePercent}% 100%)`;
});

const priceCoverage = computed(() => coveragePercent(safeStats.value.total_catalogo, safeStats.value.sin_precio_actual));
const disponibilidadCoverage = computed(() => coveragePercent(safeStats.value.total_catalogo, safeStats.value.sin_disponibilidad));
const reglaCoverage = computed(() => coveragePercent(safeStats.value.total_catalogo, safeStats.value.sin_regla));

function categoryWidth(total) {
    const value = Number(total) || 0;
    return `${Math.max((value / maxCategoria.value) * 100, 8)}%`;
}

function topWidth(total) {
    const value = Number(total) || 0;
    return `${Math.max((value / maxTop.value) * 100, 6)}%`;
}

function coveragePercent(total, missing) {
    if (!total) {
        return 0;
    }

    return Math.max(0, Math.min(100, Math.round(((total - missing) / total) * 100)));
}

function percentLabel(value) {
    return `${value}%`;
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
.catalogo-dashboard {
    --bg-start: #0a1a2f;
    --bg-mid: #0f2e4f;
    --bg-end: #1b2540;
    --surface: rgba(15, 23, 42, 0.78);
    --surface-soft: rgba(30, 41, 59, 0.5);
    --border: rgba(96, 165, 250, 0.26);
    --text: #e2e8f0;
    --text-soft: #93a9c6;
    --accent: #60a5fa;
    --accent-2: #38bdf8;
    min-height: 100vh;
    position: relative;
    background: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-mid) 50%, var(--bg-end) 100%);
    overflow: hidden;
}

.dashboard-pattern {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 15% 18%, rgba(56, 189, 248, 0.18), transparent 32%),
        radial-gradient(circle at 85% 75%, rgba(59, 130, 246, 0.22), transparent 35%);
    opacity: 0.75;
    pointer-events: none;
}

.dashboard-shell {
    position: relative;
    z-index: 1;
    width: min(1200px, 92%);
    margin: 0 auto;
    padding: 2.25rem 0 3rem;
}

.hero {
    margin-bottom: 1.5rem;
    animation: slideUp 0.55s ease both;
}

.hero-tag {
    display: inline-block;
    margin: 0;
    padding: 0.35rem 0.75rem;
    font-size: 0.82rem;
    letter-spacing: 0.04em;
    border-radius: 999px;
    color: #cbe7ff;
    border: 1px solid var(--border);
    background: rgba(37, 99, 235, 0.15);
}

.hero-title {
    margin: 0.8rem 0 0.35rem;
    font-size: clamp(1.8rem, 3vw, 2.5rem);
    font-weight: 800;
    line-height: 1.1;
    color: var(--text);
}

.hero-subtitle {
    margin: 0;
    color: var(--text-soft);
    max-width: 64ch;
}

.hero-actions {
    margin-top: 0.9rem;
}

.hero-btn {
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.52rem 0.9rem;
    border-radius: 0.6rem;
    font-weight: 600;
    font-size: 0.84rem;
    color: #e0f2fe;
    border: 1px solid rgba(125, 211, 252, 0.42);
    background: linear-gradient(135deg, rgba(2, 132, 199, 0.94), rgba(3, 105, 161, 0.92));
    box-shadow: 0 8px 24px rgba(2, 132, 199, 0.24);
    transition: transform 0.12s ease, opacity 0.2s ease;
}

.hero-btn:hover {
    transform: translateY(-1px);
    opacity: 0.95;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.9rem;
    margin-bottom: 1.2rem;
}

.stat-card {
    background: linear-gradient(160deg, rgba(30, 41, 59, 0.65), rgba(15, 23, 42, 0.85));
    border: 1px solid var(--border);
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 8px 26px rgba(0, 0, 0, 0.25);
    backdrop-filter: blur(8px);
    animation: slideUp 0.6s ease both;
}

.stat-card-soft {
    border-color: rgba(125, 211, 252, 0.25);
}

.stat-card-alert {
    border-color: rgba(251, 191, 36, 0.4);
    background: linear-gradient(160deg, rgba(59, 43, 18, 0.35), rgba(15, 23, 42, 0.85));
}

.stat-label {
    margin: 0;
    color: var(--text-soft);
    font-size: 0.9rem;
}

.stat-value {
    margin: 0.35rem 0 0;
    color: var(--text);
    font-size: clamp(1.35rem, 2vw, 1.9rem);
    font-weight: 800;
}

.quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
    gap: 0.8rem;
    margin-bottom: 1.2rem;
}

.quick-link {
    text-decoration: none;
    text-align: center;
    color: var(--text);
    font-weight: 600;
    border-radius: 0.85rem;
    padding: 0.75rem 0.6rem;
    border: 1px solid rgba(125, 211, 252, 0.24);
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.22), rgba(59, 130, 246, 0.24));
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    animation: slideUp 0.65s ease both;
}

.quick-link:hover {
    transform: translateY(-2px);
    border-color: rgba(125, 211, 252, 0.45);
    box-shadow: 0 10px 22px rgba(30, 64, 175, 0.32);
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.bar-chart {
    display: grid;
    gap: 0.55rem;
}

.bar-row {
    display: grid;
    grid-template-columns: minmax(120px, 1.2fr) minmax(140px, 3fr) auto;
    align-items: center;
    gap: 0.65rem;
}

.bar-label {
    color: #dce8f7;
    font-size: 0.84rem;
}

.bar-track {
    height: 0.62rem;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.22);
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #38bdf8, #60a5fa);
}

.bar-value {
    color: #e2e8f0;
    font-weight: 700;
    font-size: 0.82rem;
}

.donut-wrap {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.8rem;
}

.donut-chart {
    width: 108px;
    height: 108px;
    border-radius: 999px;
    display: grid;
    place-items: center;
}

.donut-center {
    width: 72px;
    height: 72px;
    border-radius: 999px;
    background: #0f172a;
    display: grid;
    place-items: center;
    color: #e2e8f0;
}

.donut-center strong {
    font-size: 1rem;
    line-height: 1;
}

.donut-center span {
    font-size: 0.72rem;
    color: #93a9c6;
}

.donut-legend p {
    margin: 0 0 0.32rem;
    color: #dce8f7;
    font-size: 0.85rem;
}

.legend-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 999px;
    margin-right: 0.35rem;
}

.legend-active {
    background: #22c55e;
}

.legend-inactive {
    background: #ef4444;
}

.coverage-list {
    display: grid;
    gap: 0.55rem;
}

.coverage-head {
    display: flex;
    justify-content: space-between;
    color: #c7d5ea;
    font-size: 0.82rem;
    margin-bottom: 0.22rem;
}

.coverage-track {
    height: 0.55rem;
    border-radius: 999px;
    overflow: hidden;
    background: rgba(148, 163, 184, 0.2);
}

.coverage-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #10b981, #06b6d4);
}

.crud-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.75rem;
}

.crud-card {
    text-decoration: none;
    color: var(--text);
    border: 1px solid rgba(147, 197, 253, 0.28);
    border-radius: 0.85rem;
    padding: 0.75rem;
    background: rgba(30, 41, 59, 0.45);
    transition: border-color 0.2s ease, transform 0.2s ease;
}

.crud-card:hover {
    border-color: rgba(147, 197, 253, 0.5);
    transform: translateY(-2px);
}

.crud-title {
    margin: 0;
    color: var(--text-soft);
    font-size: 0.82rem;
}

.crud-count {
    margin: 0.32rem 0;
    font-size: 1.3rem;
    font-weight: 800;
}

.crud-cta {
    margin: 0;
    font-size: 0.78rem;
    color: #7dd3fc;
}

.panel-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 1rem;
    padding: 1rem;
    backdrop-filter: blur(10px);
    animation: slideUp 0.72s ease both;
}

.panel-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 0.75rem;
}

.panel-head h2 {
    margin: 0;
    color: var(--text);
    font-size: 1.05rem;
}

.panel-head span {
    color: var(--text-soft);
    font-size: 0.82rem;
}

.ranking-table {
    width: 100%;
    border-collapse: collapse;
}

.ranking-table th,
.ranking-table td {
    text-align: left;
    padding: 0.62rem 0.45rem;
}

.ranking-table th {
    color: #b6c5da;
    font-weight: 600;
    font-size: 0.82rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
}

.ranking-table td {
    color: #e5edf8;
    border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}

.category-list {
    display: grid;
    gap: 0.8rem;
}

.category-row {
    display: grid;
    grid-template-columns: minmax(120px, 1fr) minmax(130px, 3fr) auto;
    gap: 0.7rem;
    align-items: center;
}

.category-title {
    color: #dce8f7;
    font-size: 0.9rem;
}

.category-track {
    position: relative;
    height: 0.58rem;
    border-radius: 999px;
    overflow: hidden;
    background: rgba(148, 163, 184, 0.22);
}

.category-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--accent-2), var(--accent));
}

.category-total {
    color: #e2e8f0;
    font-weight: 700;
    font-size: 0.85rem;
    min-width: 2.2rem;
    text-align: right;
}

.empty-state {
    margin: 0.4rem 0 0;
    color: var(--text-soft);
}

.action-link {
    color: #7dd3fc;
    text-decoration: none;
    font-weight: 600;
}

.mb-panel {
    margin-bottom: 1rem;
}

.mt-panel {
    margin-top: 1rem;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 900px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .dashboard-shell {
        width: 93%;
        padding-top: 1.3rem;
    }

    .hero-subtitle {
        font-size: 0.95rem;
    }

    .category-row {
        grid-template-columns: 1fr;
        gap: 0.4rem;
    }

    .category-total {
        text-align: left;
    }

    .bar-row {
        grid-template-columns: 1fr;
        gap: 0.3rem;
    }

    .donut-wrap {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>