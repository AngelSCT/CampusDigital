import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import clickAway from './directives/clickAway';

const appName = import.meta.env.VITE_APP_NAME || 'Campus Digital';

// Helper global para rutas
window.route = (name, params) => {
    const routes = {
        'login': '/login',
        'register': '/register',
        'logout': '/logout',
        'password.request': '/forgot-password',
        'password.email': '/forgot-password',
        'password.reset': '/reset-password',
        'password.update': '/reset-password',
        'verification.notice': '/email/verify',
        'verification.send': '/email/verification-notification',
        'dashboard': '/dashboard',
        'perfil.show': '/perfil',
        'perfil.update': '/perfil/actualizar',
        'perfil.photo.update': '/perfil/foto',
        'perfil.photo.delete': '/perfil/foto',
        'user-password.update': '/user/password',
        'admin.usuarios.index': '/admin/usuarios',
        'admin.usuarios.create': '/admin/usuarios/create',
        'admin.usuarios.store': '/admin/usuarios',
        'admin.usuarios.edit': '/admin/usuarios/:id/edit',
        'admin.usuarios.update': '/admin/usuarios/:id',
        'admin.usuarios.destroy': '/admin/usuarios/:id',
        'admin.usuarios.toggle-block': '/admin/usuarios/:id/toggle-block',
        'admin.usuarios.export': '/admin/usuarios/export',
        'admin.usuarios.export-by-role': '/admin/usuarios/export-by-role',
        'admin.usuarios.export-pdf': '/admin/usuarios/export-pdf',
        'admin.usuarios.export-by-role-pdf': '/admin/usuarios/export-by-role-pdf',
        'admin.reportes.usuarios': '/admin/reportes/usuarios',
        'admin.reportes.accesos': '/admin/reportes/accesos',
        'admin.reportes.actividad': '/admin/reportes/actividad',
        'admin.roles.index': '/admin/roles',
        'admin.bitacora.export-accesos-pdf': '/admin/bitacora/export-accesos-pdf',
        'admin.bitacora.export-accesos-periodo': '/admin/bitacora/export-accesos-periodo',
        'admin.bitacora.export-accesos-periodo-pdf': '/admin/bitacora/export-accesos-periodo-pdf',
        'admin.bitacora.export-actividad-pdf': '/admin/bitacora/export-actividad-pdf',
        'admin.bitacora.export-actividad-periodo': '/admin/bitacora/export-actividad-periodo',
        'admin.bitacora.export-actividad-periodo-pdf': '/admin/bitacora/export-actividad-periodo-pdf',
        'admin.bitacora.export-actividad-modulo': '/admin/bitacora/export-actividad-modulo',
        'admin.bitacora.export-actividad-modulo-pdf': '/admin/bitacora/export-actividad-modulo-pdf',
        'admin.roles.create': '/admin/roles/create',
        'admin.roles.store': '/admin/roles',
        'admin.roles.edit': '/admin/roles/:id/edit',
        'admin.roles.update': '/admin/roles/:id',
        'admin.roles.destroy': '/admin/roles/:id',

        'admin.permisos.index': '/admin/permisos',
        'admin.permisos.create': '/admin/permisos/create',
        'admin.permisos.store': '/admin/permisos',
        'admin.permisos.edit': '/admin/permisos/:id/edit',
        'admin.permisos.update': '/admin/permisos/:id',
        'admin.permisos.destroy': '/admin/permisos/:id',

        'admin.bitacora.accesos': '/admin/bitacora/accesos',
        'admin.bitacora.actividad': '/admin/bitacora/actividad',
        'admin.bitacora.export-accesos': '/admin/bitacora/export-accesos',
        'admin.bitacora.export-actividad': '/admin/bitacora/export-actividad',
    };
    
    let url = routes[name] || '/';
    
    if (params) {
        if (typeof params === 'object') {
            Object.keys(params).forEach(key => {
                url = url.replace(`:${key}`, params[key]);
            });
        } else {
            url = url.replace(':id', params);
        }
    }
    
    return url;
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);
        
        // Hacer route() disponible en todos los componentes
        app.config.globalProperties.route = window.route;
        app.directive('click-away', clickAway);

        
        return app.mount(el);
    },
    progress: {
        color: '#1E40AF',
    },
});