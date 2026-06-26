<?php

/** Funciones de autenticación y control de acceso. */


define('SESSION_TIMEOUT', 30 * 60); // 30 minutos de inactividad

/** Redirige al login y detiene la ejecución si no hay sesión activa o expiró. */
function requiereLogin(): void {
    if (!isset($_SESSION['usuario'])) {
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
    if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: index.php?controller=auth&action=login&timeout=1');
        exit;
    }
    $_SESSION['ultima_actividad'] = time();
}

/**
 * Verifica que el usuario tenga el permiso indicado; responde 403 si no lo tiene.
 * @param string $codigo  Código del permiso requerido (ej. "ver_notas").
 */
function requierePermiso(string $codigo): void {
    requiereLogin();

    if (!in_array($codigo, $_SESSION['permisos'] ?? [])) {
        http_response_code(403);
        require_once BASE_PATH . '/views/errors/403.php';
        exit;
    }
}

/**
 * Devuelve true si el usuario tiene el permiso indicado, sin redirigir.
 * @param  string $codigo  Código del permiso a verificar.
 * @return bool
 */
function tienePermiso(string $codigo): bool {
    return in_array($codigo, $_SESSION['permisos'] ?? []);
}

/** Devuelve true si el usuario autenticado tiene rol "alumno". */
function esAlumno(): bool {
    return ($_SESSION['usuario']['NombreRol'] ?? '') === 'alumno';
}

/** Devuelve true si el usuario autenticado tiene rol "docente". */
function esDocente(): bool {
    return ($_SESSION['usuario']['NombreRol'] ?? '') === 'docente';
}

/** Devuelve true si el usuario autenticado tiene rol "admin". */
function esAdmin(): bool {
    return ($_SESSION['usuario']['NombreRol'] ?? '') === 'admin';
}
