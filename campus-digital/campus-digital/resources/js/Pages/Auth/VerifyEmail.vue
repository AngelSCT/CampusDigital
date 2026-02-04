<template>
    <div class="verify-wrapper">
        <!-- Fondo oscuro con patrón -->
        <div class="background-pattern"></div>
        
        <div class="verify-container">
            <!-- Logos institucionales -->
            <div class="institutional-logos">
                <div class="logo-box">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="logo-box">
                    <i class="fas fa-university"></i>
                </div>
                <div class="logo-box">
                    <i class="fas fa-book"></i>
                </div>
            </div>

            <!-- Card principal -->
            <div class="verify-card">
                <!-- Header -->
                <div class="verify-header">
                    <div class="icon-wrapper">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div class="brand-section">
                        <h1 class="brand-title">
                            <span class="brand-highlight">Verificar</span>
                            <span class="brand-normal">Email</span>
                        </h1>
                    </div>
                    <p class="subtitle">
                        Gracias por registrarte. Antes de continuar, verifica tu dirección de correo electrónico haciendo clic en el enlace que te enviamos.
                    </p>
                </div>

                <!-- Alert de éxito -->
                <div v-if="status === 'verification-link-sent'" class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div class="alert-content">
                        <p class="alert-title">¡Enlace enviado!</p>
                        <p class="alert-message">Se ha enviado un nuevo enlace de verificación a tu correo electrónico.</p>
                    </div>
                </div>

                <!-- Info adicional -->
                <div class="info-box">
                    <div class="info-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="info-content">
                        <p class="info-title">¿No recibiste el correo?</p>
                        <p class="info-text">Revisa tu carpeta de spam o correo no deseado. Si aún no lo encuentras, puedes solicitar un nuevo enlace.</p>
                    </div>
                </div>

                <!-- Formulario -->
                <form @submit.prevent="submit" class="verify-form">
                    <button type="submit" class="btn-resend" :disabled="form.processing">
                        <span v-if="form.processing">
                            <i class="fas fa-spinner fa-spin"></i>
                            Enviando...
                        </span>
                        <span v-else>
                            <i class="fas fa-paper-plane"></i>
                            Reenviar Email de Verificación
                        </span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="verify-footer">
                    <div class="divider">
                        <span>o</span>
                    </div>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="logout-link"
                    >
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </Link>
                </div>
            </div>

            <!-- Tips de verificación -->
            <div class="tips-section">
                <p class="tips-title">
                    <i class="fas fa-lightbulb"></i>
                    Consejos útiles:
                </p>
                <ul class="tips-list">
                    <li>
                        <i class="fas fa-check"></i>
                        El enlace de verificación expira en 60 minutos
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        Revisa la carpeta de spam si no lo ves
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        Asegúrate de que el correo sea correcto
                    </li>
                </ul>
            </div>

            <!-- Icono de seguridad -->
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Verificación segura</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Wrapper principal */
.verify-wrapper {
    min-height: 100vh;
    position: relative;
        background: linear-gradient(
    135deg,
    #0a1a2f 0%,
    #0f2e4f 35%,
    #123a5f 50%,
    #1a2b4a 65%,
    #3b1b3f 85%,
    #4a1f3d 100%
    );
    overflow: hidden;
}

/* Patrón de fondo optimizado */
.background-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.03;
    background-image: 
        radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.2) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(59, 130, 246, 0.2) 0%, transparent 50%);
    pointer-events: none;
}

/* Container principal */
.verify-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    position: relative;
    z-index: 1;
}

