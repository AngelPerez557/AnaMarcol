<?php

class Auth
{
    // Clave del array en $_SESSION donde se almacena el usuario autenticado
    private static string $sessionKey = 'user';

    // ─────────────────────────────────────────────
    // AUTENTICACIÓN — LOGIN / LOGOUT
    // ─────────────────────────────────────────────

    // Almacena los datos del usuario en sesión al iniciar sesión correctamente
    public static function login(array $userData): void
    {
        // Regenera el ID de sesión para prevenir Session Fixation
        session_regenerate_id(true);

        $_SESSION[self::$sessionKey] = [
            'id'       => $userData['id'],
            'nombre'   => $userData['nombre'],
            'email'    => $userData['email'],
            'rol_id'   => $userData['rol_id'],
            'rol_slug' => $userData['rol_slug'],
            'permisos' => $userData['permisos'] ?? [],
        ];
    }

    // Destruye la sesión completamente y redirige al login
    public static function logout(): void
    {
        // Limpia todas las variables de sesión
        $_SESSION = [];

        // Elimina la cookie de sesión del navegador
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destruye la sesión en el servidor
        session_destroy();

        header('Location: ' . APP_URL . 'Auth/index');
        exit();
    }

    // ─────────────────────────────────────────────
    // VERIFICACIÓN DE SESIÓN
    // ─────────────────────────────────────────────

    // Retorna true si hay un usuario autenticado en sesión
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION[self::$sessionKey]['id']);
    }

    // Fuerza autenticación — redirige al login si no hay sesión activa
    // Se llama al inicio de cada método de controlador protegido
    public static function check(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_URL . 'Auth/index');
            exit();
        }
    }

    // ─────────────────────────────────────────────
    // ACCESO A DATOS DEL USUARIO EN SESIÓN
    // ─────────────────────────────────────────────

    // Retorna todos los datos del usuario autenticado
    public static function user(): ?array
    {
        return $_SESSION[self::$sessionKey] ?? null;
    }

    // Retorna un campo específico del usuario autenticado
    // Ej: Auth::get('nombre') → "Juan Pérez"
    public static function get(string $field): mixed
    {
        return $_SESSION[self::$sessionKey][$field] ?? null;
    }

    // Retorna el ID del usuario autenticado
    public static function id(): ?int
    {
        return $_SESSION[self::$sessionKey]['id'] ?? null;
    }

    // Retorna el slug del rol del usuario autenticado
    // Ej: "admin", "empleado", "cliente"
    public static function role(): ?string
    {
        return $_SESSION[self::$sessionKey]['rol_slug'] ?? null;
    }

    // ─────────────────────────────────────────────
    // RBAC — CONTROL DE PERMISOS
    // ─────────────────────────────────────────────

    // Verifica si el usuario autenticado tiene un permiso específico
    // Ej: Auth::can('usuarios.crear') → true | false
    public static function can(string $permission): bool
    {
        $permisos = $_SESSION[self::$sessionKey]['permisos'] ?? [];
        return in_array($permission, $permisos, true);
    }

    // Verifica si el usuario tiene alguno de los permisos del array
    // Ej: Auth::canAny(['usuarios.crear', 'usuarios.editar']) → true si tiene al menos uno
    public static function canAny(array $permissions): bool
    {
        $permisos = $_SESSION[self::$sessionKey]['permisos'] ?? [];
        return !empty(array_intersect($permissions, $permisos));
    }

    // Verifica si el usuario tiene todos los permisos del array
    // Ej: Auth::canAll(['usuarios.crear', 'usuarios.editar']) → true solo si tiene ambos
    public static function canAll(array $permissions): bool
    {
        $permisos = $_SESSION[self::$sessionKey]['permisos'] ?? [];
        return empty(array_diff($permissions, $permisos));
    }

    // Fuerza verificación de permiso — redirige a 403 si no tiene acceso
    // Se usa en controladores para proteger métodos específicos
    public static function require(string $permission): void
    {
        if (!self::can($permission)) {
            http_response_code(403);

            if (APP_ENV === 'development') {
                die("<h2 style='font-family:monospace;color:#c0392b;'>403 | Sin permiso: '{$permission}'</h2>");
            }

            $view403 = VIEWS_PATH . '403' . DS . 'index.php';
            if (file_exists($view403)) {
                require_once $view403;
            } else {
                die('<h2>403 | Acceso denegado.</h2>');
            }
            exit();
        }
    }

    // Verifica si el usuario autenticado tiene un rol específico
    // Ej: Auth::hasRole('admin') → true | false
    public static function hasRole(string $roleSlug): bool
    {
        return self::role() === $roleSlug;
    }
}