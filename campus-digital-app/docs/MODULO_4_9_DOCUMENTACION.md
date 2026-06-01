# Módulo 4.9 — Gestión de Proveedores, Tiendas y Repartidores

> **Proyecto:** CampusDigital — Plataforma de Servicios, Compras y Saldo Digital del Campus  
> **Asignatura:** Ingeniería de Software (SCD-1011)  
> **Sprint:** Módulo 4.9  
> **Stack:** Laravel + Inertia.js + Vue 3 + PostgreSQL  

---

## 1. Propósito del módulo

El Módulo 4.9 gestiona la **capa de proveedores** del ecosistema CampusDigital. Cubre tres entidades principales:

| Entidad | Descripción |
|---|---|
| **Proveedor** | Usuario externo con rol `proveedor` que posee una o varias Tiendas |
| **Tienda** | Punto de venta registrado en el campus (cafetería, librería, souvenirs, etc.) |
| **Repartidor** | Usuario con rol `repartidor` asignado a una Tienda para gestionar entregas |

El módulo incluye:
- CRUD completo de Tiendas y Repartidores desde el panel admin
- Asignación de rol `proveedor` a usuarios existentes
- Asignación de rol `repartidor` a usuarios existentes y vinculación con su Tienda
- Integración con el Módulo 4.3 (Catálogo) vía API de integración
- Panel de métricas y reportes del proveedor

---

## 2. Arquitectura

```
┌──────────────────────────────────────────────────────────┐
│                    Frontend (Vue 3 + Inertia.js)         │
│  Pages/Admin/Proveedores.vue   Pages/Admin/Tiendas.vue   │
│  Pages/Admin/Repartidores.vue                            │
└────────────────────┬─────────────────────────────────────┘
                     │  Inertia Props / Axios HTTP
┌────────────────────▼─────────────────────────────────────┐
│                   Backend (Laravel 11)                   │
│                                                          │
│  Controllers (Web/Admin):                                │
│    AdminProveedorController    TiendaController          │
│    RepartidorController                                  │
│                                                          │
│  Controllers (API):                                      │
│    ProviderApiController       CatalogoIntegracionApiCtrl│
│                                                          │
│  Models:                                                 │
│    Tienda   Repartidor   Usuario   Rol                   │
└────────────────────┬─────────────────────────────────────┘
                     │  Eloquent ORM
┌────────────────────▼─────────────────────────────────────┐
│              PostgreSQL — campus_digital                  │
│   tiendas   repartidores   usuarios   usuario_rol        │
└──────────────────────────────────────────────────────────┘
```

---

## 3. Estructura de archivos

### 3.1 Controladores Web (Admin)

| Archivo | Responsabilidad |
|---|---|
| `app/Http/Controllers/Admin/AdminProveedorController.php` | CRUD de Proveedores, búsqueda de usuarios, asignación de rol |
| `app/Http/Controllers/Admin/TiendaController.php` | CRUD de Tiendas, subida de logo |
| `app/Http/Controllers/Admin/RepartidorController.php` | CRUD de Repartidores, asignación de rol y tienda |

### 3.2 Controladores API

| Archivo | Responsabilidad |
|---|---|
| `app/Http/Controllers/Api/ProviderApiController.php` | Métricas y reportes del proveedor |
| `app/Http/Controllers/Api/CatalogoIntegracionApiController.php` | Integración catálogo Módulo 4.3 → 4.9 |

### 3.3 Modelos

| Archivo | Tabla | Relaciones |
|---|---|---|
| `app/Models/Tienda.php` | `tiendas` | `hasMany(Repartidor)`, `belongsTo(Usuario)` |
| `app/Models/Repartidor.php` | `repartidores` | `belongsTo(Tienda)`, `belongsTo(Usuario)` |

### 3.4 Vistas Vue (Inertia)

| Archivo | Descripción |
|---|---|
| `resources/js/Pages/Admin/Proveedores.vue` | Listado y gestión de proveedores |
| `resources/js/Pages/Admin/Tiendas.vue` | Listado y gestión de tiendas con logo |
| `resources/js/Pages/Admin/Repartidores.vue` | Listado y gestión de repartidores |

