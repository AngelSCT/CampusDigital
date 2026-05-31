<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Reportes de Monedero
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <!-- TABS -->
          <div class="border-b">
            <div class="flex">
              <button
                @click="tabActivo = 'estado-cuenta'"
                :class="[
                  'px-6 py-4 font-medium transition',
                  tabActivo === 'estado-cuenta'
                    ? 'border-b-2 border-blue-500 text-blue-600'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                Estado de Cuenta
              </button>
              <button
                @click="tabActivo = 'movimientos'"
                :class="[
                  'px-6 py-4 font-medium transition',
                  tabActivo === 'movimientos'
                    ? 'border-b-2 border-blue-500 text-blue-600'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                Movimientos por Período
              </button>
              <button
                @click="tabActivo = 'uso-categoria'"
                :class="[
                  'px-6 py-4 font-medium transition',
                  tabActivo === 'uso-categoria'
                    ? 'border-b-2 border-blue-500 text-blue-600'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                Uso por Categoría
              </button>
            </div>
          </div>

          <!-- TAB 1: ESTADO DE CUENTA -->
          <div v-if="tabActivo === 'estado-cuenta'" class="p-6">
            <div class="bg-gray-50 p-4 rounded mb-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                  <select 
                    v-model="filtros.usuario_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                  >
                    <option value="">Seleccionar usuario</option>
                    <option v-for="user in usuarios" :key="user.id" :value="user.id">
                      {{ user.nombre }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                  <input 
                    type="date" 
                    v-model="filtros.desde"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                  <input 
                    type="date" 
                    v-model="filtros.hasta"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                  />
                </div>
              </div>
              <div class="flex gap-2 mt-4">
                <button 
                  @click="cargarEstadoCuenta"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                >
                  Cargar Reporte
                </button>
                <button 
                  @click="exportarPDF('estado-cuenta')"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                  v-if="reporteActual"
                >
                  📄 Descargar PDF
                </button>
                <button 
                  @click="exportarCSV('estado-cuenta')"
                  class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                  v-if="reporteActual"
                >
                  📊 Descargar CSV
                </button>
              </div>
            </div>

            <div v-if="reporteActual" class="overflow-x-auto">
              <div class="mb-4 p-4 bg-blue-50 rounded">
                <p class="text-sm"><strong>Usuario:</strong> {{ reporteActual.usuario?.nombre }}</p>
                <p class="text-sm"><strong>Período:</strong> {{ reporteActual.periodo.desde }} a {{ reporteActual.periodo.hasta }}</p>
                <p class="text-sm"><strong>Saldo Actual:</strong> ${{ formatCurrency(reporteActual.saldo_actual) }}</p>
                <p class="text-sm"><strong>Total Cargos:</strong> ${{ formatCurrency(reporteActual.total_cargos) }}</p>
                <p class="text-sm"><strong>Total Abonos:</strong> ${{ formatCurrency(reporteActual.total_abonos) }}</p>
              </div>

              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium">Fecha</th>
                    <th class="px-4 py-2 text-left font-medium">Tipo</th>
                    <th class="px-4 py-2 text-left font-medium">Módulo</th>
                    <th class="px-4 py-2 text-left font-medium">Concepto</th>
                    <th class="px-4 py-2 text-right font-medium">Monto</th>
                    <th class="px-4 py-2 text-right font-medium">Saldo Nuevo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="mov in reporteActual.movimientos" :key="mov.id" class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">{{ formatDate(mov.created_at) }}</td>
                    <td class="px-4 py-3">
                      <span v-if="mov.tipo === 'abono'" class="text-green-600 font-semibold">✓ Abono</span>
                      <span v-else class="text-red-600 font-semibold">✗ Cargo</span>
                    </td>
                    <td class="px-4 py-3">{{ mov.modulo }}</td>
                    <td class="px-4 py-3">{{ mov.concepto }}</td>
                    <td class="px-4 py-3 text-right">{{ mov.tipo === 'abono' ? '+' : '-' }}${{ formatCurrency(mov.monto) }}</td>
                    <td class="px-4 py-3 text-right">${{ formatCurrency(mov.saldo_nuevo) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 2: MOVIMIENTOS POR PERÍODO -->
          <div v-if="tabActivo === 'movimientos'" class="p-6">
            <div class="bg-gray-50 p-4 rounded mb-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                  <input 
                    type="date" 
                    v-model="filtrosMovimientos.desde"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                  <input 
                    type="date" 
                    v-model="filtrosMovimientos.hasta"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Módulo (opcional)</label>
                  <select 
                    v-model="filtrosMovimientos.modulo"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                  >
                    <option value="">Todos</option>
                    <option value="cafeteria">Cafetería</option>
                    <option value="copias">Copias/Impresiones</option>
                    <option value="souvenirs">Souvenirs</option>
                    <option value="biblioteca">Biblioteca</option>
                  </select>
                </div>
              </div>
              <div class="flex gap-2 mt-4">
                <button 
                  @click="cargarMovimientos"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                >
                  Cargar Reporte
                </button>
                <button 
                  @click="exportarPDF('movimientos')"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                  v-if="reporteMovimientos"
                >
                  📄 Descargar PDF
                </button>
                <button 
                  @click="exportarCSV('movimientos')"
                  class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                  v-if="reporteMovimientos"
                >
                  📊 Descargar CSV
                </button>
              </div>
            </div>

            <div v-if="reporteMovimientos" class="overflow-x-auto">
              <div class="mb-4 p-4 bg-blue-50 rounded">
                <p class="text-sm"><strong>Total Movimientos:</strong> {{ reporteMovimientos.resumen.total_movimientos }}</p>
                <p class="text-sm"><strong>Total Cargos:</strong> ${{ formatCurrency(reporteMovimientos.resumen.total_cargos) }}</p>
                <p class="text-sm"><strong>Total Abonos:</strong> ${{ formatCurrency(reporteMovimientos.resumen.total_abonos) }}</p>
                <p class="text-sm"><strong>Saldo Neto:</strong> ${{ formatCurrency(reporteMovimientos.resumen.saldo_neto) }}</p>
              </div>

              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium">Fecha</th>
                    <th class="px-4 py-2 text-left font-medium">Usuario</th>
                    <th class="px-4 py-2 text-left font-medium">Tipo</th>
                    <th class="px-4 py-2 text-left font-medium">Módulo</th>
                    <th class="px-4 py-2 text-left font-medium">Concepto</th>
                    <th class="px-4 py-2 text-right font-medium">Monto</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="mov in reporteMovimientos.movimientos" :key="mov.id" class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">{{ formatDate(mov.created_at) }}</td>
                    <td class="px-4 py-3">{{ mov.usuario.nombre }}</td>
                    <td class="px-4 py-3">
                      <span v-if="mov.tipo === 'abono'" class="text-green-600 font-semibold">✓ Abono</span>
                      <span v-else class="text-red-600 font-semibold">✗ Cargo</span>
                    </td>
                    <td class="px-4 py-3">{{ mov.modulo }}</td>
                    <td class="px-4 py-3">{{ mov.concepto }}</td>
                    <td class="px-4 py-3 text-right">{{ mov.tipo === 'abono' ? '+' : '-' }}${{ formatCurrency(mov.monto) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 3: USO POR CATEGORÍA -->
          <div v-if="tabActivo === 'uso-categoria'" class="p-6">
            <div class="bg-gray-50 p-4 rounded mb-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                  <input 
                    type="date" 
                    v-model="filtrosCategoria.desde"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                  <input 
                    type="date" 
                    v-model="filtrosCategoria.hasta"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                  />
                </div>
              </div>
              <div class="flex gap-2 mt-4">
                <button 
                  @click="cargarCategoria"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                >
                  Cargar Reporte
                </button>
                <button 
                  @click="exportarPDF('uso-categoria')"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                  v-if="reporteCategoria"
                >
                  📄 Descargar PDF
                </button>
                <button 
                  @click="exportarCSV('uso-categoria')"
                  class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                  v-if="reporteCategoria"
                >
                  📊 Descargar CSV
                </button>
              </div>
            </div>

            <div v-if="reporteCategoria" class="overflow-x-auto">
              <div class="mb-4 p-4 bg-blue-50 rounded">
                <p class="text-sm"><strong>Total General:</strong> ${{ formatCurrency(reporteCategoria.total_general) }}</p>
              </div>

              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium">Categoría</th>
                    <th class="px-4 py-2 text-right font-medium">Consumo Cargo</th>
                    <th class="px-4 py-2 text-right font-medium">Consumo Abono</th>
                    <th class="px-4 py-2 text-right font-medium">Transacciones</th>
                    <th class="px-4 py-2 text-right font-medium">Usuarios Únicos</th>
                    <th class="px-4 py-2 text-right font-medium">%</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="cat in reporteCategoria.categorias" :key="cat.modulo" class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ cat.modulo }}</td>
                    <td class="px-4 py-3 text-right text-red-600 font-semibold">${{ formatCurrency(cat.total_cargo) }}</td>
                    <td class="px-4 py-3 text-right text-green-600 font-semibold">${{ formatCurrency(cat.total_abono) }}</td>
                    <td class="px-4 py-3 text-right">{{ cat.cantidad }}</td>
                    <td class="px-4 py-3 text-right">{{ cat.usuarios_unicos }}</td>
                    <td class="px-4 py-3 text-right">{{ cat.porcentaje }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const tabActivo = ref('estado-cuenta');
const usuarios = ref(window.usuarios || []);

const filtros = ref({
  usuario_id: '',
  desde: getFecha30DiasAtras(),
  hasta: new Date().toISOString().split('T')[0],
});

const filtrosMovimientos = ref({
  desde: getFecha30DiasAtras(),
  hasta: new Date().toISOString().split('T')[0],
  modulo: '',
});

const filtrosCategoria = ref({
  desde: getFecha30DiasAtras(),
  hasta: new Date().toISOString().split('T')[0],
});

const reporteActual = ref(null);
const reporteMovimientos = ref(null);
const reporteCategoria = ref(null);

function getFecha30DiasAtras() {
  const d = new Date();
  d.setDate(d.getDate() - 30);
  return d.toISOString().split('T')[0];
}

function formatCurrency(value) {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
  }).format(value || 0);
}

function formatDate(date) {
  return new Date(date).toLocaleString('es-AR');
}

async function cargarEstadoCuenta() {
  if (!filtros.value.usuario_id) {
    alert('Selecciona un usuario');
    return;
  }
  try {
    const response = await axios.get('/api/admin/monedero/reportes/estado-cuenta', {
      params: filtros.value,
    });
    reporteActual.value = response.data;
  } catch (error) {
    console.error('Error:', error);
  }
}

async function cargarMovimientos() {
  try {
    const response = await axios.get('/api/admin/monedero/reportes/movimientos', {
      params: filtrosMovimientos.value,
    });
    reporteMovimientos.value = response.data;
  } catch (error) {
    console.error('Error:', error);
  }
}

async function cargarCategoria() {
  try {
    const response = await axios.get('/api/admin/monedero/reportes/uso-categoria', {
      params: filtrosCategoria.value,
    });
    reporteCategoria.value = response.data;
  } catch (error) {
    console.error('Error:', error);
  }
}

function exportarPDF(tipo) {
  // TODO: Implementar descarga de PDF
  alert(`Descargando PDF de ${tipo}`);
}

function exportarCSV(tipo) {
  // TODO: Implementar descarga de CSV
  alert(`Descargando CSV de ${tipo}`);
}
</script>
