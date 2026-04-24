<?php

class RateLimiter
{
    private static int $maxIntentos     = 5;
    private static int $bloqueoSegundos = 900; // 15 minutos

    public static function check(string $ip): bool
    {
        $key  = 'rate_' . md5($ip);
        $data = $_SESSION[$key] ?? null;
        if (!$data) return true;

        if (isset($data['bloqueado_hasta'])) {
            if (time() < $data['bloqueado_hasta']) return false;
            unset($_SESSION[$key]);
        }
        return true;
    }

    public static function registrarFallo(string $ip): void
    {
        $key  = 'rate_' . md5($ip);
        $data = $_SESSION[$key] ?? ['intentos' => 0];
        $data['intentos']++;
        $data['ultimo_intento'] = time();
        if ($data['intentos'] >= self::$maxIntentos) {
            $data['bloqueado_hasta'] = time() + self::$bloqueoSegundos;
        }
        $_SESSION[$key] = $data;
    }

    public static function limpiar(string $ip): void
    {
        unset($_SESSION['rate_' . md5($ip)]);
    }

    public static function minutosRestantes(string $ip): int
    {
        $key  = 'rate_' . md5($ip);
        $data = $_SESSION[$key] ?? null;
        if (!$data || !isset($data['bloqueado_hasta'])) return 0;
        return (int) ceil(($data['bloqueado_hasta'] - time()) / 60);
    }
}
