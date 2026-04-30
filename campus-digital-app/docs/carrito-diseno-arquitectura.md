# Módulo Carrito — Documento de Diseño de Arquitectura

> Versión 1.2 · 2026-04-29  
> Equipo: Módulo Carrito — Proyecto Integrador CampusDigital  
> Stack: Laravel 11 · Inertia.js · Vue 3 · Axios · PHPUnit

---

## Índice

1. [Arquitectura General](#1-arquitectura-general)
2. [Modelo de Datos](#2-modelo-de-datos)
3. [Especificación de Endpoints](#3-especificación-de-endpoints)
4. [Diseño del JWT](#4-diseño-del-jwt)
5. [Contrato del Control Vue](#5-contrato-del-control-vue)
6. [Plan de Pruebas PHPUnit](#6-plan-de-pruebas-phpunit)
7. [Riesgos de Seguridad y Mitigaciones](#7-riesgos-de-seguridad-y-mitigaciones)

---

## Changelog v1.2

| # | Cambio | Secciones tocadas |
|---|--------|-------------------|
| C7 | Contrato definitivo con módulo Saldo: 4 endpoints, auth interna, ciclo reservar/confirmar/liberar | Anexo C (nuevo), SaldoClient, CheckoutService, ReintentaConciliacion |

**C7 — Contrato definitivo de integración con módulo Saldo.** Se amplía `SaldoClient` con cuatro métodos (`reservar`, `confirmar`, `liberar`, `cargoForzoso`). `CheckoutService::confirmar()` ahora cierra el ciclo reservar → confirmar con rollback automático vía `liberar()` en caso de excepción. `ReintentaConciliacion` usa `cargoForzoso()` directamente (no `reservar()`) porque el servicio ya fue entregado. Todas las llamadas llevan `X-Internal-Token`. Ver contrato completo en **Anexo C**.

---

## Changelog v1.1

| # | Cambio | Secciones tocadas |
|---|--------|-------------------|
| C1 | Backend proxy obligatorio | 1.3, 1.4, 5.2, 5.5, 5.6 (nueva), 7.9 (nueva) |
| C2 | Visualización one-time del JWT | 2.2 tokens_modulo, 4.7 (nueva), 7.1 |
| C3 | Cantidad incremental en ítem duplicado | 3.2 POST items, 6.2 caso #16 |
| C4 | Rotación obligatoria del refresh token | 3.2 refresh, 4.3, 6.4, 7.6 |
| C5 | Integración Saldo: HTTP interna + escrow | 1.3, 2.2 carritos, 2.2 conciliaciones (nueva), 3.2 checkout, 6.2, 7.10 (nueva) |
| C6 | Reemplazar `parent_jti` por `pair_jti` + `replaces_jti` | 2.2 tokens_modulo, 4.2, 4.6 (nueva subsección) |

**C1 — Backend del módulo cliente como proxy obligatorio.** El JWT del módulo deja de viajar al navegador. El control Vue ahora apunta a rutas proxy del propio backend del módulo cliente, que sostiene el token en configuración/sesión y agrega el header `Authorization`. Para facilitar la adopción, el módulo Carrito provee el trait reutilizable `ConsumesCartApi`. Se elimina la prop `moduleToken` del componente y se ajusta `apiBaseUrl`.

**C2 — Visualización one-time del JWT en el panel admin.** El JWT recién emitido se muestra una única vez en el panel interno, en un campo `type="password"` con auto-ocultado a los 30 segundos. Tras la primera entrega, el sistema marca `entregado_at` en BD y nunca vuelve a serializar el JWT. Sigue el mismo modelo que AWS IAM secret keys y GitHub PATs.

**C3 — Cantidad incremental al agregar ítem duplicado.** `POST .../items` deja de responder 409 si el ítem ya existe: ahora incrementa la cantidad del ítem existente y valida que no supere `cantidad_maxima`. El 409 queda reservado para estados terminales del carrito.

**C4 — Rotación obligatoria del refresh token.** Cada uso del refresh token emite un par access+refresh nuevo y revoca el par anterior. Si llega un refresh ya rotado (jti revocado con motivo `rotacion`), se interpreta como posible robo y se revoca toda la cadena del módulo.

**C5 — Integración con módulo Saldo: HTTP interna + escrow por categoría.** El checkout llama al módulo Saldo vía HTTP interna. Si Saldo no responde, el comportamiento depende de la regla `permite_pago_diferido` de la categoría: falla con 503 (false) o pasa a estado `confirmado_pendiente_conciliacion` (true), con topes de exposición por usuario y globales, y reconciliación por Job en cola.

**C6 — Columnas `pair_jti` + `replaces_jti` en lugar de `parent_jti`.** La columna ambigua `parent_jti` se reemplaza por dos columnas precisas: `pair_jti` (token compañero emitido en el mismo par) y `replaces_jti` (token anterior en la cadena de rotación). El claim `access_jti` se elimina del payload del refresh token ya que ese pareo ahora vive en BD.

---

## 1. Arquitectura General

### 1.1 Actores del sistema

| Actor | Descripción |
|-------|-------------|
| **Admin Carrito** | Miembro del equipo que opera el panel interno (aprueba/rechaza solicitudes, revoca tokens) |
| **Módulo Cliente** | Cualquier módulo del sistema (Biblioteca, Cafetería, etc.) que consume la API del carrito |
| **Usuario Final** | El alumno/empleado que interactúa con el carrito a través de la UI del módulo cliente |
| **API Pública** | Tres endpoints sin autenticación para descubrimiento y onboarding |
| **API Privada** | Todos los endpoints de operación del carrito, protegidos con JWT de módulo |
| **Panel Interno** | SPA Inertia+Vue protegida por auth Laravel normal, solo para el equipo Carrito |

### 1.2 Diagrama de flujo — Onboarding de un módulo nuevo

```
Equipo Módulo Cliente
        │
        ▼
POST /api/public/modulos/solicitud
        │  ← devuelve folio
        ▼
GET  /api/public/modulos/solicitud/{folio}/estado
        │  (polling hasta aprobación)
        │
        ▼  aprobación MANUAL
┌──────────────────────────────┐
│  Panel Interno Admin Carrito │
│  (Inertia + Vue)             │
│  [Aprobar] o [Rechazar]      │
└───────────┬──────────────────┘
            │ aprobado
            ▼
  Emisión de access_token + refresh_token (JWT HS256)
  → guardados en tokens_modulo (solo jti + metadata)
  → JWT en claro entregado UNA sola vez al admin para
    que lo comparta de forma segura al equipo cliente
```

### 1.3 Diagrama de flujo — Operación normal del carrito

> [v1.1 — C1] El JWT ya no viaja al navegador. El control Vue habla con el proxy del backend del módulo cliente, que agrega el header Authorization internamente. [v1.1 — C5] El checkout llama al módulo Saldo vía HTTP interna.

```
┌─────────────────────────────────────────────────────┐
│  Navegador del Usuario Final                        │
│                                                     │
│  <CartControl                                       │
│    :user-ref="matricula"                            │
│    :config="cartConfig"      ← reglas del módulo   │
│    :api-base-url="'/biblioteca/cart-proxy'"         │
│    @checkout-success="..."                          │
│    @checkout-error="..."                            │
│  />                                                 │
│  (importado de @/Modules/Cart/Control/index.js)     │
└─────────────┬───────────────────────────────────────┘
              │  Axios → /biblioteca/cart-proxy/*
              │  (SIN JWT, solo sesión de usuario)
              ▼
┌─────────────────────────────────────────────────────┐
│  Backend del Módulo Cliente (ej. Biblioteca)        │
│  · Usa trait ConsumesCartApi                        │
│  · Lee JWT de config('cart.module_token')           │
│  · Expone rutas /biblioteca/cart-proxy/*            │
└─────────────┬───────────────────────────────────────┘
              │  Http::withToken($jwt) → /api/cart/*
              │  Authorization: Bearer <jwt>  [server-to-server]
              ▼
┌─────────────────────────────────────────────────────┐
│  Middleware auth.module.jwt                         │
│  1. Verifica firma HS256                            │
│  2. Verifica exp / iat                              │
│  3. Verifica jti no revocado (tabla tokens_modulo)  │
│  4. Verifica categoría ∈ categorias_autorizadas     │
└─────────────┬───────────────────────────────────────┘
              │  pasa
              ▼
┌─────────────────────────────────────────────────────┐
│  API Privada del Carrito                            │
│  · CartController                                   │
│  · ItemController                                   │
│  · CheckoutController ──────────────────────────┐  │
│  · TokenController (refresh)                    │  │
└─────────────┬───────────────────────────────────┼──┘
              │                                   │ HTTP interna [v1.1 — C5]
              ▼                                   ▼
┌─────────────────────────┐   ┌────────────────────────────────┐
│  Base de datos (MySQL)  │   │  Módulo Saldo Digital          │
│  + Bitácora de acciones │   │  POST /api/internal/saldo/     │
│  + conciliaciones_      │   │  reservar (hold de fondos)     │
│    pendientes           │   │  (si requiere_saldo=true)      │
└─────────────────────────┘   └────────────────────────────────┘
```

### 1.4 Separación de responsabilidades

> [v1.1 — C1, C5]

| Capa | Responsable |
|------|-------------|
| Lógica de negocio del carrito | Backend Laravel — API privada del módulo Carrito |
| Autenticación y autorización de módulos | Middleware `auth.module.jwt` |
| **Custodia del JWT de módulo** | **Backend del módulo cliente** (nunca el navegador) |
| **Proxy hacia la API Carrito** | **Backend del módulo cliente** usando trait `ConsumesCartApi` |
| Presentación y orquestación de llamadas | Control Vue `CartControl.vue` (habla con el proxy, no con la API Carrito directamente) |
| Gestión del ciclo de vida de módulos | Panel interno Admin |
| Configuración específica por módulo | Props del control Vue (`config`) |
| **Validación y reserva de saldo** | **Módulo Saldo Digital** vía HTTP interna server-to-server |

---

## 2. Modelo de Datos

### 2.1 Diagrama ER (texto)

```
categorias ──< reglas_categoria
    │
    └──< solicitudes_modulo
              │
              ▼ (aprobación)
         modulos_clientes ──< tokens_modulo (jti)
              │
              └──< carritos ──< items_carrito
                       │
                       └──< bitacora
```

### 2.2 Tablas

---

#### `categorias`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | |
| `slug` | VARCHAR(60) UNIQUE | `producto`, `servicio`, `reserva`, `prestamo`, `ticket` |
| `nombre` | VARCHAR(120) | Nombre legible |
| `descripcion` | TEXT NULL | |
| `activa` | BOOLEAN | Soft-disable |
| `created_at` / `updated_at` | TIMESTAMP | |

---

#### `reglas_categoria`

Reglas base por categoría (pueden ser sobreescritas por configuración del módulo a nivel payload JWT).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | |
| `categoria_id` | BIGINT FK → categorias | |
| `clave` | VARCHAR(80) | Ej: `cantidad_maxima`, `requiere_saldo`, `permite_devolucion`, `duracion_maxima_horas` |
| `valor` | VARCHAR(255) | Valor por defecto como string/json |
| `tipo_dato` | ENUM(`int`,`bool`,`string`,`json`) | Para castear al leer |
| `created_at` / `updated_at` | TIMESTAMP | |

**Índice:** `UNIQUE(categoria_id, clave)`

---

#### `solicitudes_modulo`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | |
| `folio` | CHAR(12) UNIQUE | Generado aleatoriamente (UUID corto) |
| `nombre_modulo` | VARCHAR(120) | |
| `tipo_modulo` | VARCHAR(60) | Ej: `biblioteca`, `cafeteria`, `copias`, `souvenirs` |
| `categorias_solicitadas` | JSON | Array de slugs de categorías |
| `contacto_nombre` | VARCHAR(120) | |
| `contacto_email` | VARCHAR(180) | |
| `descripcion` | TEXT NULL | |
| `estado` | ENUM(`pendiente`,`aprobada`,`rechazada`) | Default: `pendiente` |
| `motivo_rechazo` | TEXT NULL | Llenado al rechazar |
| `revisado_por` | BIGINT FK → users NULL | Admin que aprobó/rechazó |
| `revisado_at` | TIMESTAMP NULL | |
| `created_at` / `updated_at` | TIMESTAMP | |

---

#### `modulos_clientes`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | |
| `solicitud_id` | BIGINT FK → solicitudes_modulo | |
| `nombre` | VARCHAR(120) | |
| `slug` | VARCHAR(60) UNIQUE | Ej: `biblioteca`, `cafeteria` |
| `tipo_modulo` | VARCHAR(60) | |
| `categorias_autorizadas` | JSON | Array de slugs aprobados |
| `activo` | BOOLEAN | Puede desactivarse sin revocar tokens |
| `created_at` / `updated_at` | TIMESTAMP | |

---

#### `tokens_modulo`

Registro de cada JWT emitido. **Nunca se guarda el JWT en claro**, solo metadatos.

> [v1.1 — C2] Columna `entregado_at` para control de entrega one-time.  
> [v1.1 — C6] `parent_jti` reemplazado por `pair_jti` + `replaces_jti`.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | |
| `modulo_id` | BIGINT FK → modulos_clientes | |
| `jti` | CHAR(36) UNIQUE | UUID v4, el claim `jti` del JWT |
| `tipo` | ENUM(`access`,`refresh`) | |
| `estado` | ENUM(`activo`,`revocado`,`expirado`) | Default: `activo` |
| `emitido_at` | TIMESTAMP | Corresponde al claim `iat` |
| `expira_at` | TIMESTAMP | Corresponde al claim `exp` |
| `entregado_at` | TIMESTAMP NULL | **[v1.1 — C2]** Momento en que el JWT se mostró por primera vez en el panel. NULL = aún no entregado. |
| `revocado_at` | TIMESTAMP NULL | |
| `revocado_por` | BIGINT FK → users NULL | |
| `motivo_revocacion` | VARCHAR(255) NULL | Ej: `manual`, `rotacion`, `compromiso_secreto` |
| `pair_jti` | CHAR(36) NULL | **[v1.1 — C6]** jti del token compañero emitido en el mismo par (access ↔ refresh). |
| `replaces_jti` | CHAR(36) NULL | **[v1.1 — C6]** jti del token anterior del mismo tipo en la cadena de rotación. NULL en el par inicial. |
| `created_at` / `updated_at` | TIMESTAMP | |

**Índice:** `INDEX(modulo_id, estado)`, `INDEX(expira_at)`, `INDEX(pair_jti)`, `INDEX(replaces_jti)`

**Ejemplo de cadena de rotación [v1.1 — C6]:**

| jti | tipo | pair_jti | replaces_jti | estado | nota |
|-----|------|----------|--------------|--------|------|
| A1 | access | R1 | NULL | revocado | par inicial |
| R1 | refresh | A1 | NULL | revocado | par inicial |
| A2 | access | R2 | A1 | activo | tras primer refresh |
| R2 | refresh | A2 | R1 | activo | tras primer refresh |

---

#### `carritos`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | |
| `uuid` | CHAR(36) UNIQUE | Identificador público del carrito |
| `modulo_id` | BIGINT FK → modulos_clientes | Módulo propietario |
| `usuario_ref` | VARCHAR(120) | ID/matrícula del usuario final (opaco, manejado por el módulo cliente) |
| `estado` | ENUM(`abierto`,`confirmado`,`cancelado`,`expirado`,`confirmado_pendiente_conciliacion`,`revertido`) | **[v1.1 — C5]** Nuevos estados para flujo de Saldo con pago diferido. |
| `requiere_saldo` | BOOLEAN | Según configuración del módulo |
| `total` | DECIMAL(10,2) | Calculado en backend al agregar/quitar ítems |
| `metadata` | JSON NULL | Datos adicionales del módulo cliente (p. ej. sala, horario) |
| `expira_at` | TIMESTAMP NULL | Carrito de sesión temporal |
| `confirmed_at` | TIMESTAMP NULL | |
| `cancelled_at` | TIMESTAMP NULL | |
| `created_at` / `updated_at` | TIMESTAMP | |

**Índice:** `INDEX(modulo_id, estado)`, `INDEX(usuario_ref)`, `INDEX(expira_at)`

---

#### `items_carrito`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | |
| `carrito_id` | BIGINT FK → carritos | |
| `categoria_id` | BIGINT FK → categorias | Tipo de ítem |
| `referencia_externa` | VARCHAR(180) | ID del recurso en el módulo cliente (libro_id, producto_id, etc.) |
| `nombre` | VARCHAR(255) | Snapshot del nombre al momento de agregar |
| `precio_unitario` | DECIMAL(10,2) | Snapshot del precio |
| `cantidad` | INT UNSIGNED | |
| `duracion_horas` | INT NULL | Para reservas/rentas |
| `estado_item` | ENUM(`activo`,`removido`,`devuelto`) | |
| `metadata` | JSON NULL | Datos adicionales (fecha reserva, asiento, etc.) |
| `added_at` | TIMESTAMP | |
| `removed_at` | TIMESTAMP NULL | |
| `created_at` / `updated_at` | TIMESTAMP | |

---

#### `bitacora`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | |
| `accion` | VARCHAR(80) | Ej: `modulo.solicitud`, `token.emision`, `token.revocacion`, `carrito.creado`, `carrito.checkout`, `carrito.cancelado`, `auth.fallo` |
| `modulo_id` | BIGINT FK → modulos_clientes NULL | |
| `user_id` | BIGINT FK → users NULL | Admin si aplica |
| `jti` | CHAR(36) NULL | Token involucrado |
| `carrito_uuid` | CHAR(36) NULL | |
| `ip_address` | VARCHAR(45) NULL | |
| `payload` | JSON NULL | Datos relevantes de la acción (sin datos sensibles) |
| `created_at` | TIMESTAMP | Solo insert, sin update |

**Índice:** `INDEX(accion)`, `INDEX(modulo_id, created_at)`, `INDEX(created_at)`

> [v1.1 — C2] Nuevas acciones de bitácora: `token.visualizado` (con `user_id` e `ip_address` del admin).

---

#### `conciliaciones_pendientes` [v1.1 — C5]

Registro de checkouts en estado `confirmado_pendiente_conciliacion` que esperan cargo en Saldo.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | |
| `carrito_uuid` | CHAR(36) FK → carritos.uuid | |
| `modulo_id` | BIGINT FK → modulos_clientes | |
| `usuario_ref` | VARCHAR(120) | Snapshot del usuario al momento del checkout |
| `monto` | DECIMAL(10,2) | Total a cobrar |
| `intentos` | INT UNSIGNED | Default: 0. Incrementa en cada reintento del job |
| `ultimo_intento_at` | TIMESTAMP NULL | |
| `proximo_intento_at` | TIMESTAMP NULL | Calculado con backoff exponencial |
| `estado_conciliacion` | ENUM(`pendiente`,`exitosa`,`deuda`,`requiere_revision_manual`) | |
| `respuesta_saldo` | JSON NULL | Última respuesta del módulo Saldo (sin datos sensibles) |
| `created_at` / `updated_at` | TIMESTAMP | |

**Regla de negocio:**
- Tope por usuario: máximo $200 MXN acumulados en `pendiente`. Si se supera, nuevos checkouts diferidos se rechazan con 503.
- Tope global configurable (ej. $50,000 MXN en `pendiente`). Configurable en `config/cart.php`.
- Backoff exponencial: 5 min → 15 min → 1 h → 4 h → manual.
- Si Saldo confirma falta de fondos al conciliar: `estado_conciliacion = 'deuda'`, carrito pasa a `revertido`.

**Índice:** `INDEX(estado_conciliacion, proximo_intento_at)`, `INDEX(usuario_ref, estado_conciliacion)`

---

## 3. Especificación de Endpoints

### 3.1 APIs Públicas (sin autenticación)

---

#### `GET /api/public/categorias`

Lista las categorías de carrito disponibles y sus reglas base.

**Request:** ninguno

**Response 200:**
```json
{
  "data": [
    {
      "slug": "producto",
      "nombre": "Producto Físico",
      "descripcion": "Artículo de inventario con stock",
      "reglas_base": {
        "cantidad_maxima": 10,
        "requiere_saldo": true,
        "permite_devolucion": false
      }
    },
    {
      "slug": "reserva",
      "nombre": "Reserva Temporal",
      "descripcion": "Reserva de espacio, equipo o sala",
      "reglas_base": {
        "cantidad_maxima": 1,
        "requiere_saldo": false,
        "permite_devolucion": true,
        "duracion_maxima_horas": 4
      }
    }
    // ...
  ]
}
```

---

#### `POST /api/public/modulos/solicitud`

Registra una solicitud de alta de módulo.

**Request body:**
```json
{
  "nombre_modulo": "Biblioteca Central",
  "tipo_modulo": "biblioteca",
  "categorias_solicitadas": ["prestamo", "reserva"],
  "contacto_nombre": "Ana García",
  "contacto_email": "ana@universidad.edu",
  "descripcion": "Módulo de préstamo y reserva de materiales bibliográficos"
}
```

**Validaciones:**
- `nombre_modulo`: required, string, max:120
- `tipo_modulo`: required, string, max:60
- `categorias_solicitadas`: required, array, min:1, cada slug debe existir en `categorias`
- `contacto_nombre`: required, string, max:120
- `contacto_email`: required, email, max:180

**Response 201:**
```json
{
  "folio": "BIB-2A9F3C",
  "mensaje": "Solicitud registrada. Recibirás notificación cuando sea revisada.",
  "estado": "pendiente"
}
```

**Errores:**

| Código | Condición |
|--------|-----------|
| 422 | Validación fallida |
| 409 | Ya existe solicitud pendiente/aprobada con ese `tipo_modulo` |

---

#### `GET /api/public/modulos/solicitud/{folio}/estado`

Consulta el estado de una solicitud sin exponer datos sensibles.

**Response 200:**
```json
{
  "folio": "BIB-2A9F3C",
  "estado": "pendiente",
  "creada_at": "2026-04-28T10:00:00Z"
}
```

En estado `rechazada`, incluye `motivo_rechazo` (texto genérico, no interno).

**Errores:**

| Código | Condición |
|--------|-----------|
| 404 | Folio no encontrado |

---

### 3.2 APIs Privadas (requieren `Authorization: Bearer <access_token>`)

Todas las respuestas de error de autenticación siguen el mismo esquema:

```json
{
  "error": "TOKEN_EXPIRED | TOKEN_INVALID | TOKEN_REVOKED | SCOPE_DENIED",
  "mensaje": "descripción legible"
}
```

---

#### `POST /api/cart/tokens/refresh`

> [v1.1 — C4] Rotación obligatoria: emite par nuevo y revoca el par anterior. Detecta reuso de refresh ya rotado.

Emite un nuevo par access+refresh usando el refresh token actual. El refresh token es de **un solo uso**.

**Request body:**
```json
{
  "refresh_token": "<jwt refresh>"
}
```

**Response 200:**
```json
{
  "access_token": "<nuevo access jwt>",
  "refresh_token": "<nuevo refresh jwt>",
  "expires_in": 3600
}
```

**Reglas de rotación:**
1. Valida que `refresh_token` sea un JWT con `token_type == "refresh"`, firma válida y no expirado.
2. Busca el `jti` en `tokens_modulo`. Si `estado == 'revocado'` con `motivo_revocacion == 'rotacion'`: **robo probable** → revoca TODOS los tokens activos del `modulo_id`, registra `token.reuso_detectado` en bitácora, responde 401.
3. Si `estado == 'activo'`: emite par nuevo (A2, R2) con `pair_jti` y `replaces_jti` correctos.
4. Marca el par viejo (A1, R1 via `pair_jti`) como `revocado` con `motivo_revocacion = 'rotacion'`.

**Errores:**

| Código | Condición |
|--------|-----------|
| 401 | Refresh token inválido, expirado, o de tipo incorrecto |
| 401 | jti revocado por rotación (reuso detectado — toda la cadena revocada) |

---

#### `POST /api/cart/carritos`

Crea un carrito de sesión para un usuario del módulo.

**Request body:**
```json
{
  "usuario_ref": "MAT-2024-001",
  "requiere_saldo": true,
  "expira_en_minutos": 30,
  "metadata": {}
}
```

**Response 201:**
```json
{
  "carrito_uuid": "550e8400-e29b-41d4-a716-446655440000",
  "estado": "abierto",
  "modulo": "biblioteca",
  "total": "0.00",
  "items": [],
  "expira_at": "2026-04-28T11:00:00Z"
}
```

**Errores:**

| Código | Condición |
|--------|-----------|
| 401 | JWT inválido |
| 403 | Módulo inactivo |
| 422 | Validación |

---

#### `GET /api/cart/carritos/{uuid}`

Consulta el carrito actual.

**Response 200:** Mismo esquema que la creación, incluyendo `items` detallados.

**Errores:**

| Código | Condición |
|--------|-----------|
| 403 | El carrito no pertenece al módulo autenticado |
| 404 | Carrito no encontrado |

---

#### `POST /api/cart/carritos/{uuid}/items`

Agrega un ítem al carrito.

**Request body:**
```json
{
  "categoria_slug": "prestamo",
  "referencia_externa": "LIBRO-ISBN-978-3-16-148410-0",
  "nombre": "Cálculo Diferencial — Stewart",
  "precio_unitario": 0.00,
  "cantidad": 1,
  "duracion_horas": 72,
  "metadata": { "fecha_devolucion": "2026-05-01" }
}
```

**Validaciones de negocio aplicadas en backend:**
- `categoria_slug` debe estar en `categorias_autorizadas` del JWT
- `cantidad` <= `cantidad_maxima` de la regla de la categoría (o la cantidad resultante si es incremento)
- `duracion_horas` <= `duracion_maxima_horas` si aplica
- Si `requiere_saldo`: valida saldo del monedero contra total proyectado

**[v1.1 — C3] Semántica de ítem duplicado:**
- Si NO existe ítem con la misma `referencia_externa` y `estado_item='activo'`: **crea** ítem nuevo.
- Si YA existe: **incrementa** `cantidad` del existente sumando la `cantidad` recibida. Si la cantidad resultante supera `cantidad_maxima`, responde 422.

**Response 200/201:**
```json
{
  "accion": "creado",
  "item_id": 42,
  "carrito_uuid": "550e...",
  "cantidad_actual": 1,
  "total_actualizado": "0.00"
}
```
```json
{
  "accion": "incrementado",
  "item_id": 42,
  "carrito_uuid": "550e...",
  "cantidad_actual": 3,
  "total_actualizado": "0.00"
}
```

**Errores:**

| Código | Condición |
|--------|-----------|
| 403 | Categoría no autorizada para este módulo (`SCOPE_DENIED`) |
| 409 | Carrito en estado terminal (`confirmado`, `cancelado`, `expirado`) |
| 422 | Regla de negocio violada (cantidad resultante excede `cantidad_maxima`, duración, saldo) |

---

#### `DELETE /api/cart/carritos/{uuid}/items/{item_id}`

Remueve un ítem del carrito.

**Response 200:**
```json
{
  "mensaje": "Ítem removido",
  "total_actualizado": "0.00"
}
```

---

#### `POST /api/cart/carritos/{uuid}/checkout`

Confirma el carrito. El backend valida todas las reglas antes de confirmar.

> [v1.1 — C5] Si `requiere_saldo=true`, llama al módulo Saldo vía HTTP interna. El comportamiento ante fallo de Saldo depende de la regla `permite_pago_diferido` de la categoría.

**Request body:**
```json
{
  "metadata_checkout": {}
}
```

**Árbol de respuestas posibles:**

```
Saldo disponible y confirma hold
  → 200  { "estado": "confirmado", ... }

Saldo no disponible (timeout/5xx) + permite_pago_diferido=true + topes OK
  → 200  { "estado": "confirmado_pendiente_conciliacion",
            "aviso": "Cargo pendiente de procesamiento" }

Saldo no disponible (timeout/5xx) + permite_pago_diferido=false
  → 503  { "error": "SALDO_NO_DISPONIBLE",
            "mensaje": "Servicio de saldo no disponible, intenta más tarde" }

Saldo no disponible + tope de usuario rebasado ($200 MXN en pendiente)
  → 503  { "error": "TOPE_USUARIO_ALCANZADO" }

Saldo disponible pero fondos insuficientes
  → 402  { "error": "SALDO_INSUFICIENTE" }
```

**Response 200 — confirmado:**
```json
{
  "carrito_uuid": "550e...",
  "estado": "confirmado",
  "total": "150.00",
  "confirmed_at": "2026-04-28T10:45:00Z"
}
```

**Response 200 — confirmado_pendiente_conciliacion:**
```json
{
  "carrito_uuid": "550e...",
  "estado": "confirmado_pendiente_conciliacion",
  "total": "35.00",
  "aviso": "Cargo pendiente de procesamiento por el módulo de Saldo"
}
```

**Errores:**

| Código | Condición |
|--------|-----------|
| 402 | Saldo insuficiente confirmado por módulo Saldo |
| 409 | Carrito ya confirmado o cancelado |
| 422 | Carrito vacío o reglas de negocio no satisfechas |
| 503 | Módulo Saldo no disponible y categoría no permite pago diferido, o tope alcanzado |

---

#### `POST /api/cart/carritos/{uuid}/cancelar`

Cancela el carrito.

**Response 200:** `{ "estado": "cancelado" }`

---

#### `GET /api/cart/historico`

Histórico de carritos del módulo autenticado.

**Query params:** `estado`, `usuario_ref`, `desde`, `hasta`, `per_page`

**Response 200:** Lista paginada de carritos con resumen de ítems.

---

#### `POST /api/cart/carritos/{uuid}/items/{item_id}/devolver`

Marca un ítem como devuelto (solo para categorías con `permite_devolucion: true`).

**Response 200:** `{ "estado_item": "devuelto" }`

**Errores:**

| Código | Condición |
|--------|-----------|
| 422 | La categoría del ítem no permite devolución |

---

## 4. Diseño del JWT

### 4.1 Algoritmo elegido: HS256

**Justificación:**
- El contexto es un sistema monorepo universitario donde el emisor y los verificadores son el mismo servicio (el backend Laravel del módulo Carrito). No hay múltiples servicios independientes verificando el token.
- HS256 (HMAC-SHA256) con un secreto compartido es suficiente y más simple de gestionar para este escenario.
- RS256 sería la elección correcta si módulos externos necesitaran verificar el token de forma independiente (sin acceso al secreto). Dado que **toda validación pasa por nuestro middleware**, HS256 es apropiado y reduce complejidad.

---

### 4.2 Estructura del payload

**Access Token:**
```json
{
  "sub": "42",
  "tipo_modulo": "biblioteca",
  "categorias_autorizadas": ["prestamo", "reserva"],
  "modulo_slug": "biblioteca",
  "iat": 1745836800,
  "exp": 1745840400,
  "jti": "a3f2c1d4-...",
  "iss": "campus-digital-carrito",
  "token_type": "access"
}
```

**Refresh Token:**
```json
{
  "sub": "42",
  "modulo_slug": "biblioteca",
  "iat": 1745836800,
  "exp": 1746009600,
  "jti": "b8e9d0f1-...",
  "iss": "campus-digital-carrito",
  "token_type": "refresh"
}
```

> [v1.1 — C6] El claim `access_jti` se eliminó del payload del refresh token. El pareo entre access y refresh ahora vive en BD via la columna `pair_jti` de `tokens_modulo`, lo cual es más seguro (la relación es authoritative en el servidor, no en el token).

---

### 4.3 Política de expiración

| Token | TTL | Renovación |
|-------|-----|------------|
| Access Token | **1 hora** | Via refresh token |
| Refresh Token | **7 días** · **un solo uso** [v1.1 — C4] | Cada uso emite un refresh nuevo; el anterior queda revocado con motivo `rotacion` |

El control Vue (vía proxy del backend cliente) detecta `401 TOKEN_EXPIRED` y llama al endpoint de refresh automáticamente antes de reintentar la petición original.

---

### 4.4 Almacenamiento del secreto de firma

- El secreto `MODULE_JWT_SECRET` se almacena en el archivo `.env` de Laravel y **nunca en el repositorio**.
- En producción, se gestiona vía variable de entorno del servidor (sin archivo `.env`).
- El secreto debe tener al menos 256 bits de entropía (64 caracteres hex).

**Generación:**
```bash
php artisan tinker --execute="echo bin2hex(random_bytes(32));"
```

---

### 4.5 Rotación de secretos

Flujo si el secreto se compromete:

1. Admin genera nuevo secreto y lo actualiza en `.env` / entorno del servidor.
2. Todos los JTIs activos son marcados como `revocado` en `tokens_modulo` (un comando Artisan: `php artisan carrito:revocar-todos`).
3. El middleware rechazará todos los tokens existentes (firma inválida o jti revocado).
4. El admin emite nuevos tokens para cada módulo activo desde el panel interno.
5. Los equipos de módulos reciben sus nuevos tokens fuera de banda (canal seguro).

---

### 4.6 Validación en Middleware `auth.module.jwt`

Orden de validación (falla rápido):

```
1. Existe header Authorization: Bearer
2. JWT tiene formato válido (3 partes)
3. Firma HS256 es válida con MODULE_JWT_SECRET
4. claim exp no ha vencido
5. claim iss == "campus-digital-carrito"
6. claim token_type == "access" (no se acepta refresh en endpoints de carrito)
7. jti existe en tokens_modulo con estado == "activo"
8. modulo_id está activo en modulos_clientes
9. [por endpoint] categoría de la acción ∈ categorias_autorizadas del payload
```

Cada falla produce un código de error específico en el response para facilitar el debug del módulo cliente.

---

### 4.7 Entrega del JWT al módulo cliente [v1.1 — C2]

El flujo de entrega sigue el modelo **one-time display** de AWS IAM secret keys y GitHub Personal Access Tokens.

**Flujo en el panel interno al aprobar una solicitud:**

1. El admin hace clic en [Aprobar]. El backend genera el par access+refresh, registra ambos jti en `tokens_modulo` con `entregado_at = NULL`.
2. La UI presenta el JWT de acceso en un `<input type="password">` con dos acciones:
   - **[Mostrar]**: revela el token en texto claro durante **30 segundos**, luego vuelve a ocultarlo automáticamente. Cada visualización registra `token.visualizado` en bitácora con `user_id` + `ip_address`.
   - **[Copiar al portapapeles]**: vía oficial recomendada.
3. Al primer render de esa pantalla, el backend marca `tokens_modulo.entregado_at = NOW()`.
4. Si el admin navega fuera y regresa (o si otro admin accede a ese registro), el sistema responde: _"Token ya entregado. Si se perdió, revoca este token y emite uno nuevo desde el panel."_ El JWT **no se vuelve a serializar ni a mostrar**.

**Reconocimiento explícito de limitación:**  
Este mecanismo garantiza que **desde el sistema mismo** el JWT no es recuperable tras la entrega. No impide que el admin lo haya guardado en otro lado (screenshot, copia manual). Es responsabilidad operativa del equipo de Carrito entregar el token únicamente al contacto autorizado del módulo cliente por un canal seguro (presencial o mensaje cifrado).

---

## 5. Contrato del Control Vue

### 5.1 Estructura de archivos en el monorepo

```
resources/js/Modules/Cart/Control/
├── CartControl.vue          ← componente principal (exportado)
├── composables/
│   ├── useCart.js           ← lógica de estado del carrito
│   ├── useCartApi.js        ← llamadas Axios a la API privada
│   └── useTokenRefresh.js   ← interceptor de refresh automático
├── components/
│   ├── CartItemList.vue
│   ├── CartItem.vue
│   ├── CartSummary.vue
│   └── CartCheckoutButton.vue
└── index.js                 ← re-export de CartControl.vue
```

---

### 5.2 Props del componente `CartControl.vue`

> [v1.1 — C1] La prop `moduleToken` se **elimina**. El JWT nunca llega al navegador. La prop `apiBaseUrl` ahora apunta al proxy del módulo cliente, no a la API Carrito directamente.

```typescript
// Definición de props (TypeScript comentado para referencia)
props: {
  // Referencia opaca al usuario final (matrícula, UUID, etc.) — requerida
  userRef: {
    type: String,
    required: true
  },

  // Configuración del comportamiento del carrito para este módulo
  config: {
    type: Object,
    required: true,
    // Estructura esperada:
    // {
    //   categoriaSlug: String,          // categoría principal de este carrito
    //   requiereSaldo: Boolean,         // ¿validar monedero?
    //   expiraEnMinutos: Number,        // TTL del carrito de sesión
    //   permitirMultiplesItems: Boolean,
    //   etiquetaBotonCheckout: String,  // texto personalizable
    //   metadataInicial: Object         // datos extra del módulo
    // }
  },

  // URL base del PROXY del módulo cliente (NO apunta a /api/cart directamente)
  // Cada módulo define su propia ruta proxy, ej: '/biblioteca/cart-proxy'
  // [v1.1 — C1] Default cambiado: ya no es '/api/cart'
  apiBaseUrl: {
    type: String,
    required: true   // requerida: cada módulo debe declarar su ruta proxy explícitamente
  }
}
```

---

### 5.3 Eventos emitidos

| Evento | Payload | Descripción |
|--------|---------|-------------|
| `checkout-success` | `{ carrito_uuid, total, confirmed_at }` | Checkout completado |
| `checkout-error` | `{ code, mensaje }` | Error en checkout (negocio o red) |
| `token-refresh-failed` | `{ error }` | Refresh falló; módulo cliente debe re-autenticar |
| `cart-updated` | `{ items, total }` | Cada vez que cambia el carrito |
| `item-added` | `{ item_id, referencia_externa }` | Ítem agregado |
| `item-removed` | `{ item_id }` | Ítem removido |

---

### 5.4 Slots

| Slot | Descripción |
|------|-------------|
| `item-header` | Contenido extra encima de la lista de ítems |
| `item-extra(item)` | Contenido extra por cada ítem (scoped) |
| `checkout-footer` | Contenido debajo del botón de checkout |
| `empty-state` | Vista cuando el carrito está vacío |

---

### 5.5 Ejemplo de uso — Módulo Biblioteca [v1.1 — C1]

#### (a) Backend de Biblioteca — Controlador proxy

```php
<?php
// app/Http/Controllers/Biblioteca/CartProxyController.php

namespace App\Http\Controllers\Biblioteca;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Support\ConsumesCartApi;
use Illuminate\Http\Request;

class CartProxyController extends Controller
{
    use ConsumesCartApi;

    public function createCart(Request $request)
    {
        return $this->createCartRequest([
            'usuario_ref' => $request->user()->matricula,
            'requiere_saldo' => false,
            'expira_en_minutos' => 60,
        ]);
    }

    public function addItem(Request $request, string $uuid)
    {
        return $this->addItemRequest($uuid, $request->validated());
    }

    public function checkout(Request $request, string $uuid)
    {
        $response = $this->checkoutRequest($uuid, $request->all());
        if ($response->successful()) {
            // Lógica post-checkout de Biblioteca (actualizar disponibilidad, etc.)
        }
        return $response;
    }

    // ... demás métodos proxy
}
```

```php
// routes/web.php (o routes/biblioteca.php) — rutas proxy del módulo Biblioteca
Route::middleware(['auth'])->prefix('biblioteca/cart-proxy')->group(function () {
    Route::post('carritos',                          [CartProxyController::class, 'createCart']);
    Route::get('carritos/{uuid}',                    [CartProxyController::class, 'getCart']);
    Route::post('carritos/{uuid}/items',             [CartProxyController::class, 'addItem']);
    Route::delete('carritos/{uuid}/items/{itemId}',  [CartProxyController::class, 'removeItem']);
    Route::post('carritos/{uuid}/checkout',          [CartProxyController::class, 'checkout']);
    Route::post('carritos/{uuid}/cancelar',          [CartProxyController::class, 'cancel']);
});
```

#### (b) Componente Vue de Biblioteca — sin JWT en el frontend

```vue
<!-- resources/js/Pages/Biblioteca/Prestamo.vue -->
<template>
  <div class="prestamo-layout">
    <h2>Solicitar Préstamo</h2>

    <!-- Tabla de búsqueda de libros... -->

    <!-- Carrito: apunta al proxy de Biblioteca, sin JWT -->
    <CartControl
      :user-ref="auth.user.matricula"
      :config="cartConfig"
      api-base-url="/biblioteca/cart-proxy"
      @checkout-success="onPrestamoConfirmado"
      @checkout-error="onPrestamoError"
    >
      <template #empty-state>
        <p>No has seleccionado ningún material.</p>
      </template>
      <template #checkout-footer>
        <p class="text-sm text-gray-500">
          Los materiales deben devolverse en 3 días hábiles.
        </p>
      </template>
    </CartControl>
  </div>
</template>

<script setup>
import { CartControl } from '@/Modules/Cart/Control/index.js'
import { usePage } from '@inertiajs/vue3'

const auth = usePage().props.auth

const cartConfig = {
  categoriaSlug: 'prestamo',
  requiereSaldo: false,
  expiraEnMinutos: 60,
  permitirMultiplesItems: true,
  etiquetaBotonCheckout: 'Confirmar Préstamo',
  metadataInicial: {}
}

function onPrestamoConfirmado({ carrito_uuid, total, confirmed_at }) {
  // Post-checkout: notificar al módulo Biblioteca
}

function onPrestamoError({ code, mensaje }) {
  console.error(`Error en préstamo [${code}]: ${mensaje}`)
}
</script>
```

> El JWT vive únicamente en `config('cart.module_token')` del backend de Biblioteca. El navegador nunca lo ve.

---

### 5.6 Trait `ConsumesCartApi` [v1.1 — C1]

**Ubicación:** `app/Modules/Cart/Support/ConsumesCartApi.php`

**Propósito:** Proveer a cualquier controlador del monorepo los métodos necesarios para comunicarse con la API privada del módulo Carrito, inyectando el JWT automáticamente y manejando el refresh transparente.

**Interfaz pública esperada:**

```php
trait ConsumesCartApi
{
    // Crea un carrito de sesión
    protected function createCartRequest(array $payload): \Illuminate\Http\Client\Response {}

    // Obtiene el estado actual de un carrito
    protected function getCartRequest(string $uuid): \Illuminate\Http\Client\Response {}

    // Agrega o incrementa un ítem
    protected function addItemRequest(string $uuid, array $payload): \Illuminate\Http\Client\Response {}

    // Remueve un ítem
    protected function removeItemRequest(string $uuid, int $itemId): \Illuminate\Http\Client\Response {}

    // Confirma el checkout
    protected function checkoutRequest(string $uuid, array $payload = []): \Illuminate\Http\Client\Response {}

    // Cancela el carrito
    protected function cancelRequest(string $uuid): \Illuminate\Http\Client\Response {}

    // Marca ítem como devuelto
    protected function returnItemRequest(string $uuid, int $itemId): \Illuminate\Http\Client\Response {}

    // Histórico de carritos del módulo
    protected function historyRequest(array $filters = []): \Illuminate\Http\Client\Response {}

    // Solicita un nuevo par de tokens usando el refresh token actual
    // Actualiza automáticamente config/sesión con los nuevos tokens
    protected function refreshTokenRequest(): \Illuminate\Http\Client\Response {}
}
```

**Comportamiento interno:**
- Lee el JWT de `config('cart.module_token')` (configurable en `.env` del módulo cliente como `CART_MODULE_TOKEN=...`).
- Usa `Http::withToken(config('cart.module_token'))->baseUrl(config('cart.api_url'))`.
- Si recibe 401 con `error == TOKEN_EXPIRED`: llama a `refreshTokenRequest()` automáticamente y reintenta la petición original una vez.
- Si el refresh también falla: lanza excepción `CartTokenExpiredException` para que el controlador la capture y notifique al admin del módulo.

**Ejemplo mínimo de configuración del módulo cliente:**

```
# .env del módulo cliente (ej. Biblioteca)
CART_MODULE_TOKEN=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
CART_API_URL=https://campus-digital.test/api/cart
```

```php
// config/cart.php del módulo cliente
return [
    'module_token'  => env('CART_MODULE_TOKEN'),
    'api_url'       => env('CART_API_URL', 'http://localhost/api/cart'),
];
```

---

## 6. Plan de Pruebas PHPUnit

### 6.1 Suite: JWT y Middleware (`AuthModuleJwtTest`)

| # | Caso de prueba | Resultado esperado |
|---|---------------|--------------------|
| 1 | Token válido, firma correcta, no expirado, jti activo | Middleware pasa (200) |
| 2 | Token sin header `Authorization` | 401 `TOKEN_MISSING` |
| 3 | Token con firma inválida (secreto distinto) | 401 `TOKEN_INVALID` |
| 4 | Token expirado (`exp` en el pasado) | 401 `TOKEN_EXPIRED` |
| 5 | Token con `exp` futuro pero `jti` revocado en BD | 401 `TOKEN_REVOKED` |
| 6 | Token de tipo `refresh` usado en endpoint de carrito | 401 `TOKEN_INVALID` |
| 7 | Token de módulo inactivo (`modulos_clientes.activo = false`) | 403 `MODULE_INACTIVE` |
| 8 | Token válido pero `iss` incorrecto | 401 `TOKEN_INVALID` |
| 9 | Token válido pero `categorias_autorizadas` no incluye la categoría de la acción | 403 `SCOPE_DENIED` |
| 10 | Token válido, módulo activo, categoría autorizada | Acceso concedido |

### 6.2 Suite: Reglas de Negocio del Carrito (`CartBusinessRulesTest`)

| # | Caso de prueba | Resultado esperado |
|---|---------------|--------------------|
| 11 | Crear carrito con `usuario_ref` y `expira_en_minutos` válidos | 201 con `carrito_uuid` |
| 12 | Agregar ítem con `cantidad` <= límite de la categoría | 201 |
| 13 | Agregar ítem con `cantidad` > límite de la categoría | 422 |
| 14 | Agregar ítem con `duracion_horas` > máximo de la categoría | 422 |
| 15 | Agregar ítem de categoría no autorizada en el JWT | 403 `SCOPE_DENIED` |
| 16 | **[v1.1 — C3]** Agregar ítem con misma `referencia_externa` existente y `estado_item='activo'` | 200 `accion:"incrementado"`, cantidad actualizada |
| 16b | **[v1.1 — C3]** Incremento que haría `cantidad` > `cantidad_maxima` de la categoría | 422 |
| 17 | Remover ítem existente | 200, total actualizado |
| 18 | Checkout con carrito vacío | 422 |
| 19 | Checkout exitoso con ítems válidos y Saldo disponible | 200, estado `confirmado` |
| 19b | **[v1.1 — C5]** Checkout con Saldo caído + `permite_pago_diferido=true` + tope OK | 200, estado `confirmado_pendiente_conciliacion` |
| 19c | **[v1.1 — C5]** Checkout con Saldo caído + `permite_pago_diferido=false` | 503 `SALDO_NO_DISPONIBLE` |
| 19d | **[v1.1 — C5]** Checkout diferido con tope de usuario ya rebasado ($200 acumulados) | 503 `TOPE_USUARIO_ALCANZADO` |
| 19e | **[v1.1 — C5]** Job de reconciliación: Saldo confirma fondos → carrito pasa a `confirmado` | `estado_conciliacion='exitosa'` en BD |
| 19f | **[v1.1 — C5]** Job de reconciliación: Saldo indica fondos insuficientes → carrito pasa a `revertido` | `estado_conciliacion='deuda'` en BD |
| 20 | Checkout con saldo insuficiente confirmado por módulo Saldo | 402 `SALDO_INSUFICIENTE` |
| 21 | Checkout de carrito ya confirmado | 409 |
| 22 | Cancelar carrito abierto | 200, estado `cancelado` |
| 23 | Devolver ítem en categoría con `permite_devolucion: false` | 422 |
| 24 | Devolver ítem en categoría con `permite_devolucion: true` | 200 |
| 25 | Carrito expirado (simulando tiempo) no puede ser operado | 409 o 422 |

### 6.3 Suite: Autorización por Módulo (`ModuleScopeTest`)

| # | Caso de prueba | Resultado esperado |
|---|---------------|--------------------|
| 26 | Módulo Cafetería intenta leer carrito de Biblioteca | 403 |
| 27 | Módulo con solo `producto` intenta agregar ítem de `reserva` | 403 `SCOPE_DENIED` |
| 28 | Módulo válido accede a su propio histórico | 200 |

### 6.4 Suite: Token Refresh (`TokenRefreshTest`)

| # | Caso de prueba | Resultado esperado |
|---|---------------|--------------------|
| 29 | Refresh con refresh token válido y activo | 200 con nuevo par access+refresh |
| 29b | **[v1.1 — C4]** Response de refresh incluye `refresh_token` nuevo (no solo access) | Assertion en ambos campos |
| 29c | **[v1.1 — C4]** Después de refresh: pair viejo (A1, R1) marcado `revocado` con motivo `rotacion` en BD | Assertion en BD |
| 29d | **[v1.1 — C4]** Access token viejo rechazado después de rotación | 401 `TOKEN_REVOKED` |
| 29e | **[v1.1 — C4]** Reuso de refresh ya rotado (jti con `motivo='rotacion'`) → toda la cadena del módulo revocada | 401, todos los jti del modulo en estado `revocado` |
| 30 | Refresh con access token (no refresh) | 401 |
| 31 | Refresh con refresh token expirado | 401 |
| 32 | Refresh con refresh token revocado manualmente | 401 |
| 33 | **[v1.1 — C6]** Nuevo par post-refresh tiene `replaces_jti` apuntando al jti anterior del mismo tipo | Assertion en BD |

### 6.5 Suite: Bitácora (`BitacoraTest`)

| # | Caso de prueba | Resultado esperado |
|---|---------------|--------------------|
| 34 | Checkout exitoso registra entrada en `bitacora` | `carrito.checkout` en BD |
| 35 | Intento de auth fallido registra `auth.fallo` | Entrada en BD sin exponer datos |
| 36 | Revocación de token registra `token.revocacion` | Entrada con `jti` en BD |

---

## 7. Riesgos de Seguridad y Mitigaciones

### 7.1 Token Leak (filtración del JWT)

**Riesgo:** El JWT del módulo se expone en logs, repositorio, o comunicación insegura.

**Mitigaciones:**
- **[v1.1 — C1]** El JWT nunca viaja al navegador del usuario final. El patrón anterior (JWT como prop Vue) fue descartado exactamente por este riesgo. El token vive únicamente en el backend del módulo cliente (`config('cart.module_token')`).
- **[v1.1 — C2]** Entrega one-time: el JWT se muestra una sola vez en el panel admin (input password, auto-ocultado a los 30s, sin segunda serialización). Mismo modelo que AWS IAM secret keys y GitHub PATs. `tokens_modulo.entregado_at` marca el primer y único acceso. Cada visualización se audita en bitácora con `token.visualizado`.
- En BD solo se almacena el `jti` y metadatos, **nunca el JWT completo**.
- TTL corto del access token (1 hora).
- Revocar inmediatamente si hay sospecha de filtración.
- Los logs de Laravel **no deben registrar headers** de las peticiones en producción.

---

### 7.2 Replay Attack (reutilización de token capturado)

**Riesgo:** Un atacante captura un JWT válido y lo reutiliza.

**Mitigaciones:**
- Comunicación **exclusivamente HTTPS** (TLS 1.2+).
- TTL corto (1 hora) limita la ventana de ataque.
- El registro de `jti` en BD permite revocación inmediata si se detecta uso sospechoso.
- La bitácora registra todos los accesos con IP; anomalías detectables.

---

### 7.3 Scope Escalation (escalada de categorías)

**Riesgo:** Un módulo autorizado para `producto` intenta operar con `reserva`.

**Mitigaciones:**
- El middleware verifica en cada request que la categoría de la acción está en `categorias_autorizadas` del payload del JWT.
- Las `categorias_autorizadas` son inmutables en el token (requieren nueva aprobación para cambiar).
- Pruebas de scope en PHPUnit (casos #9, #27).

---

### 7.4 Cross-Module Cart Access (acceso a carrito ajeno)

**Riesgo:** Módulo Cafetería intenta leer o modificar carrito creado por Biblioteca.

**Mitigaciones:**
- Al resolver cualquier `carrito_uuid`, el backend verifica que `carritos.modulo_id == módulo del JWT`.
- Responde 403 si no coincide (no 404, para no revelar existencia).
- Prueba PHPUnit caso #26.

---

### 7.5 JWT Forgery (falsificación de token)

**Riesgo:** Atacante crea un JWT con claims arbitrarios.

**Mitigaciones:**
- Firma HS256 con secreto de 256 bits. Sin el secreto, el token es inválido.
- El middleware verifica firma **antes** de cualquier otra validación.
- El secreto nunca está en el repositorio.

---

### 7.6 Refresh Token Abuse (abuso del refresh token)

**Riesgo:** El refresh token de larga vida es comprometido.

**Mitigaciones:**
- El refresh token también tiene `jti` registrado en BD y puede revocarse.
- TTL de 7 días (no ilimitado).
- El refresh token solo es válido en el endpoint `/api/cart/tokens/refresh`.
- Al revocar el access token desde el panel, se revoca también su `pair_jti` (refresh compañero).
- **[v1.1 — C4] Detección de reuso:** Si un refresh token que ya fue rotado (revocado con `motivo='rotacion'`) vuelve a usarse, el sistema interpreta esto como robo probable y revoca **toda la cadena de tokens activos** del módulo, registrando `token.reuso_detectado` en bitácora. Esto neutraliza al atacante aunque haya obtenido un refresh token histórico.

---

### 7.7 Bitácora Poisoning / Log Injection

**Riesgo:** Datos de entrada maliciosos se escriben en la bitácora y confunden análisis.

**Mitigaciones:**
- La bitácora registra datos estructurados (JSON), no strings libres del usuario.
- Los campos de texto libre se sanitizan antes de registrarse.
- El `usuario_ref` y `referencia_externa` se tratan siempre como datos opacos.

---

### 7.8 Carrito como Vector de Enumeración

**Riesgo:** Consultar `GET /api/cart/carritos/{uuid}` y enumerar UUIDs para acceder a datos de otros módulos.

**Mitigaciones:**
- Los `carrito_uuid` son UUID v4 (128 bits de entropía), no enumerables.
- La verificación de `modulo_id` en cada request hace inútil la enumeración aunque se adivine un UUID.

---

### 7.9 JWT Exposure en Frontend [v1.1 — C1]

**Riesgo:** El JWT del módulo queda expuesto en el navegador del usuario final: DevTools, localStorage, memoria JS, extensiones del navegador, XSS.

**Por qué es relevante:** La arquitectura original (v1.0) pasaba el JWT como prop Vue (`moduleToken`). Esto significaba que el token de módulo —con acceso a la API completa del carrito en nombre de todo un módulo— existía en el contexto de ejecución del navegador de cada usuario. Un XSS o una extensión maliciosa podría haberlo exfiltrado y usado para operar carritos arbitrarios del módulo completo.

**Mitigación aplicada en v1.1:** El JWT se elimina del frontend por completo. El control Vue ahora apunta al proxy del backend del módulo cliente, que sostiene el token en configuración del servidor. El navegador solo tiene credenciales de sesión de usuario (cookie de Laravel), no credenciales de módulo. Un XSS en el frontend solo puede afectar la sesión del usuario afectado, no comprometer la credencial de toda la integración.

---

### 7.10 Acumulación de Deuda por Saldo Indisponible [v1.1 — C5]

**Riesgo:** Si el módulo Saldo está caído durante un período prolongado y muchas categorías tienen `permite_pago_diferido=true`, el sistema puede acumular una deuda significativa que luego no se puede recuperar (usuarios sin fondos, deudas no cobradas).

**Mitigaciones:**
- **Tope por usuario:** máximo $200 MXN acumulados en estado `confirmado_pendiente_conciliacion`. Nuevos checkouts diferidos del mismo usuario se rechazan hasta que el tope baje.
- **Tope global configurable:** si el acumulado global supera el umbral (ej. $50,000 MXN), todos los checkouts diferidos se rechazan hasta que Saldo se recupere y concilie.
- **`permite_pago_diferido=false` por defecto:** solo se activa explícitamente para categorías de monto bajo (cafetería, copias, souvenirs). Categorías de alto monto (trámites, reservas de equipo) fallan rápido con 503.
- **Backoff exponencial:** el job de reconciliación no martilla a Saldo; escala gradualmente (5 min → 15 min → 1 h → 4 h → revisión manual).
- **Visibilidad:** el dashboard del módulo Carrito expone el monto total en `pendiente_conciliacion` como métrica de riesgo en tiempo real.

---
las tablas se prefijaron con cart_ para coexistir con otros módulos del monorepo. Los modelos hacen el mapeo vía $table." Así queda documentado y la evaluación cruzada no te lo señala como inconsistencia.

---

## Anexo C — Contrato de integración con módulo Saldo [v1.2 — C7]

Todas las llamadas incluyen el header:

```
X-Internal-Token: <valor de config('cart.saldo.internal_token') / env CART_SALDO_INTERNAL_TOKEN>
```

---

### C.1 Reservar fondos

```
POST /api/internal/saldo/reservar
```

**Body:**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `usuario_ref` | string | Matrícula o identificador del usuario |
| `monto` | decimal | Monto a reservar (MXN) |
| `carrito_uuid` | string | UUID del carrito |
| `modulo_slug` | string | Slug del módulo cliente que solicita |
| `concepto` | string | Descripción breve del cobro (ej. `"checkout"`) |

**Respuestas:**

| HTTP | Body | Significado |
|------|------|-------------|
| 200 | `{ ok: true, reserva_id, saldo_disponible_post_reserva, expira_at }` | Fondos reservados |
| 402 | `{ ok: false, motivo: "fondos_insuficientes", saldo_disponible }` | Saldo insuficiente |
| 422 | `{ ok: false, motivo: "..." }` | Error de validación |
| 503 | `{ ok: false, motivo: "servicio_no_disponible" }` | Saldo no disponible |

---

### C.2 Confirmar reserva

```
POST /api/internal/saldo/confirmar/{reserva_id}
```

**Body:**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `carrito_uuid` | string | UUID del carrito asociado |

**Respuestas:**

| HTTP | Body | Significado |
|------|------|-------------|
| 200 | `{ ok: true, movimiento_id, saldo_post_cargo }` | Cargo aplicado |
| 409 | `{ ok: false, motivo: "reserva_expirada" \| "reserva_ya_consumida" }` | Reserva inválida |

---

### C.3 Liberar reserva

```
POST /api/internal/saldo/liberar/{reserva_id}
```

Body: vacío.

**Respuestas:**

| HTTP | Body | Significado |
|------|------|-------------|
| 200 | `{ ok: true, saldo_restaurado }` | Fondos devueltos |

---

### C.4 Cargo forzoso

```
POST /api/internal/saldo/cargo-forzoso
```

Usado exclusivamente por `ReintentaConciliacion` para cobrar el servicio ya entregado, pudiendo dejar el saldo en negativo.

**Body:**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `usuario_ref` | string | Matrícula del usuario |
| `monto` | decimal | Monto a cargar |
| `carrito_uuid` | string | UUID del carrito |
| `concepto` | string | Descripción (ej. `"conciliacion_diferida"`) |
| `carrito_estado` | string | Estado actual del carrito en el sistema |

**Respuestas:**

| HTTP | Body | Significado |
|------|------|-------------|
| 200 | `{ ok: true, movimiento_id, saldo_resultante }` | Cargo registrado |

---

### C.5 Flujo de ciclo de vida de una reserva

```
reservar() ──► confirmar()          ← camino feliz
     │
     └──► liberar()                 ← rollback por excepción inesperada
```

`CheckoutService` garantiza que si ocurre cualquier excepción después de `reservar()` y antes de que `confirmar()` retorne normalmente, se invoca `liberar()` en un bloque `finally`.

---

*Fin del Documento de Diseño v1.2*
