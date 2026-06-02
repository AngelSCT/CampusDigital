import axios from 'axios';

/**
 * Thin Axios wrapper that calls the module's OWN proxy routes.
 * The proxy lives in the client module's backend — it adds the
 * Authorization header and calls the Cart API internally.
 * The JWT never reaches the browser.
 */
export function useCartApi(apiBaseUrl) {
    const base = apiBaseUrl.replace(/\/$/, '');

    function post(path, data = {}) {
        return axios.post(`${base}${path}`, data);
    }

    function get(path, params = {}) {
        return axios.get(`${base}${path}`, { params });
    }

    function del(path) {
        return axios.delete(`${base}${path}`);
    }

    function patch(path, data = {}) {
        return axios.patch(`${base}${path}`, data);
    }

    return {
        createCart:           (payload)               => post('/carritos', payload),
        getCart:              (uuid)                  => get(`/carritos/${uuid}`),
        addItem:              (uuid, item)            => post(`/carritos/${uuid}/items`, item),
        removeItem:           (uuid, itemId)          => del(`/carritos/${uuid}/items/${itemId}`),
        checkout:             (uuid, meta)            => post(`/carritos/${uuid}/checkout`, { metadata_checkout: meta }),
        cancel:               (uuid)                  => post(`/carritos/${uuid}/cancelar`),
        validarDestinatario:  (data) => axios.post(
            `${base}/validar-destinatario`,
            data,
            { headers: { 'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '') } }
        ),
        marcarRegalo:         (uuid, itemId, data)    => patch(`/carritos/${uuid}/items/${itemId}/regalo`, data),
    };
}
