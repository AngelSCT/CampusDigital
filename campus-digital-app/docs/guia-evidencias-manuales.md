# Guía de Evidencias Manuales

Pasos exactos para capturar las 4 evidencias que requieren intervención humana.  
Prerequisito: Laragon corriendo, base de datos migrada, `.env` con `APP_URL=http://campus-digital-app.test`.

---

## TC-01 — Captura Postman: JWT válido, access token funcional

**Objetivo:** demostrar que un access token válido permite acceso a un endpoint protegido.

### Paso 1 — Obtener un access token

1. Abre Postman.
2. Crea una request **POST** `http://campus-digital-app.test/api/cart/tokens`.
3. En **Body → raw → JSON** envía:
   ```json
   {
     "modulo_id": <ID del módulo activo en tu BD>
   }
   ```
4. Haz clic en **Send**.
5. Copia el valor de `access_token` de la respuesta.

> Si no tienes un módulo registrado, ejecuta en Tinker:
> ```bash
> php artisan tinker
> >>> App\Models\Cart\ModuloCliente::create(['solicitud_id'=>1,'nombre'=>'Demo','slug'=>'demo','tipo_modulo'=>'biblioteca','categorias_autorizadas'=>['prestamo'],'activo'=>true]);
> ```

### Paso 2 — Llamar al endpoint protegido

1. Crea una request **GET** `http://campus-digital-app.test/api/cart/carritos` (o cualquier ruta con middleware `auth.module.jwt`).
2. En **Authorization → Bearer Token** pega el `access_token` copiado.
3. Haz clic en **Send**.
4. Verifica que el status sea **200 OK**.

### Paso 3 — Capturar evidencia

- Toma screenshot de Postman mostrando:
  - URL de la request
  - Header `Authorization: Bearer <token>`
  - Status **200 OK** en la respuesta
- Guarda como `evidencia-tc01-jwt-valido.png`

---

## TC-03 — Video demo: Checkout exitoso, confirmación correcta

**Objetivo:** demostrar el flujo completo de checkout con Saldo disponible.

### Paso 1 — Preparar datos

Ejecuta en Tinker (`php artisan tinker`) — **pega todo el bloque de una sola vez**:
```php
$modulo = App\Models\Cart\ModuloCliente::where('activo', true)->first();
$par    = (new App\Modules\Cart\Services\ModuleTokenService())->issuePair($modulo);
echo "ACCESS TOKEN:\n" . $par['access_token'] . "\n\n";

$carrito = (new App\Modules\Cart\Services\CarritoService())->crear($modulo, [
    'usuario_ref'    => 'MAT-DEMO-001',
    'requiere_saldo' => false,
]);

$cat = App\Models\Cart\Categoria::where('slug', 'prestamo')->first();
App\Models\Cart\ItemCarrito::create([
    'carrito_id'         => $carrito->id,
    'categoria_id'       => $cat->id,
    'referencia_externa' => 'LIBRO-001',
    'nombre'             => 'Fundamentos de Laravel',
    'precio_unitario'    => 50.00,
    'cantidad'           => 1,
    'added_at'           => now(),
]);
$carrito->update(['total' => '50.00']);
echo "UUID carrito: " . $carrito->uuid . "\n";
```

### Paso 2 — Ejecutar checkout en Postman

1. Crea request **POST** `http://campus-digital-app.test/api/cart/carritos/<UUID>/checkout`
2. **Authorization → Bearer Token**: pega el `access_token`.
3. **Body → raw → JSON**:
   ```json
   {
     "metadata_checkout": { "canal": "demo" }
   }
   ```
4. Haz clic en **Send**.
5. Verifica respuesta `200 OK` con `"estado": "confirmado"`.

### Paso 3 — Grabar video

Graba con cualquier herramienta (OBS, Loom, ShareX):
- Muestra la request completa (URL, token, body)
- Muestra la respuesta: status 200, campo `estado: confirmado`, campo `confirmed_at`
- Duración recomendada: 30-60 segundos
- Guarda como `evidencia-tc03-checkout-exitoso.mp4`

---

## TC-04 — Captura Postman: Error 422, validación backend

**Objetivo:** demostrar que el backend rechaza con 422 cuando el carrito está vacío.

