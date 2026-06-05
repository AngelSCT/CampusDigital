<script setup>
import { computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Components/AuthLayout.vue'

const props = defineProps({
    productos:        Array,
    categorias:       Array,
    categoria_activa: Number,
})

function filtrar(categoriaId) {
    if (categoriaId) {
        router.get('/tienda', { categoria_id: categoriaId }, { preserveState: true, replace: true })
    } else {
        router.get('/tienda', {}, { preserveState: true, replace: true })
    }
}

function badgeMotivo(motivo) {
    const map = {
        INACTIVO:             'Inactivo',
        CATEGORY_UNAVAILABLE: 'Categoría no disponible',
        PRICE_UNAVAILABLE:    'Sin precio',
        OUT_OF_STOCK:         'Sin stock',
    }
    return map[motivo] ?? motivo
}

const hayProductos = computed(() => props.productos.length > 0)
</script>

<template>
    <AuthLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <!-- ── Encabezado ──────────────────────────────────────────────── -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-100">Productos y Servicios</h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Explora el catálogo y agrega lo que necesitas al carrito
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500 bg-slate-800/60 border border-slate-700/50
                                 px-3 py-1.5 rounded-full">
                        {{ productos.length }}
                        {{ productos.length === 1 ? 'artículo' : 'artículos' }}
                    </span>

                    <Link id="btn-mi-carrito-header"
                          :href="route('catalogo.mi-carrito')"
                          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                                 bg-blue-600/20 text-blue-400 ring-1 ring-blue-500/40
                                 hover:bg-blue-600/30 hover:ring-blue-500/60
                                 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7A1 1 0 007 17h11M7 13L5.4 5M17 17a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        Mi carrito
                    </Link>
                </div>
            </div>

            <!-- ── Filtro de categorías ────────────────────────────────────── -->
            <div class="flex flex-wrap gap-2">
                <button
                    id="filtro-todas"
                    @click="filtrar(null)"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-200
                           border"
                    :class="!categoria_activa
                        ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/25'
                        : 'bg-slate-800/60 text-slate-400 border-slate-700/50 hover:border-blue-500/50 hover:text-slate-200'"
                >
                    Todas
                </button>
                <button
                    v-for="cat in categorias"
                    :key="cat.id_categoria"
                    :id="`filtro-${cat.id_categoria}`"
                    @click="filtrar(cat.id_categoria)"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-200 border"
                    :class="categoria_activa === cat.id_categoria
                        ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/25'
                        : 'bg-slate-800/60 text-slate-400 border-slate-700/50 hover:border-blue-500/50 hover:text-slate-200'"
                >
                    {{ cat.nombre }}
                </button>
            </div>

            <!-- ── Estado vacío ────────────────────────────────────────────── -->
            <div v-if="!hayProductos"
                 class="flex flex-col items-center justify-center py-24 gap-4 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-800/60 border border-slate-700/40
                            flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"/>
                    </svg>
                </div>
                <p class="text-lg font-semibold text-slate-300">No hay productos disponibles</p>
                <p class="text-sm text-slate-500">
                    {{ categoria_activa ? 'Prueba con otra categoría.' : 'El catálogo está vacío por ahora.' }}
                </p>
                <button v-if="categoria_activa" @click="filtrar(null)"
                        class="text-sm text-blue-400 hover:text-blue-300 font-medium transition-colors">
                    Ver todos los artículos →
                </button>
            </div>

            <!-- ── Grid de productos ───────────────────────────────────────── -->
            <div v-else class="grid gap-4"
                 style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))">

                <article
                    v-for="p in productos"
                    :key="p.id_catalogo"
                    class="group bg-gradient-to-br from-slate-800/70 to-slate-900/70
                           backdrop-blur-sm border rounded-2xl p-5
                           flex flex-col gap-3 transition-all duration-200"
                    :class="p.cart_disponible
                        ? 'border-blue-500/10 hover:border-blue-500/30 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-0.5'
                        : 'border-slate-700/30 opacity-60'"
                >
                    <!-- Badges -->
                    <div class="flex flex-wrap gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full"
                              :class="p.tipo === 'servicio'
                                  ? 'bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/25'
                                  : 'bg-blue-500/15 text-blue-400 ring-1 ring-blue-500/25'">
                            {{ p.tipo === 'servicio' ? 'Servicio' : 'Producto' }}
                        </span>
                        <span v-if="p.categoria_nombre"
                              class="text-xs font-medium px-2 py-0.5 rounded-full
                                     bg-slate-700/50 text-slate-400 ring-1 ring-slate-600/40">
                            {{ p.categoria_nombre }}
                        </span>
                    </div>

                    <!-- Nombre -->
                    <h2 class="font-bold text-slate-100 leading-snug">
                        {{ p.nombre }}
                    </h2>

                    <!-- Descripción truncada -->
                    <p class="text-sm text-slate-400 leading-relaxed flex-grow"
                       style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                        {{ p.descripcion }}
                    </p>

                    <!-- Precio -->
                    <div class="font-semibold">
                        <span v-if="p.precio_vigente" class="text-lg text-emerald-400">
                            ${{ p.precio_vigente }}
                        </span>
                        <span v-else class="text-sm text-slate-500 italic">Sin precio</span>
                    </div>

                    <!-- Badge indisponible -->
                    <div v-if="!p.cart_disponible"
                         class="flex items-center gap-1.5 text-xs font-medium
                                text-red-400 bg-red-500/10 ring-1 ring-red-500/25
                                rounded-lg px-3 py-1.5">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-10.75a.75.75 0 011.5 0v4.5a.75.75 0 01-1.5 0v-4.5zm.75 7a.75.75 0 100-1.5.75.75 0 000 1.5z"
                                  clip-rule="evenodd"/>
                        </svg>
                        {{ badgeMotivo(p.motivo_no_disponible) }}
                    </div>

                    <!-- Botón -->
                    <Link
                        :id="`btn-ver-${p.id_catalogo}`"
                        :href="`/catalogo/${p.id_catalogo}`"
                        class="mt-auto flex items-center justify-center gap-2 px-4 py-2.5
                            rounded-xl text-sm font-semibold transition-all duration-200
                            text-white"
                        :class="p.cart_disponible
                            ? 'bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30'
                            : 'bg-slate-700/50 text-slate-500 cursor-not-allowed pointer-events-none'"
                        :aria-disabled="!p.cart_disponible"
                    >
                        Ver y agregar al carrito
                    </Link>
                </article>

            </div>
        </div>
    </AuthLayout>
</template>
