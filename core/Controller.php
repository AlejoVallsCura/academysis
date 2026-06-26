<?php

/** Clase base para todos los controladores; provee render y redirect. */
abstract class Controller {

    /**
     * Incluye una vista dentro del layout general de la página.
     * @param string $view  Ruta relativa a /views/ sin extensión (ej. "alumno/notas").
     * @param array  $data  Variables que la vista necesita para renderizarse.
     */
    protected function render(string $view, array $data = []): void {
        // Convierte las claves del array en variables locales accesibles por la vista.
        extract($data);

        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . "/views/{$view}.php";
        require_once BASE_PATH . '/views/layouts/footer.php';
    }

    /**
     * Envía una redirección HTTP y detiene la ejecución.
     * @param string $url  URL destino de la redirección.
     */
    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }
}
