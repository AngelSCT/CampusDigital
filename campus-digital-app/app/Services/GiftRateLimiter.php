<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Rate limiting con escalamiento de penas para acciones de regalo.
 *
 * Ventana deslizante de 3 min / 3 intentos.
 * Strike 1  → bloqueo 60 min
 * Strike 2+ → bloqueo 24 h
 */
class GiftRateLimiter
{
    private const WINDOW_SECS  = 180;       // 3 minutos
    private const MAX_ATTEMPTS = 3;
    private const STRIKES_TTL  = 172_800;   // 48 horas en segundos
    private const BAN_STRIKE_1 = 60;        // minutos
    private const BAN_STRIKE_2 = 1_440;     // 24 horas en minutos

    /**
     * Lanza ValidationException si el usuario está actualmente bloqueado.
     */
    public static function checkBlocked(int $userId): void
    {
        $blockedUntil = Cache::get("gift_blocked_until_{$userId}");

        if (! $blockedUntil || now()->timestamp >= $blockedUntil) {
            return;
        }

        $remainingSecs = $blockedUntil - now()->timestamp;
        $remainingMins = (int) ceil($remainingSecs / 60);

        $msg = $remainingMins >= 60
            ? 'Tu cuenta está bloqueada para enviar regalos por ' . ceil($remainingMins / 60) . ' hora(s) debido a actividad sospechosa.'
            : "Tu cuenta está bloqueada para enviar regalos por {$remainingMins} minuto(s). Intenta más tarde.";

        throw ValidationException::withMessages(['mensaje' => $msg]);
    }

    /**
     * Registra un intento. Si se alcanza el límite de intentos, aplica el
     * bloqueo correspondiente y lanza ValidationException.
     */
    public static function recordAttempt(int $userId): void
    {
        $attemptsKey = "gift_attempt_{$userId}";

        // Inicializar con TTL solo en el primer intento de la ventana
        if (! Cache::has($attemptsKey)) {
            Cache::put($attemptsKey, 0, now()->addSeconds(self::WINDOW_SECS));
        }

        $attempts = Cache::increment($attemptsKey);

        if ($attempts < self::MAX_ATTEMPTS) {
            return; // dentro del límite, sin acción
        }

        // ── Límite superado: escalar strike y aplicar ban ─────────────────────
        $strikesKey = "gift_strikes_{$userId}";
        if (! Cache::has($strikesKey)) {
            Cache::put($strikesKey, 0, now()->addSeconds(self::STRIKES_TTL));
        }
        $strikes = Cache::increment($strikesKey);

        $banMinutes   = $strikes === 1 ? self::BAN_STRIKE_1 : self::BAN_STRIKE_2;
        $blockedUntil = now()->addMinutes($banMinutes)->timestamp;

        Cache::put("gift_blocked_until_{$userId}", $blockedUntil, $banMinutes * 60);
        Cache::forget($attemptsKey); // resetear contador de intentos

        $msg = $strikes === 1
            ? 'Demasiados intentos de regalo en poco tiempo. Cuenta bloqueada por 60 minutos (Strike 1).'
            : "Actividad sospechosa detectada. Cuenta bloqueada por 24 horas (Strike {$strikes}).";

        throw ValidationException::withMessages(['mensaje' => $msg]);
    }
}
