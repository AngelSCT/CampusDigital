# Campus Digital — API REST

Backend de la plataforma Campus Digital, construido con Laravel y PostgreSQL. Maneja usuarios, roles, permisos, monedero universitario, pedidos por módulo y lectura de tarjetas RFID.

---

## Requisitos

- PHP 8.2+
- Composer
- PostgreSQL 16
- Laravel 11

---

## Instalación

```bash
git clone https://github.com/tu-usuario/campus-digital-app.git
cd campus-digital-app
composer install
```

Copia el archivo de entorno y ajusta los valores según tu máquina:

```bash
cp .env.example .env
php artisan key:generate
```

Crea la base de datos en PostgreSQL:

```sql
CREATE USER campus_user WITH PASSWORD 'postgres';
CREATE DATABASE campus_digital OWNER campus_user;
```

Importa el dump SQL incluido en el repositorio:

```bash
psql -U campus_user -d campus_digital -f base-datos-campus-digital.sql
```

Levanta el servidor:

```bash
php artisan serve
```

La API queda disponible en `http://localhost:8000/api/v1`

---

## Variables de entorno (.env)

El archivo `.env` va en la raíz del proyecto. Los valores más importantes:

```env
APP_NAME="Campus Digital"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=campus_digital
DB_USERNAME=campus_user
DB_PASSWORD=postgres

API_KEYS=4f8a2b1c9d3e7f6a0b5c8d2e1f4a7b3c6d9e2f5a8b1c4d7e0f3a6b9c2d5e8f1a
```

Si quieres conectar a Supabase en lugar de PostgreSQL local, cambia el bloque de base de datos por:

```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=tu_password
DB_SSLMODE=require
```

Y limpia la configuración:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Autenticación de la API

Todas las rutas (excepto `rfid/auth`) requieren estos dos headers:

| Header | Valor |
|---|---|
| `X-API-KEY` | `4f8a2b1c9d3e7f6a0b5c8d2e1f4a7b3c6d9e2f5a8b1c4d7e0f3a6b9c2d5e8f1a` |
| `Accept` | `application/json` |

Se puede probar con Insomnia o Postman.

---

## Endpoints

Base URL: `http://localhost:8000/api/v1`

### Usuarios

```
GET    /usuarios                    Lista paginada (15 por página por defecto)
GET    /usuarios?search=juan        Busca por nombre, apellido o email
GET    /usuarios?bloqueado=true     Filtra por estado de bloqueo
GET    /usuarios?per_page=5         Cambia el tamaño de página
GET    /usuarios/{id}               Detalle de un usuario
POST   /usuarios                    Crea un usuario nuevo
PUT    /usuarios/{id}               Actualiza datos del usuario
DELETE /usuarios/{id}               Soft delete
POST   /usuarios/{id}/toggle-block  Bloquea o desbloquea según estado actual
```

### Roles y Permisos

```
GET    /roles
GET    /roles/{id}
POST   /roles
PUT    /roles/{id}
DELETE /roles/{id}

GET    /permisos
GET    /permisos/{id}
POST   /permisos
PUT    /permisos/{id}
DELETE /permisos/{id}
```

### Asignaciones

```
GET    /rol-permisos                 Todas las asignaciones permiso → rol
GET    /rol-permisos?rol_id=1        Permisos de un rol específico
POST   /rol-permisos                 Asigna un permiso a un rol
DELETE /rol-permisos/{id}            Quita el permiso

GET    /usuario-roles                Todas las asignaciones rol → usuario
GET    /usuario-roles?usuario_id=1   Roles de un usuario
GET    /usuario-roles?rol_id=1       Usuarios con ese rol
POST   /usuario-roles                Asigna un rol a un usuario
DELETE /usuario-roles/{id}           Quita el rol
```

### Perfiles

```
GET  /usuario-perfiles
GET  /usuario-perfiles/{id}
PUT  /usuario-perfiles/{id}         Actualiza fecha de nacimiento, género, dirección
```

### Sesiones

```
GET  /sesiones
GET  /sesiones?activa=true
GET  /sesiones?usuario_id=1
GET  /sesiones?desde=2026-02-01&hasta=2026-02-28
GET  /sesiones?per_page=5
GET  /sesiones/{id}
```

### Bitácora

```
GET  /bitacora/accesos                              Historial de logins y logouts
GET  /bitacora/accesos?evento=login_success
GET  /bitacora/accesos?evento=login_failed
GET  /bitacora/accesos?exito=false
GET  /bitacora/accesos?usuario_id=1
GET  /bitacora/accesos?email=admin@campusdigital.com
GET  /bitacora/accesos?desde=2026-02-04&hasta=2026-02-04
GET  /bitacora/accesos/{id}

GET  /bitacora/actividad                            Acciones administrativas
GET  /bitacora/actividad?modulo=seguridad
GET  /bitacora/actividad?accion=eliminar_usuario
GET  /bitacora/actividad?target_tabla=usuario
GET  /bitacora/actividad/{id}
```

### Tarjetas universitarias

```
GET    /tarjetas
GET    /tarjetas?estado=activa           Estados: activa, bloqueada, perdida, cancelada
GET    /tarjetas?usuario_id=1
GET    /tarjetas/uid/{uid}               Busca por UID físico de la tarjeta
GET    /tarjetas/{id}
POST   /tarjetas                         Registra una tarjeta nueva
PUT    /tarjetas/{id}
DELETE /tarjetas/{id}
POST   /tarjetas/{id}/bloquear
POST   /tarjetas/{id}/desbloquear
```

### Lecturas de tarjeta

