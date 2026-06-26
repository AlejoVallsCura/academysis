<?php

/** Modelo para las operaciones CRUD del administrador. */
class AdminModel extends Model {

    // ============================================================
    //  CARRERAS
    // ============================================================

    /** Devuelve todas las carreras con la cantidad de materias asignadas. */
    public function getCarreras(): array {
        $stmt = $this->db->prepare("
            SELECT c.*, COUNT(m.CodMateria) AS totalMaterias
              FROM Carrera c
              LEFT JOIN Materia m ON m.CodCarrera = c.CodCarrera
             GROUP BY c.CodCarrera
             ORDER BY c.NomCarrera
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Devuelve una carrera por su código, o [] si no existe. */
    public function getCarreraById(string $cod): array {
        $stmt = $this->db->prepare("SELECT * FROM Carrera WHERE CodCarrera = :cod");
        $stmt->execute([':cod' => $cod]);
        return $stmt->fetch() ?: [];
    }

    /** Inserta o actualiza una carrera. */
    public function saveCarrera(array $d, bool $isNew): void {
        if ($isNew) {
            $stmt = $this->db->prepare("
                INSERT INTO Carrera (CodCarrera, NomCarrera, DurAnios)
                VALUES (:cod, :nom, :dur)
            ");
        } else {
            $stmt = $this->db->prepare("
                UPDATE Carrera SET NomCarrera = :nom, DurAnios = :dur WHERE CodCarrera = :cod
            ");
        }
        $stmt->execute([':cod' => $d['CodCarrera'], ':nom' => $d['NomCarrera'], ':dur' => $d['DurAnios']]);
    }

    /** Devuelve todas las carreras para selects. */
    public function getCarrerasList(): array {
        $stmt = $this->db->prepare("SELECT CodCarrera, NomCarrera, DurAnios FROM Carrera ORDER BY NomCarrera");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ============================================================
    //  MATERIAS
    // ============================================================

    /** Devuelve todas las materias con su carrera y año de cursada. */
    public function getMaterias(): array {
        $stmt = $this->db->prepare("
            SELECT m.*, c.NomCarrera
              FROM Materia m
              LEFT JOIN Carrera c ON c.CodCarrera = m.CodCarrera
             ORDER BY c.NomCarrera, m.Anio, m.NomMateria
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Devuelve una materia por su código, o [] si no existe. */
    public function getMateriaById(string $cod): array {
        $stmt = $this->db->prepare("SELECT * FROM Materia WHERE CodMateria = :cod");
        $stmt->execute([':cod' => $cod]);
        return $stmt->fetch() ?: [];
    }

    /** Inserta o actualiza una materia según $isNew. */
    public function saveMateria(array $d, bool $isNew): void {
        if ($isNew) {
            $stmt = $this->db->prepare("
                INSERT INTO Materia (CodMateria, NomMateria, CodCarrera, Anio)
                VALUES (:cod, :nom, :carrera, :anio)
            ");
        } else {
            $stmt = $this->db->prepare("
                UPDATE Materia SET NomMateria = :nom, CodCarrera = :carrera, Anio = :anio
                WHERE CodMateria = :cod
            ");
        }
        $stmt->execute([
            ':cod'     => $d['CodMateria'],
            ':nom'     => $d['NomMateria'],
            ':carrera' => $d['CodCarrera'] ?: null,
            ':anio'    => $d['Anio'] ?: null,
        ]);
    }

    /** Invierte el estado Activo de una materia. */
    public function toggleMateriaActivo(string $cod): void {
        $stmt = $this->db->prepare("UPDATE Materia SET Activo = NOT Activo WHERE CodMateria = :cod");
        $stmt->execute([':cod' => $cod]);
    }

    // ============================================================
    //  CURSOS
    // ============================================================

    /** Devuelve todos los cursos con nombre de materia, docente, aula y horarios calculados. */
    public function getCursos(): array {
        $stmt = $this->db->prepare("
            SELECT c.*, m.NomMateria, m.CodCarrera, au.Numero AS Aula, au.Edificio,
                   d.Nombre AS DocNombre, d.Apellido AS DocApellido,
                   (SELECT GROUP_CONCAT(
                               CONCAT(ch.Dia, ' ', TIME_FORMAT(ch.HoraInicio,'%H:%i'),
                                      '–', TIME_FORMAT(ch.HoraFin,'%H:%i'))
                               ORDER BY FIELD(ch.Dia,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado')
                               SEPARATOR ' | ')
                    FROM CursoHorario ch WHERE ch.IDCurso = c.IDCurso) AS Horarios,
                   (SELECT ROUND(SUM(TIMESTAMPDIFF(MINUTE, ch.HoraInicio, ch.HoraFin) / 60.0) * 32)
                    FROM CursoHorario ch WHERE ch.IDCurso = c.IDCurso) AS CargaHoraria
              FROM Curso c
              JOIN Materia m ON m.CodMateria = c.CodMateria
              JOIN Aula au   ON au.IDAula     = c.IDAula
              JOIN Docente d ON d.Legajo       = c.Legajo
             ORDER BY c.AnioLectivo DESC, m.NomMateria
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Devuelve un curso por su ID, o [] si no existe. */
    public function getCursoById(int $id): array {
        $stmt = $this->db->prepare("SELECT * FROM Curso WHERE IDCurso = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    /** Inserta o actualiza un curso. Devuelve el IDCurso resultante. */
    public function saveCurso(array $d, bool $isNew): int {
        if ($isNew) {
            $stmt = $this->db->prepare("
                INSERT INTO Curso (AnioLectivo, CodMateria, Legajo, IDAula)
                VALUES (:anio, :cod, :leg, :aula)
            ");
        } else {
            $stmt = $this->db->prepare("
                UPDATE Curso SET AnioLectivo = :anio, CodMateria = :cod,
                                 Legajo = :leg, IDAula = :aula
                WHERE IDCurso = :id
            ");
        }
        $params = [
            ':anio' => $d['AnioLectivo'],
            ':cod'  => $d['CodMateria'],
            ':leg'  => $d['Legajo'],
            ':aula' => $d['IDAula'],
        ];
        if (!$isNew) $params[':id'] = $d['IDCurso'];
        $stmt->execute($params);
        return $isNew ? (int)$this->db->lastInsertId() : (int)$d['IDCurso'];
    }

    /** Devuelve los horarios de un curso ordenados por día de la semana. */
    public function getHorariosByCurso(int $idCurso): array {
        $stmt = $this->db->prepare("
            SELECT IDCursoHorario, Dia, HoraInicio, HoraFin
              FROM CursoHorario
             WHERE IDCurso = :id
             ORDER BY FIELD(Dia,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado')
        ");
        $stmt->execute([':id' => $idCurso]);
        return $stmt->fetchAll();
    }

    /** Reemplaza todos los horarios de un curso con los provistos. */
    public function saveHorarios(int $idCurso, array $horarios): void {
        $this->db->prepare("DELETE FROM CursoHorario WHERE IDCurso = :id")
                 ->execute([':id' => $idCurso]);
        $stmt = $this->db->prepare("
            INSERT INTO CursoHorario (IDCurso, Dia, HoraInicio, HoraFin)
            VALUES (:id, :dia, :hi, :hf)
        ");
        foreach ($horarios as $h) {
            if (!empty($h['Dia']) && !empty($h['HoraInicio']) && !empty($h['HoraFin'])) {
                $stmt->execute([
                    ':id'  => $idCurso,
                    ':dia' => $h['Dia'],
                    ':hi'  => $h['HoraInicio'],
                    ':hf'  => $h['HoraFin'],
                ]);
            }
        }
    }

    /** Invierte el estado Activo de un curso. */
    public function toggleCursoActivo(int $id): void {
        $stmt = $this->db->prepare("UPDATE Curso SET Activo = NOT Activo WHERE IDCurso = :id");
        $stmt->execute([':id' => $id]);
    }

    // ============================================================
    //  ALUMNOS
    // ============================================================

    /** Devuelve todos los alumnos con carrera. */
    public function getAlumnos(): array {
        $stmt = $this->db->prepare("
            SELECT a.*, c.NomCarrera
              FROM Alumno a
              LEFT JOIN Carrera c ON c.CodCarrera = a.CodCarrera
             ORDER BY a.Apellido, a.Nombre
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Devuelve un alumno con su dirección y carrera, o [] si no existe. */
    public function getAlumnoByDni(int $dni): array {
        $stmt = $this->db->prepare("
            SELECT a.*, d.Calle, d.Numero AS DirNumero, d.Ciudad, d.Provincia, d.CP, c.NomCarrera
              FROM Alumno a
              JOIN Direccion d ON d.IDDireccion = a.IDDireccion
              LEFT JOIN Carrera c ON c.CodCarrera = a.CodCarrera
             WHERE a.DNI = :dni
        ");
        $stmt->execute([':dni' => $dni]);
        return $stmt->fetch() ?: [];
    }

    /** Devuelve true si ya existe un alumno con ese DNI. */
    public function existeAlumnoPorDni(int $dni): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM Alumno WHERE DNI = :dni");
        $stmt->execute([':dni' => $dni]);
        return (bool)$stmt->fetch();
    }

    /** Devuelve true si ya existe un alumno con ese email (excluyendo un DNI al editar). */
    public function existeAlumnoPorEmail(string $email, int $excludeDni = 0): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM Alumno WHERE Email = :email AND DNI != :dni");
        $stmt->execute([':email' => $email, ':dni' => $excludeDni]);
        return (bool)$stmt->fetch();
    }

    /** Crea un alumno nuevo con su dirección y usuario de acceso. */
    public function crearAlumno(array $d): void {
        // 1. Inserta la dirección
        $stmt = $this->db->prepare("
            INSERT INTO Direccion (Calle, Numero, Ciudad, Provincia, CP)
            VALUES (:calle, :num, :ciudad, :prov, :cp)
        ");
        $stmt->execute([
            ':calle'  => $d['Calle'],
            ':num'    => $d['DirNumero'],
            ':ciudad' => $d['Ciudad'],
            ':prov'   => $d['Provincia'],
            ':cp'     => $d['CP'] ?? null,
        ]);
        $idDir = $this->db->lastInsertId();

        // 2. Inserta el alumno
        $stmt = $this->db->prepare("
            INSERT INTO Alumno (DNI, Nombre, Apellido, FechaNacimiento, Telefono, Email, FechaIngreso, IDDireccion, CodCarrera)
            VALUES (:dni, :nom, :ape, :fn, :tel, :email, :fi, :iddir, :carrera)
        ");
        $stmt->execute([
            ':dni'     => $d['DNI'],
            ':nom'     => $d['Nombre'],
            ':ape'     => $d['Apellido'],
            ':fn'      => $d['FechaNacimiento'],
            ':tel'     => $d['Telefono'] ?? null,
            ':email'   => $d['Email'],
            ':fi'      => $d['FechaIngreso'],
            ':iddir'   => $idDir,
            ':carrera' => $d['CodCarrera'] ?: null,
        ]);

        // 3. Crea el usuario de acceso
        $idRol = $this->db->query("SELECT IDRol FROM Rol WHERE NombreRol = 'alumno'")->fetchColumn();
        $hash  = password_hash($d['Password'], PASSWORD_BCRYPT);
        $stmt  = $this->db->prepare("
            INSERT INTO Usuario (Email, Password, IDRol, DNI, Estado)
            VALUES (:email, :pass, :rol, :dni, 1)
        ");
        $stmt->execute([':email' => $d['Email'], ':pass' => $hash, ':rol' => $idRol, ':dni' => $d['DNI']]);
    }

    /** Actualiza los datos de un alumno y su dirección. */
    public function editarAlumno(array $d): void {
        // Actualiza dirección
        $stmt = $this->db->prepare("
            UPDATE Direccion d
              JOIN Alumno a ON a.IDDireccion = d.IDDireccion
               SET d.Calle = :calle, d.Numero = :num, d.Ciudad = :ciudad, d.Provincia = :prov, d.CP = :cp
             WHERE a.DNI = :dni
        ");
        $stmt->execute([
            ':calle'  => $d['Calle'],
            ':num'    => $d['DirNumero'],
            ':ciudad' => $d['Ciudad'],
            ':prov'   => $d['Provincia'],
            ':cp'     => $d['CP'] ?? null,
            ':dni'    => $d['DNI'],
        ]);

        // Actualiza alumno
        $stmt = $this->db->prepare("
            UPDATE Alumno SET Nombre = :nom, Apellido = :ape, FechaNacimiento = :fn,
                              Telefono = :tel, Email = :email, FechaIngreso = :fi,
                              CodCarrera = :carrera
             WHERE DNI = :dni
        ");
        $stmt->execute([
            ':nom'     => $d['Nombre'],
            ':ape'     => $d['Apellido'],
            ':fn'      => $d['FechaNacimiento'],
            ':tel'     => $d['Telefono'] ?? null,
            ':email'   => $d['Email'],
            ':fi'      => $d['FechaIngreso'],
            ':carrera' => $d['CodCarrera'] ?: null,
            ':dni'     => $d['DNI'],
        ]);
    }

    /** Invierte el estado Activo de un alumno (solo visual, no afecta el login). */
    public function toggleAlumnoActivo(int $dni): void {
        $stmt = $this->db->prepare("UPDATE Alumno SET Activo = NOT Activo WHERE DNI = :dni");
        $stmt->execute([':dni' => $dni]);
    }

    // ============================================================
    //  DOCENTES
    // ============================================================

    /** Devuelve todos los docentes. */
    public function getDocentes(): array {
        $stmt = $this->db->prepare("SELECT * FROM Docente ORDER BY Apellido, Nombre");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Devuelve un docente por su legajo, o [] si no existe. */
    public function getDocenteByLegajo(int $legajo): array {
        $stmt = $this->db->prepare("SELECT * FROM Docente WHERE Legajo = :leg");
        $stmt->execute([':leg' => $legajo]);
        return $stmt->fetch() ?: [];
    }

    /** Crea un docente nuevo con su usuario de acceso. */
    public function crearDocente(array $d): void {
        $stmt = $this->db->prepare("
            INSERT INTO Docente (Nombre, Apellido, DNI, Titulo, Especialidad, Email)
            VALUES (:nom, :ape, :dni, :tit, :esp, :email)
        ");
        $stmt->execute([
            ':nom'   => $d['Nombre'],
            ':ape'   => $d['Apellido'],
            ':dni'   => $d['DNI'],
            ':tit'   => $d['Titulo'] ?? null,
            ':esp'   => $d['Especialidad'] ?? null,
            ':email' => $d['Email'],
        ]);
        $legajo = $this->db->lastInsertId();

        $idRol = $this->db->query("SELECT IDRol FROM Rol WHERE NombreRol = 'docente'")->fetchColumn();
        $hash  = password_hash($d['Password'], PASSWORD_BCRYPT);
        $stmt  = $this->db->prepare("
            INSERT INTO Usuario (Email, Password, IDRol, Legajo, Estado)
            VALUES (:email, :pass, :rol, :leg, 1)
        ");
        $stmt->execute([':email' => $d['Email'], ':pass' => $hash, ':rol' => $idRol, ':leg' => $legajo]);
    }

    /** Actualiza los datos de un docente. */
    public function editarDocente(array $d): void {
        $stmt = $this->db->prepare("
            UPDATE Docente SET Nombre = :nom, Apellido = :ape, DNI = :dni,
                               Titulo = :tit, Especialidad = :esp, Email = :email
             WHERE Legajo = :leg
        ");
        $stmt->execute([
            ':nom'   => $d['Nombre'],
            ':ape'   => $d['Apellido'],
            ':dni'   => $d['DNI'],
            ':tit'   => $d['Titulo'] ?? null,
            ':esp'   => $d['Especialidad'] ?? null,
            ':email' => $d['Email'],
            ':leg'   => $d['Legajo'],
        ]);
    }

    /** Invierte el estado Activo de un docente. */
    public function toggleDocenteActivo(int $legajo): void {
        $stmt = $this->db->prepare("UPDATE Docente SET Activo = NOT Activo WHERE Legajo = :leg");
        $stmt->execute([':leg' => $legajo]);
    }

    // ============================================================
    //  ADMINISTRADORES
    // ============================================================

    /** Devuelve todos los usuarios con rol admin. */
    public function getAdmins(): array {
        $stmt = $this->db->prepare("
            SELECT u.IDUsuario, u.Nombre, u.Email, u.Estado, u.CreadoEn, u.UltimoAcceso
              FROM Usuario u
              JOIN Rol r ON r.IDRol = u.IDRol
             WHERE r.NombreRol = 'admin'
             ORDER BY u.CreadoEn DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Crea un nuevo usuario administrador. */
    public function crearAdmin(array $d): void {
        $idRol = $this->db->query("SELECT IDRol FROM Rol WHERE NombreRol = 'admin'")->fetchColumn();
        $hash  = password_hash($d['Password'], PASSWORD_BCRYPT);
        $stmt  = $this->db->prepare("
            INSERT INTO Usuario (Nombre, Email, Password, IDRol, Estado)
            VALUES (:nombre, :email, :pass, :rol, 1)
        ");
        $stmt->execute([
            ':nombre' => $d['Nombre'],
            ':email'  => $d['Email'],
            ':pass'   => $hash,
            ':rol'    => $idRol,
        ]);
    }

    /** Invierte el estado (activo/inactivo) de un usuario admin. */
    public function toggleAdminActivo(int $idUsuario): void {
        $stmt = $this->db->prepare("UPDATE Usuario SET Estado = NOT Estado WHERE IDUsuario = :id");
        $stmt->execute([':id' => $idUsuario]);
    }

    // ============================================================
    //  DATOS PARA SELECTS
    // ============================================================

    /** Devuelve las aulas para el select del formulario de curso. */
    public function getAulas(): array {
        $stmt = $this->db->prepare("SELECT IDAula, Numero, Edificio FROM Aula ORDER BY Edificio, Numero");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Devuelve las materias activas para el select del formulario de curso. */
    public function getMateriasList(): array {
        $stmt = $this->db->prepare("
            SELECT m.CodMateria, m.NomMateria, m.CodCarrera
              FROM Materia m
             WHERE m.Activo = 1
             ORDER BY m.CodCarrera, m.Anio, m.NomMateria
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Devuelve los docentes activos para el select del formulario de curso. */
    public function getDocentesList(): array {
        $stmt = $this->db->prepare("SELECT Legajo, Nombre, Apellido FROM Docente WHERE Activo = 1 ORDER BY Apellido, Nombre");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Devuelve conteos de entidades para el dashboard del admin. */
    public function getResumen(): array {
        return [
            'carreras' => $this->db->query("SELECT COUNT(*) FROM Carrera")->fetchColumn(),
            'materias' => $this->db->query("SELECT COUNT(*) FROM Materia  WHERE Activo = 1")->fetchColumn(),
            'cursos'   => $this->db->query("SELECT COUNT(*) FROM Curso    WHERE Activo = 1")->fetchColumn(),
            'alumnos'  => $this->db->query("SELECT COUNT(*) FROM Alumno   WHERE Activo = 1")->fetchColumn(),
            'docentes' => $this->db->query("SELECT COUNT(*) FROM Docente  WHERE Activo = 1")->fetchColumn(),
        ];
    }
}
