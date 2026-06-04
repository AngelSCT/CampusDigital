# 🎓 Dashboard Administrativo — Monedero Universitario
> Convertido de React/TypeScript → **Laravel 11** + Blade + Tailwind CSS

---

## 📁 Estructura del proyecto

```
laravel-dashboard/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/AuthController.php       # Login, registro, logout
│   │   ├── DashboardController.php       # Pantalla principal
│   │   ├── RechargeController.php        # Recargar saldo
│   │   ├── TransactionController.php     # Historial de movimientos
│   │   ├── WalletController.php          # Detalle del monedero
│   │   ├── ProfileController.php         # Configuración / perfil
│   │   └── SupportController.php        # Soporte
│   └── Models/
│       ├── User.php
│       ├── Wallet.php
│       └── Transaction.php
├── database/
│   ├── migrations/                       # 3 migraciones
│   └── seeders/DatabaseSeeder.php        # Datos demo
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php                 # Layout principal (sidebar + nav)
│   │   └── auth.blade.php               # Layout de autenticación
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   └── student/
│       ├── home.blade.php               # Dashboard principal
│       ├── recharge.blade.php           # Recargar saldo
│       ├── movements.blade.php          # Historial con filtros
│       ├── wallet.blade.php             # Mi monedero
│       ├── config.blade.php             # Configuración
│       └── support.blade.php           # Soporte
└── routes/web.php                        # Todas las rutas
```

---

## 🚀 Instalación paso a paso

### 1. Clonar / descomprimir el proyecto
```bash
cd tu-carpeta
unzip laravel-dashboard.zip
cd laravel-dashboard
```

### 2. Instalar dependencias PHP
```bash
composer install
```

### 3. Instalar dependencias JS (opcional, usa CDN por defecto)
```bash
npm install
npm run build
```

### 4. Configurar el entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Base de datos (SQLite — sin configuración extra)
```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed
```

> Para MySQL, edita `.env` y cambia `DB_CONNECTION=mysql` con tus credenciales.

### 6. Iniciar servidor
```bash
php artisan serve
```

Abre **http://localhost:8000**

---

## 👤 Usuario demo (seeder)

| Campo    | Valor                    |
|----------|--------------------------|
| Email    | ana@universidad.mx       |
| Password | password                 |
| Saldo    | $1,250.50                |
| Carrera  | Ingeniería en Sistemas   |

---

## 🗺️ Rutas disponibles

| Método | URL                     | Nombre              | Descripción              |
|--------|-------------------------|---------------------|--------------------------|
| GET    | /login                  | login               | Formulario de login      |
| POST   | /login                  | —                   | Procesar login           |
| GET    | /register               | register            | Formulario de registro   |
| POST   | /register               | —                   | Crear cuenta             |
| POST   | /logout                 | logout              | Cerrar sesión            |
| GET    | /dashboard              | dashboard           | Pantalla principal       |
| GET    | /recargar               | recharge.index      | Formulario de recarga    |
| POST   | /recargar               | recharge.store      | Procesar recarga         |
| GET    | /movimientos            | movements.index     | Historial + filtros      |
| GET    | /monedero               | wallet.index        | Detalle del monedero     |
| GET    | /configuracion          | profile.index       | Perfil y ajustes         |
| PUT    | /configuracion/perfil   | profile.update      | Actualizar perfil        |
| PUT    | /configuracion/contrasena | profile.password  | Cambiar contraseña       |
| GET    | /soporte                | support.index       | Centro de soporte        |
| POST   | /soporte                | support.store       | Enviar mensaje           |

---

## 🎨 Stack tecnológico

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Blade Templates + Tailwind CSS (vía CDN)
- **Base de datos:** SQLite (por defecto) / MySQL compatible
- **Autenticación:** Laravel Auth nativa (sin Breeze/Jetstream)

---

## 💡 Notas

- El CSS se carga vía **CDN de Tailwind** para que funcione sin necesidad de ejecutar `npm run build`. Para producción se recomienda compilar con Vite.
- El seeder crea 12 transacciones de ejemplo distribuidas en los últimos 18 días.
- Para agregar más categorías de gasto, edita el seeder o crea transacciones desde Tinker: `php artisan tinker`.
