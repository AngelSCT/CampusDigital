<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Bitacora;
use App\Models\Cart\ModuloCliente;
use App\Models\Cart\TokenModulo;
use App\Modules\Cart\Exceptions\ModuleInactiveException;
use App\Modules\Cart\Exceptions\TokenExpiredException;
use App\Modules\Cart\Exceptions\TokenInvalidException;
use App\Modules\Cart\Exceptions\TokenReuseDetectedException;
use App\Modules\Cart\Exceptions\TokenRevokedException;
use Illuminate\Support\Str;

class ModuleTokenService
{
    private const ISSUER = 'campus-digital-carrito';

    private string $secret;
    private int $ttlAccess;
    private int $ttlRefresh;

    public function __construct()
    {
        $this->secret     = config('cart.jwt.secret', '');
        $this->ttlAccess  = (int) config('cart.jwt.ttl_access', 3600);
        $this->ttlRefresh = (int) config('cart.jwt.ttl_refresh', 604800);
    }

    /**
     * Emite un par access+refresh inicial para un módulo aprobado.
     * Devuelve los JWTs en claro; el caller es responsable de la entrega one-time.
     */
    public function issuePair(ModuloCliente $modulo): array
    {
        $now        = now();
        $accessJti  = (string) Str::uuid();
        $refreshJti = (string) Str::uuid();

        $accessToken = $this->encode([
            'sub'                    => (string) $modulo->id,
            'tipo_modulo'            => $modulo->tipo_modulo,
            'categorias_autorizadas' => $modulo->categorias_autorizadas,
            'modulo_slug'            => $modulo->slug,
            'iat'                    => $now->getTimestamp(),
            'exp'                    => $now->copy()->addSeconds($this->ttlAccess)->getTimestamp(),
            'jti'                    => $accessJti,
            'iss'                    => self::ISSUER,
            'token_type'             => TokenModulo::TIPO_ACCESS,
        ]);

        $refreshToken = $this->encode([
            'sub'         => (string) $modulo->id,
            'modulo_slug' => $modulo->slug,
            'iat'         => $now->getTimestamp(),
            'exp'         => $now->copy()->addSeconds($this->ttlRefresh)->getTimestamp(),
            'jti'         => $refreshJti,
            'iss'         => self::ISSUER,
            'token_type'  => TokenModulo::TIPO_REFRESH,
        ]);

        // Persiste ambos con pair_jti cruzado; replaces_jti=null porque es el par inicial.
        TokenModulo::create([
            'modulo_id'    => $modulo->id,
            'jti'          => $accessJti,
            'tipo'         => TokenModulo::TIPO_ACCESS,
            'estado'       => TokenModulo::ESTADO_ACTIVO,
            'emitido_at'   => $now,
            'expira_at'    => $now->copy()->addSeconds($this->ttlAccess),
            'pair_jti'     => $refreshJti,
            'replaces_jti' => null,
        ]);

        TokenModulo::create([
            'modulo_id'    => $modulo->id,
            'jti'          => $refreshJti,
            'tipo'         => TokenModulo::TIPO_REFRESH,
            'estado'       => TokenModulo::ESTADO_ACTIVO,
            'emitido_at'   => $now,
            'expira_at'    => $now->copy()->addSeconds($this->ttlRefresh),
            'pair_jti'     => $accessJti,
            'replaces_jti' => null,
        ]);

        Bitacora::create([
            'accion'    => Bitacora::ACCION_TOKEN_EMISION,
            'modulo_id' => $modulo->id,
            'jti'       => $accessJti,
            'payload'   => ['tipo' => 'par_inicial'],
        ]);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => $this->ttlAccess,
        ];
    }

    /**
     * Valida un refresh token y emite un par nuevo, revocando el par viejo.
     * Detecta reuso de refresh ya rotado (posible robo).
     *
     * @throws TokenInvalidException
     * @throws TokenExpiredException
     * @throws TokenRevokedException
     * @throws ModuleInactiveException
     * @throws TokenReuseDetectedException
     */
    public function rotate(string $refreshTokenString): array
    {
        $payload = $this->decode($refreshTokenString); // lanza TokenInvalidException si firma/formato inválido

        if (($payload['token_type'] ?? null) !== TokenModulo::TIPO_REFRESH) {
            throw new TokenInvalidException();
        }

        if (($payload['exp'] ?? 0) < now()->getTimestamp()) {
            throw new TokenExpiredException();
        }

        if (($payload['iss'] ?? null) !== self::ISSUER) {
            throw new TokenInvalidException();
        }

        $jti    = $payload['jti'] ?? null;
        $record = TokenModulo::where('jti', $jti)->first();

        if (!$record) {
            throw new TokenInvalidException();
        }

        // Detección de reuso: refresh ya rotado anteriormente.
        if ($record->estado === TokenModulo::ESTADO_REVOCADO &&
            $record->motivo_revocacion === TokenModulo::MOTIVO_ROTACION) {
            $this->handleReuseDetected($record->modulo_id, $jti);
            throw new TokenReuseDetectedException();
        }

        if ($record->estado !== TokenModulo::ESTADO_ACTIVO) {
            throw new TokenRevokedException();
        }

        $modulo = ModuloCliente::find($record->modulo_id);
        if (!$modulo || !$modulo->activo) {
            throw new ModuleInactiveException();
        }

        return $this->issueRotatedPair($modulo, $record);
    }

    /**
     * Revoca un token (y su par) por jti.
     */
    public function revoke(string $jti, string $motivo = TokenModulo::MOTIVO_MANUAL, ?int $adminUserId = null): void
    {
        $record = TokenModulo::where('jti', $jti)->first();

        if (!$record) {
            throw new TokenInvalidException();
        }

        $jtis = array_filter([$jti, $record->pair_jti]);
        TokenModulo::whereIn('jti', $jtis)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->update([
                'estado'            => TokenModulo::ESTADO_REVOCADO,
                'revocado_at'       => now(),
                'revocado_por'      => $adminUserId,
                'motivo_revocacion' => $motivo,
            ]);

        Bitacora::create([
            'accion'    => Bitacora::ACCION_TOKEN_REVOCACION,
            'modulo_id' => $record->modulo_id,
            'user_id'   => $adminUserId,
            'jti'       => $jti,
            'payload'   => ['motivo' => $motivo],
        ]);
    }

    /**
     * Aplica los 9 pasos de validación (sección 4.6).
     * El paso 1 (header Authorization) lo aplica el middleware antes de llamar aquí.
     * El paso 9 (scope por endpoint) lo aplica el middleware después de este método.
     *
     * @return array El payload completo del JWT.
     * @throws TokenInvalidException
     * @throws TokenExpiredException
     * @throws TokenRevokedException
     * @throws ModuleInactiveException
     */
    public function validate(string $accessTokenString): array
    {
        // Pasos 2 + 3: formato y firma.
        $payload = $this->decode($accessTokenString);

        // Paso 4: exp.
        if (($payload['exp'] ?? 0) < now()->getTimestamp()) {
            throw new TokenExpiredException();
        }

        // Paso 5: iss.
        if (($payload['iss'] ?? null) !== self::ISSUER) {
            throw new TokenInvalidException();
        }

        // Paso 6: token_type debe ser 'access'.
        if (($payload['token_type'] ?? null) !== TokenModulo::TIPO_ACCESS) {
            throw new TokenInvalidException();
        }

        // Paso 7: jti en tokens_modulo con estado='activo'.
        $record = TokenModulo::where('jti', $payload['jti'] ?? '')->first();

        if (!$record) {
            // jti desconocido — nunca emitido por nosotros.
            throw new TokenInvalidException();
        }

        if ($record->estado !== TokenModulo::ESTADO_ACTIVO) {
            throw new TokenRevokedException();
        }

        // Paso 8: módulo activo.
        $modulo = ModuloCliente::find($record->modulo_id);
        if (!$modulo || !$modulo->activo) {
            throw new ModuleInactiveException();
        }

        // Paso 9 delegado al middleware (scope por categoría).
        return $payload;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internos
    // ─────────────────────────────────────────────────────────────────────────

    private function issueRotatedPair(ModuloCliente $modulo, TokenModulo $oldRefreshRecord): array
    {
        $now           = now();
        $newAccessJti  = (string) Str::uuid();
        $newRefreshJti = (string) Str::uuid();
        $oldAccessJti  = $oldRefreshRecord->pair_jti; // par del refresh anterior

        $newAccessToken = $this->encode([
            'sub'                    => (string) $modulo->id,
            'tipo_modulo'            => $modulo->tipo_modulo,
            'categorias_autorizadas' => $modulo->categorias_autorizadas,
            'modulo_slug'            => $modulo->slug,
            'iat'                    => $now->getTimestamp(),
            'exp'                    => $now->copy()->addSeconds($this->ttlAccess)->getTimestamp(),
            'jti'                    => $newAccessJti,
            'iss'                    => self::ISSUER,
            'token_type'             => TokenModulo::TIPO_ACCESS,
        ]);

        $newRefreshToken = $this->encode([
            'sub'         => (string) $modulo->id,
            'modulo_slug' => $modulo->slug,
            'iat'         => $now->getTimestamp(),
            'exp'         => $now->copy()->addSeconds($this->ttlRefresh)->getTimestamp(),
            'jti'         => $newRefreshJti,
            'iss'         => self::ISSUER,
            'token_type'  => TokenModulo::TIPO_REFRESH,
        ]);

        TokenModulo::create([
            'modulo_id'    => $modulo->id,
            'jti'          => $newAccessJti,
            'tipo'         => TokenModulo::TIPO_ACCESS,
            'estado'       => TokenModulo::ESTADO_ACTIVO,
            'emitido_at'   => $now,
            'expira_at'    => $now->copy()->addSeconds($this->ttlAccess),
            'pair_jti'     => $newRefreshJti,
            'replaces_jti' => $oldAccessJti,
        ]);

        TokenModulo::create([
            'modulo_id'    => $modulo->id,
            'jti'          => $newRefreshJti,
            'tipo'         => TokenModulo::TIPO_REFRESH,
            'estado'       => TokenModulo::ESTADO_ACTIVO,
            'emitido_at'   => $now,
            'expira_at'    => $now->copy()->addSeconds($this->ttlRefresh),
            'pair_jti'     => $newAccessJti,
            'replaces_jti' => $oldRefreshRecord->jti,
        ]);

        // Revoca el par viejo con motivo='rotacion'.
        $revocables = array_filter([$oldAccessJti, $oldRefreshRecord->jti]);
        TokenModulo::whereIn('jti', $revocables)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->update([
                'estado'            => TokenModulo::ESTADO_REVOCADO,
                'revocado_at'       => $now,
                'motivo_revocacion' => TokenModulo::MOTIVO_ROTACION,
            ]);

        return [
            'access_token'  => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in'    => $this->ttlAccess,
        ];
    }

    private function handleReuseDetected(int $moduloId, string $jti): void
    {
        TokenModulo::where('modulo_id', $moduloId)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->update([
                'estado'            => TokenModulo::ESTADO_REVOCADO,
                'revocado_at'       => now(),
                'motivo_revocacion' => TokenModulo::MOTIVO_COMPROMISO,
            ]);

        Bitacora::create([
            'accion'    => Bitacora::ACCION_TOKEN_REUSO_DETECTADO,
            'modulo_id' => $moduloId,
            'jti'       => $jti,
            'payload'   => ['accion' => 'cadena_revocada_por_reuso'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Codificación JWT (HS256 puro, sin dependencias externas)
    // ─────────────────────────────────────────────────────────────────────────

    private function encode(array $payload): string
    {
        $h   = $this->b64u(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $b   = $this->b64u(json_encode($payload));
        $sig = $this->b64u(hash_hmac('sha256', "{$h}.{$b}", $this->secret, true));
        return "{$h}.{$b}.{$sig}";
    }

    /** @throws TokenInvalidException */
    private function decode(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new TokenInvalidException();
        }

        [$h, $b, $s] = $parts;
        $expected = $this->b64u(hash_hmac('sha256', "{$h}.{$b}", $this->secret, true));

        if (!hash_equals($expected, $s)) {
            throw new TokenInvalidException();
        }

        $payload = json_decode($this->b64uDecode($b), true);
        if (!is_array($payload)) {
            throw new TokenInvalidException();
        }

        return $payload;
    }

    private function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function b64uDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
