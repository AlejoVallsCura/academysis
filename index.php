<?php

/** Punto de entrada único de la aplicación. Configura el entorno y despacha al Router. */

// Inicia la sesión PHP.
session_start();

// Ruta absoluta raíz del proyecto, disponible en toda la aplicación.
define('BASE_PATH', __DIR__);

// Carga la clase Database antes que Model y los modelos concretos.
require_once BASE_PATH . '/config/database.php';

// Carga las funciones de autenticación (requiereLogin, tienePermiso, etc.).
require_once BASE_PATH . '/config/auth.php';

// Carga las clases base abstractas.
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Controller.php';

// Incluye automáticamente todos los modelos del directorio /models/.
foreach (glob(BASE_PATH . '/models/*.php') as $file) {
    require_once $file;
}

require_once BASE_PATH . '/core/Router.php';

// Sin parámetros en la URL: redirige al dashboard o al login según sesión.
if (empty($_GET['controller'])) {
    if (isset($_SESSION['usuario'])) {
        $rol = $_SESSION['usuario']['NombreRol'];
        header("Location: index.php?controller={$rol}&action=dashboard");
    } else {
        header('Location: index.php?controller=auth&action=login');
    }
    exit;
}

// Instancia el Router y despacha la petición.
$router = new Router();
$router->dispatch();
