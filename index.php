<?php

/**
 * index.php — Front Controller
 * Punto de entrada único — toda petición pasa por aquí
 * AnaMarcolMakeupStudios — DeskCod
 */

// ─────────────────────────────────────────────
// 1. HEADERS DE SEGURIDAD HTTP
// Se envían antes de cualquier output
// ─────────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ─────────────────────────────────────────────
// 2. CORE
// Define.php primero — todo depende de sus constantes
// Usar / en lugar de DS porque DS aún no está definido
// ─────────────────────────────────────────────
require_once __DIR__ . '/Config/Define.php';
require_once __DIR__ . '/Config/AutoLoad.php';
require_once __DIR__ . '/Config/Core/Auth.php';
require_once __DIR__ . '/Config/Core/RateLimiter.php';
require_once __DIR__ . '/Config/JRequest.php';
require_once __DIR__ . '/Config/JRouter.php';

AutoLoad::run();

// ─────────────────────────────────────────────
// 3. SESIÓN SEGURA
// SESSION_NAME único evita conflictos entre proyectos
// httponly → cookie inaccesible desde JS
// samesite → protección básica contra CSRF
// ─────────────────────────────────────────────
session_name(SESSION_NAME);

session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Genera el token una vez por sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Timeout de sesión — 2 horas de inactividad ──────
define('SESSION_TIMEOUT', 7200);
if (isset($_SESSION['ultima_actividad'])) {
    if (time() - $_SESSION['ultima_actividad'] > SESSION_TIMEOUT) {
        $esCliente = isset($_SESSION['cliente']);
        session_unset();
        session_destroy();
        header('Location: ' . ($esCliente
            ? APP_URL . 'Tienda/login?expired=1'
            : APP_URL . 'Auth/index?expired=1'));
        exit();
    }
}
$_SESSION['ultima_actividad'] = time();

// ─────────────────────────────────────────────
// 4. RUTA ACTUAL
// Extrae el primer segmento de la URL para
// determinar si es pública o protegida
// ─────────────────────────────────────────────
$urlActual = strtolower(trim($_GET['url'] ?? '', '/'));
$segmento  = explode('/', $urlActual)[0];

// ─────────────────────────────────────────────
// 5. IGNORAR ASSETS DEL NAVEGADOR
// El navegador pide favicon.ico, robots.txt, etc.
// automáticamente — si no los ignoramos se guardan
// como redirect_after_login y corrompen la redirección
// ─────────────────────────────────────────────
$assetsIgnorados = ['favicon.ico', 'robots.txt', 'sitemap.xml', 'apple-touch-icon.png'];
$requestUri      = strtolower($_SERVER['REQUEST_URI'] ?? '');

foreach ($assetsIgnorados as $asset) {
    if (str_contains($requestUri, $asset)) {
        http_response_code(404);
        exit();
    }
}

// ─────────────────────────────────────────────
// 6. LOGOUT
// Se procesa antes de cualquier otra lógica
// ─────────────────────────────────────────────
if ($segmento === 'auth' && isset($_GET['url']) && strpos($_GET['url'], 'logout') !== false) {
    Auth::logout();
    exit();
}

// ─────────────────────────────────────────────
// 7. CONTROL DE ACCESO
// PUBLIC_ROUTES definido en Define.php
// Si no está logueado y la ruta no es pública → login
// ─────────────────────────────────────────────
$esRutaPublica = in_array($segmento, PUBLIC_ROUTES, true);

if (!Auth::isLoggedIn() && !$esRutaPublica) {
    // Guardar solo rutas limpias — sin extensiones de archivo
    $requestUriClean = $_SERVER['REQUEST_URI'] ?? '';
    if (!preg_match('/\.[a-z]{2,4}$/i', $requestUriClean)) {
        $_SESSION['redirect_after_login'] = $requestUriClean;
    }
    header('Location: ' . APP_URL . 'Auth/index');
    exit();
}

// ─────────────────────────────────────────────
// 8. TEMPLATE + ROUTER
// Rutas públicas NO cargan el Template del panel admin
// Tienen su propio layout (Tienda, Login, Auth)
// ─────────────────────────────────────────────
$rutasSinTemplate = ['login', 'auth', 'tienda', 'api'];

// Rutas completas sin template (incluyen segmento + método)
$rutasCompletasSinTemplate = ['caja/recibo'];

// Métodos que retornan JSON — no cargar template
$metodosJson = ['toggle', 'delete', 'save', 'saveVariante', 'deleteVariante',
                'darkMode', 'buscar', 'barras', 'cobrar', 'search',
                'cambiarEstado', 'saveProductos', 'saveConfig',
                'dia', 'verificar', 'cambiarEstadoCita', 'saveConfigCitas',
                'checkout', 'guardarRegistro', 'procesarLogin', 'agendarCita',
                'obtener', 'marcarLeida', 'marcarTodas', 'eliminar',
                'marcarTour', 'activarTour', 'verificarStock'];

$metodoActual     = strtolower(explode('/', $urlActual)[1] ?? '');
$metodosJsonLower = array_map('strtolower', $metodosJson);

// Verificar si la URL completa empieza con alguna ruta sin template
$esRutaCompletaSinTemplate = (bool) array_filter(
    $rutasCompletasSinTemplate,
    fn($r) => str_starts_with($urlActual, $r)
);

if (!in_array($segmento, $rutasSinTemplate, true)
    && !$esRutaCompletaSinTemplate
    && !in_array($metodoActual, $metodosJsonLower, true)) {
    require_once TEMPLATE_PATH . 'index.php';
}

JRouter::run(new JRequest());