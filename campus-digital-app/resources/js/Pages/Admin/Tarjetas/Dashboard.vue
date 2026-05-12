<template>
    <AuthLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        Dashboard · Tarjetas RFID/NFC
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">Monitoreo en tiempo real del módulo de identificación</p>
                </div>
                <div class="flex gap-3">
                    <a :href="route('admin.tarjetas.reportes.index')"
                       class="inline-flex items-center px-4 py-2 border border-cyan-500/30 rounded-lg text-sm text-cyan-400 hover:bg-cyan-500/10 transition-all duration-200">
                        Reportes
                    </a>
                    <a :href="route('admin.tarjetas.index')"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-br from-cyan-600 to-blue-600 rounded-lg text-sm font-medium text-white shadow-lg shadow-cyan-500/20 transition-all duration-200">
                        Gestionar Tarjetas
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Total Tarjetas</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.total_tarjetas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-green-500/20 p-5">
                    <p class="text-xs text-green-400 uppercase tracking-wider">Activas</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.activas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 p-5">
                    <p class="text-xs text-red-400 uppercase tracking-wider">Bloqueadas</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.bloqueadas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-5">
                    <p class="text-xs text-cyan-400 uppercase tracking-wider">Lecturas Hoy</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.lecturas_hoy }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-5">
                    <p class="text-xs text-blue-400 uppercase tracking-wider">Esta Semana</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.lecturas_semana }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 shadow-xl shadow-cyan-500/5 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Lecturas por Día (últimos 14 días)</h2>
                    <div class="flex items-end gap-1 h-32">
                        <div v-for="dia in lecturasPorDia" :key="dia.fecha"
                             class="flex-1 flex flex-col items-center gap-1 group">
                            <div class="relative w-full flex flex-col justify-end" style="height: 100px;">
                                <div
                                    :style="{ height: maxLecturas > 0 ? (dia.total / maxLecturas * 100) + '%' : '2px' }"
                                    class="w-full bg-gradient-to-t from-cyan-600 to-blue-500 rounded-t transition-all duration-300 group-hover:from-cyan-500 group-hover:to-blue-400 min-h-[2px]">
                                </div>
                                <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-slate-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                                    {{ dia.total }} lecturas
                                </div>
                            </div>
                            <span class="text-xs text-slate-500 w-full text-center truncate" style="font-size:9px;">
                                {{ formatDia(dia.fecha) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-4 mt-4">
                        <span class="flex items-center gap-1.5 text-xs text-slate-400">
                            <span class="w-3 h-3 rounded-full bg-gradient-to-r from-cyan-600 to-blue-500 inline-block"></span>
                            Total lecturas
                        </span>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 shadow-xl shadow-blue-500/5 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Uso por Módulo (30 días)</h2>
                    <div v-if="lecturasPorModulo.length === 0" class="text-center py-6 text-slate-500 text-sm">Sin datos</div>
                    <div v-else class="space-y-3">
                        <div v-for="m in lecturasPorModulo" :key="m.modulo">
                            <div class="flex justify-between text-xs text-slate-400 mb-1">
                                <span class="capitalize">{{ m.modulo.replace('_', ' ') }}</span>
                                <span>{{ m.total }}</span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-1.5">
                                <div
                                    :style="{ width: maxModulo > 0 ? (m.total / maxModulo * 100) + '%' : '0%' }"
                                    class="h-1.5 rounded-full bg-gradient-to-r from-blue-600 to-cyan-500 transition-all duration-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-700">
                        <h2 class="text-base font-semibold text-white">Usuarios Más Activos (30 días)</h2>
                    </div>
                    <div class="divide-y divide-slate-700/50">
                        <div v-if="usuariosActivos.length === 0" class="px-6 py-8 text-center text-slate-500 text-sm">Sin actividad reciente</div>
                        <div v-for="(item, i) in usuariosActivos.slice(0, 5)" :key="item.id"
                             class="flex items-center gap-4 px-6 py-3 hover:bg-slate-700/20 transition-colors duration-150">
                            <span class="text-lg font-bold text-slate-500 w-6 text-center">{{ i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">
                                    {{ item.usuario?.nombre }} {{ item.usuario?.apellido }}
                                </p>
                                <p class="text-xs text-slate-400 truncate font-mono">{{ item.uid }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-cyan-400">{{ item.total_lecturas }}</p>
                                <p class="text-xs text-slate-500">lecturas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-red-500/20 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-white">Tarjetas Bloqueadas</h2>
                        <a :href="route('admin.tarjetas.index') + '?estado=bloqueada'"
                           class="text-xs text-red-400 hover:text-red-300 transition-colors duration-200">Ver todas →</a>
                    </div>
                    <div class="divide-y divide-slate-700/50">
                        <div v-if="tarjetasBloqueadas.length === 0" class="px-6 py-8 text-center text-slate-500 text-sm">
                            No hay tarjetas bloqueadas
                        </div>
                        <div v-for="t in tarjetasBloqueadas" :key="t.id"
                             class="flex items-center gap-3 px-6 py-3 hover:bg-slate-700/20 transition-colors duration-150">
                            <div class="w-2 h-2 rounded-full flex-shrink-0"
                                 :class="t.estado === 'perdida' ? 'bg-yellow-400' : 'bg-red-400'"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">
                                    {{ t.usuario?.nombre }} {{ t.usuario?.apellido }}
                                </p>
                                <p class="text-xs text-slate-400 font-mono truncate">{{ t.uid }}</p>
                            </div>
                            <div class="text-right">
                                <span :class="t.estado === 'perdida' ? 'text-yellow-400' : 'text-red-400'"
                                      class="text-xs capitalize">{{ t.estado }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h2 class="text-base font-semibold text-white">Lecturas Recientes</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Resultado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <tr v-if="lecturasRecientes.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">Sin lecturas recientes</td>
                            </tr>
                            <tr v-for="l in lecturasRecientes" :key="l.id" class="hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-6 py-3 text-sm text-slate-400 whitespace-nowrap">{{ formatDateTime(l.created_at) }}</td>
                                <td class="px-6 py-3 text-sm text-white">
                                    {{ l.tarjeta?.usuario ? `${l.tarjeta.usuario.nombre} ${l.tarjeta.usuario.apellido}` : 'Desconocido' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-300 capitalize">{{ l.modulo.replace('_', ' ') }}</td>
                                <td class="px-6 py-3 text-sm text-slate-300 capitalize">{{ l.tipo_lectura.replace('_', ' ') }}</td>
                                <td class="px-6 py-3">
                                    <span :class="l.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                                        {{ l.exito ? 'Exitoso' : 'Fallido' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-yellow-500/20 p-4">
                    <p class="text-xs text-yellow-400 uppercase tracking-wider">Perdidas</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.perdidas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-600/30 p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Canceladas</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.canceladas }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-purple-500/20 p-4">
                    <p class="text-xs text-purple-400 uppercase tracking-wider">Con PIN</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.con_pin }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-orange-500/20 p-4">
                    <p class="text-xs text-orange-400 uppercase tracking-wider">Sin PIN</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.sin_pin }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-teal-500/20 p-4">
                    <p class="text-xs text-teal-400 uppercase tracking-wider">Lecturas Mes</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.lecturas_mes }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-green-500/20 p-4">
                    <p class="text-xs text-green-400 uppercase tracking-wider">Tasa Éxito</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.tasa_exito_mes }}%</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-4">
                    <p class="text-xs text-cyan-400 uppercase tracking-wider">Logins RFID Hoy</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.logins_rfid_hoy }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-indigo-500/20 p-4">
                    <p class="text-xs text-indigo-400 uppercase tracking-wider">Sin uso 30d</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.tarjetas_sin_uso_30d }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-green-500/20 p-5">
                    <p class="text-xs text-green-400 uppercase tracking-wider">Exitosas Hoy</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.lecturas_exitosas_hoy }}</p>
                    <p class="text-xs text-slate-500 mt-1">de {{ stats.lecturas_hoy }} totales</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 p-5">
                    <p class="text-xs text-red-400 uppercase tracking-wider">Fallidas Hoy</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.lecturas_fallidas_hoy }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-5">
                    <p class="text-xs text-blue-400 uppercase tracking-wider">Confirmaciones Hoy</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.confirmaciones_hoy }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-500/20 p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Consultas Saldo Hoy</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ stats.consultas_saldo_hoy }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Lecturas por Hora del Día (últimos 7 días)</h2>
                    <div class="flex items-end gap-0.5 h-28">
                        <div v-for="h in lecturasPorHora" :key="h.hora" class="flex-1 flex flex-col items-center gap-1 group">
                            <div class="relative w-full flex flex-col justify-end" style="height: 90px;">
                                <div :style="{ height: maxHora > 0 ? (h.total / maxHora * 100) + '%' : '2px' }"
                                    class="w-full bg-gradient-to-t from-indigo-600 to-purple-500 rounded-t min-h-[2px] group-hover:from-indigo-500 group-hover:to-purple-400 transition-all duration-200">
                                </div>
                                <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-slate-700 text-white text-xs px-1.5 py-0.5 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                                    {{ h.total }}
                                </div>
                            </div>
                            <span class="text-slate-600 text-center" style="font-size:7px;">{{ h.hora % 3 === 0 ? h.label : '' }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Hora pico: {{ horaPico }}</p>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Distribución de Estados</h2>
                    <div class="space-y-3">
                        <div v-for="e in distribucionEstados" :key="e.estado">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="capitalize" :class="colorEstado(e.estado)">{{ e.estado }}</span>
                                <span class="text-slate-400">{{ e.total }}</span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-2">
                                <div :style="{ width: stats.total_tarjetas > 0 ? (e.total / stats.total_tarjetas * 100) + '%' : '0%' }"
                                    :class="bgColorEstado(e.estado)"
                                    class="h-2 rounded-full transition-all duration-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-700">
                        <p class="text-xs text-slate-500">Nuevas esta semana: <span class="text-cyan-400 font-bold">{{ stats.nuevas_esta_semana }}</span></p>
                        <p class="text-xs text-slate-500 mt-1">Logins RFID semana: <span class="text-cyan-400 font-bold">{{ stats.logins_rfid_semana }}</span></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-purple-500/20 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Lecturas por Tipo (30 días)</h2>
                    <div class="space-y-4">
                        <div v-for="t in lecturasPorTipo" :key="t.tipo_lectura">
                            <div class="flex justify-between text-xs text-slate-400 mb-1">
                                <span class="capitalize">{{ t.tipo_lectura.replace('_', ' ') }}</span>
                                <span>{{ t.total }} <span class="text-green-400">({{ t.exitosas }} ok)</span></span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-1.5">
                                <div :style="{ width: maxTipo > 0 ? (t.total / maxTipo * 100) + '%' : '0%' }"
                                    class="h-1.5 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 transition-all duration-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-blue-500/20 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Tasa de Éxito por Módulo (30 días)</h2>
                    <div class="space-y-3">
                        <div v-for="m in exitoFalloPorModulo" :key="m.modulo">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-slate-400 capitalize">{{ m.modulo }}</span>
                                <span :class="m.tasa >= 90 ? 'text-green-400' : m.tasa >= 70 ? 'text-yellow-400' : 'text-red-400'">
                                    {{ m.tasa }}%
                                </span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-2 flex overflow-hidden rounded-full">
                                <div :style="{ width: m.tasa + '%' }"
                                    :class="m.tasa >= 90 ? 'bg-green-500' : m.tasa >= 70 ? 'bg-yellow-500' : 'bg-red-500'"
                                    class="h-2 transition-all duration-500">
                                </div>
                                <div :style="{ width: (100 - m.tasa) + '%' }" class="h-2 bg-red-900/40"></div>
                            </div>
                            <p class="text-xs text-slate-600 mt-0.5">{{ m.exitosas }} ok · {{ m.fallidas }} fallidas de {{ m.total }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-teal-500/20 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Evolución Semanal (8 semanas)</h2>
                    <div class="flex items-end gap-2 h-32">
                        <div v-for="s in evolucionSemanal" :key="s.semana" class="flex-1 flex flex-col items-center gap-1 group">
                            <div class="relative w-full flex flex-col justify-end" style="height: 100px;">
                                <div :style="{ height: maxSemanal > 0 ? (s.total / maxSemanal * 100) + '%' : '2px' }"
                                    class="w-full bg-gradient-to-t from-teal-600 to-cyan-500 rounded-t min-h-[2px] group-hover:from-teal-500">
                                </div>
                                <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-slate-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 z-10 pointer-events-none">
                                    {{ s.total }}
                                </div>
                            </div>
                            <span class="text-xs text-slate-500">{{ s.semana }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-cyan-500/20 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Logins RFID por Día (14 días)</h2>
                    <div class="flex items-end gap-1 h-28">
                        <div v-for="d in loginsRfidDias" :key="d.fecha" class="flex-1 flex flex-col items-center gap-1 group">
                            <div class="relative w-full" style="height: 90px;">
                                <div class="absolute bottom-0 w-full flex flex-col justify-end" style="height: 100%;">
                                    <div :style="{ height: maxLoginsRfid > 0 ? (d.exitosos / maxLoginsRfid * 100) + '%' : '0' }"
                                        class="w-full bg-green-500/70 rounded-t min-h-0">
                                    </div>
                                    <div :style="{ height: maxLoginsRfid > 0 ? (d.fallidos / maxLoginsRfid * 100) + '%' : '0' }"
                                        class="w-full bg-red-500/60 min-h-0">
                                    </div>
                                </div>
                                <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-slate-700 text-white text-xs px-1.5 py-0.5 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 z-10 pointer-events-none">
                                    {{ d.exitosos }}✓ {{ d.fallidos }}✗
                                </div>
                            </div>
                            <span class="text-slate-600 text-center" style="font-size:8px;">{{ formatDia(d.fecha) }}</span>
                        </div>
                    </div>
                    <div class="flex gap-4 mt-3">
                        <span class="flex items-center gap-1.5 text-xs text-slate-400"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>Exitosos</span>
                        <span class="flex items-center gap-1.5 text-xs text-slate-400"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>Fallidos</span>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-purple-500/20 shadow-xl shadow-purple-500/5 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Tarjetas Registradas por Día (últimos 28 días)</h2>
                    <div class="flex items-end gap-0.5 h-28">
                        <div v-for="d in registrosTarjetas" :key="d.fecha"
                            class="flex-1 flex flex-col items-center gap-1 group">
                            <div class="relative w-full flex flex-col justify-end" style="height: 90px;">
                                <div :style="{ height: maxRegistros > 0 ? (d.total / maxRegistros * 100) + '%' : '2px' }"
                                    class="w-full bg-gradient-to-t from-purple-600 to-violet-400 rounded-t min-h-[2px] group-hover:from-purple-500 transition-all duration-200">
                                </div>
                                <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-slate-700 text-white text-xs px-1.5 py-0.5 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                                    {{ d.total }} tarjeta{{ d.total !== 1 ? 's' : '' }}
                                </div>
                            </div>
                            <span class="text-slate-600 text-center" style="font-size:7px;">
                                {{ d.fecha.slice(8) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-4 mt-3">
                        <span class="flex items-center gap-1.5 text-xs text-slate-400">
                            <span class="w-3 h-3 rounded-full bg-gradient-to-r from-purple-600 to-violet-400 inline-block"></span>
                            Tarjetas registradas
                        </span>
                        <span class="text-xs text-slate-500 ml-auto">
                            Total período: {{ registrosTarjetas.reduce((a, b) => a + b.total, 0) }}
                        </span>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Top Confirmaciones por Módulo</h2>
                    <div v-if="topConfirmaciones.length === 0" class="text-center py-6 text-slate-500 text-sm">Sin datos</div>
                    <div v-else class="space-y-3">
                        <div v-for="m in topConfirmaciones" :key="m.modulo" class="flex items-center justify-between">
                            <span class="text-sm text-slate-300 capitalize">{{ m.modulo }}</span>
                            <span class="text-sm font-bold text-teal-400">{{ m.total }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Top Consultas de Saldo</h2>
                    <div v-if="topConsultas.length === 0" class="text-center py-6 text-slate-500 text-sm">Sin datos</div>
                    <div v-else class="space-y-3">
                        <div v-for="m in topConsultas" :key="m.modulo" class="flex items-center justify-between">
                            <span class="text-sm text-slate-300 capitalize">{{ m.modulo }}</span>
                            <span class="text-sm font-bold text-blue-400">{{ m.total }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-red-500/20 p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Bloqueos por Mes (6 meses)</h2>
                    <div v-if="bloqueosHistorico.length === 0" class="text-center py-6 text-slate-500 text-sm">Sin bloqueos recientes</div>
                    <div v-else class="space-y-2">
                        <div v-for="b in bloqueosHistorico" :key="b.mes" class="flex items-center justify-between">
                            <span class="text-sm text-slate-400">{{ b.mes }}</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-slate-700 rounded-full h-1.5">
                                    <div :style="{ width: maxBloqueos > 0 ? (b.total / maxBloqueos * 100) + '%' : '0%' }"
                                        class="h-1.5 rounded-full bg-red-500 transition-all duration-500">
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-red-400">{{ b.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h2 class="text-base font-semibold text-white">Últimos Logins por Tarjeta RFID</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">IP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Resultado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <tr v-if="ultimosLoginsRfid.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">Sin logins RFID registrados</td>
                            </tr>
                            <tr v-for="l in ultimosLoginsRfid" :key="l.id" class="hover:bg-slate-700/20 transition-colors duration-150">
                                <td class="px-6 py-3 text-sm text-slate-400 whitespace-nowrap">{{ formatDateTime(l.created_at) }}</td>
                                <td class="px-6 py-3 text-sm text-white">{{ l.nombre }} {{ l.apellido }}</td>
                                <td class="px-6 py-3 text-sm text-slate-400 font-mono">{{ l.email }}</td>
                                <td class="px-6 py-3 text-sm text-slate-500 font-mono">{{ l.ip }}</td>
                                <td class="px-6 py-3">
                                    <span :class="l.exito ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                                        {{ l.exito ? 'Exitoso' : 'Fallido' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    stats: Object,
    lecturasPorDia: Array,
    lecturasPorModulo: Array,
    usuariosActivos: Array,
    tarjetasBloqueadas: Array,
    lecturasRecientes: Array,
    lecturasPorTipo:     Array,
    lecturasPorHora:     Array,
    exitoFalloPorModulo: Array,
    evolucionSemanal:    Array,
    registrosTarjetas:   Array,
    loginsRfidDias:      Array,
    topConfirmaciones:   Array,
    topConsultas:        Array,
    bloqueosHistorico:   Array,
    ultimosLoginsRfid:   Array,
    distribucionEstados: Array,
});

const maxLecturas = computed(() => Math.max(...props.lecturasPorDia.map(d => d.total), 1));
const maxModulo   = computed(() => Math.max(...props.lecturasPorModulo.map(m => m.total), 1));

const maxRegistros = computed(() => Math.max(...(props.registrosTarjetas ?? []).map(d => d.total), 1));
const maxHora      = computed(() => Math.max(...(props.lecturasPorHora ?? []).map(h => h.total), 1));
const maxTipo      = computed(() => Math.max(...(props.lecturasPorTipo ?? []).map(t => t.total), 1));
const maxSemanal   = computed(() => Math.max(...(props.evolucionSemanal ?? []).map(s => s.total), 1));
const maxLoginsRfid= computed(() => Math.max(...(props.loginsRfidDias ?? []).map(d => d.total), 1));
const maxBloqueos  = computed(() => Math.max(...(props.bloqueosHistorico ?? []).map(b => b.total), 1));

const horaPico = computed(() => {
    if (!props.lecturasPorHora?.length) return '—';
    const max = props.lecturasPorHora.reduce((a, b) => a.total >= b.total ? a : b);
    return max.total > 0 ? max.label : '—';
});

function formatDia(fecha) {
    const d = new Date(fecha + 'T00:00:00');
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
}
function formatDateTime(d) {
    if (!d) return '-';
    return new Date(d).toLocaleString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}

function colorEstado(estado) {
    return { activa: 'text-green-400', bloqueada: 'text-red-400', perdida: 'text-yellow-400', cancelada: 'text-slate-400' }[estado] ?? 'text-slate-400';
}
function bgColorEstado(estado) {
    return { activa: 'bg-green-500', bloqueada: 'bg-red-500', perdida: 'bg-yellow-500', cancelada: 'bg-slate-500' }[estado] ?? 'bg-slate-500';
}


</script>