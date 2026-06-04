<template>
    <Head title="Biblioteca — Préstamos" />

    <div class="min-h-screen bg-slate-900">
        <header class="bg-slate-800/80 backdrop-blur border-b border-slate-700">
            <div class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <h1 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                    Biblioteca — Catálogo de Préstamos
                </h1>
                <span class="px-2.5 py-0.5 text-xs font-medium bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-full">
                    Demo
                </span>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Error de conectividad (nivel página) -->
            <transition name="fade">
                <div v-if="errorConexion"
                     class="mb-6 flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium">{{ errorConexion }}</span>
                    <button @click="errorConexion = null" class="ml-auto text-red-400 hover:text-red-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </transition>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

                <!-- Catálogo -->
                <div class="lg:col-span-2 space-y-4">
                    <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wide">
                        Libros disponibles
                    </h2>

                    <div v-for="libro in catalogo" :key="libro.isbn"
                         class="flex items-center justify-between rounded-xl border border-slate-700/50 bg-gradient-to-br from-slate-800 to-slate-900 p-4">
                        <div>
                            <p class="font-medium text-slate-100">{{ libro.titulo }}</p>
                            <p class="text-sm text-slate-400">{{ libro.autor }} · ISBN {{ libro.isbn }}</p>
                        </div>
                        <button
                            @click="agregarLibro(libro)"
                            class="rounded-lg bg-blue-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-600 transition-colors">
                            Agregar
                        </button>
                    </div>
                </div>

                <!-- Carrito embebido -->
                <div class="lg:col-span-1">
                    <div class="sticky top-6 rounded-xl border border-slate-700/50 bg-slate-800 overflow-hidden">
                        <div class="border-b border-slate-700 px-4 py-3">
                            <h2 class="text-sm font-semibold text-slate-300">Mi carrito de préstamos</h2>
                        </div>

                        <CartControl
                            ref="cartRef"
                            user-ref="MAT-DEMO-001"
                            :config="cartConfig"
                            api-base-url="/demo/biblioteca/cart-proxy"
                            @checkout-success="onCheckoutSuccess"
                            @cart-error="onCartError" />
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { CartControl } from '@/Modules/Cart/Control/index.js';

const cartRef       = ref(null);
const errorConexion = ref(null);

const cartConfig = {
    categoriaSlug:          'prestamo',
    requiereSaldo:          false,
    expiraEnMinutos:        60,
    permitirMultiplesItems: true,
    etiquetaBotonCheckout:  'Confirmar préstamos',
    metadataInicial:        { origen: 'biblioteca' },
};

const catalogo = [
    { isbn: '978-0-13-468599-1', titulo: 'Clean Code',                    autor: 'Robert C. Martin'    },
    { isbn: '978-0-13-110362-7', titulo: 'The C Programming Language',    autor: 'Kernighan & Ritchie' },
    { isbn: '978-0-13-235088-4', titulo: 'The Pragmatic Programmer',      autor: 'Hunt & Thomas'       },
    { isbn: '978-0-596-51774-8', titulo: 'JavaScript: The Good Parts',    autor: 'Douglas Crockford'   },
];

function agregarLibro(libro) {
    cartRef.value?.addItem({
        categoria_slug:     'prestamo',
        referencia_externa: libro.isbn,
        nombre:             libro.titulo,
        precio_unitario:    0.00,
        cantidad:           1,
        duracion_dias:      7,
    });
}

function onCheckoutSuccess(event) {
    console.log('[Biblioteca Demo] Checkout exitoso:', event);
}

function onCartError(event) {
    if (event.code === 'CART_NO_DISPONIBLE') {
        errorConexion.value = event.mensaje;
    }
    console.error('[Biblioteca Demo] Error carrito:', event);
}
</script>
