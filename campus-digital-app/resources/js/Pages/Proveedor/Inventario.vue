<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    productos: Array,
    tienda: Object,
    tipos: Object
});

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

            <!-- Product Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
