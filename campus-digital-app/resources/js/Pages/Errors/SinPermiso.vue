<template>
    <div class="sin-permiso-wrapper">
        <div class="bg-grid"></div>
        <div class="bg-glow"></div>

        <div class="sin-permiso-card">
            <div class="icono-wrapper">
                <div class="icono-ring">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none">
                        <circle cx="32" cy="32" r="28" stroke="#3b82f6" stroke-width="1.5" stroke-dasharray="4 3" opacity="0.4"/>
                        <circle cx="32" cy="32" r="20" stroke="#60a5fa" stroke-width="1.5" opacity="0.6"/>
                        <path d="M22 22L42 42M42 22L22 42" stroke="#f87171" stroke-width="2.5"
                              stroke-linecap="round"/>
                        <circle cx="32" cy="32" r="5" fill="#f8717122" stroke="#f87171" stroke-width="1"/>
                    </svg>
                </div>
                <div class="icono-pulse"></div>
            </div>

            <div class="badge-acceso">
                <span class="badge-dot"></span>
                ACCESO RESTRINGIDO
            </div>

            <h1>Acceso denegado</h1>
            <p class="subtitulo">No tienes permiso para acceder a esta sección.</p>
            <p class="detalle">
                Si crees que esto es un error, contacta al administrador del sistema para solicitar los permisos necesarios.
            </p>

            <div class="divisor"></div>

            <div class="acciones">
                <button class="btn-secundario" @click="$inertia.visit(route('dashboard'))">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Ir al inicio
                </button>
                <button class="btn-primario" @click="$inertia.visit(route('perfil.show'))">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Mi perfil
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap');

.sin-permiso-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #060d1a;
    background-image:
        radial-gradient(ellipse 80% 50% at 50% -10%, #0f2a5a55 0%, transparent 70%),
        radial-gradient(ellipse 60% 40% at 80% 90%, #1e3a8a33 0%, transparent 60%);
    padding: 2rem;
    position: relative;
    overflow: hidden;
    font-family: 'Inter', sans-serif;
}

.bg-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(59, 130, 246, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(59, 130, 246, 0.04) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
}

.bg-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, #1d4ed815 0%, transparent 65%);
    pointer-events: none;
}

.sin-permiso-card {
    background: linear-gradient(135deg, #0d1f3c 0%, #0a1628 100%);
    border: 1px solid #1e3a6e;
    border-radius: 20px;
    padding: 3rem 2.5rem 2.5rem;
    max-width: 440px;
    width: 100%;
    text-align: center;
    box-shadow:
        0 0 0 1px #3b82f611,
        0 8px 40px rgba(0, 0, 0, 0.6),
        0 0 80px rgba(29, 78, 216, 0.07),
        inset 0 1px 0 rgba(255, 255, 255, 0.04);
    position: relative;
    animation: fadeUp 0.5s ease both;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}

.sin-permiso-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 1px;
    background: linear-gradient(90deg, transparent, #3b82f655, transparent);
    border-radius: 1px;
}

.icono-wrapper {
    position: relative;
    width: 88px;
    height: 88px;
    margin: 0 auto 1.75rem;
}

.icono-ring {
    width: 100%;
    height: 100%;
    animation: spin-slow 12s linear infinite;
}

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}

.icono-ring svg {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 0 12px rgba(248, 113, 113, 0.35));
}

.icono-pulse {
    position: absolute;
    inset: 12px;
    border-radius: 50%;
    background: rgba(248, 113, 113, 0.06);
    animation: pulse 2.4s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.4; }
    50%       { transform: scale(1.15); opacity: 0.08; }
}

.badge-acceso {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.25);
    color: #fca5a5;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    padding: 0.3rem 0.85rem;
    border-radius: 999px;
    margin-bottom: 1.25rem;
    font-family: 'Syne', sans-serif;
}

.badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #f87171;
    box-shadow: 0 0 6px #f87171;
    animation: blink 1.6s ease-in-out infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.3; }
}

h1 {
    font-family: 'Syne', sans-serif;
    font-size: 1.7rem;
    font-weight: 700;
    color: #e2eeff;
    margin-bottom: 0.6rem;
    letter-spacing: -0.02em;
}

.subtitulo {
    font-size: 0.95rem;
    color: #7ea8d8;
    margin-bottom: 0.75rem;
    font-weight: 400;
}

.detalle {
    font-size: 0.82rem;
    color: #3d6496;
    margin-bottom: 1.75rem;
    line-height: 1.7;
}

.divisor {
    height: 1px;
    background: linear-gradient(90deg, transparent, #1e3a6e, transparent);
    margin-bottom: 1.75rem;
}

.acciones {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primario,
.btn-secundario {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border-radius: 10px;
    padding: 0.6rem 1.35rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'Inter', sans-serif;
}

.btn-primario {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    color: #e0eeff;
    border: 1px solid #3b82f640;
    box-shadow: 0 4px 16px rgba(29, 78, 216, 0.3), inset 0 1px 0 rgba(255,255,255,0.1);
}

.btn-primario:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 6px 22px rgba(37, 99, 235, 0.45), inset 0 1px 0 rgba(255,255,255,0.12);
    transform: translateY(-1px);
}

.btn-primario:active {
    transform: translateY(0);
}

.btn-secundario {
    background: transparent;
    color: #7ea8d8;
    border: 1px solid #1e3a6e;
}

.btn-secundario:hover {
    background: rgba(30, 58, 110, 0.35);
    border-color: #3b6bb5;
    color: #bcd6f8;
    transform: translateY(-1px);
}

.btn-secundario:active {
    transform: translateY(0);
}
</style>