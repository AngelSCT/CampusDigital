<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { Link } from '@inertiajs/vue3'

defineOptions({
    layout: AuthLayout
})
</script>

<template>
<div class="dashboard-container">

    <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Gestión de Usuarios
                    </h1>
                    <p class="mt-1 text-sm text-white">Administra los usuarios del sistema</p>
                </div>
                <div class="flex gap-3">
                <div class="relative" v-click-away="() => showExportMenu = false">
                    <button @click="toggleExportMenu" type="button" class="inline-flex items-center px-4 py-2 border border-slate-600 shadow-sm text-sm font-medium rounded-lg text-white bg-gradient-to-br from-slate-700 to-slate-800 hover:from-slate-600 hover:to-slate-700 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
<!-- Menú desplegable -->
<div v-if="showExportMenu" class="absolute right-0 mt-2 w-64 rounded-lg shadow-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-blue-500/20 z-10">
    <div class="py-1">
        <!-- Exportar todos - CSV -->
        <a :href="route('admin.usuarios.export')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200">
            <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <div>
                <div class="font-medium">Todos los usuarios (CSV)</div>
                <div class="text-xs text-slate-400">Exportar lista completa</div>
            </div>
        </a>

            <!-- Exportar todos - PDF -->
            <a :href="route('admin.usuarios.export-pdf')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200 border-t border-slate-700">
                <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <div>
                    <div class="font-medium">Todos los usuarios (PDF)</div>
                    <div class="text-xs text-slate-400">Documento completo</div>
                </div>
            </a>
            
            <!-- Exportar por rol - CSV -->
            <a :href="route('admin.usuarios.export-by-role')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200 border-t border-slate-700">
                <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <div>
                    <div class="font-medium">Reporte por roles (CSV)</div>
                    <div class="text-xs text-slate-400">Usuarios agrupados</div>
                </div>
            </a>

            <!-- Exportar por rol - PDF -->
            <a :href="route('admin.usuarios.export-by-role-pdf')" class="flex items-center px-4 py-3 text-sm text-white hover:bg-slate-700/50 transition-colors duration-200 border-t border-slate-700">
                <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <div>
                    <div class="font-medium">Reporte por roles (PDF)</div>
                    <div class="text-xs text-slate-400">Documento profesional</div>
                </div>
            </a>
        </div>
    </div>
                </div>
                    <a :href="route('admin.usuarios.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-lg shadow-blue-500/30 text-sm font-medium rounded-lg text-white bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Usuario
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 shadow-xl shadow-blue-500/10 rounded-xl border border-blue-500/20 p-4">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Buscar</label>
                        <input 
                            v-model="filterForm.search" 
                            type="text" 
                            placeholder="Nombre, email..." 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Rol</label>
                        <select 
                            v-model="filterForm.rol" 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todos</option>
                            <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Estado</label>
                        <select 
                            v-model="filterForm.estado" 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>

                    <!-- Email Verificado -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Email Verificado</label>
                        <select 
                            v-model="filterForm.verificado" 
                            class="block w-full rounded-lg bg-slate-700/50 border border-slate-600 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200"
                        >
                            <option value="">Todos</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="md:col-span-4 flex justify-end gap-2">
                        <button 
                            type="button" 
                            @click="clearFilters" 
                            class="px-4 py-2 border border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-slate-700/50 transition-all duration-200"
                        >
                            Limpiar
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-gradient-to-br from-blue-600 to-blue-700 border border-transparent rounded-lg text-sm font-medium text-white hover:from-blue-500 hover:to-blue-600 shadow-lg shadow-blue-500/30 transition-all duration-200"
                        >
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>
    </div>
    <!-- MÉTRICAS -->
    <div class="stats-grid">

        <!-- TOTAL -->
        <div class="stat-card primary-card">
            <div class="card-glow primary"></div>

            <div class="stat-header">
                <span class="stat-label">Total recargado</span>
            </div>

            <h2 class="stat-value">${{ total }}</h2>
        </div>

        <!-- EXITOSAS -->
        <div class="stat-card success-card">
            <div class="card-glow success"></div>

            <div class="stat-header">
                <span class="stat-label">Recargas exitosas</span>
            </div>

            <h2 class="stat-value">{{ exitosas }}</h2>
        </div>

        <!-- FALLIDAS -->
        <div class="stat-card error-card">
            <div class="card-glow error"></div>

            <div class="stat-header">
                <span class="stat-label">Recargas fallidas</span>
            </div>

            <h2 class="stat-value">{{ fallidas }}</h2>
        </div>
    </div>

    <!-- ACCIONES -->
    <div class="quick-actions">
        <Link :href="route('admin.recargas.create')" class="quick-card">
            Nueva recarga
        </Link>
    </div>
</div>
</template>

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

    /* TRANSICIÓN SUAVE */
    .stat-card {
        border: 1px solid rgba(59, 130, 246, 0.2);
        transition: all 0.7s ease;
    }

    /* ESTADO NORMAL (AZUL) */
    .primary-card {
        border-color: rgba(59, 130, 246, 0.3);
    }

    /* SUCCESS (VERDE) */
    .success-card {
        border-color: #22c55e;
        box-shadow: 0 0 15px rgba(34, 197, 94, 0.5);
    }

    /* ERROR (ROJO) */
    .error-card {
        border-color: #ef4444;
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.5);
    }

    .card-glow.success {
        background: rgba(0, 255, 51, 0.2);
    }

    .card-glow.error {
        background: rgba(239, 68, 68, 0.2);
    }
}
</style>