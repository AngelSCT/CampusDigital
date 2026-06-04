<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthLayout from '@/Components/AuthLayout.vue';

const monto = ref('')
const props = defineProps({
    saldo: Object
});

const enviar = () => {
    if (!monto.value) {
        alert('Ingresa un monto')
        return
    }

    router.post('/modulo_8/recargar', {
        monto: monto.value,
        metodo_pago: 'Tarjeta'
    }, {
        onSuccess: () => {
            alert('Recarga exitosa')
            monto.value = ''
        }
    })
}
</script>

<template>
<AuthLayout>
    <div class="dashboard-container">

        <!-- Header -->
        <div class="dashboard-header">
            <h1 class="dashboard-title">Recargar saldo</h1>
            <p class="dashboard-subtitle">
                Agrega crédito a tu cuenta de forma rápida y segura
            </p>
        </div>

        <div class="stats-grid">

    <div class="stat-card primary-card">
        <div class="card-glow primary"></div>

        <div class="stat-header">
            <div class="stat-icon-box primary">
                <!-- Icono -->
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" />
                </svg>
            </div>

            <div>
                <p class="stat-label">Saldo actual</p>
            </div>
        </div>

        <div class="stat-value-section">
            <p class="stat-number">
                ${{ $props.saldo.saldo }}
            </p>
        </div>

        <a href="#" class="stat-link">
            <!--<span>Ver movimientos</span>-->
            <svg class="link-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

</div>

        <!-- Card principal -->
        <div class="content-grid">
            <div class="content-card">

                <div class="content-header">
                    <h2 class="content-title">Nueva recarga</h2>
                </div>

                <div class="content-body">

                    <!-- Input -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="stat-label">Monto</label>
                        <input
                            v-model="monto"
                            type="number"
                            placeholder="Ej. 100"
                            style="
                                width: 100%;
                                margin-top: 0.5rem;
                                padding: 0.75rem 1rem;
                                border-radius: 0.75rem;
                                border: 1px solid rgba(59,130,246,0.3);
                                background: #0f172a;
                                color: white;
                                outline: none;
                                transition: 0.2s;
                            "
                            onfocus="this.style.borderColor='#3b82f6'"
                            onblur="this.style.borderColor='rgba(59,130,246,0.3)'"
                        >
                    </div>

                    <!-- Botón -->
                    <button
                        @click="enviar"
                        style="
                            width: 100%;
                            padding: 0.9rem;
                            border-radius: 0.75rem;
                            border: none;
                            font-weight: 700;
                            cursor: pointer;
                            color: white;
                            background: linear-gradient(135deg, #1E40AF 0%, #3b82f6 100%);
                            box-shadow: 0 10px 25px rgba(30, 64, 175, 0.4);
                            transition: all 0.3s ease;
                        "
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'"
                    >
                        Recargar saldo
                    </button>

                </div>
            </div>
        </div>

    </div>
</AuthLayout>
</template>