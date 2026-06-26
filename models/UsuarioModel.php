<?php

/** Modelo de autenticación y permisos de usuario. */
class UsuarioModel extends Model {

    /** Verifica las credenciales y devuelve los datos del usuario, o false si son incorrectas. */
    public function login(string $email, string $password): array|false {
        $stmt = $this->db->prepare("
            SELECT u.*, r.NombreRol
              FROM Usuario u
              JOIN Rol r ON r.IDRol = u.IDRol
             WHERE u.Email = :email AND u.Estado = 1
        ");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($password, $usuario['Password'])) {
            return false;
        }

        // Obtiene el nombre completo según el tipo de usuario
        if ($usuario['DNI']) {
            $s = $this->db->prepare("SELECT CONCAT(Nombre,' ',Apellido) AS NombreCompleto FROM Alumno WHERE DNI = :v");
            $s->execute([':v' => $usuario['DNI']]);
            $usuario['NombreCompleto'] = $s->fetch()['NombreCompleto'] ?? $email;
        } elseif ($usuario['Legajo']) {
            $s = $this->db->prepare("SELECT CONCAT(Nombre,' ',Apellido) AS NombreCompleto FROM Docente WHERE Legajo = :v");
            $s->execute([':v' => $usuario['Legajo']]);
            $usuario['NombreCompleto'] = $s->fetch()['NombreCompleto'] ?? $email;
        } else {
            // Admin: usa el campo Nombre de la tabla Usuario
            $usuario['NombreCompleto'] = $usuario['Nombre'] ?? 'Administrador';
        }

        return $usuario;
    }

    /** Devuelve los códigos de permiso asignados a un rol. */
    public function getPermisos(int $idRol): array {
        $stmt = $this->db->prepare("
            SELECT p.Codigo
              FROM Permiso p
              JOIN Rol_Permiso rp ON rp.IDPermiso = p.IDPermiso
             WHERE rp.IDRol = :idRol
        ");
        $stmt->execute([':idRol' => $idRol]);
        return array_column($stmt->fetchAll(), 'Codigo');
    }

    /** Actualiza la fecha y hora del último acceso del usuario. */
    public function actualizarUltimoAcceso(int $idUsuario): void {
        $stmt = $this->db->prepare("UPDATE Usuario SET UltimoAcceso = NOW() WHERE IDUsuario = :id");
        $stmt->execute([':id' => $idUsuario]);
    }
}
