<template>
    <AuthLayout>
        <div class="dashboard-container">
            <!-- Header -->
            <div class="dashboard-header">
                <div>
                    <h1 class="dashboard-title">Dashboard - Administrador</h1>
                    <p class="dashboard-subtitle">Panel de control y métricas del sistema</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <!-- Usuarios Activos -->
                <div class="stat-card primary-card">
                    <div class="card-glow primary"></div>
                    <div class="stat-header">
                        <div class="stat-icon-box primary">
                            <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="stat-label">Usuarios Activos</span>
                    </div>
                    <div class="stat-value-section">
                        <h2 class="stat-number">{{ stats.usuarios_activos }}</h2>
                    </div>
                    <a :href="route('admin.usuarios.index')" class="stat-link primary">
                        <span>Ver todos</span>
                        <svg class="link-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>

                <!-- Accesos Exitosos -->
                <div class="stat-card success-card">
                    <div class="card-glow success"></div>
                    <div class="stat-header">
                        <div class="stat-icon-box success">
                            <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="stat-label">Accesos Exitosos (7d)</span>
                    </div>
                    <div class="stat-value-section">
                        <h2 class="stat-number">{{ stats.accesos_exitosos }}</h2>
                    </div>
                    <a :href="route('admin.bitacora.accesos')" class="stat-link success">
                        <span>Ver bitácora de Accesos</span>
                        <svg class="link-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>

                <!-- Accesos Fallidos -->
                <div class="stat-card error-card">
                    <div class="card-glow error"></div>
                    <div class="stat-header">
                        <div class="stat-icon-box error">
                            <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="stat-label">Accesos Fallidos (7d)</span>
                    </div>
                    <div class="stat-value-section">
                        <h2 class="stat-number">{{ stats.accesos_fallidos }}</h2>
                    </div>
                    <a :href="route('admin.bitacora.accesos')" class="stat-link error">
                        <span>Ver bitácora de Accesos</span>
                        <svg class="link-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Accesos Rápidos -->
            <div class="section">
                <h3 class="section-title">Accesos Rápidos</h3>
                <div class="quick-grid">
                    <a :href="route('admin.usuarios.index')" class="quick-card primary">
                        <svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Usuarios</span>
                    </a>

                    <a :href="route('admin.roles.index')" class="quick-card success">
                        <svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Roles</span>
                    </a>

                    <a :href="route('admin.permisos.index')" class="quick-card warning">
                        <svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <span>Permisos</span>
                    </a>

                    <a :href="route('admin.bitacora.actividad')" class="quick-card secondary">
                        <svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Bitácora de Actividad</span>
                    </a>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Usuarios por Rol -->
                <div class="content-card">
                    <div class="content-header">
                        <h3 class="content-title">Usuarios por Rol</h3>
                    </div>
                    <div class="content-body">
                        <div class="roles-list">
                            <div v-for="rol in stats.usuarios_por_rol" :key="rol.nombre" class="role-row">
                                <span class="role-name">{{ rol.nombre }}</span>
                                <div class="role-stats">
                                    <span class="role-count">{{ rol.total }}</span>
                                    <span class="role-label">usuario(s)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="content-card">
                    <div class="content-header">
                        <h3 class="content-title">Actividad Reciente</h3>
                    </div>
                    <div class="content-body">
                        <div class="activity-table-wrapper">
                            <table class="activity-table">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Evento</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="actividad in stats.actividad_reciente" :key="actividad.id">
                                        <td class="user-col">{{ actividad.email_intentado || 'N/A' }}</td>
                                        <td class="event-col">{{ actividad.evento }}</td>
                                        <td>
                                            <span class="badge" :class="actividad.exito ? 'badge-success' : 'badge-error'">
                                                {{ actividad.exito ? 'Éxito' : 'Fallido' }}
                                            </span>
                                        </td>
                                        <td class="date-col">{{ formatDate(actividad.created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    stats: Object,
});

function formatDate(date) {
    return new Date(date).toLocaleString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap');

.dashboard-container {
    font-family: 'Inter', sans-serif;
    padding: 2rem;
    min-height: 100vh;
    background: #0f172a;
}

/* Header */
.dashboard-header {
    margin-bottom: 2.5rem;
}

.dashboard-title {
    font-size: 2.25rem;
    font-weight: 800;
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}

.dashboard-subtitle {
    font-family: 'Roboto', sans-serif;
    color: #ffffff;
    font-size: 1rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    position: relative;
    background: #1e293b;
    border: 1px solid;
    border-radius: 1.25rem;
    padding: 1.75rem;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, currentColor 0%, transparent 100%);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.primary-card {
    border-color: rgba(30, 64, 175, 0.5);
    color: #3b82f6;
}

.success-card {
    border-color: rgba(22, 163, 74, 0.5);
    color: #22c55e;
}

.error-card {
    border-color: rgba(220, 38, 38, 0.5);
    color: #ef4444;
}

.card-glow {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    opacity: 0.03;
    pointer-events: none;
}

.card-glow.primary {
    background: radial-gradient(circle, #1E40AF 0%, transparent 70%);
}

.card-glow.success {
    background: radial-gradient(circle, #16A34A 0%, transparent 70%);
}

.card-glow.error {
    background: radial-gradient(circle, #DC2626 0%, transparent 70%);
}

.stat-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-icon-box {
    width: 56px;
    height: 56px;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon-box.primary {
    background: linear-gradient(135deg, #1E40AF 0%, #3b82f6 100%);
    box-shadow: 0 8px 20px rgba(30, 64, 175, 0.4);
}

.stat-icon-box.success {
    background: linear-gradient(135deg, #16A34A 0%, #22c55e 100%);
    box-shadow: 0 8px 20px rgba(22, 163, 74, 0.4);
}

.stat-icon-box.error {
    background: linear-gradient(135deg, #DC2626 0%, #ef4444 100%);
    box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);
}

.stat-icon {
    width: 32px;
    height: 32px;
    color: white;
}

.stat-label {
    font-family: 'Roboto', sans-serif;
    color: #ffffff;
    font-size: 0.9rem;
    font-weight: 500;
}

.stat-value-section {
    margin-bottom: 1.5rem;
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1;
}

.stat-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.stat-link span {
    color: currentColor;
}

.link-arrow {
    width: 18px;
    height: 18px;
    transition: transform 0.2s ease;
}

.stat-link:hover .link-arrow {
    transform: translateX(4px);
}

/* Section */
.section {
    margin-bottom: 2.5rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 1.25rem;
}

/* Quick Grid */
.quick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
}

.quick-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: #1e293b;
    border: 1px solid;
    border-radius: 1rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.quick-card:hover {
    transform: translateX(8px);
}

.quick-card.primary {
    border-color: rgba(30, 64, 175, 0.4);
}

.quick-card.primary:hover {
    border-color: #1E40AF;
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.15) 0%, rgba(59, 130, 246, 0.1) 100%);
}

.quick-card.success {
    border-color: rgba(22, 163, 74, 0.4);
}

.quick-card.success:hover {
    border-color: #16A34A;
    background: linear-gradient(135deg, rgba(22, 163, 74, 0.15) 0%, rgba(34, 197, 94, 0.1) 100%);
}

.quick-card.warning {
    border-color: rgba(245, 158, 11, 0.4);
}

.quick-card.warning:hover {
    border-color: #F59E0B;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(251, 191, 36, 0.1) 100%);
}

.quick-card.secondary {
    border-color: rgba(100, 116, 139, 0.4);
}

.quick-card.secondary:hover {
    border-color: #64748B;
    background: linear-gradient(135deg, rgba(100, 116, 139, 0.15) 0%, rgba(148, 163, 184, 0.1) 100%);
}

.quick-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
}

.quick-card.primary .quick-icon {
    color: #3b82f6;
}

.quick-card.success .quick-icon {
    color: #22c55e;
}

.quick-card.warning .quick-icon {
    color: #fbbf24;
}

.quick-card.secondary .quick-icon {
    color: #94a3b8;
}

.quick-card span {
    font-size: 1rem;
    font-weight: 600;
    color: #ffffff;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 1.5rem;
}

.content-card {
    background: #1e293b;
    border: 1px solid rgba(30, 64, 175, 0.3);
    border-radius: 1.25rem;
    overflow: hidden;
}

.content-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(30, 64, 175, 0.2);
}

.content-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffffff;
}

.content-body {
    padding: 1.5rem;
}

/* Roles List */
.roles-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.role-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem;
    background: #0f172a;
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 0.875rem;
    transition: all 0.2s ease;
}

.role-row:hover {
    background: #1e293b;
    border-color: rgba(59, 130, 246, 0.4);
    transform: translateX(4px);
}

.role-name {
    font-family: 'Roboto', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    color: #3b82f6;
    text-transform: capitalize;
}

.role-stats {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.role-count {
    font-size: 1.75rem;
    font-weight: 800;
    color: #ffffff;
}

.role-label {
    font-size: 0.8125rem;
    color: #ffffff;
}

/* Activity Table */
.activity-table-wrapper {
    overflow-x: auto;
}

.activity-table {
    width: 100%;
    border-collapse: collapse;
}

.activity-table thead {
    background: #0f172a;
}

.activity-table th {
    padding: 1rem;
    text-align: left;
    font-family: 'Roboto', sans-serif;
    font-size: 0.75rem;
    font-weight: 700;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.activity-table tbody tr {
    border-bottom: 1px solid rgba(30, 64, 175, 0.15);
    transition: background 0.2s ease;
}

.activity-table tbody tr:hover {
    background: #0f172a;
}

.activity-table td {
    padding: 1rem;
    font-size: 0.9rem;
}

.user-col {
    color: #ffffff;
    font-weight: 500;
}

.event-col {
    color: #ffffff;
}

.date-col {
    color: #ffffff;
    font-size: 0.8125rem;
}

.badge {
    display: inline-flex;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.025em;
}

.badge-success {
    background: rgba(22, 163, 74, 0.2);
    color: #86efac;
    border: 1px solid rgba(34, 197, 94, 0.4);
}

.badge-error {
    background: rgba(220, 38, 38, 0.2);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.4);
}

/* Responsive */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1.5rem 1rem;
    }

    .dashboard-title {
        font-size: 1.875rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .quick-grid {
        grid-template-columns: 1fr;
    }

    .stat-number {
        font-size: 2.5rem;
    }
}

@media (max-width: 480px) {
    .dashboard-title {
        font-size: 1.5rem;
    }

    .stat-number {
        font-size: 2rem;
    }

    .content-grid {
        grid-template-columns: 1fr;
    }
}
</style>