### Paso 1 — Crear un carrito vacío

En Tinker:
```php
$modulo = App\Models\Cart\ModuloCliente::where('activo', true)->first();
$par    = (new App\Modules\Cart\Services\ModuleTokenService())->issuePair($modulo);
echo "ACCESS TOKEN:\n" . $par['access_token'] . "\n";

$carrito = App\Models\Cart\Carrito::create([
    'uuid'           => \Illuminate\Support\Str::uuid(),
    'modulo_id'      => $modulo->id,
    'usuario_ref'    => 'MAT-EMPTY-001',
    'estado'         => 'abierto',
    'requiere_saldo' => false,
    'total'          => '0.00',
]);
echo "UUID carrito vacío: " . $carrito->uuid . "\n";
```

### Paso 2 — Intentar checkout

1. Postman **POST** `http://campus-digital-app.test/api/cart/carritos/<UUID>/checkout`
2. **Authorization → Bearer Token**: access token del paso anterior.
3. **Body → raw → JSON**: `{}`
4. Haz clic en **Send**.
5. Verifica **status 422** con body:
   ```json
   {
     "error": "CHECKOUT_ERROR",
     "mensaje": "No se puede hacer checkout de un carrito vacío."
   }
   ```

### Paso 3 — Capturar evidencia

- Screenshot mostrando:
  - URL con el UUID del carrito
  - Status **422 Unprocessable Content**
  - Body con `"error": "CHECKOUT_ERROR"`
- Guarda como `evidencia-tc04-error-422.png`

---

## TC-06 — Captura de Logs Laravel: Error 503, servicio externo no disponible

**Objetivo:** demostrar que cuando Saldo no responde y la categoría no permite diferido, el sistema devuelve 503 y lo registra en logs.

### Paso 1 — Configurar Saldo como caído

En `.env`, apunta a una URL inválida temporalmente:
```
SALDO_BASE_URL=http://localhost:9999
```
Luego ejecuta `php artisan config:clear`.

### Paso 2 — Preparar carrito con requiere_saldo=true

En Tinker:
```php
$modulo = App\Models\Cart\ModuloCliente::where('slug','demo')->first();
$svc = new App\Modules\Cart\Services\ModuleTokenService();
$par = $svc->issuePair($modulo);
echo "ACCESS TOKEN:\n" . $par['access_token'] . "\n";

$carrito = App\Models\Cart\Carrito::create([
    'uuid'           => \Illuminate\Support\Str::uuid(),
    'modulo_id'      => $modulo->id,
    'usuario_ref'    => 'MAT-503-001',
    'estado'         => 'abierto',
    'requiere_saldo' => true,
    'total'          => '100.00',
]);
$cat = App\Models\Cart\Categoria::where('slug','prestamo')->first();
App\Models\Cart\ItemCarrito::create([
    'carrito_id'         => $carrito->id,
    'categoria_id'       => $cat->id,
    'referencia_externa' => 'LIBRO-503',
    'nombre'             => 'Test 503',
    'precio_unitario'    => 100.00,
    'cantidad'           => 1,
    'added_at'           => now(),
]);
$carrito->update(['total' => '100.00']);
echo "UUID: " . $carrito->uuid . "\n";
```

### Paso 3 — Ejecutar checkout

1. Postman **POST** `http://campus-digital-app.test/api/cart/carritos/<UUID>/checkout`
2. Bearer token del paso anterior.
3. Body: `{}`
4. Verifica **status 503** con `"error": "SALDO_NO_DISPONIBLE"`.

### Paso 4 — Capturar logs de Laravel

Abre una terminal y ejecuta:
```powershell
Get-Content "storage\logs\laravel.log" -Tail 30
```

Busca la entrada que contenga `SaldoUnavailableException` o `saldo.no_disponible`.

### Paso 5 — Capturar evidencia

- Screenshot 1: Postman con status **503** y body `"error": "SALDO_NO_DISPONIBLE"`
- Screenshot 2: Terminal con el extracto del log de Laravel
- Guarda como `evidencia-tc06-error-503-postman.png` y `evidencia-tc06-error-503-log.png`

### Paso 6 — Restaurar configuración

```
SALDO_BASE_URL=<valor original>
```
Ejecuta `php artisan config:clear` nuevamente.
