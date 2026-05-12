<template>
    <div class="login-wrapper">

        <div class="login-split">

            <div class="split-left">
                <div class="corner-tl"></div>
                <div class="corner-br"></div>
                <div class="form-container">

                    <div class="form-header" v-show="activeTab === 'password'">
                        <div class="brand">
                            <div class="brand-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div>
                                <h1 class="brand-title">
                                    <span class="brand-blue">Campus</span>
                                    <span class="brand-dark">Digital</span>
                                </h1>
                                <p class="brand-sub">Sistema de Gestión Escolar</p>
                            </div>
                        </div>
                        <h2 class="welcome-title">Bienvenido de nuevo</h2>
                        <p class="welcome-sub">Ingresa tus credenciales para continuar</p>
                    </div>

                    <div class="brand-compact" v-show="activeTab === 'rfid'">
                        <div class="brand-icon brand-icon-sm">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <span class="brand-compact-title">
                            <span class="brand-blue">Campus</span>
                            <span class="brand-dark">Digital</span>
                        </span>
                    </div>

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
                            @click="onTabRfid"
                            :class="activeTab === 'rfid' ? 'tab-active' : 'tab-inactive'"
                            class="tab-btn">
                            <i class="fas fa-wifi"></i>
                            Tarjeta RFID
                        </button>
                    </div>

                    <div v-show="activeTab === 'password'">
                        <form @submit.prevent="submit" class="login-form">

                            <div v-if="form.errors.email || form.errors.password" class="alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ form.errors.email || form.errors.password || 'Error en las credenciales' }}</span>
                            </div>

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

                            <div class="form-options">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" v-model="form.remember" class="checkbox-input" />
                                    <span class="checkbox-label">Recordarme</span>
                                </label>
                                <Link :href="route('password.request')" class="forgot-link">
                                    ¿Olvidaste tu contraseña?
                                </Link>
                            </div>

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

                    <div v-show="activeTab === 'rfid'">
                        <form @submit.prevent="submitRfid" class="login-form">

                            <div class="qr-login-box">
                                <p class="qr-login-label">
                                    <i class="fas fa-mobile-alt"></i>
                                    Escanea con la app móvil para entrar
                                </p>
                                <canvas ref="canvasQrLogin" class="qr-login-canvas"></canvas>
                                <div class="qr-login-code-row">
                                    <span class="qr-login-code-hint">Código:</span>
                                    <input
                                        v-model="codigoQrLogin"
                                        type="text"
                                        maxlength="32"
                                        class="qr-login-code-input"
                                        @input="codigoQrLogin = codigoQrLogin.toUpperCase(); regenerarQr()"
                                    />
                                </div>
                                <p class="qr-login-hint">O ingresa el UID y PIN manualmente abajo</p>
                            </div>

                            <div v-if="rfidErrors.uid" class="alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ rfidErrors.uid }}</span>
                            </div>

                            <div v-if="rfidSuccess" class="alert-success">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ rfidSuccess }}</span>
                            </div>

                            <div v-if="rfidErrors.pin" class="alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ rfidErrors.pin }}</span>
                            </div>

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

                    <div class="form-footer">
                        <div class="divider"><span>o</span></div>
                        <p class="register-text">
                            ¿No tienes cuenta?
                            <Link :href="route('register')" class="register-link">Regístrate aquí</Link>
                        </p>
                    </div>

                </div>
            </div>

            <div
                class="split-right"
                :class="{ 'has-bg-image': loginBg }"
                :style="loginBg ? { backgroundImage: `url(${loginBg})` } : {}"
            >
                <!-- Gradiente inferior para que el contenido destaque -->
                <div class="right-overlay-bottom"></div>

                <div class="right-content">

                    <!-- Badge institucional -->
                    <div class="right-badge">
                        <i class="fas fa-university"></i>
                        <span>Instituto Tecnológico</span>
                    </div>

                    <!-- Título principal -->
                    <div class="right-text">
                        <h2>Gestión<br><span class="right-title-accent">Escolar</span></h2>
                        <p>Administra usuarios, materias y roles desde un solo lugar de forma segura y eficiente.</p>
                    </div>

                    <!-- Stats como pills modernos -->
                    <div class="right-stats">
                        <div class="stat-pill">
                            <div class="stat-pill-icon"><i class="fas fa-user-graduate"></i></div>
                            <span class="stat-pill-label">Alumnos</span>
                        </div>
                        <div class="stat-pill">
                            <div class="stat-pill-icon"><i class="fas fa-book-open"></i></div>
                            <span class="stat-pill-label">Materias</span>
                        </div>
                        <div class="stat-pill">
                            <div class="stat-pill-icon"><i class="fas fa-shield-alt"></i></div>
                            <span class="stat-pill-label">Roles</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted, onUnmounted, watch, nextTick } from 'vue';
