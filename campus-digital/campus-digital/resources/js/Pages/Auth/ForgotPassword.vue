<template>
    <div class="forgot-wrapper">
        <!-- Fondo oscuro con patrón -->
        <div class="background-pattern"></div>
        
        <div class="forgot-container">
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
            <div class="forgot-card">
                <!-- Header -->
                <div class="forgot-header">
                    <div class="icon-wrapper">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="brand-section">
                        <h1 class="brand-title">
                            <span class="brand-highlight">Recuperar</span>
                            <span class="brand-normal">Contraseña</span>
                        </h1>
                    </div>
                    <p class="subtitle">
                        Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                    </p>
                </div>

                <!-- Alert de éxito -->
                <div v-if="status" class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ status }}</span>
                </div>

                <!-- Formulario -->
                <form @submit.prevent="submit" class="forgot-form">
                    <!-- Alert de error -->
                    <div v-if="form.errors.email" class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ form.errors.email }}</span>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>
                            Correo Electrónico
                        </label>
                        <input
                            type="email"
                            id="email"
                            v-model="form.email"
                            class="form-input"
                            :class="{ 'input-error': form.errors.email }"
                            placeholder="tu.email@ejemplo.com"
                            required
                            autofocus
                        />
                    </div>

                    <!-- Botón enviar -->
                    <button type="submit" class="btn-submit" :disabled="form.processing">
                        <span v-if="form.processing">
                            <i class="fas fa-spinner fa-spin"></i>
                            Enviando enlace...
                        </span>
                        <span v-else>
                            <i class="fas fa-paper-plane"></i>
                            Enviar Enlace de Recuperación
                        </span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="forgot-footer">
                    <div class="divider">
                        <span>o</span>
                    </div>
                    <Link :href="route('login')" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        Volver al inicio de sesión
                    </Link>
                </div>
            </div>

            <!-- Icono de seguridad -->
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Proceso seguro y encriptado</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Wrapper principal */
.forgot-wrapper {
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
.forgot-container {
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

/* Card de forgot */
.forgot-card {
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 1.5rem;
    padding: 2.5rem;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* Header */
.forgot-header {
    text-align: center;
    margin-bottom: 2rem;
}

.icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(37, 99, 235, 0.2) 100%);
    border: 2px solid rgba(59, 130, 246, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-wrapper i {
    font-size: 2.5rem;
    color: #3b82f6;
}

.brand-title {
    font-size: 2.25rem;
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
    max-width: 380px;
    margin: 0 auto;
}

/* Formulario */
.forgot-form {
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #e2e8f0;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-label i {
    color: #3b82f6;
    font-size: 0.9rem;
}

.form-input {
    width: 100%;
    padding: 0.875rem 1rem;
    background: rgba(30, 41, 59, 0.5);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 0.75rem;
    color: #e2e8f0;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-input::placeholder {
    color: #64748b;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: rgba(30, 41, 59, 0.7);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input.input-error {
    border-color: #ef4444;
    background: rgba(127, 29, 29, 0.2);
}

/* Botón de enviar */
.btn-submit {
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

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Alerts */
.alert-success {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 0.75rem;
    color: #86efac;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.alert-success i {
    font-size: 1.1rem;
    flex-shrink: 0;
}

.alert-error {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.3);
    border-radius: 0.75rem;
    color: #fca5a5;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.alert-error i {
    font-size: 1.1rem;
    flex-shrink: 0;
}

/* Footer */
.forgot-footer {
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

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
}

.back-link:hover {
    color: #60a5fa;
    background: rgba(59, 130, 246, 0.1);
}

.back-link i {
    font-size: 0.875rem;
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
    .forgot-container {
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

    .forgot-card {
        padding: 2rem 1.5rem;
    }

    .brand-title {
        font-size: 2rem;
    }

    .icon-wrapper {
        width: 70px;
        height: 70px;
    }

    .icon-wrapper i {
        font-size: 2rem;
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

    .forgot-card {
        padding: 1.75rem 1.25rem;
    }

    .brand-title {
        font-size: 1.75rem;
    }

    .icon-wrapper {
        width: 60px;
        height: 60px;
    }

    .icon-wrapper i {
        font-size: 1.75rem;
    }
}
</style>