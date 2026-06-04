import { ref, computed } from 'vue';
import { useCartApi } from './useCartApi.js';

export function useCart(userRef, config, apiBaseUrl, emit) {
    const api = useCartApi(apiBaseUrl);

    const storageKey = `campus_cart_uuid_${userRef}`;

    // CarritoService::toArray returns 'carrito_uuid', not 'uuid'.
    // Normalize to 'uuid' so all computed properties work consistently.
    function normalizeCart(data) {
        if (!data || typeof data !== 'object') return data;
        if ('carrito_uuid' in data && !('uuid' in data)) {
            return { ...data, uuid: data.carrito_uuid };
        }
        return data;
    }

    const cart         = ref(null);
    const loading      = ref(false);
    const checkingOut  = ref(false);
    const error        = ref(null);
    const confirmed    = ref(false);
    const pendingCharge = ref(false);

    const items = computed(() => cart.value?.items ?? []);
    const total = computed(() => cart.value?.total ?? 0);
    const uuid  = computed(() => cart.value?.uuid ?? null);

    async function init() {
        loading.value = true;
        error.value   = null;

        // Try to recover an existing open cart from localStorage
        const savedUuid = localStorage.getItem(storageKey);
        if (savedUuid) {
            try {
                const res = await api.getCart(savedUuid);
                const data = normalizeCart(res.data);
                if (data?.uuid && data?.estado === 'abierto') {
                    cart.value = data;
                    emit('cart-ready', { carrito_uuid: cart.value.uuid });
                    loading.value = false;
                    return;
                }
            } catch {
                // Cart no longer valid (expired, cancelled, etc.) — fall through
                localStorage.removeItem(storageKey);
            }
        }

        // Create new cart
        try {
            const res = await api.createCart({
                usuario_ref:        userRef,
                requiere_saldo:     config.requiereSaldo    ?? false,
                expira_en_minutos:  config.expiraEnMinutos  ?? 30,
                metadata:           config.metadataInicial  ?? {},
            });
            cart.value = normalizeCart(res.data);
            if (cart.value?.uuid) {
                localStorage.setItem(storageKey, cart.value.uuid);
            }
            emit('cart-ready', { carrito_uuid: cart.value.uuid });
        } catch (e) {
            error.value = buildError(e);
            emit('cart-error', error.value);
        } finally {
            loading.value = false;
        }
    }

    async function addItem(item) {
        // If cart hasn't initialized yet (e.g. fast click before mount completes), wait for it
        if (!uuid.value && !loading.value) await init();
        if (!uuid.value) return;
        // Save UUID before any mutation: ItemController returns {accion, ...} not the full cart
        const cartUuid = uuid.value;
        error.value = null;
        try {
            await api.addItem(cartUuid, item);
            const cartRes  = await api.getCart(cartUuid);
            cart.value = normalizeCart(cartRes.data);
            emit('cart-updated', { items: items.value, total: total.value });
            emit('item-added', { item_id: item.item_id ?? null, referencia_externa: item.referencia_externa });
        } catch (e) {
            error.value = buildError(e);
            emit('cart-error', error.value);
        }
    }

    async function removeItem(itemId) {
        if (!uuid.value) return;
        // Save UUID before any mutation: ItemController::destroy returns partial response
        const cartUuid = uuid.value;
        error.value = null;
        try {
            await api.removeItem(cartUuid, itemId);
            const cartRes  = await api.getCart(cartUuid);
            cart.value = normalizeCart(cartRes.data);
            emit('cart-updated', { items: items.value, total: total.value });
            emit('item-removed', { item_id: itemId });
        } catch (e) {
            error.value = buildError(e);
            emit('cart-error', error.value);
        }
    }

    async function doCheckout(metadataCheckout = {}) {
        if (!uuid.value || checkingOut.value) return;
        checkingOut.value = true;
        error.value       = null;
        try {
            const res    = await api.checkout(uuid.value, metadataCheckout);
            const result = res.data;

            confirmed.value    = true;
            pendingCharge.value = result.estado === 'confirmado_pendiente_conciliacion';

            // Cart confirmed — clear persistence so next visit starts fresh
            localStorage.removeItem(storageKey);

            emit('checkout-success', {
                carrito_uuid: result.uuid,
                total:        result.total,
                confirmed_at: result.confirmed_at,
                estado:       result.estado,
            });
        } catch (e) {
            const err = buildError(e);
            error.value = err;
            emit('checkout-error', err);
        } finally {
            checkingOut.value = false;
        }
    }

    function buildError(e) {
        const status  = e.response?.status;
        const body    = e.response?.data ?? {};
        const code    = body.error ?? body.code ?? 'ERROR';
        const mensaje = body.message ?? body.error ?? 'Error inesperado.';
        return { code, mensaje, http_status: status };
    }

    return {
        cart, items, total, uuid,
        loading, checkingOut, error, confirmed, pendingCharge,
        init, addItem, removeItem, doCheckout,
    };
}