import QRCode from 'qrcode';

const loginBg = '/images/Campus.webp';

const activeTab    = ref('password');
const showPassword = ref(false);

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const rfidForm       = reactive({ uid: '', pin: '' });
const rfidErrors     = reactive({ uid: '', pin: '' });
const rfidProcessing = ref(false);
const rfidSuccess    = ref('');
const uidInputRef    = ref(null);

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
            clearInterval(loginPollingInterval);
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

const canvasQrLogin = ref(null);
const codigoQrLogin = ref(
    localStorage.getItem('login_qr_codigo') ?? 'LOGIN-QR-01'
);

watch(codigoQrLogin, (v) => {
    localStorage.setItem('login_qr_codigo', v);
});

async function regenerarQr() {
    await nextTick();
    if (canvasQrLogin.value && codigoQrLogin.value.trim()) {
        await QRCode.toCanvas(canvasQrLogin.value, codigoQrLogin.value, {
            width:  130,
            margin: 2,
            color:  { dark: '#7c3aed', light: '#ffffff' },
        });
    }
}

let loginPollingInterval = null;
let ultimoLoginTimestamp = null;

async function verificarLoginPendiente() {
    try {
        const res  = await fetch('/simulador/login-pendiente');
        const data = await res.json();

        if (data.uid && data.pin && data.timestamp && data.timestamp !== ultimoLoginTimestamp) {
            ultimoLoginTimestamp = data.timestamp;
            rfidForm.uid = data.uid;
            rfidForm.pin = data.pin;
            submitRfid();
        }
    } catch (_) {}
}

function onTabRfid() {
    activeTab.value = 'rfid';
    setTimeout(async () => {
        await regenerarQr();
        uidInputRef.value?.focus();
    }, 50);
}

watch(activeTab, (tab) => {
    if (tab === 'rfid') {
        ultimoLoginTimestamp = null;
        loginPollingInterval = setInterval(verificarLoginPendiente, 1500);
        setTimeout(() => regenerarQr(), 50);
    } else {
        clearInterval(loginPollingInterval);
        loginPollingInterval = null;
    }
});

onMounted(() => {});

onUnmounted(() => {
    clearInterval(loginPollingInterval);
});
</script>

<style scoped>
* { margin: 0; padding: 0; box-sizing: border-box; }


.login-wrapper {
    height: 100vh;
    overflow: hidden;
    background: #0a1628;
    display: flex;
    align-items: stretch;
}
.login-split {
    display: flex;
    width: 100%;
    height: 100vh;
    overflow: hidden;
}

.split-left {
    width: 45%;
    height: 100%;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem 2rem;
    overflow: hidden;
    position: relative;
}

.split-left::before {
    content: '';
    position: absolute;
    top: 18px; right: 18px;
    width: 90px; height: 90px;
    background: linear-gradient(135deg, rgba(168,85,247,0.18), rgba(236,72,153,0.12));
    border: 2px solid rgba(168,85,247,0.25);
    border-radius: 14px;
    transform: rotate(15deg);
    pointer-events: none;
    box-shadow:
        22px 22px 0 -4px rgba(236,72,153,0.12),
        22px 22px 0 -2px rgba(236,72,153,0.2),
        42px 42px 0 -8px rgba(168,85,247,0.15),
        42px 42px 0 -6px rgba(168,85,247,0.22);
}


