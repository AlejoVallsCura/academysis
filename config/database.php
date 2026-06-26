<?php

/** Gestiona la conexión PDO a la base de datos como instancia única (Singleton). */
class Database {

    /** Almacena la única instancia PDO de la aplicación. */
    private static ?PDO $instance = null;

    /** Devuelve la conexión PDO, creándola si no existe todavía. */
    public static function getConnection(): PDO {

        if (self::$instance === null) {

            $dsn  = 'mysql:host=localhost;dbname=academisys;charset=utf8mb4';

            // Opciones: lanzar excepciones en errores, fetch asociativo, prepared statements reales.
            $opts = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$instance = new PDO($dsn, 'root', '', $opts);
        }

        return self::$instance;
    }

    /** Impide instanciar la clase desde afuera. */
    private function __construct() {}

    /** Impide clonar la instancia. */
    private function __clone()   {}
}
