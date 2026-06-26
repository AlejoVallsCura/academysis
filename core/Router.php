<?php

/** Lee la URL y delega la ejecución al controlador y acción correspondientes. */
class Router {

    /** Lista blanca de controladores permitidos. */
    private array $allowed = ['auth', 'alumno', 'docente', 'admin'];

    /** Lee controller y action de la URL, valida y ejecuta la acción correspondiente. */
    public function dispatch(): void {

        // Sanitiza el controlador: solo letras, minúsculas. Por defecto 'auth'.
        $controller = strtolower(preg_replace('/[^a-zA-Z]/', '', $_GET['controller'] ?? 'auth'));

        // Sanitiza la acción: solo letras. Por defecto 'login'.
        $action     = preg_replace('/[^a-zA-Z]/', '', $_GET['action'] ?? 'login');

        // Verifica que el controlador esté en la lista blanca.
        if (!in_array($controller, $this->allowed)) {
            $this->notFound();
            return;
        }

        // Construye el nombre de clase. Ej: "alumno" → "AlumnoController".
        $className = ucfirst($controller) . 'Controller';

        $classFile = BASE_PATH . "/controllers/{$className}.php";

        // Verifica que el archivo del controlador exista en disco.
        if (!file_exists($classFile)) {
            $this->notFound();
            return;
        }

        require_once $classFile;

        // Instancia dinámicamente el controlador.
        $obj = new $className();

        // Verifica que el método de acción exista en el controlador.
        if (!method_exists($obj, $action)) {
            $this->notFound();
            return;
        }

        // Invoca el método de acción. Ej: $obj->dashboard().
        $obj->$action();
    }

    /** Responde con HTTP 404 y muestra un mensaje de error. */
    private function notFound(): void {
        http_response_code(404);
        echo '<div style="font-family:sans-serif;text-align:center;margin-top:80px">
                <h2>404 — Página no encontrada</h2>
                <a href="index.php">Volver al inicio</a>
              </div>';
    }
}
