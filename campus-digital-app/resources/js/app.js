import "./bootstrap";
import * as api from "./services/api";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import clickAway from "./directives/clickAway";

//AXIOS ACTIVADO EXPLICITO PARA DEV - AXIOS YA ESTA CONFIGURADIO DESDE BOOTSTRAP.JS
// Desactivado temporalmente para evitar ruido 401 en consola.
// Si necesitas probar los endpoints, define VITE_API_KEY en .env.
// if (import.meta.env.DEV) {
//     const apiKey = import.meta.env.VITE_API_KEY;
//     const headers = { 'X-API-KEY': apiKey };
//     window.axios.get('/api/v1/usuarios', { params: { per_page: 5 }, headers })
//         .then(r => console.info('[Axios] GET /api/v1/usuarios →', r.data))
//         .catch(e => console.error('[Axios] GET /api/v1/usuarios ✗', e.response?.data));
//     window.axios.get('/api/v1/tarjetas', { params: { per_page: 5 }, headers })
//         .then(r => console.info('[Axios] GET /api/v1/tarjetas →', r.data))
//         .catch(e => console.error('[Axios] GET /api/v1/tarjetas ✗', e.response?.data));
//     window.axios.get('/api/v1/sesiones', { params: { per_page: 5 }, headers })
//         .then(r => console.info('[Axios] GET /api/v1/sesiones →', r.data))
//         .catch(e => console.error('[Axios] GET /api/v1/sesiones ✗', e.response?.data));
//     window.axios.get('/api/v1/roles', { headers })
//         .then(r => console.info('[Axios] GET /api/v1/roles →', r.data))
//         .catch(e => console.error('[Axios] GET /api/v1/roles ✗', e.response?.data));
// }

const appName = import.meta.env.VITE_APP_NAME || "Campus Digital";

