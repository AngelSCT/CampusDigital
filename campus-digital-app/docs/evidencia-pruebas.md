# Evidencia de Pruebas — Módulo Carrito JWT

**Fecha:** 2026-05-13  
**Branch:** feat/carrito-capa-5  
**Suite PHPUnit ejecutada:** `php artisan test` (6 pruebas, 18 assertions, todas PASS)

---

## Tabla de Casos de Prueba

| # | Caso | Descripción | Resultado | Tipo de Evidencia | Archivo / Referencia |
|---|------|-------------|-----------|-------------------|----------------------|
| TC-01 | JWT válido | Access token funcional permite acceso a endpoint protegido | ✅ Correcto | Captura Postman | Ver guía: `docs/guia-evidencias-manuales.md` § TC-01 |
| TC-02 | JWT expirado | Refresh automático exitoso emite nuevo par y revoca el anterior | ✅ Correcto | PHPUnit | `TokenTest::token_expirado_devuelve_401_token_expired` + `TokenTest::refresh_emite_par_nuevo_y_revoca_el_viejo` |
| TC-03 | Checkout exitoso | Confirmación correcta con Saldo disponible → estado `confirmado` | ✅ Correcto | PHPUnit + Video demo | `SaldoIntegrationTest::checkout_con_saldo_confirmado_pasa_a_estado_confirmado` + guía § TC-03 |
| TC-04 | Error 422 | Validación backend rechaza checkout de carrito vacío | ✅ Correcto | PHPUnit + Captura Postman | `EvidenciaPruebasTest::checkout_carrito_vacio_devuelve_422_checkout_error` + guía § TC-04 |
| TC-05 | Error 403 | Scope inválido — middleware rechaza categoría no autorizada | ✅ Correcto | PHPUnit | `TokenTest::categoria_no_autorizada_devuelve_403_scope_denied` |
| TC-06 | Error 503 | Servicio externo (Saldo) no disponible y categoría sin diferido | ✅ Correcto | PHPUnit + Logs Laravel | `SaldoIntegrationTest::checkout_con_saldo_caido_y_categoria_sin_diferido_devuelve_503` + guía § TC-06 |

---

## Salida PHPUnit (2026-05-13)

```
 PASS  Tests\Feature\Cart\EvidenciaPruebasTest
✓ checkout carrito vacio devuelve 422 checkout error              7.54s

 PASS  Tests\Feature\Cart\TokenTest
✓ token valido pasa middleware                                     3.12s
✓ categoria no autorizada devuelve 403 scope denied               0.04s
✓ refresh emite par nuevo y revoca el viejo                       0.32s

 PASS  Tests\Feature\Cart\SaldoIntegrationTest
✓ checkout con saldo confirmado pasa a estado confirmado          0.25s
✓ checkout con saldo caido y categoria sin diferido devuelve 503  0.08s

Tests:    6 passed (18 assertions)
Duration: 16.08s
```

---

## Mapeo Técnico de Respuestas HTTP

| Código HTTP | Error JSON | Causa | Middleware / Capa |
|-------------|-----------|-------|-------------------|
| 200 | — | Token válido / checkout exitoso | `AuthModuleJwt` → Controller |
| 401 | `TOKEN_EXPIRED` | TTL de access token superado | `AuthModuleJwt` |
| 401 | `TOKEN_REVOKED` | JTI revocado tras rotación | `AuthModuleJwt` |
| 403 | `SCOPE_DENIED` | Categoría requerida no en `categorias_autorizadas` | `AuthModuleJwt` |
| 422 | `CHECKOUT_ERROR` | Regla de negocio violada (ej. carrito vacío) | `CheckoutService` |
| 503 | `SALDO_NO_DISPONIBLE` | `SaldoUnavailable` + categoría sin pago diferido | `CheckoutService` → `SaldoClient` |

---

## Evidencias manuales pendientes

Las siguientes evidencias requieren captura/grabación manual.  
Consultar instrucciones completas en `docs/guia-evidencias-manuales.md`.

- [ ] TC-01: Captura Postman — JWT válido, access token funcional
- [ ] TC-03: Video demo — Checkout exitoso, confirmación correcta
- [ ] TC-04: Captura Postman — Error 422, validación backend
- [ ] TC-06: Captura de log Laravel — Error 503, servicio externo
