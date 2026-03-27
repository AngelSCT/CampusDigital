import "./bootstrap";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import clickAway from "./directives/clickAway";

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
        "admin.usuarios.export-by-role-pdf":
            "/admin/usuarios/export-by-role-pdf",
        "admin.reportes.usuarios": "/admin/reportes/usuarios",
        "admin.reportes.accesos": "/admin/reportes/accesos",
        "admin.reportes.actividad": "/admin/reportes/actividad",
        "admin.roles.index": "/admin/roles",
        "admin.bitacora.export-accesos-pdf":
            "/admin/bitacora/export-accesos-pdf",
        "admin.bitacora.export-accesos-periodo":
            "/admin/bitacora/export-accesos-periodo",
        "admin.bitacora.export-accesos-periodo-pdf":
            "/admin/bitacora/export-accesos-periodo-pdf",
        "admin.bitacora.export-actividad-pdf":
            "/admin/bitacora/export-actividad-pdf",
        "admin.bitacora.export-actividad-periodo":
            "/admin/bitacora/export-actividad-periodo",
        "admin.bitacora.export-actividad-periodo-pdf":
            "/admin/bitacora/export-actividad-periodo-pdf",
        "admin.bitacora.export-actividad-modulo":
            "/admin/bitacora/export-actividad-modulo",
        "admin.bitacora.export-actividad-modulo-pdf":
            "/admin/bitacora/export-actividad-modulo-pdf",
        "admin.roles.create": "/admin/roles/create",
        "admin.roles.store": "/admin/roles",
        "admin.roles.show": "/admin/roles/:id",
        "admin.roles.edit": "/admin/roles/:id/edit",
        "admin.roles.update": "/admin/roles/:id",
        "admin.roles.destroy": "/admin/roles/:id",

        "admin.permisos.index": "/admin/permisos",
        "admin.permisos.create": "/admin/permisos/create",
        "admin.permisos.store": "/admin/permisos",
        "admin.permisos.show": "/admin/permisos/:id",
        "admin.permisos.edit": "/admin/permisos/:id/edit",
        "admin.permisos.update": "/admin/permisos/:id",
        "admin.permisos.destroy": "/admin/permisos/:id",

        "admin.bitacora.accesos": "/admin/bitacora/accesos",
        "admin.bitacora.actividad": "/admin/bitacora/actividad",
        "admin.bitacora.export-accesos": "/admin/bitacora/export-accesos",
        "admin.bitacora.export-actividad": "/admin/bitacora/export-actividad",

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
        "admin.tarjetas.reportes.export-csv":
            "/admin/tarjetas/reportes/export-csv",
        "admin.tarjetas.reportes.export-incidentes":
            "/admin/tarjetas/reportes/export-incidentes",
        "admin.tarjetas.reportes.export-lecturas-pdf":
            "/admin/tarjetas/reportes/export-lecturas-pdf",
        "admin.tarjetas.reportes.export-modulo-csv":
            "/admin/tarjetas/reportes/export-modulo-csv",
        "admin.tarjetas.reportes.export-modulo-pdf":
            "/admin/tarjetas/reportes/export-modulo-pdf",
        "admin.tarjetas.reportes.export-incidentes-pdf":
            "/admin/tarjetas/reportes/export-incidentes-pdf",

        // Lector
        "lector.index": "/lector",
        "lector.leer": "/lector/leer",
        "lector.confirmar-pedido": "/lector/confirmar-pedido",

        //UUID DE INICIO DE SESSION
        "rfid.login": "/auth/rfid-login",

        //CONFIG DE PIN DE USUARIO CON UUID
        "mi-tarjeta.show": "/mi-tarjeta",
        "mi-tarjeta.pin.store": "/mi-tarjeta/pin",
        "mi-tarjeta.escanear": "/mi-tarjeta/escanear",
        "mi-tarjeta.pin": "/mi-tarjeta/pin",

        //RUTA DE ERROR DE PERMISO POR MIDDLEWARE
        "sin-permiso": "/sin-permiso",

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

        //RUTAS DE UBICACIONES
        "admin.ubicaciones.index": "/admin/ubicaciones",
        "admin.ubicaciones.store": "/admin/ubicaciones",
        "admin.ubicaciones.show": "/admin/ubicaciones/:id",
        "admin.ubicaciones.update": "/admin/ubicaciones/:id",
        "admin.ubicaciones.destroy": "/admin/ubicaciones/:id",

        //RUTAS DE EQUIPOS ACTIVOS
        "admin.equipos-activos.index": "/admin/equipos-activos",
        "admin.equipos-activos.store": "/admin/equipos-activos",
        "admin.equipos-activos.show": "/admin/equipos-activos/:id",
        "admin.equipos-activos.update": "/admin/equipos-activos/:id",
        "admin.equipos-activos.destroy": "/admin/equipos-activos/:id",

        //RUTAS DE TICKETS
        "admin.tickets.index": "/admin/tickets",
        "admin.tickets.store": "/admin/tickets",
        "admin.tickets.show": "/admin/tickets/:id",
        "admin.tickets.update": "/admin/tickets/:id",
        "admin.tickets.destroy": "/admin/tickets/:id",

        //RUTAS DE MANTENIMIENTOS PREVENTIVOS
        "admin.mantenimientos-preventivos.index":
            "/admin/mantenimientos-preventivos",
        "admin.mantenimientos-preventivos.store":
            "/admin/mantenimientos-preventivos",
        "admin.mantenimientos-preventivos.show":
            "/admin/mantenimientos-preventivos/:id",
        "admin.mantenimientos-preventivos.update":
            "/admin/mantenimientos-preventivos/:id",
        "admin.mantenimientos-preventivos.destroy":
            "/admin/mantenimientos-preventivos/:id",

        //RUTAS DE ASIGNACIONES TECNICAS
        "admin.asignaciones-tecnicas.index": "/admin/asignaciones-tecnicas",
        "admin.asignaciones-tecnicas.store": "/admin/asignaciones-tecnicas",
        "admin.asignaciones-tecnicas.show": "/admin/asignaciones-tecnicas/:id",
        "admin.asignaciones-tecnicas.update":
            "/admin/asignaciones-tecnicas/:id",
        "admin.asignaciones-tecnicas.destroy":
            "/admin/asignaciones-tecnicas/:id",

        //RUTAS DEL EXPLORADOR DE ARCHIVOS
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
    };

    let url = routes[name] || "/";

    if (params) {
        if (typeof params === "object") {
            Object.keys(params).forEach((key) => {
                url = url.replace(`:${key}`, params[key]);
            });
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
        app.directive("click-away", clickAway);

        return app.mount(el);
    },
    progress: {
        color: "#1E40AF",
    },
});