// ROUTEADOR GLOBAL DE LA APLICACION
window.route = (name, params) => {
    const routes = {
        login: "/login",
        register: "/register",
        logout: "/logout",
        "password.request": "/forgot-password",
        "password.email": "/forgot-password",
        "password.reset": "/reset-password",
        "password.update": "/reset-password",
        "verification.notice": "/email/verify",
        "verification.send": "/email/verification-notification",
        dashboard: "/dashboard",
        "perfil.show": "/perfil",
        "perfil.update": "/perfil/actualizar",
        "perfil.photo.update": "/perfil/foto",
        "perfil.photo.delete": "/perfil/foto",
        "user-password.update": "/user/password",
        "sin-permiso": "/sin-permiso",

        // Usuarios
        "admin.usuarios.index": "/admin/usuarios",
        "admin.usuarios.show": "/admin/usuarios/:id",
        "admin.usuarios.create": "/admin/usuarios/create",
        "admin.usuarios.store": "/admin/usuarios",
        "admin.usuarios.edit": "/admin/usuarios/:id/edit",
        "admin.usuarios.update": "/admin/usuarios/:id",
        "admin.usuarios.destroy": "/admin/usuarios/:id",
        "admin.usuarios.toggle-block": "/admin/usuarios/:id/toggle-block",
        "admin.usuarios.export": "/admin/usuarios/export",
        "admin.usuarios.export-by-role": "/admin/usuarios/export-by-role",
        "admin.usuarios.export-pdf": "/admin/usuarios/export-pdf",
        "admin.usuarios.export-by-role-pdf": "/admin/usuarios/export-by-role-pdf",

        // Reportes
        "admin.reportes.usuarios": "/admin/reportes/usuarios",
        "admin.reportes.accesos": "/admin/reportes/accesos",
        "admin.reportes.actividad": "/admin/reportes/actividad",

        // Roles
        "admin.roles.index": "/admin/roles",
        // Tiendas
        "admin.tiendas.index": "/admin/tiendas",
        "admin.tiendas.manage": "/admin/tiendas/gestion",
        "admin.tiendas.store": "/admin/tiendas",
        "admin.tiendas.update": "/admin/tiendas/:id",
        "admin.tiendas.destroy": "/admin/tiendas/:id",

        // Proveedores
        "admin.proveedores.index": "/admin/proveedores",
        "admin.proveedores.manage": "/admin/proveedores/gestion",
        "admin.proveedores.asignar": "/admin/proveedores/:id/asignar",

        // Repartidores
        "admin.repartidores.index": "/admin/repartidores",
        "admin.repartidores.toggle": "/admin/repartidores/:id/toggle",

        "admin.roles.index": "/admin/roles",
        "admin.bitacora.export-accesos-pdf": "/admin/bitacora/export-accesos-pdf",
        "admin.bitacora.export-accesos-periodo": "/admin/bitacora/export-accesos-periodo",
        "admin.bitacora.export-accesos-periodo-pdf": "/admin/bitacora/export-accesos-periodo-pdf",
        "admin.bitacora.export-actividad-pdf": "/admin/bitacora/export-actividad-pdf",
        "admin.bitacora.export-actividad-periodo": "/admin/bitacora/export-actividad-periodo",
        "admin.bitacora.export-actividad-periodo-pdf": "/admin/bitacora/export-actividad-periodo-pdf",
        "admin.bitacora.export-actividad-modulo": "/admin/bitacora/export-actividad-modulo",
        "admin.bitacora.export-actividad-modulo-pdf": "/admin/bitacora/export-actividad-modulo-pdf",
        "admin.roles.create": "/admin/roles/create",
        "admin.roles.store": "/admin/roles",
        "admin.roles.show": "/admin/roles/:id",
        "admin.roles.edit": "/admin/roles/:id/edit",
        "admin.roles.update": "/admin/roles/:id",
        "admin.roles.destroy": "/admin/roles/:id",

        // Permisos
        "admin.permisos.index": "/admin/permisos",
        "admin.permisos.create": "/admin/permisos/create",
        "admin.permisos.store": "/admin/permisos",
        "admin.permisos.show": "/admin/permisos/:id",
        "admin.permisos.edit": "/admin/permisos/:id/edit",
        "admin.permisos.update": "/admin/permisos/:id",
        "admin.permisos.destroy": "/admin/permisos/:id",

        // Bitácora
        "admin.bitacora.accesos": "/admin/bitacora/accesos",
        "admin.bitacora.actividad": "/admin/bitacora/actividad",
        "admin.bitacora.export-accesos": "/admin/bitacora/export-accesos",
        "admin.bitacora.export-actividad": "/admin/bitacora/export-actividad",
        "admin.bitacora.export-accesos-pdf": "/admin/bitacora/export-accesos-pdf",
        "admin.bitacora.export-accesos-periodo": "/admin/bitacora/export-accesos-periodo",
        "admin.bitacora.export-accesos-periodo-pdf": "/admin/bitacora/export-accesos-periodo-pdf",
        "admin.bitacora.export-actividad-pdf": "/admin/bitacora/export-actividad-pdf",
        "admin.bitacora.export-actividad-periodo": "/admin/bitacora/export-actividad-periodo",
        "admin.bitacora.export-actividad-periodo-pdf": "/admin/bitacora/export-actividad-periodo-pdf",
        "admin.bitacora.export-actividad-modulo": "/admin/bitacora/export-actividad-modulo",
        "admin.bitacora.export-actividad-modulo-pdf": "/admin/bitacora/export-actividad-modulo-pdf",

        // Tarjetas
        "admin.tarjetas.dashboard": "/admin/tarjetas/dashboard",
        "admin.tarjetas.index": "/admin/tarjetas",
        "admin.tarjetas.create": "/admin/tarjetas/create",
        "admin.tarjetas.store": "/admin/tarjetas",
        "admin.tarjetas.show": "/admin/tarjetas/:id",
        "admin.tarjetas.edit": "/admin/tarjetas/:id/edit",
        "admin.tarjetas.update": "/admin/tarjetas/:id",
        "admin.tarjetas.destroy": "/admin/tarjetas/:id",
        "admin.tarjetas.toggle-block": "/admin/tarjetas/:id/toggle-block",
        "admin.tarjetas.reportes.index": "/admin/tarjetas/reportes/index",
        "admin.tarjetas.reportes.export-csv": "/admin/tarjetas/reportes/export-csv",
        "admin.tarjetas.reportes.export-incidentes": "/admin/tarjetas/reportes/export-incidentes",
        "admin.tarjetas.reportes.export-lecturas-pdf": "/admin/tarjetas/reportes/export-lecturas-pdf",
        "admin.tarjetas.reportes.export-modulo-csv": "/admin/tarjetas/reportes/export-modulo-csv",
        "admin.tarjetas.reportes.export-modulo-pdf": "/admin/tarjetas/reportes/export-modulo-pdf",
        "admin.tarjetas.reportes.export-incidentes-pdf": "/admin/tarjetas/reportes/export-incidentes-pdf",

        // Lector
        "lector.index": "/lector",
        "lector.leer": "/lector/leer",
        "lector.confirmar-pedido": "/lector/confirmar-pedido",

        // RFID
        "rfid.login": "/auth/rfid-login",
        "mi-tarjeta.show": "/mi-tarjeta",
        "mi-tarjeta.pin.store": "/mi-tarjeta/pin",
        "mi-tarjeta.escanear": "/mi-tarjeta/escanear",
        "mi-tarjeta.pin": "/mi-tarjeta/pin",

        // Áreas
        "admin.areas.index": "/admin/areas",
        "admin.areas.store": "/admin/areas",
        "admin.areas.show": "/admin/areas/:id",
        "admin.areas.update": "/admin/areas/:id",
        "admin.areas.destroy": "/admin/areas/:id",

        // Categorías de Ticket
        "admin.categorias-ticket.index": "/admin/categorias-ticket",
        "admin.categorias-ticket.store": "/admin/categorias-ticket",
        "admin.categorias-ticket.show": "/admin/categorias-ticket/:id",
        "admin.categorias-ticket.update": "/admin/categorias-ticket/:id",
        "admin.categorias-ticket.destroy": "/admin/categorias-ticket/:id",

        // Ubicaciones
        "admin.ubicaciones.index": "/admin/ubicaciones",
        "admin.ubicaciones.store": "/admin/ubicaciones",
        "admin.ubicaciones.show": "/admin/ubicaciones/:id",
        "admin.ubicaciones.update": "/admin/ubicaciones/:id",
        "admin.ubicaciones.destroy": "/admin/ubicaciones/:id",

        // Equipos Activos
        "admin.equipos-activos.index": "/admin/equipos-activos",
        "admin.equipos-activos.store": "/admin/equipos-activos",
        "admin.equipos-activos.show": "/admin/equipos-activos/:id",
        "admin.equipos-activos.update": "/admin/equipos-activos/:id",
        "admin.equipos-activos.destroy": "/admin/equipos-activos/:id",

        // Tickets
        "admin.tickets.index": "/admin/tickets",
        "admin.tickets.store": "/admin/tickets",
        "admin.tickets.show": "/admin/tickets/:id",
        "admin.tickets.update": "/admin/tickets/:id",
        "admin.tickets.destroy": "/admin/tickets/:id",

        // Mantenimientos Preventivos
        "admin.mantenimientos-preventivos.index": "/admin/mantenimientos-preventivos",
        "admin.mantenimientos-preventivos.store": "/admin/mantenimientos-preventivos",
        "admin.mantenimientos-preventivos.show": "/admin/mantenimientos-preventivos/:id",
        "admin.mantenimientos-preventivos.update": "/admin/mantenimientos-preventivos/:id",
        "admin.mantenimientos-preventivos.destroy": "/admin/mantenimientos-preventivos/:id",

        // Asignaciones Técnicas
        "admin.asignaciones-tecnicas.index": "/admin/asignaciones-tecnicas",
        "admin.asignaciones-tecnicas.store": "/admin/asignaciones-tecnicas",
        "admin.asignaciones-tecnicas.show": "/admin/asignaciones-tecnicas/:id",
        "admin.asignaciones-tecnicas.update": "/admin/asignaciones-tecnicas/:id",
        "admin.asignaciones-tecnicas.destroy": "/admin/asignaciones-tecnicas/:id",

        // Archivos
        "archivos.index": "/archivos",
        "archivos.carpeta.crear": "/archivos/carpeta",
        "archivos.carpeta.eliminar": "/archivos/carpeta/:id",
        "archivos.carpeta.renombrar": "/archivos/carpeta/:id/renombrar",
        "archivos.subir": "/archivos/subir",
        "archivos.descargar": "/archivos/:id/descargar",
        "archivos.previsualizar": "/archivos/:id/previsualizar",
        "archivos.eliminar": "/archivos/:id",
        "archivos.marcar-visto": "/archivos/:id/marcar-visto",
        "archivos.desmarcar-visto": "/archivos/:id/desmarcar-visto",
        "archivos.nota": "/archivos/:id/nota",

        // Módulo 8 - Recargas
        "modulo_8.index": "/modulo_8",
        "modulo_8.recargar.form": "/modulo_8/recargar",
        "modulo_8.recargar": "/modulo_8/recargar",
        "modulo_8.recargar.reintentar": "/modulo_8/recargar/:id/reintentar",
        "modulo_8.comprobante": "/modulo_8/recargar/:id/comprobante",
        "modulo_8.reportes.historial": "/modulo_8/reportes/historial",
        "modulo_8.reportes.fallidos": "/modulo_8/reportes/fallidos",
        "modulo_8.reportes.conciliacion": "/modulo_8/reportes/conciliacion",
        "modulo_8.saldo": "/modulo_8/saldo",
        "modulo_8.pagar": "/modulo_8/pagar",
        "modulo_8.movimientos": "/modulo_8/movimientos",
        "modulo_8.comprobantes": "/modulo_8/comprobantes",
        "proveedor.operativo.index": "/proveedor/operativo",
        "proveedor.inventario.index": "/proveedor/inventario",
        "proveedor.productos.store": "/proveedor/productos",
        "proveedor.productos.update": "/proveedor/productos/:id",
        "proveedor.productos.destroy": "/proveedor/productos/:id",
        "proveedor.reportes.index":  "/proveedor/reportes",
        // Monedero - Recargas (estudiante)
        "monedero.recargas": "/monedero/recargas",
        "monedero.recargas.store": "/monedero/recargas",

        // ── MÓDULO 4.2: MONEDERO DIGITAL (ADMIN) ──────────────────────────────
        "admin.monedero.dashboard": "/admin/monedero/dashboard",

        "admin.monedero.reportes.index": "/admin/monedero/reportes",
        "admin.monedero.reportes.estado-cuenta": "/admin/monedero/reportes/estado-cuenta",
        "admin.monedero.reportes.movimientos": "/admin/monedero/reportes/movimientos",
        "admin.monedero.reportes.uso-categoria": "/admin/monedero/reportes/uso-categoria",

        "admin.monedero.exportes.estado-cuenta-pdf": "/admin/monedero/exportes/estado-cuenta/pdf",
        "admin.monedero.exportes.estado-cuenta-csv": "/admin/monedero/exportes/estado-cuenta/csv",
        "admin.monedero.exportes.movimientos-pdf": "/admin/monedero/exportes/movimientos/pdf",
        "admin.monedero.exportes.movimientos-csv": "/admin/monedero/exportes/movimientos/csv",
        "admin.monedero.exportes.uso-categoria-pdf": "/admin/monedero/exportes/uso-categoria/pdf",
        "admin.monedero.exportes.uso-categoria-csv": "/admin/monedero/exportes/uso-categoria/csv",

        "admin.monedero.reglas.index": "/admin/monedero/reglas",
        "admin.monedero.reglas.create": "/admin/monedero/reglas/create",
        "admin.monedero.reglas.store": "/admin/monedero/reglas",
        "admin.monedero.reglas.show": "/admin/monedero/reglas/:id",
        "admin.monedero.reglas.edit": "/admin/monedero/reglas/:id/edit",
        "admin.monedero.reglas.update": "/admin/monedero/reglas/:id",
        "admin.monedero.reglas.destroy": "/admin/monedero/reglas/:id",
    };

    let url = routes[name] || "/";
    if (params) {
        if (typeof params === "object") {
            const queryParams = {};

            Object.keys(params).forEach((key) => {
                if (url.includes(`:${key}`)) {
                    url = url.replace(`:${key}`, params[key]);
                } else {
                    queryParams[key] = params[key];
                }
            });

            const query = new URLSearchParams(queryParams).toString();
            if (query) url = `${url}?${query}`;

        } else {
            url = url.replace(":id", params);
        }
    }

    return url;
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) }).use(plugin);

        app.config.globalProperties.route = window.route;
        app.config.globalProperties.$api = api;
        app.directive("click-away", clickAway);

        return app.mount(el);
    },
    progress: {
        color: "#1E40AF",
    },
});