### 3.5 Migraciones

| Archivo | Tabla creada |
|---|---|
| `database/migrations/..._create_tiendas_table.php` | `tiendas` |
| `database/migrations/..._create_repartidores_table.php` | `repartidores` |

---

## 4. Esquema de base de datos

### Tabla `tiendas`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | bigint PK | Identificador único |
| `nombre` | varchar(150) | Nombre de la tienda |
| `descripcion` | text nullable | Descripción del negocio |
| `categoria` | varchar(80) | Categoría (cafetería, librería, etc.) |
| `logo_url` | varchar nullable | Ruta relativa al logo subido |
| `activo` | boolean | Estado activo/inactivo |
| `usuario_id` | bigint FK | Usuario propietario (proveedor) |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

### Tabla `repartidores`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | bigint PK | Identificador único |
| `usuario_id` | bigint FK | Usuario con rol repartidor |
| `tienda_id` | bigint FK | Tienda a la que pertenece |
| `activo` | boolean | Estado activo/inactivo |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

---

## 5. Flujos principales

### 5.1 Registrar un nuevo Proveedor

```
Admin busca usuario por nombre/correo
    ↓ GET /admin/api/usuarios/buscar?q={query}
Admin selecciona usuario
    ↓ POST /admin/proveedores
        body: { usuario_id, nombre_negocio?, descripcion? }
Sistema asigna rol "proveedor" al usuario (tabla usuario_rol)
Sistema crea registro en tabla "tiendas" si se proporcionó nombre_negocio
```

### 5.2 Registrar una Tienda

```
Admin llena formulario de tienda
    ↓ POST /admin/tiendas (multipart/form-data)
        body: { nombre, descripcion, categoria, logo (file), usuario_id, activo }
Sistema guarda logo en storage/app/public/tiendas/logos/
Sistema crea registro en tabla "tiendas"
```

### 5.3 Registrar un Repartidor

```
Admin busca usuario por nombre/correo
    ↓ GET /admin/api/usuarios/buscar?q={query}
Admin selecciona usuario y tienda destino
    ↓ POST /admin/repartidores
        body: { usuario_id, tienda_id }
Sistema asigna rol "repartidor" al usuario (tabla usuario_rol)
Sistema crea registro en tabla "repartidores"
```

---

## 6. Permisos y roles

| Rol | Acceso |
|---|---|
| `admin` | Acceso total al módulo (CRUD completo) |
| `proveedor` | Solo puede ver/gestionar sus propias tiendas |
| `repartidor` | Solo puede ver pedidos de su tienda asignada |

> Los roles se gestionan en las tablas `roles`, `permisos`, `rol_permiso` y `usuario_rol` del Módulo 4.1.

---

## 7. Módulos consumidos

| Módulo | Qué se consume |
|---|---|
| **Módulo 4.1** | Sistema de usuarios, roles y permisos |
| **Módulo 4.3 (Catálogo)** | Productos por vendedor vía `CatalogoIntegracionApiController` |
| **Módulo 4.10 (Monedero)** | Pedidos y saldo para métricas del proveedor |

---

## 8. Módulos que consumen este módulo

| Módulo | Qué consume |
|---|---|
| **Módulo 4.3** | Lista de tiendas/vendedores registrados |
| **Módulo 4.4 (Carrito)** | Tiendas activas para checkout |
| **Módulo 4.10** | Datos del repartidor para asignación de pedidos |

---

## 9. Pruebas unitarias

Las pruebas se encuentran en `tests/Unit/` y `tests/Feature/` siguiendo los lineamientos de PHPUnit.

```bash
# Ejecutar todas las pruebas
php artisan test

# Solo las del módulo proveedor
php artisan test --filter=Proveedor
php artisan test --filter=Tienda
php artisan test --filter=Repartidor
```

---

## 10. Comandos útiles

```bash
# Iniciar el servidor de desarrollo
php artisan serve

# Compilar assets de frontend
npm run dev

# Ejecutar migraciones pendientes
php artisan migrate

# Limpiar todas las cachés
php artisan optimize:clear

# Ver todas las rutas del módulo
php artisan route:list | findstr "proveedor\|tienda\|repartidor"
```
