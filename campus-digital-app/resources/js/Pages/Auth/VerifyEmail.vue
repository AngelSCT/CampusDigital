<template>
    <div class="login-wrapper">
        <div class="login-split">

            <!-- ══════════════════════════════════════════════════
                 LADO IZQUIERDO — Contenido
            ══════════════════════════════════════════════════ -->
            <div class="split-left">
                <div class="corner-tl"></div>
                <div class="corner-br"></div>

                <div class="form-container">

                    <!-- Header -->
                    <div class="form-header">
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

                        <div class="envelope-icon-wrapper">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>

                        <h2 class="welcome-title">
                            <span class="title-purple">Verificar</span> Email
                        </h2>
                        <p class="welcome-sub">
                            Gracias por registrarte. Verifica tu correo haciendo clic en el enlace que te enviamos.
                        </p>
                    </div>

                    <!-- Alert de éxito -->
                    <div v-if="status === 'verification-link-sent'" class="alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <p class="alert-title">¡Enlace enviado!</p>
                            <p class="alert-msg">Revisa tu bandeja de entrada.</p>
                        </div>
                    </div>

                    <!-- Info box -->
                    <div class="info-box">
                        <div class="info-icon-wrap">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="info-content">
                            <p class="info-title">¿No recibiste el correo?</p>
                            <p class="info-text">Revisa tu carpeta de spam. Si no lo encuentras, solicita un nuevo enlace.</p>
                        </div>
                    </div>

                    <!-- Botón reenviar -->
                    <form @submit.prevent="submit" class="login-form">
                        <button type="submit" class="btn-login" :disabled="form.processing">
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
                    <div class="form-footer">
                        <div class="divider"><span>o</span></div>

                        <!-- Tips -->
                        <div class="tips-section">
                            <p class="tips-title">
                                <i class="fas fa-lightbulb"></i>
                                Consejos útiles
                            </p>
                            <ul class="tips-list">
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    El enlace expira en 60 minutos
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    Revisa la carpeta de spam
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    Verifica que el correo sea correcto
                                </li>
                            </ul>
                        </div>

                        <div class="footer-bottom">
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="logout-btn"
                            >
                                <i class="fas fa-sign-out-alt"></i>
                                Cerrar Sesión
                            </Link>
                            <div class="security-badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>Verificación segura</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ══════════════════════════════════════════════════
                 LADO DERECHO — Panel con imagen
            ══════════════════════════════════════════════════ -->
            <div
                class="split-right"
                :class="{ 'has-bg-image': loginBg }"
                :style="loginBg ? { backgroundImage: `url(${loginBg})` } : {}"
            >
                <div class="right-overlay-bottom"></div>

                <div class="right-content">

                    <div class="right-badge">
                        <i class="fas fa-university"></i>
                        <span>Instituto Tecnológico</span>
                    </div>

                    <div class="right-text">
                        <h2>Casi<br><span class="right-title-accent">listo</span></h2>
                        <p>Solo falta verificar tu correo para acceder a todas las herramientas del Campus Digital.</p>
                    </div>

                    <div class="right-stats">
                        <div class="stat-pill">
                            <div class="stat-pill-icon"><i class="fas fa-envelope"></i></div>
                            <span class="stat-pill-label">Correo</span>
                        </div>
                        <div class="stat-pill">
                            <div class="stat-pill-icon"><i class="fas fa-check-circle"></i></div>
                            <span class="stat-pill-label">Verificado</span>
                        </div>
                        <div class="stat-pill">
                            <div class="stat-pill-icon"><i class="fas fa-shield-alt"></i></div>
                            <span class="stat-pill-label">Seguro</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const loginBg = '/images/Campus.webp';

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<style scoped>
* { margin: 0; padding: 0; box-sizing: border-box; }

/* ══════════════════════════════════════════════════
   LAYOUT PRINCIPAL
══════════════════════════════════════════════════ */
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

/* ══════════════════════════════════════════════════
   LADO IZQUIERDO
══════════════════════════════════════════════════ */
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

/* Esquina superior derecha */
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

/* Esquina inferior izquierda */
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

/* Esquina superior izquierda */
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

/* Esquina inferior derecha */
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

/* Brand */
.brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
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

