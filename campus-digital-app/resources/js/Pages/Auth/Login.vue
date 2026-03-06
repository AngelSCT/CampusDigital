<template>
    <div class="login-wrapper">
        <!-- Fondo oscuro con patrón -->
        <div class="background-pattern"></div>
        
        <div class="login-container">
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
            <div class="login-card">
                <!-- Header -->
                <div class="login-header">
                    <div class="brand-section">
                        <h1 class="brand-title">
                            <span class="brand-highlight">Campus</span>
                            <span class="brand-normal">Digital</span>
                        </h1>
                    </div>
                    <p class="subtitle">Sistema de Gestión Escolar</p>
                </div>

                <!-- Tabs -->
                <div class="tabs">
                    <button
                        type="button"
                        @click="activeTab = 'password'"
                        :class="activeTab === 'password' ? 'tab-active' : 'tab-inactive'"
                        class="tab-btn">
                        <i class="fas fa-envelope"></i>
                        Correo
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'rfid'"
                        :class="activeTab === 'rfid' ? 'tab-active' : 'tab-inactive'"
                        class="tab-btn">
                        <i class="fas fa-wifi"></i>
                        Tarjeta RFID
                    </button>
                </div>

                <!-- ─── TAB: EMAIL / PASSWORD ─────────────────────────── -->
                <div v-show="activeTab === 'password'">
                    <form @submit.prevent="submit" class="login-form">
                        <!-- Alert de errores -->
                        <div v-if="form.errors.email || form.errors.password" class="alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ form.errors.email || form.errors.password || 'Error en las credenciales' }}</span>
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
                                autocomplete="username"
                            />
                        </div>

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
                                    autocomplete="current-password"
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
                        </div>

                        <!-- Opciones -->
                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input
                                    type="checkbox"
                                    v-model="form.remember"
                                    class="checkbox-input"
                                />
                                <span class="checkbox-label">Recordarme</span>
                            </label>
                            <Link :href="route('password.request')" class="forgot-link">
                                ¿Olvidaste tu contraseña?
                            </Link>
                        </div>

                        <!-- Botón login -->
                        <button type="submit" class="btn-login" :disabled="form.processing">
                            <span v-if="form.processing">
                                <i class="fas fa-spinner fa-spin"></i>
                                Ingresando...
                            </span>
                            <span v-else>
                                <i class="fas fa-sign-in-alt"></i>
                                Iniciar Sesión
                            </span>
                        </button>
                    </form>
                </div>

                <!-- ─── TAB: RFID ──────────────────────────────────────── -->
                <div v-show="activeTab === 'rfid'">
                    <form @submit.prevent="submitRfid" class="login-form">

                        <!-- Ilustración animada -->
                        <div class="rfid-illustration">
                            <div class="rfid-ring rfid-ring-1"></div>
                            <div class="rfid-ring rfid-ring-2"></div>
                            <div class="rfid-ring rfid-ring-3"></div>
                            <div class="rfid-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                        </div>

                        <p class="rfid-hint">
                            Acerca tu tarjeta al lector o escribe el UID manualmente
                        </p>

                        <!-- Alert de error RFID -->
                        <div v-if="rfidErrors.uid" class="alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ rfidErrors.uid }}</span>
                        </div>

                        <!-- Alert de éxito (mientras redirige) -->
                        <div v-if="rfidSuccess" class="alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ rfidSuccess }}</span>
                        </div>

                        <div v-if="rfidErrors.pin" class="alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ rfidErrors.pin }}</span>
                        </div>

                        <!-- UID input -->
                        <div class="form-group">
                            <label for="uid" class="form-label">
                                <i class="fas fa-wifi"></i>
                                UID de la Tarjeta
                            </label>
                            <input
                                type="text"
                                id="uid"
                                v-model="rfidForm.uid"
                                ref="uidInputRef"
                                class="form-input uid-input"
                                :class="{ 'input-error': rfidErrors.uid, 'uid-scanning': rfidProcessing }"
                                placeholder="Escanea o escribe el UID..."
                                maxlength="64"
                                autocomplete="off"
                                @keyup.enter="submitRfid"
                            />
                            <p class="uid-note">
                                <i class="fas fa-info-circle"></i>
                                Si tienes un lector USB, el UID se captura automáticamente al acercar la tarjeta.
                            </p>
                        </div>

                        <!-- PIN -->
                        <div class="form-group">
                            <label for="pin" class="form-label">
                                <i class="fas fa-lock"></i>
                                PIN (4 dígitos)
                            </label>
                            <input
                                type="password"
                                id="pin"
                                v-model="rfidForm.pin"
                                class="form-input uid-input"
                                :class="{ 'input-error': rfidErrors.pin }"
                                placeholder="••••"
                                maxlength="4"
                                inputmode="numeric"
                                pattern="[0-9]{4}"
                                autocomplete="off"
                            />
                        </div>

                        <!-- Botón -->
                        <button type="submit" class="btn-rfid" :disabled="rfidProcessing || !rfidForm.uid || !rfidForm.pin">
                            <span v-if="rfidProcessing">
                                <i class="fas fa-spinner fa-spin"></i>
                                Verificando tarjeta...
                            </span>
                            <span v-else>
                                <i class="fas fa-wifi"></i>
                                Acceder con tarjeta
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Footer (siempre visible) -->
                <div class="login-footer">
                    <div class="divider">
                        <span>o</span>
                    </div>
                    <p class="register-text">
                        ¿No tienes cuenta?
                        <Link :href="route('register')" class="register-link">
                            Regístrate aquí
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Icono de seguridad -->
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Conexión segura</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted } from 'vue';

