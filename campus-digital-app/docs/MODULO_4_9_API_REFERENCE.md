# API Reference — Módulo 4.9 (Proveedores, Tiendas y Repartidores)

> **Base URL:** `http://127.0.0.1:8000`  
> **Autenticación API:** Header `X-API-KEY: {tu_api_key}` (ver `.env` → `API_KEY`)  
> **Autenticación Web:** Sesión Laravel (cookie `laravel_session`)  
> **Formato:** `application/json`  

---

## Índice

1. [APIs Web (Admin — Inertia)](#1-apis-web-admin--inertia)
   - [Búsqueda de Usuarios](#11-búsqueda-de-usuarios)
   - [Proveedores](#12-proveedores)
   - [Tiendas](#13-tiendas)
   - [Repartidores](#14-repartidores)
2. [APIs REST (JSON)](#2-apis-rest-json)
   - [Métricas del Proveedor](#21-métricas-del-proveedor)
   - [Reportes del Proveedor](#22-reportes-del-proveedor)
   - [Integración Catálogo 4.3 → 4.9](#23-integración-catálogo-43--49)
3. [APIs de Otros Módulos Consumidas](#3-apis-de-otros-módulos-consumidas)
4. [Ejemplos cURL](#4-ejemplos-curl)
5. [Códigos de respuesta](#5-códigos-de-respuesta)

---

## 1. APIs Web (Admin — Inertia)

Estas rutas requieren sesión activa con rol `admin`. Devuelven páginas Inertia (Vue) o JSON según el tipo de petición.

---

### 1.1 Búsqueda de Usuarios

Endpoint auxiliar usado por los modales de asignación para buscar usuarios existentes.

#### `GET /admin/api/usuarios/buscar`

**Parámetros de query:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `q` | string | ✅ | Texto a buscar (nombre o correo) |

**Respuesta exitosa `200`:**
```json
[
  {
    "id": 5,
    "nombre": "Juan García",
    "correo": "juan.garcia@campus.edu"
  },
  {
    "id": 12,
    "nombre": "María López",
    "correo": "maria.lopez@campus.edu"
  }
]
```

**Ejemplo de uso (Axios desde Vue):**
```js
const { data } = await axios.get('/admin/api/usuarios/buscar', {
  params: { q: 'juan' }
})
// data => [{ id, nombre, correo }, ...]
```

---

### 1.2 Proveedores

#### `GET /admin/proveedores`
Muestra la página de gestión de proveedores.  
**Respuesta:** Página Inertia `Admin/Proveedores`

---

#### `POST /admin/proveedores`
Registra un nuevo proveedor (asigna rol `proveedor` al usuario y opcionalmente crea tienda).

**Body `application/json` o `multipart/form-data`:**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `usuario_id` | integer | ✅ | ID del usuario a convertir en proveedor |
| `nombre_negocio` | string | ❌ | Nombre de la tienda a crear simultáneamente |
| `descripcion` | string | ❌ | Descripción del negocio |
| `categoria` | string | ❌ | Categoría del negocio |

**Respuesta exitosa:** Redirect con flash `success`

**Ejemplo cURL:**
```bash
curl -X POST http://127.0.0.1:8000/admin/proveedores \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: {csrf_token}" \
  -d '{"usuario_id": 5, "nombre_negocio": "Cafetería Central"}'
```

---

#### `DELETE /admin/proveedores/{id}`
Revoca el rol `proveedor` del usuario indicado.

**Parámetro de ruta:** `id` — ID del registro en tabla `tiendas` o del usuario proveedor.

**Respuesta exitosa:** Redirect con flash `success`

---

### 1.3 Tiendas

#### `GET /admin/tiendas`
Muestra la página de gestión de tiendas.  
**Respuesta:** Página Inertia `Admin/Tiendas`

---

#### `POST /admin/tiendas`
Crea una nueva tienda con logo opcional.

**Body `multipart/form-data`:**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `nombre` | string | ✅ | Nombre de la tienda (max 150 chars) |
| `descripcion` | string | ❌ | Descripción del negocio |
| `categoria` | string | ✅ | Categoría (cafetería, librería, souvenirs, etc.) |
| `logo` | file | ❌ | Imagen del logo (jpg/png/webp, max 2MB) |
| `usuario_id` | integer | ✅ | ID del usuario propietario |
| `activo` | boolean | ❌ | Estado (default: `true`) |

**Respuesta exitosa:** Redirect con flash `success`

---

#### `PUT /admin/tiendas/{id}`
Actualiza una tienda existente.

**Body `multipart/form-data`** — mismos campos que `POST`.

**Respuesta exitosa:** Redirect con flash `success`

---

#### `DELETE /admin/tiendas/{id}`
Elimina una tienda.

**Respuesta exitosa:** Redirect con flash `success`

---

### 1.4 Repartidores

#### `GET /admin/repartidores`
Muestra la página de gestión de repartidores.  
**Respuesta:** Página Inertia `Admin/Repartidores`

---

#### `POST /admin/repartidores`
Registra un nuevo repartidor (asigna rol `repartidor` y vincula con tienda).

**Body `application/json`:**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `usuario_id` | integer | ✅ | ID del usuario a convertir en repartidor |
| `tienda_id` | integer | ✅ | ID de la tienda a la que se asigna |

**Respuesta exitosa:** Redirect con flash `success`

**Ejemplo cURL:**
```bash
curl -X POST http://127.0.0.1:8000/admin/repartidores \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: {csrf_token}" \
  -d '{"usuario_id": 8, "tienda_id": 2}'
```

---

#### `PUT /admin/repartidores/{id}`
Actualiza la tienda asignada a un repartidor.

**Body `application/json`:**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `tienda_id` | integer | ✅ | Nueva tienda a asignar |
| `activo` | boolean | ❌ | Estado del repartidor |

---

#### `DELETE /admin/repartidores/{id}`
Revoca el rol `repartidor` del usuario y elimina el registro.

---

## 2. APIs REST (JSON)

Estas rutas requieren el header `X-API-KEY`. Se usan para integración entre módulos y paneles de estadísticas.

---

### 2.1 Métricas del Proveedor

#### `GET /api/proveedor/metrics`

Devuelve métricas globales del ecosistema de proveedores.

**Headers requeridos:**
```
X-API-KEY: {api_key}
Accept: application/json
```

**Respuesta exitosa `200`:**
```json
{
  "total_tiendas": 12,
  "tiendas_activas": 10,
  "total_repartidores": 8,
  "repartidores_activos": 7,
  "total_proveedores": 5,
  "pedidos_hoy": 43,
  "ingresos_mes": 15420.50
}
```

---

### 2.2 Reportes del Proveedor

#### `GET /api/proveedor/reports`

Devuelve un reporte detallado por tienda.

**Headers requeridos:**
```
X-API-KEY: {api_key}
Accept: application/json
```

**Parámetros de query opcionales:**

| Parámetro | Tipo | Descripción |
|---|---|---|
| `tienda_id` | integer | Filtrar por tienda específica |
| `fecha_inicio` | date (Y-m-d) | Inicio del período |
| `fecha_fin` | date (Y-m-d) | Fin del período |

**Respuesta exitosa `200`:**
```json
{
  "periodo": {
    "inicio": "2026-05-01",
    "fin": "2026-05-31"
  },
  "tiendas": [
    {
      "id": 1,
      "nombre": "Cafetería Central",
      "categoria": "cafetería",
      "pedidos": 120,
      "ingresos": 5400.00,
      "repartidores_asignados": 2
    }
  ]
}
```

---

### 2.3 Integración Catálogo 4.3 → 4.9

Endpoints consumidos por el Módulo 4.9 desde el catálogo del Módulo 4.3.

#### `GET /api/catalogo-integracion/vendedores`

Lista todos los vendedores/tiendas disponibles con sus productos activos.

**Headers requeridos:**
```
X-API-KEY: {api_key}
Accept: application/json
```

**Respuesta exitosa `200`:**
```json
[
  {
    "id_vendedor": 3,
    "nombre_tienda": "Librería Campus",
    "categoria": "librería",
    "productos_activos": 45,
    "logo_url": "/storage/tiendas/logos/libreria.jpg"
  }
]
```

---

#### `GET /api/catalogo-integracion/vendedor/{id_vendedor}`

Obtiene el catálogo completo de un vendedor específico.

**Parámetro de ruta:** `id_vendedor` — ID de la tienda/vendedor.

**Respuesta exitosa `200`:**
```json
{
  "vendedor": {
    "id": 3,
    "nombre": "Librería Campus",
    "descripcion": "Libros y material escolar",
    "categoria": "librería"
  },
  "productos": [
    {
      "id": 101,
      "nombre": "Cuaderno universitario",
      "precio": 45.00,
      "disponible": true,
      "stock": 200
    }
  ]
}
```

---

## 3. APIs de Otros Módulos Consumidas

El Módulo 4.9 consume las siguientes APIs de otros módulos:

### Del Módulo 4.1 (Usuarios y Roles)

| Endpoint | Método | Para qué se usa |
|---|---|---|
| `/api/usuarios` | GET | Listar usuarios disponibles |
| `/api/roles` | GET | Obtener IDs de roles `proveedor` y `repartidor` |
| `/api/usuario-roles` | POST | Asignar rol a un usuario |
| `/api/usuario-roles/{id}` | DELETE | Revocar rol de un usuario |

### Del Módulo 4.10 (Monedero / Pedidos)

| Endpoint | Método | Para qué se usa |
|---|---|---|
| `/api/pedidos` | GET | Obtener pedidos para métricas de tienda |
| `/api/saldo-movimientos` | GET | Consultar movimientos para reportes de ingresos |

---

## 4. Ejemplos cURL

### Listar todas las tiendas

```bash
curl -X GET http://127.0.0.1:8000/api/catalogo-integracion/vendedores \
  -H "X-API-KEY: tu_api_key_aqui" \
  -H "Accept: application/json"
```

### Crear un proveedor

```bash
curl -X POST http://127.0.0.1:8000/admin/proveedores \
  -b "laravel_session={session_cookie}" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: {csrf_token}" \
  -d '{
    "usuario_id": 15,
    "nombre_negocio": "Souvenirs Campus",
    "descripcion": "Artículos de recuerdo universitario",
    "categoria": "souvenirs"
  }'
```

### Crear una tienda con logo

```bash
curl -X POST http://127.0.0.1:8000/admin/tiendas \
  -b "laravel_session={session_cookie}" \
  -H "X-XSRF-TOKEN: {csrf_token}" \
  -F "nombre=Fotocopias Express" \
  -F "categoria=copias" \
  -F "usuario_id=7" \
  -F "activo=1" \
  -F "logo=@/ruta/al/logo.png"
```

### Asignar repartidor

```bash
curl -X POST http://127.0.0.1:8000/admin/repartidores \
  -b "laravel_session={session_cookie}" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: {csrf_token}" \
  -d '{
    "usuario_id": 22,
    "tienda_id": 4
  }'
```

### Obtener métricas del proveedor

```bash
curl -X GET http://127.0.0.1:8000/api/proveedor/metrics \
  -H "X-API-KEY: tu_api_key_aqui" \
  -H "Accept: application/json"
```

### Buscar usuarios para asignar

```bash
curl -X GET "http://127.0.0.1:8000/admin/api/usuarios/buscar?q=garcia" \
  -b "laravel_session={session_cookie}" \
  -H "Accept: application/json"
```

---

## 5. Códigos de respuesta

| Código | Significado |
|---|---|
| `200 OK` | Solicitud exitosa |
| `201 Created` | Recurso creado correctamente |
| `302 Found` | Redirect (rutas web con Inertia) |
| `401 Unauthorized` | Falta autenticación o API Key inválida |
| `403 Forbidden` | Sin permisos para el recurso |
| `404 Not Found` | Recurso no encontrado |
| `422 Unprocessable Entity` | Error de validación (ver campo `errors`) |
| `500 Internal Server Error` | Error del servidor |

### Formato de error de validación `422`

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "usuario_id": ["El campo usuario_id es obligatorio."],
    "tienda_id": ["La tienda seleccionada no existe."]
  }
}
```

---

## 6. Variables de entorno relevantes

```env
# Base de datos
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=campus_digital
DB_USERNAME=campus_user
DB_PASSWORD=tu_password

# API Key para endpoints REST
API_KEY=tu_api_key_secreta

# Storage para logos de tiendas
FILESYSTEM_DISK=public
```

> ⚠️ Después de cambiar variables del `.env` ejecutar: `php artisan config:clear`