/* Ícono sobre */
.envelope-icon-wrapper {
    width: 58px; height: 58px;
    margin: 0 auto 0.75rem;
    background: linear-gradient(135deg, rgba(168,85,247,0.12), rgba(236,72,153,0.08));
    border: 2px solid rgba(168,85,247,0.25);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.envelope-icon-wrapper i { font-size: 1.4rem; color: #a855f7; }

.form-header { text-align: center; margin-bottom: 0.9rem; }
.welcome-title {
    font-size: 1.4rem; font-weight: 700;
    color: #1e293b; margin-bottom: 0.2rem;
}
.title-purple { color: #a855f7; }
.welcome-sub  { font-size: 0.78rem; color: #94a3b8; line-height: 1.5; }

/* Alert éxito */
.alert-success {
    display: flex; align-items: flex-start; gap: 0.6rem;
    padding: 0.65rem 0.9rem;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 14px; margin-bottom: 0.75rem;
}
.alert-success i { font-size: 1rem; color: #22c55e; flex-shrink: 0; margin-top: 0.1rem; }
.alert-title { font-size: 0.78rem; font-weight: 700; color: #16a34a; margin-bottom: 0.1rem; }
.alert-msg   { font-size: 0.72rem; color: #4ade80; }

/* Info box */
.info-box {
    display: flex; gap: 0.65rem;
    padding: 0.65rem 0.85rem;
    background: #faf5ff;
    border: 1.5px solid #e9d5ff;
    border-radius: 14px;
    margin-bottom: 0.85rem;
}
.info-icon-wrap {
    width: 32px; height: 32px; flex-shrink: 0;
    background: rgba(168,85,247,0.12);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.info-icon-wrap i { color: #a855f7; font-size: 0.85rem; }
.info-content { flex: 1; }
.info-title { font-size: 0.78rem; font-weight: 700; color: #1e293b; margin-bottom: 0.15rem; }
.info-text  { font-size: 0.72rem; color: #94a3b8; line-height: 1.45; }

/* Formulario */
.login-form { margin-bottom: 0; }

.btn-login {
    width: 100%; padding: 0.72rem;
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

/* Footer */
.form-footer { padding-top: 0.75rem; border-top: 1px solid #f1f5f9; margin-top: 0.75rem; }
.divider { text-align: center; position: relative; margin-bottom: 0.7rem; }
.divider span {
    color: #94a3b8; font-size: 0.78rem; background: #fff;
    padding: 0 0.75rem; position: relative; z-index: 1;
}
.divider::before {
    content: ''; position: absolute; top: 50%; left: 0; right: 0;
    height: 1px; background: #e2e8f0;
}

/* Tips */
.tips-section { margin-bottom: 0.85rem; }
.tips-title {
    display: flex; align-items: center; gap: 0.35rem;
    font-size: 0.72rem; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 0.45rem;
}
.tips-title i { color: #f59e0b; font-size: 0.75rem; }
.tips-list { list-style: none; display: flex; flex-direction: column; gap: 0.3rem; }
.tips-list li {
    display: flex; align-items: center; gap: 0.4rem;
    color: #94a3b8; font-size: 0.76rem;
}
.tips-list li i { color: #34d399; font-size: 0.7rem; flex-shrink: 0; }

/* Footer bottom */
.footer-bottom {
    display: flex; align-items: center;
    justify-content: space-between;
    padding-top: 0.6rem;
    border-top: 1px solid #f8f0ff;
    flex-wrap: wrap; gap: 0.5rem;
}
.logout-btn {
    display: inline-flex; align-items: center; gap: 0.4rem;
    color: #ef4444; background: #fef2f2;
    border: 1.5px solid #fecaca;
    padding: 0.4rem 0.9rem; border-radius: 50px;
    font-size: 0.78rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s ease;
    text-decoration: none;
}
.logout-btn:hover {
    background: #fee2e2;
    border-color: #f87171;
}
.logout-btn i { font-size: 0.72rem; }

.security-badge {
    display: flex; align-items: center; gap: 0.35rem;
    color: #94a3b8; font-size: 0.7rem;
}
.security-badge i { color: #a855f7; font-size: 0.75rem; }

/* ══════════════════════════════════════════════════
   LADO DERECHO
══════════════════════════════════════════════════ */
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
    bottom: 0; left: 0; right: 0; height: 55%;
    background: linear-gradient(to top,
        rgba(5, 8, 20, 0.92) 0%,
        rgba(5, 8, 20, 0.7) 40%,
        transparent 100%);
    z-index: 2; pointer-events: none;
}
.right-content {
    position: relative; z-index: 3;
    width: 100%; padding: 0 2.5rem 2.5rem; max-width: 520px;
}
.right-badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: rgba(168,85,247,0.25);
    border: 1px solid rgba(168,85,247,0.4);
    border-radius: 50px; padding: 0.35rem 0.9rem;
    margin-bottom: 1.2rem; backdrop-filter: blur(8px);
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
    font-size: 0.92rem; color: rgba(255,255,255,0.65);
    line-height: 1.65; max-width: 360px;
}
.right-stats { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.stat-pill {
    display: flex; align-items: center; gap: 0.5rem;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 50px; padding: 0.5rem 1rem;
    backdrop-filter: blur(12px); transition: all 0.25s ease;
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
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.stat-pill-icon i { font-size: 0.78rem; color: #e9d5ff; }
.stat-pill-label  { font-size: 0.8rem; color: rgba(255,255,255,0.85); font-weight: 600; letter-spacing: 0.3px; }

/* ══════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════ */
@media (max-height: 700px) and (min-width: 769px) {
    .brand                  { margin-bottom: 0.5rem; }
    .welcome-title          { font-size: 1.15rem; }
    .welcome-sub            { font-size: 0.72rem; }
    .envelope-icon-wrapper  { width: 44px; height: 44px; margin-bottom: 0.5rem; }
    .envelope-icon-wrapper i{ font-size: 1.1rem; }
    .info-box               { padding: 0.5rem 0.75rem; margin-bottom: 0.6rem; }
    .form-footer            { padding-top: 0.5rem; margin-top: 0.5rem; }
    .tips-section           { margin-bottom: 0.6rem; }
    .split-left             { padding: 0.75rem 2rem; }
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
}

@media (max-width: 480px) {
    .split-right { display: none; }
    .split-left  { padding: 1.75rem 1.25rem; }
    .footer-bottom { flex-direction: column; align-items: center; }
}
</style>