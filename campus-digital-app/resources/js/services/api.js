/**
 * Servicio centralizado de llamadas Axios a la API REST.
 * Consume los controladores:
 *   - UsuarioApiController         → /api/usuarios
 *   - UsuarioRolApiController      → /api/usuario-roles
 *   - UsuarioSesionApiController   → /api/sesiones
 *   - TarjetaUniversitariaApiController → /api/tarjetas
 */

import axios from 'axios';

// Axios ya viene configurado en bootstrap.js con el token CSRF.
// Aquí solo aseguramos la base URL y cabeceras comunes.
axios.defaults.baseURL = '/';
axios.defaults.headers.common['Accept'] = 'application/json';

// ─────────────────────────────────────────────
// USUARIOS  →  UsuarioApiController
// ─────────────────────────────────────────────

/**
 * GET /api/usuarios
 * Lista paginada con filtros opcionales.
 *
 * Ejemplo de respuesta:
 * {
 *   "current_page": 1,
 *   "data": [{ "id": 1, "nombre": "Juan", "apellido": "Pérez", "email": "juan@uni.mx", ... }],
 *   "per_page": 15,
 *   "total": 42
 * }
 */
export async function getUsuarios(params = {}) {
    const response = await axios.get('/api/usuarios', { params });
    return response.data;
}

/**
 * GET /api/usuarios/{id}
 *
 * Ejemplo de respuesta:
 * { "id": 1, "nombre": "Juan", "apellido": "Pérez", "email": "juan@uni.mx", "perfil": {...}, "roles": [...] }
 */
export async function getUsuario(id) {
    const response = await axios.get(`/api/usuarios/${id}`);
    return response.data;
}

/**
 * POST /api/usuarios
 * Body: { nombre, apellido, email, password, telefono? }
 *
 * Ejemplo de respuesta (201):
 * { "id": 5, "nombre": "Ana", "apellido": "López", "email": "ana@uni.mx", ... }
 */
export async function createUsuario(data) {
    const response = await axios.post('/api/usuarios', data);
    return response.data;
}

/**
 * PUT /api/usuarios/{id}
 * Body: campos a actualizar (nombre, apellido, email, telefono, password)
 *
 * Ejemplo de respuesta:
 * { "id": 5, "nombre": "Ana", "apellido": "González", ... }
 */
export async function updateUsuario(id, data) {
    const response = await axios.put(`/api/usuarios/${id}`, data);
    return response.data;
}

/**
 * DELETE /api/usuarios/{id}
 *
 * Ejemplo de respuesta:
 * { "message": "Usuario eliminado." }
 */
export async function deleteUsuario(id) {
    const response = await axios.delete(`/api/usuarios/${id}`);
    return response.data;
}

/**
 * POST /api/usuarios/{id}/toggle-block
 *
 * Ejemplo de respuesta:
 * { "message": "Usuario bloqueado.", "bloqueado": true }
 */
export async function toggleBloqueoUsuario(id) {
    const response = await axios.post(`/api/usuarios/${id}/toggle-block`);
    return response.data;
}


// ─────────────────────────────────────────────
// ROLES DE USUARIO  →  UsuarioRolApiController
// ─────────────────────────────────────────────

/**
 * GET /api/usuario-roles
 * Params opcionales: usuario_id, rol_id
 *
 * Ejemplo de respuesta:
 * [{ "id": 1, "usuario_id": 3, "rol_id": 2, "usuario": {...}, "rol": { "nombre": "admin" } }]
 */
export async function getUsuarioRoles(params = {}) {
    const response = await axios.get('/api/usuario-roles', { params });
    return response.data;
}

/**
 * POST /api/usuario-roles
 * Body: { usuario_id, rol_id }
 *
 * Ejemplo de respuesta (201):
 * { "id": 8, "usuario_id": 3, "rol_id": 2, "usuario": {...}, "rol": {...} }
 *
 * Ejemplo de respuesta (409 - ya tiene el rol):
 * { "message": "El usuario ya tiene ese rol." }
 */
export async function asignarRol(data) {
    const response = await axios.post('/api/usuario-roles', data);
    return response.data;
}

/**
 * DELETE /api/usuario-roles/{id}
 *
 * Ejemplo de respuesta:
 * { "message": "Rol revocado del usuario." }
 */
export async function revocarRol(id) {
    const response = await axios.delete(`/api/usuario-roles/${id}`);
    return response.data;
}


