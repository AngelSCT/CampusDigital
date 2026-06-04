<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    productos: Array,
    tienda: Object,
    tipos: Object,
    catalogo_43: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const activeTab = ref('propios');

const filteredCatalogo = computed(() => {
    if (!props.catalogo_43) return [];
    return props.catalogo_43.filter(item => 
        item.nombre.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (item.descripcion && item.descripcion.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

const getItemPrice = (item) => {
    if (item.precio && typeof item.precio === 'object') {
        return item.precio.valor;
    }
    return item.precio || 0;
};

const showModal = ref(false);
const editingProducto = ref(null);

const form = useForm({
    nombre: '',
    descripcion: '',
    precio: 0,
    stock: 0,
    activo: true,
    imagen_url: ''
});

const openCreateModal = () => {
    editingProducto.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (producto) => {
    editingProducto.value = producto;
    form.nombre = producto.nombre;
    form.descripcion = producto.descripcion;
    form.precio = producto.precio;
    form.stock = producto.stock;
    form.activo = producto.activo;
    form.imagen_url = producto.imagen_url;
    showModal.value = true;
};

const submit = () => {
    if (editingProducto.value) {
        form.put(`/proveedor/productos/${editingProducto.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/proveedor/productos', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProducto = (id) => {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        form.delete(`/proveedor/productos/${id}`);
    }
};

const closeModal = () => {
    showModal.value = false;
    editingProducto.value = null;
    form.reset();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
};
</script>

<template>
    <Head title="Gestión de Inventario" />
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white">Inventario: {{ tienda.nombre }}</h1>
                    <p class="text-slate-400">Gestiona productos para tu {{ tipos[tienda.tipo] || 'negocio' }}</p>
                </div>
                
                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-600/20"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Producto
                </button>
            </div>

            <!-- Tabs Navigation -->
            <div v-if="catalogo_43 && catalogo_43.length > 0" class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-800 gap-4">
                <div class="flex gap-6 pb-px">
                    <button 
                        @click="activeTab = 'propios'"
                        :class="['pb-4 text-sm font-bold border-b-2 transition-all px-1 relative', activeTab === 'propios' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-400 hover:text-white']"
                    >
                        Mis Productos
                        <span class="ml-2 bg-slate-850 text-slate-300 text-xs px-2 py-0.5 rounded-full font-semibold">{{ productos.length }}</span>
                    </button>
                    <button 
                        @click="activeTab = 'institucional'"
                        :class="['pb-4 text-sm font-bold border-b-2 transition-all px-1 flex items-center gap-2 relative', activeTab === 'institucional' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-white']"
                    >
                        Catálogo Institucional Oficial
                        <span class="bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs px-2 py-0.5 rounded-full font-semibold">{{ catalogo_43.length }}</span>
                    </button>
                </div>

                <!-- Search box only for Institutional Catalog tab -->
                <div v-if="activeTab === 'institucional'" class="pb-3 sm:pb-0 relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Buscar en catálogo oficial..." 
                        class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-9 pr-4 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                    />
                </div>
            </div>

            <!-- Product Grid (Mis Productos) -->
            <div v-if="activeTab === 'propios'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Empty state own products -->
                <div v-if="productos.length === 0" class="col-span-full py-12 flex flex-col items-center justify-center text-slate-500 border-2 border-dashed border-slate-800 rounded-3xl bg-slate-950/20">
                    <svg class="w-16 h-16 text-slate-700 mb-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="font-semibold text-lg text-slate-400">Tu tienda no tiene productos</p>
                    <p class="text-sm text-slate-500 mt-1">Registra productos locales usando el botón de arriba</p>
                </div>

                <div 
                    v-for="producto in productos" 
                    :key="producto.id"
                    class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-600 transition-all group"
                >
                    <div class="h-40 bg-slate-800 relative overflow-hidden">
                        <img v-if="producto.imagen_url" :src="producto.imagen_url" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="absolute top-2 right-2">
                             <span :class="['px-2 py-1 rounded text-[10px] font-bold uppercase', producto.activo ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30']">
                                {{ producto.activo ? 'Activo' : 'Inactivo' }}
                             </span>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-white truncate pr-2">{{ producto.nombre }}</h3>
                            <span class="text-blue-400 font-bold">{{ formatCurrency(producto.precio) }}</span>
                        </div>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-4 h-8">{{ producto.descripcion || 'Sin descripción' }}</p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase font-bold text-slate-500">Stock</span>
                                <span :class="['font-mono font-bold', producto.stock <= 5 ? 'text-red-400' : 'text-slate-300']">{{ producto.stock }}</span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button 
                                @click="openEditModal(producto)"
                                class="flex-1 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-bold transition-colors"
                            >
                                Editar
                            </button>
                            <button 
                                @click="deleteProducto(producto.id)"
                                class="px-3 py-1.5 bg-red-900/20 hover:bg-red-900/40 text-red-400 rounded-lg text-xs font-bold transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catálogo Institucional (Read-only) Grid -->
            <div v-if="activeTab === 'institucional'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Empty state search -->
                <div v-if="filteredCatalogo.length === 0" class="col-span-full py-12 flex flex-col items-center justify-center text-slate-500 border-2 border-dashed border-slate-800 rounded-3xl bg-slate-950/20">
                    <svg class="w-16 h-16 text-slate-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-semibold text-lg text-slate-400">No se encontraron productos institucionales</p>
                    <p class="text-sm text-slate-500 mt-1">Intenta con otra palabra clave en tu búsqueda</p>
                </div>

                <div 
                    v-for="item in filteredCatalogo" 
                    :key="item.id_cv"
                    class="bg-slate-900/60 border border-slate-850 rounded-2xl overflow-hidden hover:border-indigo-500/40 hover:shadow-lg hover:shadow-indigo-500/5 transition-all group flex flex-col"
                >
                    <div class="h-36 bg-slate-850/60 relative overflow-hidden flex items-center justify-center border-b border-slate-900/40">
                        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-950/20 via-slate-950 to-slate-900 opacity-80"></div>
                        <div class="relative z-10 flex flex-col items-center justify-center text-indigo-400/80 group-hover:text-indigo-400 transition-colors">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-[9px] uppercase font-extrabold tracking-widest text-indigo-500/50 mt-2">Módulo 4.3</span>
                        </div>
                        
                        <div class="absolute top-3 left-3">
                             <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase bg-gradient-to-r from-indigo-500 to-violet-600 text-white shadow-md shadow-indigo-500/10">
                                Oficial
                             </span>
                        </div>
                        <div class="absolute top-3 right-3">
                             <span :class="['px-2 py-0.5 rounded text-[8px] font-bold uppercase border', item.disponible_ahora ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20']">
                                {{ item.disponible_ahora ? 'Disponible Ahora' : 'Fuera de Horario' }}
                             </span>
                        </div>
                    </div>
                    
                    <div class="p-4 flex flex-col flex-1 justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-1.5 gap-2">
                                <h3 class="font-bold text-white truncate text-sm group-hover:text-indigo-300 transition-colors pr-1">{{ item.nombre }}</h3>
                                <span class="text-indigo-400 font-extrabold text-sm shrink-0">{{ formatCurrency(getItemPrice(item)) }}</span>
                            </div>
                            <p class="text-xs text-slate-400 line-clamp-2 mb-3 min-h-[2rem]">{{ item.descripcion || 'Sin descripción oficial' }}</p>
                            
                            <div v-if="item.categoria" class="mb-3">
                                <span class="inline-flex items-center text-[10px] text-slate-400 bg-slate-900 border border-slate-800 rounded-md px-1.5 py-0.5">
                                    {{ item.categoria.nombre }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2 mt-auto">
                            <!-- Rule warning banner -->
                            <div v-if="item.regla" class="text-[10px] bg-indigo-950/15 border border-indigo-900/30 rounded-xl p-2.5 flex items-start gap-2 text-indigo-300/80">
                                <svg class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><strong>Regla ({{ item.regla.tipo_regla }}):</strong> {{ item.regla.descripcion }}</span>
                            </div>

                            <div v-if="item.disponibilidad" class="text-[10px] bg-slate-900 border border-slate-850 rounded-xl p-2.5 flex items-start gap-2 text-slate-400">
                                <svg class="w-4 h-4 text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Disponibilidad: <strong class="capitalize text-slate-300">{{ item.disponibilidad.dia_semana }}</strong> de {{ item.disponibilidad.hora_inicio.substring(0,5) }} a {{ item.disponibilidad.hora_fin.substring(0,5) }}</span>
                            </div>

                            <!-- Read-only footer info -->
                            <div class="pt-2 border-t border-slate-800/60 flex items-center justify-between text-[9px] text-slate-500">
                                <span>Stock actual: <strong class="font-mono text-slate-400">{{ item.stock_actual !== null ? item.stock_actual : 'N/A' }}</strong></span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    Cátalogo Base
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                <div class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-white">{{ editingProducto ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
                        <button @click="closeModal" class="text-slate-500 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form @submit.prevent="submit" class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nombre</label>
                                <input v-model="form.nombre" type="text" required class="w-full bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-blue-500" />
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Descripción</label>
                                <textarea v-model="form.descripcion" class="w-full bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-blue-500" rows="2"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Precio</label>
                                <input v-model="form.precio" type="number" step="0.01" required class="w-full bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-blue-500" />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Stock Inicial</label>
                                <input v-model="form.stock" type="number" required class="w-full bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-blue-500" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">URL Imagen</label>
                                <input v-model="form.imagen_url" type="text" class="w-full bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-blue-500" placeholder="https://..." />
                            </div>

                            <div class="flex items-center gap-2">
                                <input v-model="form.activo" type="checkbox" id="activo" class="rounded bg-slate-800 border-slate-700 text-blue-600" />
                                <label for="activo" class="text-sm text-slate-300">Producto Activo</label>
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="closeModal" class="flex-1 px-4 py-2 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 transition-colors">Cancelar</button>
                            <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-500 transition-colors disabled:opacity-50">
                                {{ editingProducto ? 'Guardar Cambios' : 'Crear Producto' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
