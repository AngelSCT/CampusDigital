<template>
    <div class="register-wrapper">
        <!-- Fondo oscuro con patrón -->
        <div class="background-pattern"></div>
        
        <div class="register-container">
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
            <div class="register-card">
                <!-- Header -->
                <div class="register-header">
                    <div class="brand-section">
                        <h1 class="brand-title">
                            <span class="brand-highlight">Campus</span>
                            <span class="brand-normal">Digital</span>
                        </h1>
                    </div>
                    <p class="subtitle">Crear una cuenta nueva</p>
                </div>

                <!-- Formulario -->
                <form @submit.prevent="submit" class="register-form">
                    <!-- Alert de errores -->
                    <div v-if="Object.keys(form.errors).length > 0" class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Por favor corrige los errores en el formulario</span>
                    </div>

                    <!-- Grid de campos -->
                    <div class="form-grid">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre" class="form-label">
                                <i class="fas fa-user"></i>
                                Nombre
                            </label>
                            <input
                                type="text"
                                id="nombre"
                                v-model="form.nombre"
                                class="form-input"
                                :class="{ 'input-error': form.errors.nombre }"
                                placeholder="Tu nombre"
                                required
                                autofocus
                            />
                            <span v-if="form.errors.nombre" class="error-message">
                                {{ form.errors.nombre }}
                            </span>
                        </div>

                        <!-- Apellido -->
                        <div class="form-group">
                            <label for="apellido" class="form-label">
                                <i class="fas fa-user"></i>
                                Apellido
                            </label>
                            <input
                                type="text"
                                id="apellido"
                                v-model="form.apellido"
                                class="form-input"
                                :class="{ 'input-error': form.errors.apellido }"
                                placeholder="Tu apellido"
                                required
                            />
                            <span v-if="form.errors.apellido" class="error-message">
                                {{ form.errors.apellido }}
                            </span>
                        </div>
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
                        />
                        <span v-if="form.errors.email" class="error-message">
                            {{ form.errors.email }}
                        </span>
                    </div>

                    <!-- Grid de contraseñas -->
                    <div class="form-grid">
                        <!-- Contraseña -->
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Contraseña
                            </label>
                            <div class="password-wrapper">
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    id="password"
                                    v-model="form.password"
                                    class="form-input"
                                    :class="{ 'input-error': form.errors.password }"
                                    placeholder="••••••••"
                                    required
                                />
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword"
                                    class="toggle-password"
                                    tabindex="-1"
                                >
                                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                </button>
                            </div>
                            <span v-if="form.errors.password" class="error-message">
                                {{ form.errors.password }}
                            </span>
                        </div>

                        <!-- Confirmar contraseña -->
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock"></i>
                                Confirmar
                            </label>
                            <div class="password-wrapper">
                                <input
                                    :type="showPasswordConfirm ? 'text' : 'password'"
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    class="form-input"
                                    :class="{ 'input-error': form.errors.password_confirmation }"
                                    placeholder="••••••••"
                                    required
                                />
                                <button 
                                    type="button" 
                                    @click="showPasswordConfirm = !showPasswordConfirm"
                                    class="toggle-password"
                                    tabindex="-1"
                                >
                                    <i :class="showPasswordConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                </button>
                            </div>
                            <span v-if="form.errors.password_confirmation" class="error-message">
                                {{ form.errors.password_confirmation }}
                            </span>
                        </div>
                    </div>

                    <!-- Indicador de fortaleza de contraseña -->
                    <div v-if="form.password" class="password-strength">
                        <div class="strength-bar">
                            <div 
                                class="strength-fill" 
                                :class="passwordStrengthClass"
                                :style="{ width: passwordStrength + '%' }"
                            ></div>
                        </div>
                        <span class="strength-text" :class="passwordStrengthClass">
                            {{ passwordStrengthText }}
                        </span>
                    </div>

                    <!-- Botón registrar -->
                    <button type="submit" class="btn-register" :disabled="form.processing">
                        <span v-if="form.processing">
                            <i class="fas fa-spinner fa-spin"></i>
                            Registrando...
                        </span>
                        <span v-else>
                            <i class="fas fa-user-plus"></i>
                            Crear Cuenta
                        </span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="register-footer">
                    <div class="divider">
                        <span>o</span>
                    </div>
                    <p class="login-text">
                        ¿Ya tienes cuenta?
                        <Link :href="route('login')" class="login-link">
                            Inicia sesión
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Icono de seguridad -->
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Tus datos están protegidos</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const form = useForm({
    nombre: '',
    apellido: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

// Calcular fortaleza de contraseña
const passwordStrength = computed(() => {
    const password = form.password;
    if (!password) return 0;
    
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.length >= 12) strength += 25;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 15;
    if (/[^A-Za-z0-9]/.test(password)) strength += 10;
    
    return Math.min(strength, 100);
});

const passwordStrengthText = computed(() => {
    const strength = passwordStrength.value;
    if (strength === 0) return '';
    if (strength < 40) return 'Débil';
    if (strength < 70) return 'Media';
    if (strength < 90) return 'Fuerte';
    return 'Muy fuerte';
});

const passwordStrengthClass = computed(() => {
    const strength = passwordStrength.value;
    if (strength < 40) return 'weak';
    if (strength < 70) return 'medium';
    if (strength < 90) return 'strong';
    return 'very-strong';
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Wrapper principal */
.register-wrapper {
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
.register-container {
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

/* Card de registro */
.register-card {
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
.register-header {
    text-align: center;
    margin-bottom: 2rem;
}

.brand-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
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
    font-size: 0.95rem;
}

/* Formulario */
.register-form {
    margin-bottom: 1.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-grid .form-group {
    margin-bottom: 0;
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

/* Password wrapper */
.password-wrapper {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.2s ease;
}

.toggle-password:hover {
    color: #3b82f6;
}

.toggle-password i {
    font-size: 1rem;
}

/* Error message */
.error-message {
    display: block;
    color: #fca5a5;
    font-size: 0.75rem;
    margin-top: 0.375rem;
}

/* Password strength */
.password-strength {
    margin-bottom: 1.5rem;
}

.strength-bar {
    width: 100%;
    height: 4px;
    background: rgba(30, 41, 59, 0.5);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.strength-fill {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-fill.weak {
    background: #ef4444;
}

.strength-fill.medium {
    background: #f59e0b;
}

.strength-fill.strong {
    background: #10b981;
}

.strength-fill.very-strong {
    background: #22c55e;
}

.strength-text {
    font-size: 0.75rem;
    font-weight: 500;
}

.strength-text.weak {
    color: #fca5a5;
}

.strength-text.medium {
    color: #fbbf24;
}

.strength-text.strong {
    color: #6ee7b7;
}

.strength-text.very-strong {
    color: #86efac;
}

/* Botón de registro */
.btn-register {
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

.btn-register:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
}

.btn-register:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Alert de error */
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
.register-footer {
    padding-top: 1.5rem;
    border-top: 1px solid rgba(59, 130, 246, 0.1);
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

.login-text {
    text-align: center;
    color: #94a3b8;
    font-size: 0.875rem;
}

.login-link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    margin-left: 0.25rem;
    transition: color 0.2s ease;
}

.login-link:hover {
    color: #60a5fa;
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
    .register-container {
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

    .register-card {
        padding: 2rem 1.5rem;
    }

    .brand-title {
        font-size: 2rem;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .form-grid .form-group {
        margin-bottom: 0;
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

    .register-card {
        padding: 1.75rem 1.25rem;
    }

    .brand-title {
        font-size: 1.75rem;
    }
}
</style>