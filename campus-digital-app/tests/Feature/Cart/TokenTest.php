<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Bitacora;
use App\Models\Cart\ModuloCliente;
use App\Models\Cart\TokenModulo;
use App\Modules\Cart\Services\ModuleTokenService;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;

class TokenTest extends CartTestCase
{
    private ModuloCliente $modulo;
    private ModuleTokenService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->modulo  = $this->createTestModuloCliente([
            'categorias_autorizadas' => ['prestamo', 'reserva'],
        ]);
        $this->service = new ModuleTokenService();

        // Rutas de prueba exclusivas para estos tests.
        Route::get('/test-cart-protected', fn() => response()->json(['ok' => true]))
            ->middleware('auth.module.jwt');

        Route::get('/test-cart-scope-denied', fn() => response()->json(['ok' => true]))
            ->middleware('auth.module.jwt:producto'); // 'producto' no está en las categorías del módulo
    }

    // ─── Sección 6.1: Validación de JWT en middleware ────────────────────────

    #[Test]
    public function token_valido_pasa_middleware(): void
    {
        $tokens = $this->service->issuePair($this->modulo);

        $this->withToken($tokens['access_token'])
            ->getJson('/test-cart-protected')
            ->assertOk()
            ->assertJsonPath('ok', true);
    }

    #[Test]
    public function sin_header_authorization_devuelve_401_token_missing(): void
    {
        $this->getJson('/test-cart-protected')
            ->assertStatus(401)
            ->assertJsonPath('error', 'TOKEN_MISSING');
    }

    #[Test]
    public function firma_invalida_devuelve_401_token_invalid(): void
    {
        // Token firmado con un secreto diferente
        $tokenConFirmaWrong = $this->buildTokenWithSecret(
            ['sub' => '1', 'jti' => 'fake', 'exp' => now()->addHour()->timestamp,
             'iss' => 'campus-digital-carrito', 'token_type' => 'access'],
            'secreto-completamente-diferente'
        );

        $this->withToken($tokenConFirmaWrong)
            ->getJson('/test-cart-protected')
            ->assertStatus(401)
            ->assertJsonPath('error', 'TOKEN_INVALID');
    }

    #[Test]
    public function token_expirado_devuelve_401_token_expired(): void
    {
        $tokens = $this->service->issuePair($this->modulo);

        // Avanza el tiempo 2 horas (más que el TTL de 1 hora del access token)
        $this->travel(2)->hours();

        $this->withToken($tokens['access_token'])
            ->getJson('/test-cart-protected')
            ->assertStatus(401)
            ->assertJsonPath('error', 'TOKEN_EXPIRED');
    }

    #[Test]
    public function jti_revocado_devuelve_401_token_revoked(): void
    {
        $tokens = $this->service->issuePair($this->modulo);

        // Revoca el access token
        $payload = $this->decodePayload($tokens['access_token']);
        $this->service->revoke($payload['jti']);

        $this->withToken($tokens['access_token'])
            ->getJson('/test-cart-protected')
            ->assertStatus(401)
            ->assertJsonPath('error', 'TOKEN_REVOKED');
    }

    #[Test]
    public function refresh_token_usado_como_access_devuelve_401_token_invalid(): void
    {
        $tokens = $this->service->issuePair($this->modulo);

        // Usa el refresh token donde se espera un access token
        $this->withToken($tokens['refresh_token'])
            ->getJson('/test-cart-protected')
            ->assertStatus(401)
            ->assertJsonPath('error', 'TOKEN_INVALID');
    }

    #[Test]
    public function modulo_inactivo_devuelve_403_module_inactive(): void
    {
        $tokens = $this->service->issuePair($this->modulo);

        $this->modulo->update(['activo' => false]);

        $this->withToken($tokens['access_token'])
            ->getJson('/test-cart-protected')
            ->assertStatus(403)
            ->assertJsonPath('error', 'MODULE_INACTIVE');
    }

    #[Test]
    public function iss_incorrecto_devuelve_401_token_invalid(): void
    {
        $token = $this->buildTokenWithOwnSecret([
            'sub'                    => (string) $this->modulo->id,
            'iss'                    => 'issuer-equivocado',
            'token_type'             => 'access',
            'jti'                    => 'jti-fake',
            'exp'                    => now()->addHour()->timestamp,
            'iat'                    => now()->timestamp,
            'categorias_autorizadas' => ['prestamo'],
        ]);

        $this->withToken($token)
            ->getJson('/test-cart-protected')
            ->assertStatus(401)
            ->assertJsonPath('error', 'TOKEN_INVALID');
    }

    #[Test]
    public function categoria_no_autorizada_devuelve_403_scope_denied(): void
    {
        // El módulo solo tiene ['prestamo', 'reserva']; la ruta de prueba exige 'producto'.
        $tokens = $this->service->issuePair($this->modulo);

        $this->withToken($tokens['access_token'])
            ->getJson('/test-cart-scope-denied')
            ->assertStatus(403)
            ->assertJsonPath('error', 'SCOPE_DENIED');
    }

    // ─── Sección 6.4: Rotación del refresh token ─────────────────────────────

    #[Test]
    public function refresh_emite_par_nuevo_y_revoca_el_viejo(): void
    {
        $first   = $this->service->issuePair($this->modulo);
        $payload = $this->decodePayload($first['refresh_token']);
        $oldJti  = $payload['jti'];

        $second = $this->postJson('/api/cart/tokens/refresh', [
            'refresh_token' => $first['refresh_token'],
        ])->assertOk()
          ->assertJsonStructure(['access_token', 'refresh_token', 'expires_in'])
          ->json();

        // El access viejo queda revocado
        $this->assertDatabaseHas('cart_tokens_modulo', [
            'jti'               => $oldJti,
            'estado'            => TokenModulo::ESTADO_REVOCADO,
            'motivo_revocacion' => TokenModulo::MOTIVO_ROTACION,
        ]);

        // Los nuevos tokens son distintos
        $this->assertNotEquals($first['access_token'],  $second['access_token']);
        $this->assertNotEquals($first['refresh_token'], $second['refresh_token']);
    }

    #[Test]
    public function access_viejo_deja_de_funcionar_tras_refresh(): void
    {
        $first  = $this->service->issuePair($this->modulo);
        $oldAccess = $first['access_token'];

        $this->postJson('/api/cart/tokens/refresh', [
            'refresh_token' => $first['refresh_token'],
        ])->assertOk();

        // El access anterior debe rechazarse ahora
        $this->withToken($oldAccess)
            ->getJson('/test-cart-protected')
            ->assertStatus(401)
            ->assertJsonPath('error', 'TOKEN_REVOKED');
    }

    #[Test]
    public function reuso_de_refresh_rotado_revoca_cadena_y_registra_bitacora(): void
    {
        $first = $this->service->issuePair($this->modulo);
        $oldRefresh = $first['refresh_token'];

        // Primera rotación legítima
        $this->postJson('/api/cart/tokens/refresh', [
            'refresh_token' => $oldRefresh,
        ])->assertOk();

        // Reuso del refresh ya rotado (ataque)
        $this->postJson('/api/cart/tokens/refresh', [
            'refresh_token' => $oldRefresh,
        ])->assertStatus(401)
          ->assertJsonPath('error', 'TOKEN_REUSE_DETECTED');

        // Toda la cadena del módulo debe quedar revocada
        $activos = TokenModulo::where('modulo_id', $this->modulo->id)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->count();
        $this->assertEquals(0, $activos);

        // Bitácora debe registrar el incidente
        $this->assertDatabaseHas('cart_bitacora', [
            'accion'    => Bitacora::ACCION_TOKEN_REUSO_DETECTADO,
            'modulo_id' => $this->modulo->id,
        ]);
    }

    // ─── Helpers exclusivos de tests ─────────────────────────────────────────

    /** Construye un JWT firmado con el mismo secreto de tests. */
    private function buildTokenWithOwnSecret(array $payload): string
    {
        return $this->buildTokenWithSecret($payload, str_repeat('ab', 32));
    }

    /** Construye un JWT firmado con un secreto arbitrario. */
    private function buildTokenWithSecret(array $payload, string $secret): string
    {
        $b64u = fn(string $d) => rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
        $h    = $b64u(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $b    = $b64u(json_encode($payload));
        $sig  = $b64u(hash_hmac('sha256', "{$h}.{$b}", $secret, true));
        return "{$h}.{$b}.{$sig}";
    }

    /** Decodifica el payload de un JWT sin verificar firma. */
    private function decodePayload(string $token): array
    {
        $parts   = explode('.', $token);
        $padded  = $parts[1] . str_repeat('=', (4 - strlen($parts[1]) % 4) % 4);
        return json_decode(base64_decode(strtr($padded, '-_', '+/')), true);
    }
}
