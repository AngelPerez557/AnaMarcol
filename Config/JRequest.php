<?php

class JRequest
{
    // Almacena los segmentos de la URL parseados
    // Ej: "Ejemplo/index/5" → ['Ejemplo', 'index', '5']
    private array $segments = [];

    // Almacena todos los parámetros GET y POST saneados
    private array $params = [];

    // Parsea la URL entrante y sanea todos los parámetros al instanciar
    public function __construct()
    {
        $this->parseUrl();
        $this->parseParams();
    }

    // ─────────────────────────────────────────────
    // PARSEO DE URL
    // ─────────────────────────────────────────────

    // Extrae y limpia los segmentos de la URL recibida por el Front Controller
    // Ej: ?url=Ejemplo/index/5 → ['Ejemplo', 'index', '5']
    private function parseUrl(): void
    {
        $url = $_GET['url'] ?? '';

        // Elimina caracteres peligrosos de la URL
        $url = filter_var(trim($url, '/'), FILTER_SANITIZE_URL);

        // Divide la URL en segmentos por "/"
        $this->segments = $url !== '' ? explode('/', $url) : [];
    }

    // ─────────────────────────────────────────────
    // PARSEO DE PARÁMETROS GET Y POST
    // ─────────────────────────────────────────────

    // Sanea y almacena todos los parámetros GET y POST
    // POST tiene prioridad sobre GET si existe la misma clave en ambos
    private function parseParams(): void
    {
        // Sanea cada valor GET eliminando tags HTML y espacios extremos
        foreach ($_GET as $key => $value) {
            if ($key === 'url') continue; // La URL ya fue procesada en parseUrl()
            $this->params[$key] = $this->sanitize($value);
        }

        // POST sobreescribe GET si comparten la misma clave
        foreach ($_POST as $key => $value) {
            $this->params[$key] = $this->sanitize($value);
        }
    }

    // Sanea un valor individual — acepta string o array de strings
    private function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            // Si el valor es un array, sanea cada elemento recursivamente
            return array_map([$this, 'sanitize'], $value);
        }

        // Elimina espacios extremos y tags HTML maliciosos
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    // ─────────────────────────────────────────────
    // ACCESO A SEGMENTOS DE URL
    // ─────────────────────────────────────────────

    // Retorna el controlador desde la URL (primer segmento)
    // Ej: "Ejemplo/index/5" → "Ejemplo"
    // Si no hay segmento retorna el controlador por defecto
    public function getController(): string
    {
        return $this->segments[0] ?? 'Dashboard';
    }

    // Retorna el método desde la URL (segundo segmento)
    // Ej: "Ejemplo/index/5" → "index"
    // Si no hay segmento retorna el método por defecto
    public function getMethod(): string
    {
        return $this->segments[1] ?? 'index';
    }

    // Retorna un segmento específico de la URL por su posición (base 0)
    // Ej: getSegment(2) en "Ejemplo/index/5" → "5"
    // Retorna null si el segmento no existe
    public function getSegment(int $index): ?string
    {
        return $this->segments[$index] ?? null;
    }

    // Retorna todos los segmentos de la URL como array
    public function getSegments(): array
    {
        return $this->segments;
    }

    // ─────────────────────────────────────────────
    // ACCESO A PARÁMETROS GET / POST
    // ─────────────────────────────────────────────

    // Retorna un parámetro GET o POST saneado por su nombre
    // Retorna $default si el parámetro no existe
    public function getParam(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    // Retorna todos los parámetros GET y POST saneados
    public function getParams(): array
    {
        return $this->params;
    }

    // ─────────────────────────────────────────────
    // DETECCIÓN DE TIPO DE PETICIÓN
    // ─────────────────────────────────────────────

    // Retorna true si la petición es POST
    public function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // Retorna true si la petición es GET
    public function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    // Retorna true si la petición viene de AJAX (XMLHttpRequest)
    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}