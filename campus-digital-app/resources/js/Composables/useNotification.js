/**
 * Composable: useNotification
 * Sistema de notificaciones reactivas para el Módulo 8
 */

import { ref, readonly } from 'vue';

/**
 * @typedef {{ type: 'success'|'error'|'warning'|'info', message: string }} Notification
 */

/**
 * Composable para gestionar notificaciones temporales.
 * Cada llamada crea su propio estado de notificación aislado.
 */
export function useNotification() {
    const notification = ref(/** @type {Notification|null} */ (null));
    let timeoutId = null;

    /**
     * Muestra una notificación
     * @param {'success'|'error'|'warning'|'info'} type
     * @param {string} message
     * @param {number} duration - milisegundos antes de ocultar (0 = permanente)
     */
    function notify(type, message, duration = 4000) {
        if (timeoutId) clearTimeout(timeoutId);
        notification.value = { type, message };

        if (duration > 0) {
            timeoutId = setTimeout(() => {
                notification.value = null;
            }, duration);
        }
    }

    /** Muestra notificación de éxito */
    function notifySuccess(message, duration = 4000) {
        notify('success', message, duration);
    }

    /** Muestra notificación de error */
    function notifyError(message, duration = 5000) {
        notify('error', message, duration);
    }

    /** Muestra notificación de advertencia */
    function notifyWarning(message, duration = 5000) {
        notify('warning', message, duration);
    }

    /** Muestra notificación informativa */
    function notifyInfo(message, duration = 4000) {
        notify('info', message, duration);
    }

    /** Cierra la notificación activa */
    function clearNotification() {
        if (timeoutId) clearTimeout(timeoutId);
        notification.value = null;
    }

    return {
        notification: readonly(notification),
        notify,
        notifySuccess,
        notifyError,
        notifyWarning,
        notifyInfo,
        clearNotification,
    };
}
