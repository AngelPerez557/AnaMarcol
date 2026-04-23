<?php

/**
 * AutoLoad.php — Cargador automático de clases
 * Registra las carpetas del sistema para que PHP resuelva
 * cualquier clase automáticamente por su nombre, sin require_once manuales.
 */

class AutoLoad
{
    // ─────────────────────────────────────────────
    // 1. DIRECTORIOS DONDE PHP BUSCARÁ CLASES
    // ─────────────────────────────────────────────
    private static array $directories = [
        CONFIG_PATH,
        CORE_PATH,
        CONTROLLERS_PATH,
        MODELS_PATH,
        ROOT . 'Entity' . DS,
    ];

    // ─────────────────────────────────────────────
    // 2. REGISTRO DEL AUTOLOADER
    // ─────────────────────────────────────────────
    public static function run(): void
    {
        spl_autoload_register(function (string $className) {

            // Normaliza namespaces a separador del SO
            // Ej: "Config\JRouter" → "Config/JRouter" (Linux) o "Config\JRouter" (Windows)
            $className = str_replace(['\\', '/'], DS, $className);

            // Elimina el prefijo de carpeta si ya está incluido en el nombre
            // Ej: "Controllers\EjemploController" no debe buscar "Controllers/Controllers/EjemploController.php"
            foreach (self::$directories as $directory) {

                $file = $directory . $className . '.php';

                // Si el archivo existe, lo carga y detiene la búsqueda
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }

                // Segunda búsqueda: elimina el segmento de carpeta del nombre de clase
                // Ej: busca "Controllers/EjemploController.php" sin repetir "Controllers"
                $classNameShort = substr($className, strrpos($className, DS) + 1);
                $fileShort      = $directory . $classNameShort . '.php';

                if (file_exists($fileShort)) {
                    require_once $fileShort;
                    return;
                }
            }
        });
    }

    // ─────────────────────────────────────────────
    // 3. REGISTRO DE DIRECTORIO ADICIONAL EN TIEMPO DE EJECUCIÓN
    // Útil para módulos o plugins futuros
    // ─────────────────────────────────────────────
    public static function addDirectory(string $path): void
    {
        if (is_dir($path) && !in_array($path, self::$directories)) {
            self::$directories[] = rtrim($path, DS) . DS;
        }
    }
}