```
GET   /tarjeta-lecturas
GET   /tarjeta-lecturas?tarjeta_id=1
GET   /tarjeta-lecturas?uid_leido=A1B2C3D4E5F6
GET   /tarjeta-lecturas?modulo=cafeteria         Módulos: cafeteria, copias, biblioteca, souvenirs, acceso
GET   /tarjeta-lecturas?tipo_lectura=consumo     Tipos: acceso, consumo, consulta_saldo, confirmacion_entrega
GET   /tarjeta-lecturas?exito=true
GET   /tarjeta-lecturas?pedido_id=1
GET   /tarjeta-lecturas?operador_id=2
GET   /tarjeta-lecturas?desde=2026-02-01&hasta=2026-02-28
GET   /tarjeta-lecturas/{id}
POST  /tarjeta-lecturas
```

### Saldo monedero

```
GET   /saldo-monederos
GET   /saldo-monederos?usuario_id=1
GET   /saldo-monederos/usuario/{id}
GET   /saldo-monederos/{id}
POST  /saldo-monederos

GET   /saldo-movimientos
GET   /saldo-movimientos?tipo=abono
GET   /saldo-movimientos?tipo=cargo
GET   /saldo-movimientos?modulo=cafeteria
GET   /saldo-movimientos?usuario_id=1
GET   /saldo-movimientos?desde=2026-02-01&hasta=2026-02-28
GET   /saldo-movimientos/{id}
POST  /saldo-movimientos
```

### Pedidos

```
GET    /pedidos
GET    /pedidos?usuario_id=1
GET    /pedidos?estado=creado         Estados: creado, aceptado, en_proceso, listo, entregado, cancelado
GET    /pedidos?modulo=cafeteria      Módulos: cafeteria, copias, souvenirs
GET    /pedidos?folio=F-ABC
GET    /pedidos?operador_id=2
GET    /pedidos?desde=2026-02-01&hasta=2026-02-28
GET    /pedidos/{id}
POST   /pedidos
PUT    /pedidos/{id}
DELETE /pedidos/{id}
POST   /pedidos/{id}/estado           Avanza el estado del pedido
POST   /pedidos/{id}/confirmar-tarjeta
```

### RFID

Este grupo de rutas está pensado para lectores físicos y sistemas externos. `rfid/auth` es pública, el resto requiere API key.

```
POST  /rfid/auth                      Autentica por UID + PIN (no requiere API key)
POST  /rfid/verificar                 Valida UID + PIN sin exponer datos sensibles
GET   /rfid/usuario/{uid}             Perfil completo del dueño de la tarjeta
GET   /rfid/saldo/{uid}               Saldo disponible y retenido
GET   /rfid/historial/{uid}           Movimientos del monedero
GET   /rfid/historial/{uid}?tipo=cargo
GET   /rfid/historial/{uid}?modulo=cafeteria
GET   /rfid/pedidos/{uid}             Pedidos activos (por defecto: creado, aceptado, en_proceso, listo)
GET   /rfid/pedidos/{uid}?estado=listo&modulo=cafeteria
GET   /rfid/lecturas/{uid}            Historial de lecturas de esa tarjeta
GET   /rfid/lecturas/{uid}?modulo=cafeteria
GET   /rfid/lecturas/{uid}?desde=2026-01-01&hasta=2026-12-31
```

---

## Bodies JSON de referencia

**Crear usuario**
```json
{
    "nombre": "Carlos",
    "apellido": "López",
    "email": "carlos@test.com",
    "password": "12345678",
    "telefono": "5551234567"
}
```

**Crear tarjeta**
```json
{
    "usuario_id": 4,
    "uid": "A1B2C3D4E5F6",
    "estado": "activa",
    "registrado_por_usuario_id": 1
}
```

**Bloquear tarjeta**
```json
{
    "motivo_bloqueo": "Tarjeta reportada como robada",
    "bloqueado_por_usuario_id": 1
}
```

**Registrar lectura**
```json
{
    "uid_leido": "A1B2C3D4E5F6",
    "modulo": "cafeteria",
    "tipo_lectura": "consumo",
    "exito": true,
    "detalle": "Lectura en caja 1",
    "operador_usuario_id": 2
}
```

**Abono al monedero**
```json
{
    "usuario_id": 4,
    "tipo": "abono",
    "monto": 100.00,
    "modulo": "recarga",
    "concepto": "Recarga de saldo en ventanilla",
    "operador_usuario_id": 1
}
```

**Cargo al monedero**
```json
{
    "usuario_id": 4,
    "tipo": "cargo",
    "monto": 35.50,
    "modulo": "cafeteria",
    "concepto": "Comida del dia",
    "operador_usuario_id": 2,
    "tarjeta_lectura_id": 1,
    "referencia_tabla": "pedido",
    "referencia_id": 5
}
```

**Crear pedido**
```json
{
    "usuario_id": 4,
    "modulo": "cafeteria",
    "total": 35.50,
    "descripcion": "Comida del dia: arroz, pollo y agua",
    "notas": "Sin chile"
}
```

**Avanzar estado de pedido**
```json
{
    "estado": "aceptado",
    "operador_usuario_id": 2
}
```

**Auth RFID**
```json
{
    "uid": "8FE785CD",
    "pin": "1234"
}
```

---

## Notas

- Todos los deletes son soft delete, los registros no se borran físicamente.
- La paginación por defecto es de 15 registros. Se puede cambiar con `?per_page=N`.
- Los filtros de fecha usan el formato `YYYY-MM-DD`.
- El monedero se crea manualmente con `POST /saldo-monederos` pasando el `usuario_id`. No se genera automático al crear el usuario.
- La extensión `citext` de PostgreSQL es necesaria para búsquedas case-insensitive en emails. Asegúrate de tenerla disponible en tu instancia antes de importar el dump.