// ─────────────────────────────────────────────
// SESIONES  →  UsuarioSesionApiController
// ─────────────────────────────────────────────

/**
 * GET /api/sesiones
 * Params opcionales: usuario_id, activa, desde, hasta, per_page
 *
 * Ejemplo de respuesta:
 * {
 *   "current_page": 1,
 *   "data": [{ "id": 1, "usuario_id": 3, "activa": true, "ip": "192.168.1.1", "inicia_at": "2026-05-12T10:00:00" }],
 *   "total": 5
 * }
 */
export async function getSesiones(params = {}) {
    const response = await axios.get('/api/sesiones', { params });
    return response.data;
}

/**
 * GET /api/sesiones/{id}
 *
 * Ejemplo de respuesta:
 * { "id": 1, "usuario_id": 3, "activa": false, "ip": "192.168.1.1", "termina_at": "2026-05-12T11:30:00", "usuario": {...} }
 */
export async function getSesion(id) {
    const response = await axios.get(`/api/sesiones/${id}`);
    return response.data;
}


// ─────────────────────────────────────────────
// TARJETAS  →  TarjetaUniversitariaApiController
// ─────────────────────────────────────────────

/**
 * GET /api/tarjetas
 * Params opcionales: usuario_id, estado, uid, per_page
 *
 * Ejemplo de respuesta:
 * {
 *   "current_page": 1,
 *   "data": [{ "id": 1, "uid": "A1B2C3D4", "estado": "activa", "usuario": {...} }],
 *   "total": 10
 * }
 */
export async function getTarjetas(params = {}) {
    const response = await axios.get('/api/tarjetas', { params });
    return response.data;
}

/**
 * GET /api/tarjetas/{id}
 *
 * Ejemplo de respuesta:
 * { "id": 1, "uid": "A1B2C3D4", "estado": "activa", "usuario": {...}, "registradoPor": {...} }
 */
export async function getTarjeta(id) {
    const response = await axios.get(`/api/tarjetas/${id}`);
    return response.data;
}

/**
 * GET /api/tarjetas/uid/{uid}
 * Busca por UID del chip NFC.
 *
 * Ejemplo de respuesta:
 * { "id": 1, "uid": "A1B2C3D4", "estado": "activa", "usuario": { "perfil": {...} } }
 *
 * Ejemplo de respuesta (404):
 * { "message": "Tarjeta no encontrada." }
 */
export async function getTarjetaPorUid(uid) {
    const response = await axios.get(`/api/tarjetas/uid/${uid}`);
    return response.data;
}

/**
 * POST /api/tarjetas
 * Body: { usuario_id, uid, estado?, registrado_por_usuario_id?, meta_json? }
 *
 * Ejemplo de respuesta (201):
 * { "id": 3, "uid": "FF001122", "estado": "activa", "usuario": {...} }
 */
export async function createTarjeta(data) {
    const response = await axios.post('/api/tarjetas', data);
    return response.data;
}

/**
 * PUT /api/tarjetas/{id}
 * Body: { uid?, estado?, motivo_bloqueo?, meta_json? }
 *
 * Ejemplo de respuesta:
 * { "id": 3, "uid": "FF001122", "estado": "perdida" }
 */
export async function updateTarjeta(id, data) {
    const response = await axios.put(`/api/tarjetas/${id}`, data);
    return response.data;
}

/**
 * DELETE /api/tarjetas/{id}
 *
 * Ejemplo de respuesta:
 * { "message": "Tarjeta eliminada." }
 */
export async function deleteTarjeta(id) {
    const response = await axios.delete(`/api/tarjetas/${id}`);
    return response.data;
}

/**
 * POST /api/tarjetas/{id}/bloquear
 * Body: { motivo_bloqueo, bloqueado_por_usuario_id? }
 *
 * Ejemplo de respuesta:
 * { "message": "Tarjeta bloqueada.", "tarjeta": { "id": 3, "estado": "bloqueada", "motivo_bloqueo": "Reportada como robada" } }
 */
export async function bloquearTarjeta(id, data) {
    const response = await axios.post(`/api/tarjetas/${id}/bloquear`, data);
    return response.data;
}

/**
 * POST /api/tarjetas/{id}/desbloquear
 *
 * Ejemplo de respuesta:
 * { "message": "Tarjeta desbloqueada.", "tarjeta": { "id": 3, "estado": "activa" } }
 */
export async function desbloquearTarjeta(id) {
    const response = await axios.post(`/api/tarjetas/${id}/desbloquear`);
    return response.data;
}