// ── Tab activo ───────────────────────────────────────────────
const activeTab = ref('password');

// ── Form de email/password (Fortify, igual que antes) ────────
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

// ── Form RFID ────────────────────────────────────────────────
const rfidForm   = reactive({ uid: '', pin: '' });
const rfidErrors = reactive({ uid: '', pin: '' });
const rfidProcessing = ref(false);
const rfidSuccess    = ref('');
const uidInputRef    = ref(null);

// Enfoca el campo UID cuando se activa el tab RFID
function onTabRfid() {
    activeTab.value = 'rfid';
    setTimeout(() => uidInputRef.value?.focus(), 50);
}

function submitRfid() {
    if (!rfidForm.uid || !rfidForm.pin) return;

    rfidProcessing.value = true;
    rfidErrors.uid       = '';
    rfidErrors.pin       = '';
    rfidSuccess.value    = '';

    router.post(route('rfid.login'), {
        uid: rfidForm.uid.toUpperCase().trim(),
        pin: rfidForm.pin,
    }, {
        preserveState: true,
        onSuccess: () => {
            rfidSuccess.value = '¡Tarjeta válida! Redirigiendo...';
        },
        onError: (errors) => {
            rfidErrors.uid = errors.uid ?? '';
            rfidErrors.pin = errors.pin ?? '';
            if (errors.uid) rfidForm.uid = '';
            rfidForm.pin = '';
            uidInputRef.value?.focus();
        },
        onFinish: () => {
            rfidProcessing.value = false;
        },
    });
}

onMounted(() => {
    // Si hay error de UID en props (redirect back), mostrar tab RFID
    // Esto ocurre si el servidor devuelve el error de uid
});
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* ── Todo lo que ya tenías (sin cambios) ─────────────────── */
.login-wrapper {
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

.background-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.03;
    background-image: 
        radial-gradient(circle at 25% 25%, rgba(235, 13, 255, 0.2) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(59, 130, 246, 0.2) 0%, transparent 50%);
    pointer-events: none;
}

.login-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    position: relative;
    z-index: 1;
}

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

