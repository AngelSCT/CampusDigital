<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    tiendas: Array,
    proveedores: Array,
    repartidores: Array,
    tipos: Object,
    tabActive: { type: String, default: 'tiendas' }
});

const activeTab = ref(props.tabActive);

const form = useForm({
    id: null,
    nombre: '',
    descripcion: '',
    tipo: 'cafeteria',
    ubicacion: '',
    activo: true,
    imagen: null
});

const isEditing = ref(false);
const showModal = ref(false);

const openCreate = () => {
    form.reset();
    isEditing.value = false;
    showModal.value = true;
};

const openEdit = (tienda) => {
    form.id = tienda.id;
    form.nombre = tienda.nombre;
    form.descripcion = tienda.descripcion;
    form.tipo = tienda.tipo;
    form.ubicacion = tienda.ubicacion;
    form.activo = tienda.activo;
    isEditing.value = true;
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.tiendas.update', form.id), {
            onSuccess: () => showModal.value = false
        });
    } else {
        form.post(route('admin.tiendas.store'), {
            onSuccess: () => showModal.value = false
        });
    }
};

const deleteTienda = (id) => {
    if (confirm('¿Estás seguro de eliminar esta tienda?')) {
        form.delete(route('admin.tiendas.destroy', id));
    }
};

const assignForm = useForm({
    tienda_id: ''
});

const assignTienda = (usuarioId) => {
    assignForm.post(route('admin.proveedores.asignar', usuarioId));
};

const toggleRepartidor = (usuarioId) => {
    form.post(route('admin.repartidores.toggle', usuarioId));
};
</script>

