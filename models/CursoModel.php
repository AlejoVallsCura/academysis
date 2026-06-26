<?php

/** Modelo para consultas sobre cursos, inscripciones y aulas. */
class CursoModel extends Model {

    /** Devuelve todos los cursos en los que está inscripto un alumno, con datos de materia, docente y aula. */
    public function getCursosByAlumno(int $dni): array {
        $stmt = $this->db->prepare("
            SELECT c.IDCurso, c.AnioLectivo,
                   m.NomMateria, m.CodMateria,
                   d.Nombre AS DocNombre, d.Apellido AS DocApellido,
                   au.Numero AS Aula, au.Edificio,
                   i.Estado AS EstadoInscripcion, i.IDInscripcion,
                   (SELECT GROUP_CONCAT(
                               CONCAT(ch.Dia, ' ', TIME_FORMAT(ch.HoraInicio,'%H:%i'),
                                      '–', TIME_FORMAT(ch.HoraFin,'%H:%i'))
                               ORDER BY FIELD(ch.Dia,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado')
                               SEPARATOR ' | ')
                    FROM CursoHorario ch WHERE ch.IDCurso = c.IDCurso) AS Horarios
              FROM Curso c
              JOIN Inscripcion i ON i.IDCurso    = c.IDCurso
              JOIN Materia m     ON m.CodMateria  = c.CodMateria
              JOIN Docente d     ON d.Legajo       = c.Legajo
              JOIN Aula au       ON au.IDAula       = c.IDAula
             WHERE i.DNI = :dni
             ORDER BY c.AnioLectivo DESC, m.NomMateria
        ");
        $stmt->execute([':dni' => $dni]);
        return $stmt->fetchAll();
    }

    /** Devuelve los cursos de un docente con la cantidad de alumnos inscriptos en cada uno. */
    public function getCursosByDocente(int $legajo): array {
        $stmt = $this->db->prepare("
            SELECT c.IDCurso, c.AnioLectivo,
                   m.NomMateria, m.CodMateria,
                   au.Numero AS Aula, au.Edificio,
                   COUNT(i.IDInscripcion) AS CantAlumnos,
                   (SELECT GROUP_CONCAT(
                               CONCAT(ch.Dia, ' ', TIME_FORMAT(ch.HoraInicio,'%H:%i'),
                                      '–', TIME_FORMAT(ch.HoraFin,'%H:%i'))
                               ORDER BY FIELD(ch.Dia,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado')
                               SEPARATOR ' | ')
                    FROM CursoHorario ch WHERE ch.IDCurso = c.IDCurso) AS Horarios
              FROM Curso c
              JOIN Materia m ON m.CodMateria = c.CodMateria
              JOIN Aula au   ON au.IDAula     = c.IDAula
              LEFT JOIN Inscripcion i ON i.IDCurso = c.IDCurso AND i.Estado != 'Baja'
             WHERE c.Legajo = :legajo
             GROUP BY c.IDCurso, c.AnioLectivo, m.NomMateria, m.CodMateria, au.Numero, au.Edificio
             ORDER BY c.AnioLectivo DESC, m.NomMateria
        ");
        $stmt->execute([':legajo' => $legajo]);
        return $stmt->fetchAll();
    }

    /** Devuelve los alumnos inscriptos en un curso con sus datos de contacto y estado. */
    public function getAlumnosByCurso(int $idCurso): array {
        $stmt = $this->db->prepare("
            SELECT a.DNI, a.Nombre, a.Apellido, a.Email,
                   i.IDInscripcion, i.Estado, i.FechaInscripcion
              FROM Inscripcion i
              JOIN Alumno a ON a.DNI = i.DNI
             WHERE i.IDCurso = :idCurso
             ORDER BY a.Apellido, a.Nombre
        ");
        $stmt->execute([':idCurso' => $idCurso]);
        return $stmt->fetchAll();
    }

    /** Devuelve los alumnos de un curso con su % de asistencia y promedio de parciales. */
    public function getAlumnosConResumen(int $idCurso): array {
        $stmt = $this->db->prepare("
            SELECT al.DNI, al.Nombre, al.Apellido,
                   i.IDInscripcion, i.Estado,
                   (SELECT COUNT(*) FROM Asistencia a
                     WHERE a.IDInscripcion = i.IDInscripcion)                     AS total_clases,
                   (SELECT COALESCE(SUM(a.Presente), 0) FROM Asistencia a
                     WHERE a.IDInscripcion = i.IDInscripcion)                     AS clases_presentes,
                   (SELECT ROUND(AVG(e.Nota), 2) FROM Evaluacion e
                     WHERE e.IDInscripcion = i.IDInscripcion
                       AND e.Tipo = 'Parcial')                                     AS prom_parciales,
                   (SELECT ROUND(MAX(e.Nota), 2) FROM Evaluacion e
                     WHERE e.IDInscripcion = i.IDInscripcion
                       AND e.Tipo = 'Final')                                       AS nota_final
              FROM Inscripcion i
              JOIN Alumno al ON al.DNI = i.DNI
             WHERE i.IDCurso = :idCurso AND i.Estado != 'Baja'
             ORDER BY al.Apellido, al.Nombre
        ");
        $stmt->execute([':idCurso' => $idCurso]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['pct_asistencia'] = $r['total_clases'] > 0
                ? round($r['clases_presentes'] / $r['total_clases'] * 100, 1)
                : null;

            if ($r['Estado'] === 'Activo') {
                $pasaAsistencia = $r['pct_asistencia'] !== null && $r['pct_asistencia'] >= 75;
                $pasaParciales  = $r['prom_parciales']  !== null && $r['prom_parciales']  >= 6;
                $pasaFinal      = $r['nota_final']       !== null && $r['nota_final']       >= 4;
                if ($pasaAsistencia && $pasaParciales && $pasaFinal) {
                    $r['estado_sugerido'] = 'Aprobado';
                } elseif ($pasaAsistencia && $pasaParciales) {
                    $r['estado_sugerido'] = 'Regular';
                } else {
                    $r['estado_sugerido'] = 'Libre';
                }
            } elseif ($r['Estado'] === 'Regular' && $r['nota_final'] !== null && $r['nota_final'] >= 4) {
                $r['estado_sugerido'] = 'Aprobado';
            } else {
                $r['estado_sugerido'] = $r['Estado'];
            }
        }
        return $rows;
    }

    /** Actualiza el estado de cada inscripción recibida en el array [IDInscripcion => Estado]. */
    public function actualizarEstados(array $estados): void {
        $stmt = $this->db->prepare("UPDATE Inscripcion SET Estado = :estado WHERE IDInscripcion = :id");
        foreach ($estados as $idInsc => $estado) {
            $stmt->execute([':estado' => $estado, ':id' => (int)$idInsc]);
        }
    }

    /** Registra el final de un alumno cambiando su estado de Regular a Aprobado. */
    public function registrarFinal(int $idInscripcion): void {
        $stmt = $this->db->prepare(
            "UPDATE Inscripcion SET Estado = 'Aprobado' WHERE IDInscripcion = :id AND Estado = 'Regular'"
        );
        $stmt->execute([':id' => $idInscripcion]);
    }

    /** Devuelve los cursos activos en los que el alumno no está inscripto. */
    public function getCursosDisponibles(int $dni): array {
        $stmt = $this->db->prepare("
            SELECT c.IDCurso, c.AnioLectivo,
                   m.CodMateria, m.NomMateria,
                   d.Nombre AS DocNombre, d.Apellido AS DocApellido,
                   au.Numero AS Aula, au.Edificio,
                   (SELECT GROUP_CONCAT(
                               CONCAT(ch.Dia, ' ', TIME_FORMAT(ch.HoraInicio,'%H:%i'),
                                      '–', TIME_FORMAT(ch.HoraFin,'%H:%i'))
                               ORDER BY FIELD(ch.Dia,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado')
                               SEPARATOR ' | ')
                    FROM CursoHorario ch WHERE ch.IDCurso = c.IDCurso) AS Horarios
              FROM Curso c
              JOIN Materia m ON m.CodMateria = c.CodMateria
              JOIN Docente d ON d.Legajo      = c.Legajo
              JOIN Aula au   ON au.IDAula      = c.IDAula
             WHERE c.Activo = 1
               AND c.IDCurso NOT IN (
                   SELECT IDCurso FROM Inscripcion WHERE DNI = :dni AND Estado != 'Baja'
               )
             ORDER BY m.NomMateria
        ");
        $stmt->execute([':dni' => $dni]);
        return $stmt->fetchAll();
    }

    /** Devuelve las correlativas de una materia que el alumno todavía no aprobó. */
    public function getCorrelativasPendientes(int $dni, string $codMateria): array {
        $stmt = $this->db->prepare("
            SELECT co.CodCorrelativa, m.NomMateria
              FROM Correlativa co
              JOIN Materia m ON m.CodMateria = co.CodCorrelativa
             WHERE co.CodMateria = :cod
               AND NOT EXISTS (
                   SELECT 1 FROM Inscripcion i
                     JOIN Curso c ON c.IDCurso = i.IDCurso
                    WHERE i.DNI = :dni
                      AND c.CodMateria = co.CodCorrelativa
                      AND i.Estado = 'Aprobado'
               )
        ");
        $stmt->execute([':cod' => $codMateria, ':dni' => $dni]);
        return $stmt->fetchAll();
    }

    /** Inscribe al alumno en un curso. */
    public function inscribir(int $dni, int $idCurso): void {
        $stmt = $this->db->prepare("
            INSERT INTO Inscripcion (DNI, IDCurso, Estado, FechaInscripcion)
            VALUES (:dni, :idcurso, 'Activo', CURDATE())
        ");
        $stmt->execute([':dni' => $dni, ':idcurso' => $idCurso]);
    }

    /** Devuelve los datos completos de un curso por su ID, o [] si no existe. */
    public function getCursoById(int $idCurso): array {
        $stmt = $this->db->prepare("
            SELECT c.*, m.NomMateria, au.Numero AS Aula,
                   d.Nombre AS DocNombre, d.Apellido AS DocApellido
              FROM Curso c
              JOIN Materia m ON m.CodMateria = c.CodMateria
              JOIN Aula au   ON au.IDAula     = c.IDAula
              JOIN Docente d ON d.Legajo       = c.Legajo
             WHERE c.IDCurso = :id
        ");
        $stmt->execute([':id' => $idCurso]);
        return $stmt->fetch() ?: [];
    }
}
