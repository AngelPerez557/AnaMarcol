<?php

class Conexion
{
    // Almacena la única instancia PDO durante toda la ejecución
    private static ?PDO $instance = null;

    // Impide crear instancias con "new Conexion()" desde fuera
    private function __construct() {}

    // Impide duplicar la instancia con clone
    private function __clone() {}

    // Retorna la instancia PDO existente o crea una nueva si no existe
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                // Construye el DSN con las constantes definidas en Define.php
                $dsn = 'mysql:host=' . DB_HOST
                     . ';port='      . DB_PORT
                     . ';dbname='    . DB_NAME
                     . ';charset='   . DB_CHARSET;

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    // Lanza excepciones en lugar de fallar silenciosamente
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                    // Retorna filas como arrays asociativos ['columna' => 'valor']
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Usa prepared statements reales del motor — previene SQL injection
                    PDO::ATTR_EMULATE_PREPARES   => false,

                    // Reutiliza conexiones abiertas — mejora rendimiento en producción
                    PDO::ATTR_PERSISTENT         => true,
                ]);

            } catch (PDOException $e) {
                // En desarrollo muestra el error real para depuración
                // En producción oculta detalles internos al usuario
                if (APP_ENV === 'development') {
                    die('Error de conexión: ' . $e->getMessage());
                } else {
                    die('Error de conexión. Contacte al administrador.');
                }
            }
        }

        // Retorna la instancia ya creada sin abrir una nueva conexión
        return self::$instance;
    }

    // Cierra la conexión PDO liberando el recurso — útil en scripts CLI o procesos batch
    public static function close(): void
    {
        self::$instance = null;
    }
}