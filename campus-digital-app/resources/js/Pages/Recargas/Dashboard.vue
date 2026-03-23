<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { Link } from '@inertiajs/vue3'

defineOptions({
    layout: AuthLayout
})

// Props que vienen de Laravel
defineProps({
    recargas: Array,
    total: Number,
    exitosas: Number,
    fallidas: Number
})
</script>

<template>
<div class="dashboard-container">

    <!-- HEADER -->
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">Módulo de Recargas</h1>
            <p class="dashboard-subtitle">
                Gestión de recargas y comprobantes
            </p>
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

            <h2 class="stat-value"><!--${{ total }}--></h2>
        </div>

        <!-- EXITOSAS -->
        <div class="stat-card success-card">
            <div class="card-glow success"></div>

            <div class="stat-header">
                <span class="stat-label">Recargas exitosas</span>
            </div>

            <h2 class="stat-value"><!--{{ exitosas }}--></h2>
        </div>

        <!-- FALLIDAS -->
        <div class="stat-card error-card">
            <div class="card-glow error"></div>

            <div class="stat-header">
                <span class="stat-label">Recargas fallidas</span>
            </div>

            <h2 class="stat-value"><!--{{ fallidas }}--></h2>
        </div>

    </div>

    <!-- ACCIONES -->
    <div class="quick-actions">
        <Link :href="route('admin.recargas.create')" class="quick-card">
            ➕ Nueva Recarga
        </Link>
    </div>

    <!-- TABLA -->
    <div class="table-container">

        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="recarga in recargas" :key="recarga.id">
                    <td><!--{{ recarga.id }}--></td>

                    <td><!--${{ recarga.monto }}--></td>

                    <td><!--{{ recarga.metodo }}--></td>

                    <td>
                        <span 
                            :class="recarga.estado === 'exitosa' 
                                ? 'badge-success' 
                                : 'badge-error'"
                        >
                            <!--{{ recarga.estado }}-->
                        </span>
                    </td>

                    <td><!--{{ recarga.created_at }}--></td>

                    <td>
                        <button 
                            class="delete-btn"
                            @click="$inertia.delete(route('admin.recargas.destroy', recarga.id))"
                        >
                            Eliminar
                        </button>
                    </td>
                </tr>

                <tr v-if="recargas.length === 0">
                    <td colspan="6" style="text-align:center;">
                        No hay registros
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

</div>
</template>

<style scoped>

/* BADGES */
.badge-success {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.8rem;
}

.badge-error {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.8rem;
}

/* BOTÓN ELIMINAR */
.delete-btn {
    background: #ef4444;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.delete-btn:hover {
    background: #dc2626;
}

</style>