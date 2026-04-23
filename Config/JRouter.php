<?php

class JRouter
{
    // Controlador por defecto cuando la URL está vacía
    private static string $defaultController = 'DashboardController';

    // Método por defecto cuando no se especifica en la URL
    private static string $defaultMethod = 'index';

    // ─────────────────────────────────────────────
    // PUNTO DE ENTRADA DEL ENRUTADOR
    // ─────────────────────────────────────────────

    // Recibe el JRequest, resuelve el controlador y método, y los ejecuta
    public static function run(JRequest $request): void
    {
        // Obtiene el nombre del controlador desde el primer segmento de URL
        // Ej: "Ejemplo/index" → "Ejemplo"
        $controllerName = self::resolveController($request->getController());

        // Obtiene el método desde el segundo segmento de URL
        // Ej: "Ejemplo/index" → "index"
        $methodName = self::resolveMethod($request->getMethod());

        // Verifica que la clase del controlador exista en el sistema
        if (!class_exists($controllerName)) {
            self::notFound("Controlador '{$controllerName}' no encontrado.");
            return;
        }

        // Instancia el controlador
        $controller = new $controllerName();

        // Verifica que el método exista dentro del controlador
        if (!method_exists($controller, $methodName)) {
            self::notFound("Método '{$methodName}' no encontrado en '{$controllerName}'.");
            return;
        }

        // Recopila parámetros adicionales desde los segmentos de URL
        // Ej: "Ejemplo/ver/5/activo" → ['5', 'activo']
        $params = self::resolveParams($request->getSegments());

        // Ejecuta el método del controlador pasando los parámetros como argumentos
        call_user_func_array([$controller, $methodName], $params);
    }

    // ─────────────────────────────────────────────
    // RESOLUCIÓN DE CONTROLADOR
    // ─────────────────────────────────────────────

    // Construye el nombre completo de la clase del controlador
    // Ej: "Ejemplo" → "EjemploController"
    // Si la URL está vacía usa el controlador por defecto
    private static function resolveController(string $name): string
    {
        if (empty($name)) {
            return self::$defaultController;
        }

        // Capitaliza el primer carácter para respetar convención de nombres de clase
        $name = ucfirst(strtolower($name));

        // Si ya termina en "Controller" no lo duplica
        if (str_ends_with($name, 'Controller')) {
            return $name;
        }

        return $name . 'Controller';
    }

    // ─────────────────────────────────────────────
    // RESOLUCIÓN DE MÉTODO
    // ─────────────────────────────────────────────

    // Retorna el nombre del método a ejecutar
    // Si no se especifica en la URL usa el método por defecto
    private static function resolveMethod(string $method): string
    {
        if (empty($method)) {
            return self::$defaultMethod;
        }

        // Convierte a camelCase por si la URL viene en minúsculas
        // Ej: "getlist" → "getList" no aplica aquí, pero sí normaliza
        return lcfirst($method);
    }

    // ─────────────────────────────────────────────
    // RESOLUCIÓN DE PARÁMETROS
    // ─────────────────────────────────────────────

    // Extrae los parámetros adicionales de la URL (segmentos 2 en adelante)
    // Segmento 0 = controlador, Segmento 1 = método, Segmento 2+ = parámetros
    // Ej: ['Ejemplo', 'ver', '5', 'activo'] → ['5', 'activo']
    private static function resolveParams(array $segments): array
    {
        // Elimina los dos primeros segmentos (controlador y método)
        return array_values(array_slice($segments, 2));
    }

    // ─────────────────────────────────────────────
    // MANEJO DE RUTAS NO ENCONTRADAS
    // ─────────────────────────────────────────────

    // Maneja errores 404 — en desarrollo muestra el mensaje, en producción vista genérica
    private static function notFound(string $message): void
    {
        http_response_code(404);

        if (APP_ENV === 'development') {
            // Muestra mensaje técnico para depuración
            die("<h2 style='font-family:monospace;color:#c0392b;'>404 | {$message}</h2>");
        }

        // En producción carga una vista 404 genérica si existe
        $view404 = VIEWS_PATH . '404' . DS . 'index.php';

        if (file_exists($view404)) {
            require_once $view404;
        } else {
            die('<h2>404 | Página no encontrada.</h2>');
        }
    }
}