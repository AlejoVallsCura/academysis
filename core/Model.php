<?php

/** Clase base para todos los modelos; provee la conexión PDO. */
abstract class Model {

    /** Conexión PDO accesible por las clases hijas. */
    protected PDO $db;

    /** Obtiene la conexión PDO al instanciar cualquier modelo hijo. */
    public function __construct() {
        $this->db = Database::getConnection();
    }
}
