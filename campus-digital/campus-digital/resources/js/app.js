import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Campus Digital';

// Helper global para rutas (simple)
window.route = (name, params) => {
    const routes = {
        'login': '/login',
        'register': '/register',
        'logout': '/logout',
        'password.request': '/forgot-password',
        'password.email': '/forgot-password',
        'password.reset': '/reset-password',
        'password.update': '/reset-password',
        'verification.send': '/email/verification-notification',
        'dashboard': '/dashboard',
    };
    
    let url = routes[name] || '/';
    
    if (params && typeof params === 'object') {
        Object.keys(params).forEach(key => {
            url = url.replace(`:${key}`, params[key]);
        });
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

        
        return app.mount(el);
    },
    progress: {
        color: '#1E40AF',
    },
});