<template>
    <AuthLayout>
        <div class="dashboard-container">

            <div class="dashboard-header">
                <div>
                    <h1 class="dashboard-title">Dashboard Administrador</h1>
                    <p class="dashboard-subtitle">Panel de control · Campus Digital</p>
                </div>
                <div class="header-meta">
                    <span class="live-dot"></span>
                    <span class="live-label">En vivo</span>
                    <span class="header-date">{{ fechaActual }}</span>
                </div>
            </div>

            <div class="resumen-24h">
                <div class="resumen-title">Últimas 24 horas</div>
                <div class="resumen-items">
                    <div class="resumen-item" v-for="item in resumen24hItems" :key="item.label">
                        <span :class="['resumen-val', item.color]">{{ item.val }}</span>
                        <span class="resumen-lbl">{{ item.label }}</span>
                    </div>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card blue">
                    <div class="kpi-top">
                        <div class="kpi-icon blue">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <span class="kpi-label">Usuarios Activos</span>
                    </div>
                    <div class="kpi-number">{{ stats.usuarios_activos }}</div>
                    <div class="kpi-sub">
                        <span class="kpi-badge blue-soft">{{ stats.usuarios_total }} total</span>
                        <span v-if="stats.usuarios_nuevos_hoy" class="kpi-badge green-soft">+{{ stats.usuarios_nuevos_hoy }} hoy</span>
                    </div>
                    <a :href="route('admin.usuarios.index')" class="kpi-link blue">Ver usuarios →</a>
                </div>
                <div class="kpi-card green">
                    <div class="kpi-top">
                        <div class="kpi-icon green">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="kpi-label">Accesos Exitosos (7d)</span>
                    </div>
                    <div class="kpi-number">{{ stats.accesos_exitosos }}</div>
                    <div class="kpi-sub">
                        <span class="kpi-badge green-soft">Tasa {{ tasaExito }}%</span>
                    </div>
                    <a :href="route('admin.bitacora.accesos')" class="kpi-link green">Ver bitácora →</a>
                </div>
                <div class="kpi-card red">
                    <div class="kpi-top">
                        <div class="kpi-icon red">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="kpi-label">Accesos Fallidos (7d)</span>
                    </div>
                    <div class="kpi-number">{{ stats.accesos_fallidos }}</div>
                    <div class="kpi-sub">
                        <span v-if="stats.usuarios_bloqueados" class="kpi-badge red-soft">{{ stats.usuarios_bloqueados }} bloqueados</span>
                    </div>
                    <a :href="route('admin.bitacora.accesos')" class="kpi-link red">Ver bitácora →</a>
                </div>
                <div class="kpi-card purple">
                    <div class="kpi-top">
                        <div class="kpi-icon purple">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                        </div>
                        <span class="kpi-label">Tarjetas Activas</span>
                    </div>
                    <div class="kpi-number">{{ stats.tarjetas_activas }}</div>
                    <div class="kpi-sub">
                        <span class="kpi-badge yellow-soft">{{ stats.lecturas_hoy }} lecturas hoy</span>
                        <span class="kpi-badge purple-soft">+{{ stats.tarjetas_este_mes }} este mes</span>
                    </div>
                    <a :href="route('admin.tarjetas.dashboard')" class="kpi-link purple">Ver tarjetas →</a>
                </div>
            </div>

            <div class="meta-grid">
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div><div class="meta-val">{{ stats.sesiones_activas }}</div><div class="meta-lbl">Sesiones activas</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <div><div class="meta-val">{{ pctEmailVerificado }}%</div><div class="meta-lbl">Emails verificados</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div><div class="meta-val">${{ formatMonto(stats.saldo_total) }}</div><div class="meta-lbl">Saldo en monederos</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <div><div class="meta-val">{{ stats.movimientos_hoy }}</div><div class="meta-lbl">Movimientos hoy</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div><div class="meta-val">{{ stats.tarjetas_bloqueadas + stats.tarjetas_perdidas }}</div><div class="meta-lbl">Tarjetas inactivas</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <div><div class="meta-val">{{ pedidosActivos }}</div><div class="meta-lbl">Pedidos activos (30d)</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1"/></svg>
                    <div><div class="meta-val">${{ formatMonto(stats.saldo_promedio) }}</div><div class="meta-lbl">Saldo promedio</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <div><div class="meta-val">${{ formatMonto(stats.ingresos_entregados) }}</div><div class="meta-lbl">Ingresos entregados (30d)</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <div><div class="meta-val">{{ stats.usuarios_sin_tarjeta }}</div><div class="meta-lbl">Sin tarjeta asignada</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <div><div class="meta-val">{{ stats.tarjetas_este_mes }}</div><div class="meta-lbl">Tarjetas este mes</div></div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <div>
                        <div class="meta-val">
                            {{ stats.usuarios_nuevos_hoy }}
                            <span :class="['meta-delta', stats.usuarios_nuevos_hoy >= stats.usuarios_nuevos_ayer ? 'pos' : 'neg']">
                                {{ stats.usuarios_nuevos_hoy >= stats.usuarios_nuevos_ayer ? '↑' : '↓' }} vs ayer
                            </span>
                        </div>
                        <div class="meta-lbl">Nuevos usuarios hoy</div>
                    </div>
                </div>
                <div class="meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    <div><div class="meta-val">{{ stats.pedidos_con_tarjeta }}</div><div class="meta-lbl">Pedidos con tarjeta (30d)</div></div>
                </div>
            </div>

            <div class="section">
                <h3 class="section-title">Accesos Rápidos</h3>
                <div class="quick-grid">
                    <a :href="route('admin.usuarios.index')" class="quick-card primary"><svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg><span>Usuarios</span></a>
                    <a :href="route('admin.roles.index')" class="quick-card success"><svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><span>Roles</span></a>
                    <a :href="route('admin.permisos.index')" class="quick-card warning"><svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg><span>Permisos</span></a>
                    <a :href="route('admin.bitacora.actividad')" class="quick-card secondary"><svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg><span>Bitácora Actividad</span></a>
                    <a :href="route('admin.bitacora.accesos')" class="quick-card info"><svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg><span>Bitácora Accesos</span></a>
                    <a :href="route('admin.tarjetas.dashboard')" class="quick-card accent"><svg class="quick-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg><span>Tarjetas</span></a>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-card wide">
                    <div class="chart-header">
                        <h3 class="chart-title">Accesos últimos 7 días</h3>
                        <div class="chart-legend">
                            <span class="legend-dot green"></span><span>Exitosos</span>
                            <span class="legend-dot red"></span><span>Fallidos</span>
                        </div>
                    </div>
                    <canvas ref="chartAccesos" height="100"></canvas>
                </div>
                <div class="chart-card">
                    <div class="chart-header"><h3 class="chart-title">Actividad por módulo (30d)</h3></div>
                    <canvas ref="chartModulos" height="180"></canvas>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-card">
                    <div class="chart-header"><h3 class="chart-title">Crecimiento de usuarios (14d)</h3></div>
                    <canvas ref="chartCrecimiento" height="130"></canvas>
                </div>
                <div class="chart-card wide">
                    <div class="chart-header">
                        <h3 class="chart-title">Actividad por hora del día (30d)</h3>
                        <span class="chart-sub">Distribución de acciones por hora</span>
                    </div>
                    <canvas ref="chartHoras" height="100"></canvas>
                </div>
            </div>

            <div class="content-grid">
                <!-- Usuarios por Rol -->
                <div class="content-card">
                    <div class="content-header"><h3 class="content-title">Usuarios por Rol</h3></div>
                    <div class="content-body">
                        <div class="roles-list">
                            <div v-for="rol in stats.usuarios_por_rol" :key="rol.nombre" class="role-row">
                                <span class="role-name">{{ rol.nombre }}</span>
                                <div class="role-bar-wrap">
                                    <div class="role-bar" :style="{ width: maxRol > 0 ? (rol.total / maxRol * 100) + '%' : '0%' }"></div>
                                </div>
                                <div class="role-stats">
                                    <span class="role-count">{{ rol.total }}</span>
                                    <span class="role-label">usu.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-card center-content">
                    <div class="content-header"><h3 class="content-title">Estado de Tarjetas</h3></div>
                    <div class="content-body donut-body">
                        <canvas ref="chartTarjetas" width="200" height="200"></canvas>
                        <div class="donut-legend">
                            <div class="donut-item"><span class="dleg green"></span><span>Activas</span><strong>{{ stats.tarjetas_activas }}</strong></div>
                            <div class="donut-item"><span class="dleg red"></span><span>Bloqueadas</span><strong>{{ stats.tarjetas_bloqueadas }}</strong></div>
                            <div class="donut-item"><span class="dleg yellow"></span><span>Perdidas</span><strong>{{ stats.tarjetas_perdidas }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="content-card">
                    <div class="content-header"><h3 class="content-title">Pedidos por Estado (30d)</h3></div>
                    <div class="content-body">
                        <div class="pedidos-list">
                            <div v-for="(item, estado) in stats.pedidos_por_estado" :key="estado" class="pedido-row">
                                <span :class="['estado-pill', estadoClass(estado)]">{{ estado }}</span>
                                <span class="pedido-count">{{ item.total }}</span>
                            </div>
                            <div v-if="!Object.keys(stats.pedidos_por_estado).length" class="empty-msg">Sin pedidos en los últimos 30 días</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title-bar">
                <h3 class="section-title">Finanzas y Operaciones</h3>
            </div>
            <div class="content-grid">
                <!-- Monederos: abonos vs cargos -->
                <div class="content-card">
                    <div class="content-header"><h3 class="content-title">Monederos (30d)</h3></div>
                    <div class="content-body">
                        <div class="finance-row">
                            <div class="finance-item green">
                                <span class="finance-label">Abonos</span>
                                <span class="finance-val">${{ formatMonto(stats.movimientos_por_tipo?.abono?.total_monto ?? 0) }}</span>
                                <span class="finance-count">{{ stats.movimientos_por_tipo?.abono?.cantidad ?? 0 }} operaciones</span>
                            </div>
                            <div class="finance-item red">
                                <span class="finance-label">Cargos</span>
                                <span class="finance-val">${{ formatMonto(stats.movimientos_por_tipo?.cargo?.total_monto ?? 0) }}</span>
                                <span class="finance-count">{{ stats.movimientos_por_tipo?.cargo?.cantidad ?? 0 }} operaciones</span>
                            </div>
                        </div>
                        <div class="divider-line"></div>
                        <h4 class="sub-title">Por módulo</h4>
                        <div class="mod-list">
                            <div v-for="m in stats.movimientos_por_modulo" :key="m.modulo" class="mod-row">
                                <span class="mod-name">{{ m.modulo }}</span>
                                <div class="mod-bar-wrap">
                                    <div class="mod-bar" :style="{ width: maxMovMonto > 0 ? (m.monto / maxMovMonto * 100) + '%' : '0%' }"></div>
                                </div>
                                <span class="mod-val">${{ formatMonto(m.monto) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-header"><h3 class="content-title">Pedidos por Módulo (30d)</h3></div>
                    <div class="content-body">
                        <div class="mod-list">
                            <div v-for="p in stats.pedidos_por_modulo" :key="p.modulo" class="mod-row">
                                <span class="mod-name">{{ p.modulo }}</span>
                                <div class="mod-bar-wrap">
                                    <div class="mod-bar amber" :style="{ width: maxPedMonto > 0 ? (p.monto / maxPedMonto * 100) + '%' : '0%' }"></div>
                                </div>
                                <div class="mod-info">
                                    <span class="mod-val">${{ formatMonto(p.monto) }}</span>
                                    <span class="mod-count">{{ p.total }} ped.</span>
                                </div>
                            </div>
                        </div>
                        <div class="divider-line"></div>
                        <div class="tarjeta-confirm">
                            <div class="confirm-item">
                                <span class="confirm-dot green"></span>
                                <span class="confirm-label">Confirmados con tarjeta</span>
                                <strong class="confirm-val">{{ stats.pedidos_con_tarjeta }}</strong>
                            </div>
                            <div class="confirm-item">
                                <span class="confirm-dot gray"></span>
                                <span class="confirm-label">Sin tarjeta</span>
                                <strong class="confirm-val">{{ stats.pedidos_sin_tarjeta }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-header"><h3 class="content-title">Lecturas por Tipo (7d)</h3></div>
                    <div class="content-body">
                        <div class="tipo-list">
                            <div v-for="t in stats.lecturas_por_tipo" :key="t.tipo_lectura" class="tipo-row">
                                <div class="tipo-info">
                                    <span class="tipo-name">{{ t.tipo_lectura.replace(/_/g, ' ') }}</span>
                                    <span class="tipo-pct">{{ t.total > 0 ? Math.round(t.exitosas / t.total * 100) : 0 }}% éxito</span>
                                </div>
                                <div class="tipo-bar-wrap">
                                    <div class="tipo-bar-bg">
                                        <div class="tipo-bar-fill" :style="{ width: maxTipoTotal > 0 ? (t.total / maxTipoTotal * 100) + '%' : '0%' }"></div>
                                    </div>
                                </div>
                                <span class="tipo-total">{{ t.total }}</span>
                            </div>
                            <div v-if="!stats.lecturas_por_tipo?.length" class="empty-msg">Sin lecturas en los últimos 7 días</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title-bar">
                <h3 class="section-title">Seguridad</h3>
            </div>
            <div class="content-grid">
                <div class="content-card">
                    <div class="content-header"><h3 class="content-title">Eventos de Acceso (7d)</h3></div>
                    <div class="content-body">
                        <div class="eventos-list">
                            <div v-for="e in stats.eventos_por_tipo" :key="e.evento" class="evento-row">
                                <div class="evento-info">
                                    <span class="evento-name">{{ e.evento }}</span>
                                    <span :class="['evento-tasa', e.exitosos === e.total ? 'green' : e.exitosos === 0 ? 'red' : 'yellow']">
                                        {{ e.exitosos }}/{{ e.total }}
                                    </span>
                                </div>
                                <div class="evento-bar-wrap">
                                    <div class="evento-bar-bg">
                                        <div class="evento-bar-fill success" :style="{ width: e.total > 0 ? (e.exitosos / e.total * 100) + '%' : '0%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-header">
                        <h3 class="content-title">IPs con Más Fallos (7d)</h3>
                        <span class="header-badge red">⚠ Posible ataque</span>
                    </div>
                    <div class="content-body">
                        <div v-if="stats.top_ips_fallidas?.length" class="ip-list">
                            <div v-for="(ip, i) in stats.top_ips_fallidas" :key="ip.ip" class="ip-row">
                                <span class="ip-rank" :class="i === 0 ? 'rank-1' : 'rank-n'">#{{ i + 1 }}</span>
                                <span class="ip-addr">{{ ip.ip }}</span>
                                <div class="ip-bar-wrap">
                                    <div class="ip-bar" :style="{ width: ip.intentos > 0 ? Math.min((ip.intentos / stats.top_ips_fallidas[0].intentos * 100), 100) + '%' : '0%' }"></div>
                                </div>
                                <span class="ip-count">{{ ip.intentos }} intentos</span>
                            </div>
                        </div>
                        <p v-else class="empty-msg">Sin intentos fallidos registrados</p>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-header"><h3 class="content-title">Usuarios Más Activos (30d)</h3></div>
                    <div class="content-body">
                        <div v-if="stats.top_usuarios_activos?.length" class="top-users">
                            <div v-for="(u, i) in stats.top_usuarios_activos" :key="u.email" class="top-user-row">
                                <div class="top-user-avatar" :class="`avatar-${i}`">{{ initials(u) }}</div>
                                <div class="top-user-info">
                                    <span class="top-user-name">{{ u.nombre }} {{ u.apellido }}</span>
                                    <span class="top-user-email">{{ u.email }}</span>
                                </div>
                                <div class="top-user-bar-wrap">
                                    <div class="top-user-bar" :style="{ width: stats.top_usuarios_activos[0].acciones > 0 ? (u.acciones / stats.top_usuarios_activos[0].acciones * 100) + '%' : '0%' }"></div>
                                </div>
                                <span class="top-user-count">{{ u.acciones }}</span>
                            </div>
                        </div>
                        <p v-else class="empty-msg">Sin actividad registrada</p>
                    </div>
                </div>
            </div>

            <div class="bottom-grid">
                <div class="content-card">
                    <div class="content-header"><h3 class="content-title">Lecturas hoy por módulo</h3></div>
                    <div class="content-body">
                        <div v-if="stats.lecturas_por_modulo?.length" class="lectura-pills">
                            <div v-for="lec in stats.lecturas_por_modulo" :key="lec.modulo" class="lectura-item">
                                <span class="lectura-modulo">{{ lec.modulo }}</span>
                                <span class="lectura-count">{{ lec.total }}</span>
                            </div>
                        </div>
                        <p v-else class="empty-msg">Sin lecturas hoy</p>
                    </div>
                </div>
                <div class="content-card wide-card">
                    <div class="content-header"><h3 class="content-title">Actividad Reciente</h3></div>
                    <div class="content-body">
                        <div class="activity-table-wrapper">
                            <table class="activity-table">
                                <thead>
                                    <tr><th>Usuario</th><th>Evento</th><th>IP</th><th>Estado</th><th>Fecha</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="actividad in stats.actividad_reciente" :key="actividad.id">
                                        <td class="user-col">{{ actividad.email_intentado || 'N/A' }}</td>
                                        <td class="event-col">{{ actividad.evento }}</td>
                                        <td class="ip-col">{{ actividad.ip || '—' }}</td>
                                        <td><span class="badge" :class="actividad.exito ? 'badge-success' : 'badge-error'">{{ actividad.exito ? 'Éxito' : 'Fallido' }}</span></td>
                                        <td class="date-col">{{ formatDate(actividad.created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Chart, registerables } from 'chart.js';
import AuthLayout from '@/Layouts/AuthLayout.vue';

Chart.register(...registerables);

const props = defineProps({ stats: Object });

const chartAccesos    = ref(null);
const chartModulos    = ref(null);
const chartTarjetas   = ref(null);
const chartCrecimiento= ref(null);
const chartHoras      = ref(null);

const tasaExito = computed(() => {
    const total = props.stats.accesos_exitosos + props.stats.accesos_fallidos;
    return total ? Math.round((props.stats.accesos_exitosos / total) * 100) : 0;
});
const pctEmailVerificado = computed(() =>
    props.stats.usuarios_total ? Math.round((props.stats.email_verificados / props.stats.usuarios_total) * 100) : 0
);
const maxRol = computed(() => Math.max(...(props.stats.usuarios_por_rol?.map(r => r.total) ?? [1])));
const pedidosActivos = computed(() => {
    const p = props.stats.pedidos_por_estado;
    return ['creado', 'aceptado', 'en_proceso', 'listo'].reduce((s, k) => s + (p[k]?.total ?? 0), 0);
});
const fechaActual = computed(() =>
    new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
);
const maxMovMonto = computed(() => Math.max(...(props.stats.movimientos_por_modulo?.map(m => m.monto) ?? [1])));
const maxPedMonto = computed(() => Math.max(...(props.stats.pedidos_por_modulo?.map(p => p.monto) ?? [1])));
const maxTipoTotal= computed(() => Math.max(...(props.stats.lecturas_por_tipo?.map(t => t.total) ?? [1])));

const resumen24hItems = computed(() => {
    const r = props.stats.resumen_24h ?? {};
    return [
        { label: 'Accesos',        val: r.accesos        ?? 0, color: 'c-blue'   },
        { label: 'Fallidos',       val: r.fallidos       ?? 0, color: 'c-red'    },
        { label: 'Lecturas',       val: r.lecturas       ?? 0, color: 'c-purple' },
        { label: 'Movimientos',    val: r.movimientos    ?? 0, color: 'c-green'  },
        { label: 'Pedidos',        val: r.pedidos        ?? 0, color: 'c-amber'  },
        { label: 'Nuevos usuarios',val: r.nuevos_usuarios ?? 0, color: 'c-cyan'  },
    ];
});

function formatDate(date) {
    return new Date(date).toLocaleString('es-MX', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
function formatMonto(val) {
    return Number(val ?? 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function estadoClass(estado) {
    return { creado:'pill-blue', aceptado:'pill-cyan', en_proceso:'pill-yellow', listo:'pill-purple', entregado:'pill-green', cancelado:'pill-red' }[estado] ?? 'pill-gray';
}
function initials(u) {
    return ((u.nombre?.[0] ?? '') + (u.apellido?.[0] ?? '')).toUpperCase();
}

onMounted(() => {
    const gc = 'rgba(30,58,138,0.18)';
    const tc = '#94a3b8';

    new Chart(chartAccesos.value, {
        type: 'line',
        data: {
            labels: props.stats.accesos_por_dia.map(d => d.dia),
            datasets: [
                { label:'Exitosos', data: props.stats.accesos_por_dia.map(d => d.exitosos), borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,0.12)', fill:true, tension:0.4, pointBackgroundColor:'#22c55e', pointRadius:4 },
                { label:'Fallidos', data: props.stats.accesos_por_dia.map(d => d.fallidos), borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,0.08)', fill:true, tension:0.4, pointBackgroundColor:'#ef4444', pointRadius:4 },
            ],
        },
        options: { responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{color:gc},ticks:{color:tc}}, y:{grid:{color:gc},ticks:{color:tc},beginAtZero:true} } },
    });

    new Chart(chartModulos.value, {
        type: 'bar',
        data: {
            labels: props.stats.actividad_por_modulo.map(m => m.modulo),
            datasets: [{ label:'Acciones', data: props.stats.actividad_por_modulo.map(m => m.total), backgroundColor:['rgba(59,130,246,0.7)','rgba(34,197,94,0.7)','rgba(245,158,11,0.7)','rgba(168,85,247,0.7)','rgba(6,182,212,0.7)','rgba(239,68,68,0.7)','rgba(249,115,22,0.7)','rgba(100,116,139,0.7)'], borderRadius:6 }],
        },
        options: { indexAxis:'y', responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{color:gc},ticks:{color:tc},beginAtZero:true}, y:{grid:{display:false},ticks:{color:tc}} } },
    });

    new Chart(chartTarjetas.value, {
        type: 'doughnut',
        data: {
            labels:['Activas','Bloqueadas','Perdidas'],
            datasets:[{ data:[props.stats.tarjetas_activas, props.stats.tarjetas_bloqueadas, props.stats.tarjetas_perdidas], backgroundColor:['rgba(34,197,94,0.8)','rgba(239,68,68,0.8)','rgba(234,179,8,0.8)'], borderColor:['#22c55e','#ef4444','#eab308'], borderWidth:1.5, hoverOffset:6 }],
        },
        options: { cutout:'68%', plugins:{legend:{display:false}} },
    });

    new Chart(chartCrecimiento.value, {
        type: 'line',
        data: {
            labels: props.stats.crecimiento_usuarios.map(d => d.dia),
            datasets:[{ label:'Nuevos', data: props.stats.crecimiento_usuarios.map(d => d.total), borderColor:'#60a5fa', backgroundColor:'rgba(96,165,250,0.15)', fill:true, tension:0.4, pointBackgroundColor:'#60a5fa', pointRadius:3 }],
        },
        options: { responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{color:gc},ticks:{color:tc,font:{size:10}}}, y:{grid:{color:gc},ticks:{color:tc},beginAtZero:true} } },
    });

    const horas = props.stats.actividad_por_hora ?? [];
    new Chart(chartHoras.value, {
        type: 'bar',
        data: {
            labels: horas.map(h => `${String(h.hora).padStart(2,'0')}h`),
            datasets:[{ label:'Acciones', data: horas.map(h => h.total), backgroundColor: horas.map(h => {
                const v = h.hora;
                if (v >= 8 && v <= 18) return 'rgba(59,130,246,0.75)';
                if (v >= 19 && v <= 22) return 'rgba(168,85,247,0.65)';
                return 'rgba(30,58,138,0.4)';
            }), borderRadius:4 }],
        },
        options: { responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{display:false},ticks:{color:tc,font:{size:9}}}, y:{grid:{color:gc},ticks:{color:tc},beginAtZero:true} } },
    });
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap');

.dashboard-container { font-family:'Inter',sans-serif; padding:2rem; min-height:100vh; background:#0f172a; }

.dashboard-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
.dashboard-title { font-size:2rem; font-weight:800; background:linear-gradient(135deg,#3b82f6,#60a5fa); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; margin-bottom:0.25rem; }
.dashboard-subtitle { color:#94a3b8; font-size:0.875rem; }
.header-meta { display:flex; align-items:center; gap:0.75rem; }
.live-dot { width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 8px #22c55e; animation:blink 1.6s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
.live-label { font-size:0.75rem; font-weight:600; color:#22c55e; }
.header-date { font-size:0.75rem; color:#475569; text-transform:capitalize; }

.resumen-24h { background:#1e293b; border:1px solid rgba(30,58,138,0.4); border-radius:0.875rem; padding:0.875rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap; }
.resumen-title { font-size:0.7rem; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.1em; white-space:nowrap; }
.resumen-items { display:flex; gap:1.5rem; flex-wrap:wrap; flex:1; }
.resumen-item { display:flex; flex-direction:column; align-items:center; gap:0.1rem; }
.resumen-val { font-size:1.25rem; font-weight:800; line-height:1; }
.resumen-lbl { font-size:0.65rem; color:#475569; text-transform:uppercase; letter-spacing:0.05em; }
.c-blue{color:#60a5fa} .c-red{color:#f87171} .c-purple{color:#c084fc} .c-green{color:#4ade80} .c-amber{color:#fbbf24} .c-cyan{color:#22d3ee}

.kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.25rem; margin-bottom:1.25rem; }
.kpi-card { background:#1e293b; border:1px solid; border-radius:1.25rem; padding:1.5rem; transition:transform 0.25s,box-shadow 0.25s; }
.kpi-card:hover { transform:translateY(-6px); box-shadow:0 20px 40px -12px rgba(0,0,0,0.5); }
.kpi-card.blue{border-color:rgba(59,130,246,0.4)} .kpi-card.green{border-color:rgba(34,197,94,0.4)} .kpi-card.red{border-color:rgba(239,68,68,0.4)} .kpi-card.purple{border-color:rgba(168,85,247,0.4)}
.kpi-top { display:flex; align-items:center; gap:0.875rem; margin-bottom:1rem; }
.kpi-icon { width:44px; height:44px; border-radius:0.875rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.kpi-icon svg { width:22px; height:22px; color:white; }
.kpi-icon.blue{background:linear-gradient(135deg,#1e40af,#3b82f6);box-shadow:0 6px 16px rgba(30,64,175,0.4)}
.kpi-icon.green{background:linear-gradient(135deg,#15803d,#22c55e);box-shadow:0 6px 16px rgba(21,128,61,0.4)}
.kpi-icon.red{background:linear-gradient(135deg,#b91c1c,#ef4444);box-shadow:0 6px 16px rgba(185,28,28,0.4)}
.kpi-icon.purple{background:linear-gradient(135deg,#7e22ce,#a855f7);box-shadow:0 6px 16px rgba(126,34,206,0.4)}
.kpi-label{color:#94a3b8;font-size:0.8rem;font-weight:500;line-height:1.3}
.kpi-number{font-size:2.75rem;font-weight:800;color:#fff;line-height:1;margin-bottom:0.75rem}
.kpi-sub{display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem}
.kpi-badge{font-size:0.7rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:999px;border:1px solid}
.kpi-badge.blue-soft{background:rgba(59,130,246,0.15);color:#93c5fd;border-color:rgba(59,130,246,0.3)}
.kpi-badge.green-soft{background:rgba(34,197,94,0.15);color:#86efac;border-color:rgba(34,197,94,0.3)}
.kpi-badge.red-soft{background:rgba(239,68,68,0.15);color:#fca5a5;border-color:rgba(239,68,68,0.3)}
.kpi-badge.yellow-soft{background:rgba(234,179,8,0.15);color:#fde68a;border-color:rgba(234,179,8,0.3)}
.kpi-badge.purple-soft{background:rgba(168,85,247,0.15);color:#d8b4fe;border-color:rgba(168,85,247,0.3)}
.kpi-link{font-size:0.8rem;font-weight:600;text-decoration:none;transition:opacity 0.2s}
.kpi-link:hover{opacity:0.75}
.kpi-link.blue{color:#60a5fa} .kpi-link.green{color:#4ade80} .kpi-link.red{color:#f87171} .kpi-link.purple{color:#c084fc}

.meta-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:0.875rem; margin-bottom:2rem; background:#1e293b; border:1px solid rgba(30,58,138,0.4); border-radius:1rem; padding:1.25rem; }
.meta-item{display:flex;align-items:center;gap:0.75rem}
.meta-item svg{width:20px;height:20px;color:#3b82f6;flex-shrink:0}
.meta-val{font-size:1.15rem;font-weight:700;color:#fff;line-height:1;display:flex;align-items:center;gap:0.4rem}
.meta-lbl{font-size:0.68rem;color:#64748b;margin-top:0.1rem}
.meta-delta{font-size:0.65rem;font-weight:600}
.meta-delta.pos{color:#4ade80} .meta-delta.neg{color:#f87171}

.section{margin-bottom:2rem}
.section-title{font-size:1.25rem;font-weight:700;color:#fff;margin-bottom:1rem}
.section-title-bar{margin-bottom:1rem;padding-bottom:0.5rem;border-bottom:1px solid rgba(30,58,138,0.3)}

.quick-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem}
.quick-card{display:flex;align-items:center;gap:1rem;padding:1.25rem 1.5rem;background:#1e293b;border:1px solid;border-radius:1rem;text-decoration:none;transition:all 0.25s}
.quick-card:hover{transform:translateX(6px)}
.quick-icon{width:36px;height:36px;flex-shrink:0}
.quick-card span{font-size:0.9375rem;font-weight:600;color:#fff}
.quick-card.primary{border-color:rgba(30,64,175,0.4)} .quick-card.primary:hover{border-color:#1E40AF;background:rgba(30,64,175,0.12)} .quick-card.primary .quick-icon{color:#3b82f6}
.quick-card.success{border-color:rgba(22,163,74,0.4)} .quick-card.success:hover{border-color:#16A34A;background:rgba(22,163,74,0.12)} .quick-card.success .quick-icon{color:#22c55e}
.quick-card.warning{border-color:rgba(245,158,11,0.4)} .quick-card.warning:hover{border-color:#F59E0B;background:rgba(245,158,11,0.12)} .quick-card.warning .quick-icon{color:#fbbf24}
.quick-card.secondary{border-color:rgba(100,116,139,0.4)} .quick-card.secondary:hover{border-color:#64748B;background:rgba(100,116,139,0.12)} .quick-card.secondary .quick-icon{color:#94a3b8}
.quick-card.info{border-color:rgba(6,182,212,0.4)} .quick-card.info:hover{border-color:#06B6D4;background:rgba(6,182,212,0.12)} .quick-card.info .quick-icon{color:#22d3ee}
.quick-card.accent{border-color:rgba(168,85,247,0.4)} .quick-card.accent:hover{border-color:#A855F7;background:rgba(168,85,247,0.12)} .quick-card.accent .quick-icon{color:#c084fc}

.charts-row{display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;margin-bottom:1.25rem}
.chart-card{background:#1e293b;border:1px solid rgba(30,58,138,0.35);border-radius:1.25rem;padding:1.5rem}
.chart-card.wide{grid-column:auto}
.chart-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem}
.chart-title{font-size:0.9375rem;font-weight:700;color:#fff}
.chart-sub{font-size:0.7rem;color:#475569}
.chart-legend{display:flex;align-items:center;gap:0.875rem;font-size:0.75rem;color:#94a3b8}
.legend-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:4px}
.legend-dot.green{background:#22c55e} .legend-dot.red{background:#ef4444}

.content-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;margin-bottom:1.5rem}
.content-card{background:#1e293b;border:1px solid rgba(30,58,138,0.3);border-radius:1.25rem;overflow:hidden}
.content-header{padding:1.25rem 1.5rem;border-bottom:1px solid rgba(30,58,138,0.25);display:flex;align-items:center;justify-content:space-between}
.content-title{font-size:0.9375rem;font-weight:700;color:#fff}
.content-body{padding:1.25rem 1.5rem}
.center-content .content-body{display:flex;flex-direction:column;align-items:center;gap:1rem}
.header-badge{font-size:0.65rem;font-weight:700;padding:0.2rem 0.6rem;border-radius:999px}
.header-badge.red{background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.3)}

.roles-list{display:flex;flex-direction:column;gap:0.75rem}
.role-row{display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;background:#0f172a;border:1px solid rgba(59,130,246,0.15);border-radius:0.75rem;transition:all 0.2s}
.role-row:hover{border-color:rgba(59,130,246,0.4);transform:translateX(3px)}
.role-name{font-size:0.875rem;font-weight:600;color:#60a5fa;text-transform:capitalize;min-width:90px}
.role-bar-wrap{flex:1;height:6px;background:rgba(30,58,138,0.3);border-radius:999px;overflow:hidden}
.role-bar{height:100%;background:linear-gradient(90deg,#1e40af,#3b82f6);border-radius:999px;transition:width 0.6s}
.role-stats{display:flex;align-items:baseline;gap:4px}
.role-count{font-size:1.1rem;font-weight:800;color:#fff}
.role-label{font-size:0.7rem;color:#64748b}

.donut-body{flex-direction:row !important;flex-wrap:wrap;justify-content:center;gap:1.5rem !important}
.donut-legend{display:flex;flex-direction:column;gap:0.625rem;justify-content:center}
.donut-item{display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:#94a3b8}
.donut-item strong{color:#fff;margin-left:auto;min-width:28px;text-align:right}
.dleg{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.dleg.green{background:#22c55e} .dleg.red{background:#ef4444} .dleg.yellow{background:#eab308}

.pedidos-list{display:flex;flex-direction:column;gap:0.625rem}
.pedido-row{display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid rgba(30,58,138,0.15)}
.pedido-row:last-child{border-bottom:none}
.pedido-count{font-size:1.1rem;font-weight:700;color:#fff}
.estado-pill{font-size:0.7rem;font-weight:700;padding:0.25rem 0.75rem;border-radius:999px;border:1px solid;text-transform:capitalize}
.pill-blue{background:rgba(59,130,246,0.15);color:#93c5fd;border-color:rgba(59,130,246,0.35)}
.pill-cyan{background:rgba(6,182,212,0.15);color:#67e8f9;border-color:rgba(6,182,212,0.35)}
.pill-yellow{background:rgba(234,179,8,0.15);color:#fde68a;border-color:rgba(234,179,8,0.35)}
.pill-purple{background:rgba(168,85,247,0.15);color:#d8b4fe;border-color:rgba(168,85,247,0.35)}
.pill-green{background:rgba(34,197,94,0.15);color:#86efac;border-color:rgba(34,197,94,0.35)}
.pill-red{background:rgba(239,68,68,0.15);color:#fca5a5;border-color:rgba(239,68,68,0.35)}
.pill-gray{background:rgba(100,116,139,0.15);color:#cbd5e1;border-color:rgba(100,116,139,0.35)}

.finance-row{display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem}
.finance-item{background:#0f172a;border:1px solid;border-radius:0.75rem;padding:0.875rem;display:flex;flex-direction:column;gap:0.25rem}
.finance-item.green{border-color:rgba(34,197,94,0.25)} .finance-item.red{border-color:rgba(239,68,68,0.25)}
.finance-label{font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#64748b}
.finance-val{font-size:1.2rem;font-weight:800;color:#fff}
.finance-item.green .finance-val{color:#4ade80} .finance-item.red .finance-val{color:#f87171}
.finance-count{font-size:0.7rem;color:#475569}
.divider-line{height:1px;background:rgba(30,58,138,0.3);margin:0.875rem 0}
.sub-title{font-size:0.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.625rem}

.mod-list{display:flex;flex-direction:column;gap:0.5rem}
.mod-row{display:flex;align-items:center;gap:0.625rem}
.mod-name{font-size:0.78rem;color:#94a3b8;min-width:80px;text-transform:capitalize}
.mod-bar-wrap{flex:1;height:6px;background:rgba(30,58,138,0.3);border-radius:999px;overflow:hidden}
.mod-bar{height:100%;background:linear-gradient(90deg,#1e40af,#3b82f6);border-radius:999px;transition:width 0.6s}
.mod-bar.amber{background:linear-gradient(90deg,#b45309,#f59e0b)}
.mod-val{font-size:0.78rem;font-weight:700;color:#fff;white-space:nowrap}
.mod-count{font-size:0.68rem;color:#475569;white-space:nowrap}
.mod-info{display:flex;flex-direction:column;align-items:flex-end;min-width:60px}

.tarjeta-confirm{display:flex;flex-direction:column;gap:0.5rem}
.confirm-item{display:flex;align-items:center;gap:0.5rem;font-size:0.8rem}
.confirm-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.confirm-dot.green{background:#22c55e} .confirm-dot.gray{background:#475569}
.confirm-label{color:#94a3b8;flex:1}
.confirm-val{font-weight:700;color:#fff}

.tipo-list{display:flex;flex-direction:column;gap:0.75rem}
.tipo-row{display:flex;align-items:center;gap:0.625rem}
.tipo-info{min-width:140px;display:flex;flex-direction:column}
.tipo-name{font-size:0.78rem;color:#cbd5e1;text-transform:capitalize}
.tipo-pct{font-size:0.65rem;color:#475569}
.tipo-bar-wrap{flex:1}
.tipo-bar-bg{height:6px;background:rgba(30,58,138,0.3);border-radius:999px;overflow:hidden}
.tipo-bar-fill{height:100%;background:linear-gradient(90deg,#7e22ce,#a855f7);border-radius:999px;transition:width 0.6s}
.tipo-total{font-size:0.85rem;font-weight:700;color:#fff;min-width:32px;text-align:right}

.eventos-list{display:flex;flex-direction:column;gap:0.75rem}
.evento-row{display:flex;flex-direction:column;gap:0.3rem}
.evento-info{display:flex;justify-content:space-between;align-items:center}
.evento-name{font-size:0.8rem;color:#cbd5e1;font-weight:500}
.evento-tasa{font-size:0.7rem;font-weight:700}
.evento-tasa.green{color:#4ade80} .evento-tasa.red{color:#f87171} .evento-tasa.yellow{color:#fbbf24}
.evento-bar-wrap{width:100%}
.evento-bar-bg{height:5px;background:rgba(30,58,138,0.3);border-radius:999px;overflow:hidden}
.evento-bar-fill{height:100%;border-radius:999px;transition:width 0.6s}
.evento-bar-fill.success{background:linear-gradient(90deg,#15803d,#22c55e)}

.ip-list{display:flex;flex-direction:column;gap:0.625rem}
.ip-row{display:flex;align-items:center;gap:0.625rem}
.ip-rank{font-size:0.7rem;font-weight:800;min-width:22px;text-align:center}
.ip-rank.rank-1{color:#f87171} .ip-rank.rank-n{color:#475569}
.ip-addr{font-family:monospace;font-size:0.78rem;color:#94a3b8;min-width:110px}
.ip-bar-wrap{flex:1;height:5px;background:rgba(30,58,138,0.3);border-radius:999px;overflow:hidden}
.ip-bar{height:100%;background:linear-gradient(90deg,#b91c1c,#ef4444);border-radius:999px;transition:width 0.6s}
.ip-count{font-size:0.75rem;font-weight:700;color:#f87171;white-space:nowrap}

.top-users{display:flex;flex-direction:column;gap:0.75rem}
.top-user-row{display:flex;align-items:center;gap:0.75rem}
.top-user-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:800;flex-shrink:0}
.avatar-0{background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff}
.avatar-1{background:linear-gradient(135deg,#15803d,#22c55e);color:#fff}
.avatar-2{background:linear-gradient(135deg,#7e22ce,#a855f7);color:#fff}
.avatar-3{background:linear-gradient(135deg,#b45309,#f59e0b);color:#fff}
.avatar-4{background:linear-gradient(135deg,#0e7490,#06b6d4);color:#fff}
.top-user-info{display:flex;flex-direction:column;min-width:0;flex:0 0 130px}
.top-user-name{font-size:0.78rem;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.top-user-email{font-size:0.65rem;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.top-user-bar-wrap{flex:1;height:5px;background:rgba(30,58,138,0.3);border-radius:999px;overflow:hidden}
.top-user-bar{height:100%;background:linear-gradient(90deg,#1e40af,#60a5fa);border-radius:999px;transition:width 0.6s}
.top-user-count{font-size:0.85rem;font-weight:700;color:#fff;min-width:28px;text-align:right}

.bottom-grid{display:grid;grid-template-columns:1fr 2fr;gap:1.25rem;margin-bottom:1.5rem}
.lectura-pills{display:flex;flex-direction:column;gap:0.625rem}
.lectura-item{display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0.875rem;background:#0f172a;border:1px solid rgba(59,130,246,0.15);border-radius:0.625rem}
.lectura-modulo{font-size:0.8rem;color:#94a3b8;text-transform:capitalize}
.lectura-count{font-size:1rem;font-weight:700;color:#fff}

.activity-table-wrapper{overflow-x:auto}
.activity-table{width:100%;border-collapse:collapse}
.activity-table thead{background:#0f172a}
.activity-table th{padding:0.75rem 1rem;text-align:left;font-size:0.7rem;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:0.05em}
.activity-table tbody tr{border-bottom:1px solid rgba(30,58,138,0.15);transition:background 0.15s}
.activity-table tbody tr:hover{background:#0f172a}
.activity-table td{padding:0.75rem 1rem;font-size:0.85rem}
.user-col{color:#fff;font-weight:500;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.event-col{color:#cbd5e1}
.ip-col{color:#64748b;font-family:monospace;font-size:0.8rem}
.date-col{color:#64748b;font-size:0.8rem;white-space:nowrap}
.badge{display:inline-flex;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.7rem;font-weight:700}
.badge-success{background:rgba(22,163,74,0.2);color:#86efac;border:1px solid rgba(34,197,94,0.35)}
.badge-error{background:rgba(220,38,38,0.2);color:#fca5a5;border:1px solid rgba(239,68,68,0.35)}
.empty-msg{color:#475569;font-size:0.875rem;padding:1rem 0;text-align:center}

@media(max-width:1200px){.charts-row{grid-template-columns:1fr}.bottom-grid{grid-template-columns:1fr}}
@media(max-width:768px){.dashboard-container{padding:1rem}.kpi-grid{grid-template-columns:1fr 1fr}.meta-grid{grid-template-columns:1fr 1fr}.content-grid{grid-template-columns:1fr}.quick-grid{grid-template-columns:1fr 1fr}.resumen-items{gap:1rem}}
@media(max-width:480px){.kpi-grid{grid-template-columns:1fr}.meta-grid{grid-template-columns:1fr}.quick-grid{grid-template-columns:1fr}}
</style>