/* Logos institucionales */
.institutional-logos {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.logo-box {
    width: 70px;
    height: 70px;
    background: rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.3);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.logo-box:hover {
    background: rgba(59, 130, 246, 0.2);
    border-color: rgba(59, 130, 246, 0.5);
    transform: translateY(-5px);
}

.logo-box i {
    font-size: 2rem;
    color: #3b82f6;
}

/* Card de verificación */
.verify-card {
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 1.5rem;
    padding: 2.5rem;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* Header */
.verify-header {
    text-align: center;
    margin-bottom: 2rem;
}

.icon-wrapper {
    width: 90px;
    height: 90px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(37, 99, 235, 0.2) 100%);
    border: 2px solid rgba(59, 130, 246, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-wrapper i {
    font-size: 3rem;
    color: #3b82f6;
}

.brand-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    line-height: 1;
}

.brand-highlight {
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.brand-normal {
    color: #e2e8f0;
}

.subtitle {
    color: #94a3b8;
    font-size: 0.9rem;
    line-height: 1.6;
    max-width: 440px;
    margin: 0 auto;
}

/* Alert de éxito */
.alert-success {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
}

.alert-success i {
    font-size: 1.5rem;
    color: #22c55e;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.alert-content {
    flex: 1;
}

.alert-title {
    color: #86efac;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.alert-message {
    color: #6ee7b7;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Info box */
.info-box {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: rgba(30, 41, 59, 0.5);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
}

.info-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-icon i {
    color: #3b82f6;
    font-size: 1.25rem;
}

.info-content {
    flex: 1;
}

.info-title {
    color: #e2e8f0;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.375rem;
}

.info-text {
    color: #94a3b8;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Formulario */
.verify-form {
    margin-bottom: 1.5rem;
}

/* Botón de reenviar */
.btn-resend {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    border-radius: 0.75rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-resend:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
}

.btn-resend:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Footer */
.verify-footer {
    padding-top: 1.5rem;
    border-top: 1px solid rgba(59, 130, 246, 0.1);
    text-align: center;
}

.divider {
    text-align: center;
    position: relative;
    margin-bottom: 1rem;
}

.divider span {
    color: #64748b;
    font-size: 0.875rem;
    background: rgba(15, 23, 42, 0.8);
    padding: 0 1rem;
    position: relative;
    z-index: 1;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(59, 130, 246, 0.1);
}

.logout-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #ef4444;
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.3);
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.logout-link:hover {
    background: rgba(220, 38, 38, 0.2);
    border-color: rgba(220, 38, 38, 0.5);
}

.logout-link i {
    font-size: 0.875rem;
}

/* Tips section */
.tips-section {
    margin-top: 1.5rem;
    max-width: 520px;
    width: 100%;
}

.tips-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #94a3b8;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.tips-title i {
    color: #fbbf24;
    font-size: 1rem;
}

.tips-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.tips-list li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    font-size: 0.875rem;
}

.tips-list li i {
    color: #22c55e;
    font-size: 0.75rem;
    flex-shrink: 0;
}

/* Badge de seguridad */
.security-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
    color: #64748b;
    font-size: 0.875rem;
}

.security-badge i {
    color: #3b82f6;
    font-size: 1rem;
}

/* Responsive */
@media (max-width: 640px) {
    .verify-container {
        padding: 1.5rem 1rem;
    }

    .institutional-logos {
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .logo-box {
        width: 60px;
        height: 60px;
    }

    .logo-box i {
        font-size: 1.75rem;
    }

    .verify-card {
        padding: 2rem 1.5rem;
    }

    .brand-title {
        font-size: 2rem;
    }

    .icon-wrapper {
        width: 75px;
        height: 75px;
    }

    .icon-wrapper i {
        font-size: 2.5rem;
    }

    .subtitle {
        font-size: 0.85rem;
    }
}

@media (max-width: 400px) {
    .institutional-logos {
        gap: 0.75rem;
    }

    .logo-box {
        width: 50px;
        height: 50px;
    }

    .logo-box i {
        font-size: 1.5rem;
    }

    .verify-card {
        padding: 1.75rem 1.25rem;
    }

    .brand-title {
        font-size: 1.75rem;
    }

    .icon-wrapper {
        width: 65px;
        height: 65px;
    }

    .icon-wrapper i {
        font-size: 2rem;
    }
}
</style>