.login-card {
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 1.5rem;
    padding: 2.5rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.login-header {
    text-align: center;
    margin-bottom: 1.5rem;
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

.login-form {
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

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-input {
    width: 1rem;
    height: 1rem;
    cursor: pointer;
    accent-color: #3b82f6;
}

.checkbox-label {
    color: #94a3b8;
    font-size: 0.875rem;
    user-select: none;
}

.forgot-link {
    color: #3b82f6;
    font-size: 0.875rem;
    text-decoration: none;
    transition: color 0.2s ease;
}

.forgot-link:hover {
    color: #60a5fa;
}

.btn-login {
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

.btn-login:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
}

.btn-login:disabled {
    opacity: 0.7;
    cursor: not-allowed;
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

.login-footer {
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

.register-text {
    text-align: center;
    color: #94a3b8;
    font-size: 0.875rem;
}

.register-link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    margin-left: 0.25rem;
    transition: color 0.2s ease;
}

.register-link:hover {
    color: #60a5fa;
}

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

/* ── NUEVOS estilos para tabs y RFID ─────────────────────── */
.tabs {
    display: flex;
    background: rgba(30, 41, 59, 0.5);
    border-radius: 0.75rem;
    padding: 0.25rem;
    margin-bottom: 1.5rem;
    gap: 0.25rem;
}

.tab-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.6rem 0.5rem;
    border: none;
    border-radius: 0.6rem;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tab-active {
    background: rgba(59, 130, 246, 0.25);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.tab-inactive {
    background: transparent;
    color: #64748b;
    border: 1px solid transparent;
}

.tab-inactive:hover {
    background: rgba(59, 130, 246, 0.08);
    color: #94a3b8;
}

/* Ilustración RFID */
.rfid-illustration {
    position: relative;
    width: 90px;
    height: 90px;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rfid-ring {
    position: absolute;
    border-radius: 50%;
    border: 2px solid rgba(59, 130, 246, 0.3);
    animation: rfid-pulse 2s ease-out infinite;
}

.rfid-ring-1 { width: 90px;  height: 90px;  animation-delay: 0s;    }
.rfid-ring-2 { width: 65px;  height: 65px;  animation-delay: 0.4s;  }
.rfid-ring-3 { width: 42px;  height: 42px;  animation-delay: 0.8s;  }

@keyframes rfid-pulse {
    0%   { opacity: 0.7; transform: scale(1);    }
    100% { opacity: 0;   transform: scale(1.15); }
}

.rfid-icon {
    position: relative;
    z-index: 2;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, rgba(59,130,246,0.3), rgba(37,99,235,0.3));
    border: 2px solid rgba(59,130,246,0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rfid-icon i {
    font-size: 1.25rem;
    color: #60a5fa;
}

.rfid-hint {
    text-align: center;
    color: #94a3b8;
    font-size: 0.85rem;
    margin-bottom: 1.25rem;
    line-height: 1.5;
}

/* Input UID */
.uid-input {
    font-family: 'Courier New', Courier, monospace;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    font-size: 1.05rem;
    text-align: center;
}

.uid-scanning {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15),
                0 0 20px rgba(59, 130, 246, 0.1);
    animation: uid-glow 1s ease-in-out infinite alternate;
}

@keyframes uid-glow {
    from { box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    to   { box-shadow: 0 0 0 3px rgba(59,130,246,0.3), 0 0 25px rgba(59,130,246,0.15); }
}

.uid-note {
    margin-top: 0.5rem;
    font-size: 0.78rem;
    color: #475569;
    display: flex;
    align-items: flex-start;
    gap: 0.35rem;
    line-height: 1.4;
}

.uid-note i {
    color: #334155;
    font-size: 0.75rem;
    margin-top: 0.1rem;
    flex-shrink: 0;
}

/* Botón RFID */
.btn-rfid {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
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

.btn-rfid:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(13, 148, 136, 0.4);
}

.btn-rfid:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Alert éxito */
.alert-success {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 0.75rem;
    color: #6ee7b7;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.alert-success i {
    font-size: 1.1rem;
    flex-shrink: 0;
}

/* ── Responsive (igual que antes + ajustes tabs) ─────────── */
@media (max-width: 640px) {
    .login-container { padding: 1.5rem 1rem; }
    .institutional-logos { gap: 1rem; margin-bottom: 1.5rem; }
    .logo-box { width: 60px; height: 60px; }
    .logo-box i { font-size: 1.75rem; }
    .login-card { padding: 2rem 1.5rem; }
    .brand-title { font-size: 2rem; }
    .form-options { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
}

@media (max-width: 400px) {
    .institutional-logos { gap: 0.75rem; }
    .logo-box { width: 50px; height: 50px; }
    .logo-box i { font-size: 1.5rem; }
    .login-card { padding: 1.75rem 1.25rem; }
    .brand-title { font-size: 1.75rem; }
}
</style>