<template>
    <AuthLayout title="Gestión de Tiendas">
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-slate-100 italic">Administración Multi-Tienda</h1>
                    <p class="mt-2 text-sm text-slate-400">Gestiona las cafeterías, papelerías y proveedores oficiales del campus.</p>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex sm:flex-none gap-3">
                    <Link :href="route('admin.tiendas.index')" class="inline-flex items-center justify-center rounded-md border border-slate-600 bg-slate-800 px-4 py-2 text-sm font-medium text-slate-200 shadow-sm hover:bg-slate-700">
                        Volver al Dashboard
                    </Link>
                    <button @click="openCreate" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                        Agregar Tienda / Punto
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mt-6 border-b border-slate-700">
                <nav class="-mb-px flex space-x-8">
                    <button @click="activeTab = 'tiendas'" :class="[activeTab === 'tiendas' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-300 hover:border-slate-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                        Tiendas y Puntos
                    </button>
                    <button @click="activeTab = 'proveedores'" :class="[activeTab === 'proveedores' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-300 hover:border-slate-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                        Asignación Proveedores
                    </button>
                    <button @click="activeTab = 'repartidores'" :class="[activeTab === 'repartidores' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-300 hover:border-slate-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                        Repartidores
                    </button>
                </nav>
            </div>

            <!-- Content -->
            <div class="mt-8">
                <!-- Tiendas Grid -->
                <div v-if="activeTab === 'tiendas'" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="tienda in tiendas" :key="tienda.id" class="bg-slate-800 rounded-lg shadow-xl overflow-hidden border border-slate-700">
                        <div class="h-32 bg-slate-700 flex items-center justify-center relative">
                            <img v-if="tienda.imagen_url" :src="'/storage/' + tienda.imagen_url" class="absolute inset-0 w-full h-full object-cover opacity-50">
                            <span class="text-3xl font-bold text-white z-10">{{ tienda.nombre.charAt(0) }}</span>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-900 text-blue-200">
                                    {{ tipos[tienda.tipo] }}
                                </span>
                                <span :class="tienda.activo ? 'text-green-400' : 'text-red-400'" class="text-xs uppercase font-bold tracking-tighter">
                                    {{ tienda.activo ? 'Activo' : 'Cerrado' }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-100">{{ tienda.nombre }}</h3>
                            <p class="mt-1 text-sm text-slate-400 line-clamp-2">{{ tienda.descripcion }}</p>
                            <div class="mt-4 flex items-center text-xs text-slate-500 space-x-4">
                                <span>📦 {{ tienda.productos_count }} Productos</span>
                                <span>🛒 {{ tienda.pedidos_count }} Pedidos</span>
                            </div>
                            <div class="mt-6 flex justify-end space-x-3">
                                <button @click="openEdit(tienda)" class="text-slate-300 hover:text-blue-400 font-medium text-sm">Editar</button>
                                <button @click="deleteTienda(tienda.id)" class="text-slate-300 hover:text-red-400 font-medium text-sm">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proveedores List -->
                <div v-if="activeTab === 'proveedores'" class="space-y-8">
                    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-700 bg-slate-900/30">
                            <h3 class="text-lg font-bold text-white italic">Gestión de Proveedores de Área</h3>
                        </div>
                        <table class="min-w-full divide-y divide-slate-700">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-widest">Proveedor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-widest">Tienda Asignada</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-widest">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="prov in proveedores" :key="prov.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white text-xs">
                                                {{ prov.nombre.charAt(0) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-slate-100">{{ prov.nombre }} {{ prov.apellido }}</div>
                                                <div class="text-xs text-slate-500">{{ prov.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select v-model="prov.tienda_id" @change="assignTienda(prov.id)" class="bg-slate-900 border-slate-600 text-slate-200 text-sm rounded-lg block w-full p-1.5 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Sin Asignar</option>
                                            <option v-for="t in tiendas" :key="t.id" :value="t.id">{{ t.nombre }}</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <Link :href="route('admin.usuarios.show', prov.id)" class="text-blue-400 hover:text-blue-300">Perfil</Link>
                                    </td>
                                </tr>
                                <tr v-if="proveedores.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-slate-500 italic">No hay proveedores registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Repartidores Management -->
                    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-700 bg-slate-900/30">
                            <h3 class="text-lg font-bold text-white italic">Gestión de Repartidores Oficiales</h3>
                        </div>
                        <table class="min-w-full divide-y divide-slate-700">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-widest">Repartidor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-widest">Tienda / Base</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-widest">Estado de Rol</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="rep in repartidores" :key="rep.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-cyan-600 flex items-center justify-center font-bold text-white text-xs">
                                                {{ rep.nombre.charAt(0) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-slate-100">{{ rep.nombre }} {{ rep.apellido }}</div>
                                                <div class="text-xs text-slate-500">{{ rep.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                        {{ rep.tienda?.nombre || 'General' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <button @click="toggleRepartidor(rep.id)" class="px-3 py-1 rounded bg-red-900/30 text-red-400 border border-red-500/30 hover:bg-red-900/50">
                                            Quitar Rol
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="repartidores.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-slate-500 italic">No hay repartidores activos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal (Create/Edit) -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" @click="showModal = false"></div>
                <div class="inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-700">
                    <form @submit.prevent="submit">
                        <div class="px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-white mb-4 italic underline decoration-blue-500">{{ isEditing ? 'Editar Tienda' : 'Nueva Tienda' }}</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Nombre</label>
                                    <input v-model="form.nombre" type="text" class="mt-1 block w-full bg-slate-900 border-slate-600 text-slate-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Tipo de Establecimiento</label>
                                    <select v-model="form.tipo" class="mt-1 block w-full bg-slate-900 border-slate-600 text-slate-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option v-for="(label, key) in tipos" :key="key" :value="key">{{ label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Descripción</label>
                                    <textarea v-model="form.descripcion" class="mt-1 block w-full bg-slate-900 border-slate-600 text-slate-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" rows="3"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Ubicación</label>
                                    <input v-model="form.ubicacion" type="text" class="mt-1 block w-full bg-slate-900 border-slate-600 text-slate-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-slate-900/50 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                {{ isEditing ? 'Actualizar' : 'Crear' }}
                            </button>
                            <button @click="showModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-700 text-base font-medium text-slate-200 hover:bg-slate-600 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