.split-left::after {
    content: '';
    position: absolute;
    bottom: 18px; left: 18px;
    width: 80px; height: 80px;
    background: linear-gradient(135deg, rgba(236,72,153,0.15), rgba(168,85,247,0.1));
    border: 2px solid rgba(236,72,153,0.22);
    border-radius: 50%;
    pointer-events: none;
    box-shadow:
        20px -18px 0 -6px rgba(168,85,247,0.18),
        20px -18px 0 -4px rgba(168,85,247,0.15),
        44px -10px 0 -12px rgba(236,72,153,0.25),
        44px -10px 0 -10px rgba(236,72,153,0.18);
}


.split-left .corner-tl {
    position: absolute;
    top: 14px; left: 14px;
    width: 0; height: 0;
    pointer-events: none;
    border-left: 28px solid transparent;
    border-right: 28px solid transparent;
    border-bottom: 48px solid rgba(168,85,247,0.12);
    filter: drop-shadow(12px 14px 0px rgba(236,72,153,0.15))
            drop-shadow(22px 26px 0px rgba(168,85,247,0.1));
}


.split-left .corner-br {
    position: absolute;
    bottom: 20px; right: 20px;
    width: 36px; height: 36px;
    background: linear-gradient(135deg, rgba(236,72,153,0.18), rgba(168,85,247,0.12));
    border: 1.5px solid rgba(236,72,153,0.28);
    border-radius: 6px;
    transform: rotate(45deg);
    pointer-events: none;
    box-shadow:
        -18px -18px 0 -4px rgba(168,85,247,0.12),
        -18px -18px 0 -2px rgba(168,85,247,0.18);
}

