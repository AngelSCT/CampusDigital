<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
        Reportes de Monedero
      </h2>
    </template>

    <div class="space-y-6">
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700">
          <!-- TABS -->
          <div class="border-b border-slate-700">
            <div class="flex">
              <button
                @click="tabActivo = 'estado-cuenta'"
                :class="[
                  'px-6 py-4 font-medium transition',
                  tabActivo === 'estado-cuenta'
                    ? 'border-b-2 border-cyan-400 text-cyan-400'
                    : 'text-slate-400 hover:text-white'
                ]"
              >
                Estado de Cuenta
              </button>
              <button
                @click="tabActivo = 'movimientos'"
                :class="[
                  'px-6 py-4 font-medium transition',
                  tabActivo === 'movimientos'
                    ? 'border-b-2 border-cyan-400 text-cyan-400'
                    : 'text-slate-400 hover:text-white'
                ]"
              >
                Movimientos por Período
              </button>
              <button
                @click="tabActivo = 'uso-categoria'"
                :class="[
                  'px-6 py-4 font-medium transition',
                  tabActivo === 'uso-categoria'
                    ? 'border-b-2 border-cyan-400 text-cyan-400'
                    : 'text-slate-400 hover:text-white'
                ]"
              >
                Uso por Categoría
              </button>
            </div>
          </div>

          <!-- TAB 1: ESTADO DE CUENTA -->
          <div v-if="tabActivo === 'estado-cuenta'" class="p-6">
            <div class="bg-slate-700/50 p-4 rounded-lg mb-6 border border-slate-600">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Usuario</label>
                  <select 
                    v-model="filtros.usuario_id"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  >
                    <option value="">Seleccionar usuario</option>
                    <option v-for="user in usuarios" :key="user.id" :value="user.id">
                      {{ user.nombre }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Desde</label>
                  <input 
                    type="date" 
                    v-model="filtros.desde"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Hasta</label>
                  <input 
                    type="date" 
                    v-model="filtros.hasta"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  />
                </div>
              </div>
              <div class="flex gap-2 mt-4">
                <button 
                  @click="cargarEstadoCuenta"
                  class="px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-cyan-500/20 hover:shadow-xl hover:shadow-cyan-500/30 transition-all duration-200"
                >
                  Cargar Reporte
                </button>
                <button 
                  @click="limpiarEstadoCuenta"
                  class="px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30 transition-all duration-200"
                >
                  Limpiar
                </button>
                <button 
                  @click="exportarPDF('estado-cuenta')"
                  class="px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30 transition-all duration-200"
                  v-if="reporteActual"
                >
                  📄 Descargar PDF
                </button>
                <button 
                  @click="exportarCSV('estado-cuenta')"
                  class="px-4 py-2 bg-gradient-to-br from-green-600 to-emerald-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-green-500/20 hover:shadow-xl hover:shadow-green-500/30 transition-all duration-200"
                  v-if="reporteActual"
                >
                  📊 Descargar CSV
                </button>
              </div>
            </div>

            <div v-if="reporteActual" class="overflow-x-auto">
              <div class="mb-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Usuario:</strong> {{ reporteActual.usuario?.nombre }}</p>
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Período:</strong> {{ reporteActual.periodo.desde }} a {{ reporteActual.periodo.hasta }}</p>
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Saldo Actual:</strong> ${{ formatCurrency(reporteActual.saldo_actual) }}</p>
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Total Cargos:</strong> ${{ formatCurrency(reporteActual.total_cargos) }}</p>
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Total Abonos:</strong> ${{ formatCurrency(reporteActual.total_abonos) }}</p>
              </div>

              <table class="w-full text-sm">
                <thead class="bg-slate-700/50 border-b border-slate-600">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Fecha</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Tipo</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Módulo</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Concepto</th>
                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Monto</th>
                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Saldo Nuevo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="mov in reporteActual.movimientos" :key="mov.id" class="border-b border-slate-700 hover:bg-slate-700/30">
                    <td class="px-4 py-3 text-slate-200">{{ formatDate(mov.created_at) }}</td>
                    <td class="px-4 py-3">
                      <span v-if="mov.tipo === 'abono'" class="text-green-400 font-semibold">✓ Abono</span>
                      <span v-else class="text-red-400 font-semibold">✗ Cargo</span>
                    </td>
                    <td class="px-4 py-3 text-slate-200">{{ mov.modulo }}</td>
                    <td class="px-4 py-3 text-slate-200">{{ mov.concepto }}</td>
                    <td class="px-4 py-3 text-right text-slate-200">{{ mov.tipo === 'abono' ? '+' : '-' }}${{ formatCurrency(mov.monto) }}</td>
                    <td class="px-4 py-3 text-right text-slate-200">${{ formatCurrency(mov.saldo_nuevo) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 2: MOVIMIENTOS POR PERÍODO -->
          <div v-if="tabActivo === 'movimientos'" class="p-6">
            <div class="bg-slate-700/50 p-4 rounded-lg mb-6 border border-slate-600">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Usuario</label>
                  <select
                    v-model="filtrosMovimientos.usuario_id"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  >
                    <option value="">Todos los usuarios</option>
                    <option v-for="user in usuarios" :key="user.id" :value="user.id">
                      {{ user.nombre }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Desde</label>
                  <input 
                    type="date" 
                    v-model="filtrosMovimientos.desde"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Hasta</label>
                  <input 
                    type="date" 
                    v-model="filtrosMovimientos.hasta"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Módulo (opcional)</label>
                  <select 
                    v-model="filtrosMovimientos.modulo"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
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
                  class="px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-cyan-500/20 hover:shadow-xl hover:shadow-cyan-500/30 transition-all duration-200"
                >
                  Cargar Reporte
                </button>
                <button 
                  @click="limpiarMovimientos"
                  class="px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30 transition-all duration-200"
                >
                  Limpiar
                </button>
                <button 
                  @click="exportarPDF('movimientos')"
                  class="px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30 transition-all duration-200"
                  v-if="reporteMovimientos"
                >
                  📄 Descargar PDF
                </button>
                <button 
                  @click="exportarCSV('movimientos')"
                  class="px-4 py-2 bg-gradient-to-br from-green-600 to-emerald-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-green-500/20 hover:shadow-xl hover:shadow-green-500/30 transition-all duration-200"
                  v-if="reporteMovimientos"
                >
                  📊 Descargar CSV
                </button>
              </div>
            </div>

            <div v-if="reporteMovimientos" class="overflow-x-auto">
              <div class="mb-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Total Movimientos:</strong> {{ reporteMovimientos.resumen.total_movimientos }}</p>
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Total Cargos:</strong> ${{ formatCurrency(reporteMovimientos.resumen.total_cargos) }}</p>
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Total Abonos:</strong> ${{ formatCurrency(reporteMovimientos.resumen.total_abonos) }}</p>
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Saldo Neto:</strong> ${{ formatCurrency(reporteMovimientos.resumen.saldo_neto) }}</p>
              </div>

              <table class="w-full text-sm">
                <thead class="bg-slate-700/50 border-b border-slate-600">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Fecha</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Usuario</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Tipo</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Módulo</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Concepto</th>
                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Monto</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="mov in reporteMovimientos.movimientos" :key="mov.id" class="border-b border-slate-700 hover:bg-slate-700/30">
                    <td class="px-4 py-3 text-slate-200">{{ formatDate(mov.created_at) }}</td>
                    <td class="px-4 py-3 text-slate-200">{{ mov.usuario?.nombre || 'N/A' }}</td>
                    <td class="px-4 py-3">
                      <span v-if="mov.tipo === 'abono'" class="text-green-400 font-semibold">✓ Abono</span>
                      <span v-else class="text-red-400 font-semibold">✗ Cargo</span>
                    </td>
                    <td class="px-4 py-3 text-slate-200">{{ mov.modulo }}</td>
                    <td class="px-4 py-3 text-slate-200">{{ mov.concepto }}</td>
                    <td class="px-4 py-3 text-right text-slate-200">{{ mov.tipo === 'abono' ? '+' : '-' }}${{ formatCurrency(mov.monto) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 3: USO POR CATEGORÍA -->
          <div v-if="tabActivo === 'uso-categoria'" class="p-6">
            <div class="bg-slate-700/50 p-4 rounded-lg mb-6 border border-slate-600">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Usuario</label>
                  <select
                    v-model="filtrosCategoria.usuario_id"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  >
                    <option value="">Todos los usuarios</option>
                    <option v-for="user in usuarios" :key="user.id" :value="user.id">
                      {{ user.nombre }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Categoría</label>
                  <select
                    v-model="filtrosCategoria.modulo"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  >
                    <option value="">Todas</option>
                    <option value="cafeteria">Cafetería</option>
                    <option value="copias">Copias/Impresiones</option>
                    <option value="souvenirs">Souvenirs</option>
                    <option value="biblioteca">Biblioteca</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Desde</label>
                  <input 
                    type="date" 
                    v-model="filtrosCategoria.desde"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-1">Hasta</label>
                  <input 
                    type="date" 
                    v-model="filtrosCategoria.hasta"
                    class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                  />
                </div>
              </div>
              <div class="flex gap-2 mt-4">
                <button 
                  @click="cargarCategoria"
                  class="px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-cyan-500/20 hover:shadow-xl hover:shadow-cyan-500/30 transition-all duration-200"
                >
                  Cargar Reporte
                </button>
                <button 
                  @click="limpiarCategoria"
                  class="px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30 transition-all duration-200"
                >
                  Limpiar
                </button>
                <button 
                  @click="exportarPDF('uso-categoria')"
                  class="px-4 py-2 bg-gradient-to-br from-red-600 to-rose-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30 transition-all duration-200"
                  v-if="reporteCategoria"
                >
                  📄 Descargar PDF
                </button>
                <button 
                  @click="exportarCSV('uso-categoria')"
                  class="px-4 py-2 bg-gradient-to-br from-green-600 to-emerald-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-green-500/20 hover:shadow-xl hover:shadow-green-500/30 transition-all duration-200"
                  v-if="reporteCategoria"
                >
                  📊 Descargar CSV
                </button>
              </div>
            </div>

            <div v-if="reporteCategoria" class="overflow-x-auto">
              <div class="mb-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                <p class="text-sm text-slate-300"><strong class="text-slate-100">Total General:</strong> ${{ formatCurrency(reporteCategoria.total_general) }}</p>
              </div>

              <table class="w-full text-sm">
                <thead class="bg-slate-700/50 border-b border-slate-600">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-slate-300 uppercase text-xs tracking-wider">Categoría</th>
                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Consumo Cargo</th>
                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Consumo Abono</th>
                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Transacciones</th>
                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">Usuarios Únicos</th>
                    <th class="px-4 py-2 text-right font-medium text-slate-300 uppercase text-xs tracking-wider">%</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="cat in reporteCategoria.categorias" :key="cat.modulo" class="border-b border-slate-700 hover:bg-slate-700/30">
                    <td class="px-4 py-3 font-medium text-slate-200">{{ cat.modulo }}</td>
                    <td class="px-4 py-3 text-right text-red-400 font-semibold">${{ formatCurrency(cat.total_cargo) }}</td>
                    <td class="px-4 py-3 text-right text-green-400 font-semibold">${{ formatCurrency(cat.total_abono) }}</td>
                    <td class="px-4 py-3 text-right text-slate-200">{{ cat.cantidad }}</td>
                    <td class="px-4 py-3 text-right text-slate-200">{{ cat.usuarios_unicos }}</td>
                    <td class="px-4 py-3 text-right text-slate-200">{{ cat.porcentaje }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>
    </div>
  </div>
</div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();

const usuarios = ref(page.props.usuarios || []);
const tabActivo = ref(page.props.tipo || 'estado-cuenta');
const reporteActual = ref(null);
const reporteMovimientos = ref(null);
const reporteCategoria = ref(null);

function aplicarProps(props) {
    if (props.tipo) tabActivo.value = props.tipo;
    if (props.usuarios) usuarios.value = props.usuarios;
    if (props.reporte) {
        switch (props.tipo) {
            case 'estado-cuenta': reporteActual.value = props.reporte; break;
            case 'movimientos':   reporteMovimientos.value = props.reporte; break;
            case 'uso-categoria': reporteCategoria.value = props.reporte; break;
        }
    }
}

aplicarProps(page.props);

watch(() => page.props, (val) => aplicarProps(val), { deep: true });

const filtros = ref({
  usuario_id: '',
  desde: getFecha30DiasAtras(),
  hasta: new Date().toISOString().split('T')[0],
});

const filtrosMovimientos = ref({
  usuario_id: '',
  desde: getFecha30DiasAtras(),
  hasta: new Date().toISOString().split('T')[0],
  modulo: '',
});

const filtrosCategoria = ref({
  usuario_id: '',
  desde: getFecha30DiasAtras(),
  hasta: new Date().toISOString().split('T')[0],
  modulo: '',
});

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

function cargarEstadoCuenta() {
  if (!filtros.value.usuario_id) {
    alert('Selecciona un usuario');
    return;
  }
  router.get('/admin/monedero/reportes/estado-cuenta', filtros.value, {
    preserveState: true,
    preserveScroll: true,
  });
}

function cargarMovimientos() {
  const params = { ...filtrosMovimientos.value };
  if (!params.usuario_id) delete params.usuario_id;
  router.get('/admin/monedero/reportes/movimientos', params, {
    preserveState: true,
    preserveScroll: true,
  });
}

function cargarCategoria() {
  const params = { ...filtrosCategoria.value };
  if (!params.usuario_id) delete params.usuario_id;
  if (!params.modulo) delete params.modulo;
  router.get('/admin/monedero/reportes/uso-categoria', params, {
    preserveState: true,
    preserveScroll: true,
  });
}

function limpiarEstadoCuenta() {
  filtros.value = { usuario_id: '', desde: getFecha30DiasAtras(), hasta: new Date().toISOString().split('T')[0] };
  reporteActual.value = null;
}

function limpiarMovimientos() {
  filtrosMovimientos.value = { usuario_id: '', desde: getFecha30DiasAtras(), hasta: new Date().toISOString().split('T')[0], modulo: '' };
  reporteMovimientos.value = null;
}

function limpiarCategoria() {
  filtrosCategoria.value = { usuario_id: '', desde: getFecha30DiasAtras(), hasta: new Date().toISOString().split('T')[0], modulo: '' };
  reporteCategoria.value = null;
}

function getExportParams(tipo) {
  const params = new URLSearchParams();
  let f, fm, fc;
  if (tipo === 'estado-cuenta') {
    f = filtros.value;
    if (f.usuario_id) params.set('usuario_id', f.usuario_id);
    if (f.desde) params.set('desde', f.desde);
    if (f.hasta) params.set('hasta', f.hasta);
  } else if (tipo === 'movimientos') {
    fm = filtrosMovimientos.value;
    if (fm.usuario_id) params.set('usuario_id', fm.usuario_id);
    if (fm.desde) params.set('desde', fm.desde);
    if (fm.hasta) params.set('hasta', fm.hasta);
    if (fm.modulo) params.set('modulo', fm.modulo);
  } else if (tipo === 'uso-categoria') {
    fc = filtrosCategoria.value;
    if (fc.usuario_id) params.set('usuario_id', fc.usuario_id);
    if (fc.desde) params.set('desde', fc.desde);
    if (fc.hasta) params.set('hasta', fc.hasta);
    if (fc.modulo) params.set('modulo', fc.modulo);
  }
  return params;
}

function exportarPDF(tipo) {
  const urls = {
    'estado-cuenta': '/admin/monedero/exportes/estado-cuenta/pdf',
    'movimientos':   '/admin/monedero/exportes/movimientos/pdf',
    'uso-categoria': '/admin/monedero/exportes/uso-categoria/pdf',
  };
  window.open(urls[tipo] + '?' + getExportParams(tipo).toString(), '_blank');
}

function exportarCSV(tipo) {
  const urls = {
    'estado-cuenta': '/admin/monedero/exportes/estado-cuenta/csv',
    'movimientos':   '/admin/monedero/exportes/movimientos/csv',
    'uso-categoria': '/admin/monedero/exportes/uso-categoria/csv',
  };
  window.open(urls[tipo] + '?' + getExportParams(tipo).toString(), '_blank');
}
</script>
