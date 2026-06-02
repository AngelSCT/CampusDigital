# ConsumesCartApi — Guía de integración para módulos clientes

## ¿Para qué sirve?

El trait `ConsumesCartApi` permite a cualquier controlador de un módulo cliente (Biblioteca, Cafetería, Productos, etc.) hablar con la API privada del Módulo Carrito sin gestionar manualmente la autenticación JWT.

Características incluidas:
- Autenticación automática con el JWT del módulo
- **Refresh automático**: si el access token expira, el trait lo renueva y reintenta la operación original sin que el caller haga nada
- Excepciones tipadas por caso de error (saldo insuficiente, token expirado, validación, etc.)
- Sin exposición del JWT al usuario final (el token viaja solo como `Authorization: Bearer` en el header interno)

---

## Configuración del .env del módulo cliente

Agrega estas variables al `.env` de tu módulo:

```env
# Tokens emitidos por el admin del Carrito al aprobar tu solicitud de módulo
CART_CLIENT_MODULE_TOKEN=eyJ...
CART_CLIENT_REFRESH_TOKEN=eyJ...

# URL base donde vive la API privada del Carrito
# En desarrollo local apunta al mismo monorepo
CART_CLIENT_API_BASE_URL=http://localhost/api/cart
```

Para obtener tus tokens de módulo, pídele al administrador del sistema que apruebe tu solicitud en `/admin/cart/solicitudes`. El admin recibirá el par de tokens en pantalla (one-time display) y te los entregará.

---

## Ejemplo completo: Controller de Biblioteca

```php
<?php

namespace App\Http\Controllers\Biblioteca;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Support\ConsumesCartApi;
use App\Modules\Cart\Exceptions\Client\InsufficientFundsException;
use App\Modules\Cart\Exceptions\Client\CartApiUnavailableException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PrestamoController extends Controller
{
    use ConsumesCartApi;

    /** Crear un carrito nuevo para el usuario actual. */
    public function iniciarPrestamo(Request $request): JsonResponse
    {
        $carrito = $this->createCart(
            usuarioRef:      $request->user()->matricula,   // identificador opaco
            requiereSaldo:   false,
            expiraEnMinutos: 30,
            metadata:        ['origen' => 'biblioteca'],
        );

        return response()->json(['carrito_uuid' => $carrito['uuid']]);
    }

    /** Agregar un libro al carrito. */
    public function agregarLibro(Request $request, string $carritoUuid): JsonResponse
    {
        $item = $this->addItem($carritoUuid, [
            'categoria_slug'     => 'prestamo',
            'referencia_externa' => $request->input('isbn'),
            'nombre'             => $request->input('titulo'),
            'precio_unitario'    => 0.00,
            'cantidad'           => 1,
            'duracion_dias'      => $request->input('dias', 7),
        ]);

        return response()->json($item);
    }

    /** Confirmar el préstamo. */
    public function confirmarPrestamo(Request $request, string $carritoUuid): JsonResponse
    {
        try {
            $resultado = $this->checkout($carritoUuid);
            return response()->json($resultado);
        } catch (InsufficientFundsException) {
            return response()->json(['error' => 'Saldo insuficiente'], 402);
        } catch (CartApiUnavailableException) {
            return response()->json(['error' => 'Servicio no disponible'], 503);
        }
    }
}
```

---

## Manejo de excepciones

| Excepción | Cuándo ocurre | HTTP sugerido |
|-----------|--------------|---------------|
| `ModuleTokenExpiredException` | El refresh también falló. El token del módulo está completamente inválido. Contactar al admin del Carrito para emitir un nuevo par. | 503 (problema del servidor, no del usuario) |
| `CartApiUnavailableException` | Respuesta 5xx del servicio Carrito. Reintentar más tarde. | 503 |
| `CartValidationException` | Datos de la petición inválidos (422). `$e->errors` contiene los errores de campo. | 422 |
| `CartScopeException` | La categoría o acción no está autorizada para este módulo (403). | 403 |
| `InsufficientFundsException` | Saldo insuficiente en la cuenta del usuario (402). | 402 |
| `SaldoUnavailableForClientException` | El módulo Saldo está caído y la categoría no admite pago diferido (503). | 503 |

---

## Responsabilidades del módulo cliente

El Módulo Carrito es deliberadamente agnóstico al usuario final. Recibe un `usuario_ref` opaco (puede ser matrícula, UUID, etc.) y no valida su identidad.

**El módulo cliente ES responsable de:**
- Autenticar al usuario final antes de crear/modificar el carrito
- Pasar un `usuario_ref` consistente (mismo valor en todas las llamadas del mismo usuario)
- No exponer el `module_token` ni el `refresh_token` en ningún endpoint público

**El módulo cliente NO necesita:**
- Gestionar la expiración del JWT de módulo (el trait lo hace automáticamente)
- Conocer la URL interna del Carrito (viene del `.env`)