.form-container {
    width: 100%;
    max-width: 370px;
    max-height: 100%;
    overflow-y: auto;
    overflow-x: hidden;
    position: relative;
    z-index: 1;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.form-container::-webkit-scrollbar { display: none; }

.form-header { overflow: hidden; }

.brand-compact {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.75rem;
}
.brand-icon-sm {
    width: 30px; height: 30px;
    background: linear-gradient(135deg, #a855f7, #ec4899);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.brand-icon-sm i { font-size: 0.9rem; color: #fff; }
.brand-compact-title { font-size: 1.05rem; font-weight: 800; line-height: 1; }

.brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.9rem;
}
.brand-icon {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, #a855f7, #ec4899);
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 16px rgba(168,85,247,0.35);
    flex-shrink: 0;
}
.brand-icon i { font-size: 1.2rem; color: #fff; }
.brand-title  { font-size: 1.3rem; font-weight: 800; line-height: 1.1; }
.brand-blue   { color: #a855f7; }
.brand-dark   { color: #1e293b; }
.brand-sub    { font-size: 0.7rem; color: #94a3b8; letter-spacing: 0.5px; }

.welcome-title {
    font-size: 1.4rem; font-weight: 700;
    color: #1e293b; margin-bottom: 0.2rem;
}
.welcome-sub {
    font-size: 0.8rem; color: #94a3b8; margin-bottom: 1.1rem;
}

.tabs {
    display: flex;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 50px;
    padding: 3px;
    margin-bottom: 1rem;
    gap: 3px;
}
.tab-btn {
    flex: 1;
    display: flex; align-items: center; justify-content: center;
    gap: 0.35rem;
    padding: 0.45rem 0.5rem;
    border: none; border-radius: 50px;
    font-size: 0.78rem; font-weight: 600;
    cursor: pointer; transition: all 0.25s ease;
}
.tab-active {
    background: linear-gradient(135deg, #a855f7, #ec4899);
    color: #fff;
    box-shadow: 0 2px 12px rgba(168,85,247,0.35);
}
.tab-inactive { background: transparent; color: #94a3b8; }
.tab-inactive:hover { color: #64748b; background: #f1f5f9; }

.login-form { margin-bottom: 0.6rem; }
.form-group { margin-bottom: 0.85rem; }

.form-label {
    display: flex; align-items: center; gap: 0.35rem;
    font-size: 0.72rem; font-weight: 600; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.6px;
    margin-bottom: 0.35rem;
}
.form-label i { color: #a855f7; font-size: 0.7rem; }

.form-input {
    width: 100%;
    padding: 0.65rem 1rem;
    border: 1.5px solid #e9d5ff;
    border-radius: 50px;
    font-size: 0.85rem; color: #1e293b;
    background: #faf5ff;
    transition: all 0.2s ease;
}
.form-input::placeholder { color: #c4b5fd; }
.form-input:focus {
    outline: none; border-color: #a855f7;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(168,85,247,0.12);
    color: #1e293b;
}
.form-input.input-error { border-color: #f87171; background: #fff5f5; }

.password-wrapper { position: relative; }
.toggle-password {
    position: absolute; right: 1rem; top: 50%;
    transform: translateY(-50%);
    background: none; border: none;
    color: #c4b5fd; cursor: pointer;
    transition: color 0.2s ease;
}
.toggle-password:hover { color: #a855f7; }

.form-options {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 0.85rem; padding: 0 0.25rem;
}
.checkbox-wrapper { display: flex; align-items: center; gap: 0.4rem; cursor: pointer; }
.checkbox-input   { accent-color: #a855f7; width: 13px; height: 13px; cursor: pointer; }
.checkbox-label   { font-size: 0.78rem; color: #64748b; user-select: none; }

.forgot-link {
    color: #a855f7; font-size: 0.78rem;
    text-decoration: none; font-weight: 500;
    transition: color 0.2s ease;
}
.forgot-link:hover { color: #7c3aed; }

.btn-login {
    width: 100%; padding: 0.75rem;
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: #fff; border: none; border-radius: 50px;
    font-size: 0.88rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 18px rgba(124,58,237,0.35);
    letter-spacing: 0.3px;
}
.btn-login:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(124,58,237,0.45);
}
.btn-login:disabled { opacity: 0.65; cursor: not-allowed; }

.btn-rfid {
    width: 100%; padding: 0.75rem;
    background: linear-gradient(135deg, #db2777, #ec4899);
    color: #fff; border: none; border-radius: 50px;
    font-size: 0.88rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 18px rgba(219,39,119,0.3);
}
.btn-rfid:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(219,39,119,0.4);
}
.btn-rfid:disabled { opacity: 0.6; cursor: not-allowed; }

.alert-error {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.6rem 0.85rem;
    background: #fef2f2; border: 1px solid #fecaca;
    border-radius: 50px; color: #dc2626;
    font-size: 0.78rem; margin-bottom: 0.75rem;
}
.alert-error i { font-size: 0.9rem; flex-shrink: 0; }

.alert-success {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.6rem 0.85rem;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 50px; color: #16a34a;
    font-size: 0.78rem; margin-bottom: 0.75rem;
}
.alert-success i { font-size: 0.9rem; flex-shrink: 0; }

.rfid-illustration {
    position: relative; width: 70px; height: 70px;
    margin: 0 auto 0.6rem;
    display: flex; align-items: center; justify-content: center;
}
.rfid-ring {
    position: absolute; border-radius: 50%;
    border: 2px solid rgba(168,85,247,0.2);
    animation: rfid-pulse 2s ease-out infinite;
}
.rfid-ring-1 { width: 70px; height: 70px; animation-delay: 0s; }
.rfid-ring-2 { width: 50px; height: 50px; animation-delay: 0.4s; }
.rfid-ring-3 { width: 33px; height: 33px; animation-delay: 0.8s; }
@keyframes rfid-pulse {
    0%   { opacity: 0.7; transform: scale(1); }
    100% { opacity: 0;   transform: scale(1.15); }
}
.rfid-icon {
    position: relative; z-index: 2;
    width: 36px; height: 36px;
    background: #faf5ff; border: 2px solid #d8b4fe;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.rfid-icon i { font-size: 1rem; color: #a855f7; }
.rfid-hint   { text-align: center; color: #94a3b8; font-size: 0.76rem; margin-bottom: 0.75rem; line-height: 1.5; }

.uid-input {
    font-family: 'Courier New', Courier, monospace;
    letter-spacing: 0.1em; text-transform: uppercase; text-align: center;
}
.uid-scanning {
    border-color: #a855f7 !important;
    animation: uid-glow 1s ease-in-out infinite alternate;
}
@keyframes uid-glow {
    from { box-shadow: 0 0 0 3px rgba(168,85,247,0.1); }
    to   { box-shadow: 0 0 0 3px rgba(168,85,247,0.25); }
}
.uid-note {
    margin-top: 0.35rem; font-size: 0.7rem; color: #94a3b8;
    display: flex; align-items: flex-start; gap: 0.3rem;
    line-height: 1.4; padding: 0 0.25rem;
}
.uid-note i { color: #c4b5fd; font-size: 0.65rem; margin-top: 0.1rem; flex-shrink: 0; }

.form-footer { padding-top: 0.75rem; border-top: 1px solid #f1f5f9; margin-top: 0.4rem; }
.divider { text-align: center; position: relative; margin-bottom: 0.6rem; }
.divider span {
    color: #94a3b8; font-size: 0.78rem; background: #fff;
    padding: 0 0.75rem; position: relative; z-index: 1;
}
.divider::before {
    content: ''; position: absolute; top: 50%; left: 0; right: 0;
    height: 1px; background: #e2e8f0;
}
.register-text { text-align: center; color: #94a3b8; font-size: 0.78rem; margin-bottom: 0.6rem; }
.register-link {
    color: #a855f7; text-decoration: none; font-weight: 600;
    margin-left: 0.2rem; transition: color 0.2s ease;
}
.register-link:hover { color: #7c3aed; }

.split-right {
    width: 55%;
    height: 100%;
    background: linear-gradient(160deg, #1a0533 0%, #2d1060 40%, #0d1a3a 100%);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.split-right.has-bg-image::before {
    content: '';
    position: absolute; inset: 0;
    background: rgba(10, 22, 40, 0.22);
    z-index: 1;
}

.right-overlay-bottom {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 55%;
    background: linear-gradient(to top,
        rgba(5, 8, 20, 0.92) 0%,
        rgba(5, 8, 20, 0.7) 40%,
        transparent 100%);
    z-index: 2;
    pointer-events: none;
}

.right-content {
    position: relative;
    z-index: 3;
    width: 100%;
    padding: 0 2.5rem 2.5rem;
    max-width: 520px;
}

.right-badge {
    display: inline-flex;
    align-items: center; gap: 0.5rem;
    background: rgba(168,85,247,0.25);
    border: 1px solid rgba(168,85,247,0.4);
    border-radius: 50px;
    padding: 0.35rem 0.9rem;
    margin-bottom: 1.2rem;
    backdrop-filter: blur(8px);
}
.right-badge i    { font-size: 0.8rem; color: #c4b5fd; }
.right-badge span { font-size: 0.75rem; color: #e9d5ff; font-weight: 500; letter-spacing: 0.3px; }

.right-text { margin-bottom: 1.5rem; }
.right-text h2 {
    font-size: 2.6rem; font-weight: 900;
    color: #fff; line-height: 1.05;
    margin-bottom: 0.75rem; letter-spacing: -0.5px;
    text-shadow: 0 2px 20px rgba(0,0,0,0.4);
}
.right-title-accent {
    background: linear-gradient(135deg, #c084fc, #f472b6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.right-text p {
    font-size: 0.92rem;
    color: rgba(255,255,255,0.65);
    line-height: 1.65; max-width: 360px;
}

.right-stats {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.stat-pill {
    display: flex; align-items: center; gap: 0.5rem;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 50px;
    padding: 0.5rem 1rem;
    backdrop-filter: blur(12px);
    transition: all 0.25s ease;
}
.stat-pill:hover {
    background: rgba(168,85,247,0.2);
    border-color: rgba(168,85,247,0.4);
    transform: translateY(-2px);
}
.stat-pill-icon {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, rgba(168,85,247,0.4), rgba(244,114,182,0.3));
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.stat-pill-icon i { font-size: 0.78rem; color: #e9d5ff; }
.stat-pill-label  { font-size: 0.8rem; color: rgba(255,255,255,0.85); font-weight: 600; letter-spacing: 0.3px; }


.qr-login-box {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 10px 10px 8px;
    background: #faf5ff; border: 1px solid #e9d5ff;
    border-radius: 12px; margin-bottom: 0.75rem;
}
.qr-login-label {
    font-size: 0.68rem; font-weight: 700; color: #7c3aed;
    text-transform: uppercase; letter-spacing: 0.5px;
    display: flex; align-items: center; gap: 0.3rem;
}
.qr-login-canvas { border-radius: 6px; display: block; }
.qr-login-code-row {
    display: flex; align-items: center; gap: 0.4rem; margin-top: 2px;
}
.qr-login-code-hint { font-size: 0.65rem; color: #94a3b8; }
.qr-login-code-input {
    width: 110px; padding: 0.2rem 0.5rem;
    background: #fff; border: 1px solid #d8b4fe;
    border-radius: 999px; font-size: 0.65rem;
    font-family: monospace; color: #7c3aed;
    text-transform: uppercase; text-align: center;
    letter-spacing: 1px; outline: none;
    transition: border-color 0.2s;
}
.qr-login-code-input:focus { border-color: #a855f7; }
.qr-login-hint {
    font-size: 0.63rem; color: #a78bfa; text-align: center;
}

@media (max-height: 700px) and (min-width: 769px) {
    .brand               { margin-bottom: 0.5rem; }
    .welcome-title       { font-size: 1.15rem; }
    .welcome-sub         { margin-bottom: 0.6rem; font-size: 0.75rem; }
    .tabs                { margin-bottom: 0.65rem; }
    .form-group          { margin-bottom: 0.6rem; }
    .login-form          { margin-bottom: 0.4rem; }
    .form-footer         { padding-top: 0.5rem; }
    .split-left          { padding: 0.75rem 2rem; }
    .rfid-illustration   { width: 55px; height: 55px; margin-bottom: 0.35rem; }
    .rfid-ring-1         { width: 55px; height: 55px; }
    .rfid-ring-2         { width: 40px; height: 40px; }
    .rfid-ring-3         { width: 26px; height: 26px; }
    .rfid-icon           { width: 30px; height: 30px; }
}

@media (max-width: 900px) and (min-width: 769px) {
    .split-left  { width: 50%; }
    .split-right { width: 50%; }
    .right-text h2 { font-size: 2rem; }
}

@media (max-width: 768px) {
    .login-wrapper  { height: auto; min-height: 100vh; overflow: visible; }
    .login-split    { flex-direction: column; height: auto; overflow: visible; }
    .split-left     { width: 100%; height: auto; overflow: visible; padding: 2rem 1.5rem; }
    .split-right    { width: 100%; height: 260px; flex-shrink: 0; overflow: hidden; align-items: flex-end; }
    .form-container { max-height: none; overflow: visible; }
    .right-content  { padding: 0 1.5rem 1.5rem; }
    .right-text h2  { font-size: 1.8rem; }
    .right-text p   { display: none; }
    .right-stats    { gap: 0.5rem; }
    .form-options   { flex-direction: column; align-items: flex-start; gap: 0.6rem; }
}

@media (max-width: 480px) {
    .split-right { display: none; }
    .split-left  { padding: 1.75rem 1.25rem; }
}
</style>