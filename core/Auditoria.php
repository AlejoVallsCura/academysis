<?php

/**
 * Helper de auditoría a nivel aplicación.
 *
 * Registra acciones del usuario (login, altas, modificaciones, bajas, consultas)
 * en la tabla `auditoria`. Se usa de forma estática desde cualquier controlador:
 *
 *     Auditoria::registrar('ALTA', 'Materia', "Creó la materia {$cod}");
 *
 * Pensado para ser "fire and forget": si la inserción falla, lo loguea pero
 * nunca interrumpe el flujo principal de la aplicación.
 */
class Auditoria {

    /* Tipos de acción admitidos. Sirven de referencia y para validar. */
    const LOGIN        = 'LOGIN';
    const LOGOUT       = 'LOGOUT';
    const ALTA         = 'ALTA';
    const MODIFICACION = 'MODIFICACION';
    const BAJA         = 'BAJA';
    const CONSULTA     = 'CONSULTA';
    const ERROR        = 'ERROR';

    /**
     * Inserta un registro en la tabla de auditoría.
     *
     * @param string $accion   Tipo de acción (usar las constantes de la clase).
     * @param string $entidad  Entidad/tabla afectada (ej. "Alumno", "Curso").
     * @param string $detalle  Descripción legible de lo ocurrido.
     */
    public static function registrar(string $accion, string $entidad = '', string $detalle = ''): void {
        try {
            $db = Database::getConnection();

            /* Toma los datos del usuario logueado desde la sesión, si existe.
             * En acciones como un login fallido puede no haber usuario en sesión. */
            $u = $_SESSION['usuario'] ?? [];

            $stmt = $db->prepare("
                INSERT INTO Auditoria (IDUsuario, Email, Rol, Accion, Entidad, Detalle, IP)
                VALUES (:idusuario, :email, :rol, :accion, :entidad, :detalle, :ip)
            ");
            $stmt->execute([
                ':idusuario' => $u['IDUsuario'] ?? null,
                ':email'     => $u['Email']     ?? null,
                ':rol'       => $u['NombreRol'] ?? null,
                ':accion'    => $accion,
                ':entidad'   => $entidad ?: null,
                ':detalle'   => $detalle ?: null,
                /* IP de origen; REMOTE_ADDR puede no estar definido en CLI */
                ':ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (PDOException $e) {
            /* La auditoría nunca debe romper la operación real: solo se loguea el fallo. */
            error_log('Auditoria::registrar - ' . $e->getMessage());
        }
    }
}
