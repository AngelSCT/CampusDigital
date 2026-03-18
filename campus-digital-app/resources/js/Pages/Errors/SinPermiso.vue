<template>
    <div class="sin-permiso-wrapper">
        <div class="sin-permiso-card">

            <div class="icono-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="30" stroke="#E53E3E" stroke-width="2.5"/>
                    <path d="M20 20L44 44M44 20L20 44" stroke="#E53E3E" stroke-width="3"
                          stroke-linecap="round"/>
                </svg>
            </div>

            <h1>Acceso denegado</h1>
            <p class="subtitulo">No tienes permiso para acceder a esta sección.</p>
            <p class="detalle">
                Si crees que esto es un error, contacta al administrador del sistema.
            </p>

            <div class="acciones">
                <button class="btn-secundario" @click="$inertia.visit(route('dashboard'))">
                    Ir al inicio
                </button>
                <button class="btn-primario" @click="$inertia.visit(route('perfil.show'))">
                    Mi perfil
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
</script>

<style scoped>
.sin-permiso-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f7f8fc;
    padding: 2rem;
}

.sin-permiso-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 3rem 2.5rem;
    max-width: 420px;
    width: 100%;
    text-align: center;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
}

.icono-wrapper {
    margin: 0 auto 1.5rem;
    width: 72px;
    height: 72px;
}

.icono-wrapper svg {
    width: 100%;
    height: 100%;
}

h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 0.5rem;
}

.subtitulo {
    font-size: 1rem;
    color: #4a5568;
    margin-bottom: 0.75rem;
}

.detalle {
    font-size: 0.875rem;
    color: #718096;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.acciones {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primario {
    background-color: #1E40AF;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 0.6rem 1.4rem;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primario:hover {
    background-color: #1e3a8a;
}

.btn-secundario {
    background-color: transparent;
    color: #1E40AF;
    border: 1.5px solid #1E40AF;
    border-radius: 8px;
    padding: 0.6rem 1.4rem;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secundario:hover {
    background-color: #EFF6FF;
}
</style>