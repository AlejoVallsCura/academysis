<?php

/** Modelo para consultas sobre cursos, inscripciones y aulas. */
class CursoModel extends Model {

    /** Devuelve todos los cursos en los que está inscripto un alumno, con datos de materia, docente y aula. */
    public function getCursosByAlumno(int $dni): array {
        $stmt = $this->db->prepare("
            SELECT c.IDCurso, c.AnioLectivo,
                   m.NomMateria, m.CodMateria, m.Anio AS AnioMateria,
                   d.Nombre AS DocNombre, d.Apellido AS DocApellido,
                   au.Numero AS Aula, au.Edificio,
                   i.Estado AS EstadoInscripcion, i.IDInscripcion,
                   (SELECT GROUP_CONCAT(
                               CONCAT(ch.Dia, ' ', TIME_FORMAT(ch.HoraInicio,'%H:%i'),
                                      '–', TIME_FORMAT(ch.HoraFin,'%H:%i'))
                               ORDER BY FIELD(ch.Dia,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado')
                               SEPARATOR ' | ')
                    FROM CursoHorario ch WHERE ch.IDCurso = c.IDCurso) AS Horarios,
                   /* Marca si esta inscripción quedó SUPERADA: existe otra de la misma materia,
                    * en un año lectivo posterior, en un estado mejor (la recursó después).
                    * Sirve para aclarar en la vista por qué se ve un 'Libre' viejo de algo ya aprobado. */
                   EXISTS (
                       SELECT 1 FROM Inscripcion i2
                         JOIN Curso c2 ON c2.IDCurso = i2.IDCurso
                        WHERE i2.DNI = i.DNI
                          AND c2.CodMateria   = c.CodMateria
                          AND c2.AnioLectivo  > c.AnioLectivo
                          AND i2.Estado IN ('Aprobado', 'Activo', 'Regular')
                   ) AS Superada
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

    /** Devuelve el DNI del alumno dueño de una inscripción, o 0 si no existe. */
    public function getDniDeInscripcion(int $idInscripcion): int {
        $stmt = $this->db->prepare("SELECT DNI FROM Inscripcion WHERE IDInscripcion = :id");
        $stmt->execute([':id' => $idInscripcion]);
        return (int)($stmt->fetchColumn() ?: 0);
    }

    /** Registra el final de un alumno cambiando su estado de Regular a Aprobado. */
    public function registrarFinal(int $idInscripcion): void {
        $stmt = $this->db->prepare(
            "UPDATE Inscripcion SET Estado = 'Aprobado' WHERE IDInscripcion = :id AND Estado = 'Regular'"
        );
        $stmt->execute([':id' => $idInscripcion]);
    }

    /**
     * Devuelve los cursos activos disponibles para inscripción.
     * Filtra por carrera del alumno, por año académico (su año o anteriores, nunca más adelante)
     * y excluye las materias que ya aprobó, está cursando (Activo) o tiene regulares.
     * Las materias en estado Libre (desaprobadas) SÍ aparecen, para poder recursarlas.
     * Incluye la columna CorrelativasPendientes para mostrar advertencias en la vista
     * sin necesitar consultas adicionales por cada fila.
     *
     * @param int    $anioMax  Año académico tope: solo materias con Anio <= este valor.
     */
    public function getCursosDisponibles(int $dni, string $codCarrera, int $anioMax): array {
        $stmt = $this->db->prepare("
            SELECT c.IDCurso, c.AnioLectivo,
                   m.CodMateria, m.NomMateria, m.Anio AS AnioMateria,
                   d.Nombre AS DocNombre, d.Apellido AS DocApellido,
                   au.Numero AS Aula, au.Edificio,
                   /* Horarios concatenados para mostrar en una celda */
                   (SELECT GROUP_CONCAT(
                               CONCAT(ch.Dia, ' ', TIME_FORMAT(ch.HoraInicio,'%H:%i'),
                                      '–', TIME_FORMAT(ch.HoraFin,'%H:%i'))
                               ORDER BY FIELD(ch.Dia,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado')
                               SEPARATOR ' | ')
                    FROM CursoHorario ch WHERE ch.IDCurso = c.IDCurso) AS Horarios,
                   /* Cuenta cuántas correlativas de esta materia el alumno todavía no aprobó */
                   (SELECT COUNT(*)
                      FROM Correlativa co
                     WHERE co.CodMateria = m.CodMateria
                       AND NOT EXISTS (
                           SELECT 1 FROM Inscripcion i2
                             JOIN Curso c2 ON c2.IDCurso = i2.IDCurso
                            WHERE i2.DNI = :dni2
                              AND c2.CodMateria = co.CodCorrelativa
                              AND i2.Estado = 'Aprobado'
                       )
                   ) AS CorrelativasPendientes,
                   /* Marca si el alumno ya tiene esta materia en estado Libre (la está por recursar) */
                   EXISTS (
                       SELECT 1 FROM Inscripcion i4
                         JOIN Curso c4 ON c4.IDCurso = i4.IDCurso
                        WHERE i4.DNI = :dni3
                          AND c4.CodMateria = m.CodMateria
                          AND i4.Estado = 'Libre'
                   ) AS EsRecursada
              FROM Curso c
              JOIN Materia m ON m.CodMateria = c.CodMateria
              JOIN Docente d ON d.Legajo      = c.Legajo
              JOIN Aula au   ON au.IDAula      = c.IDAula
             WHERE c.Activo = 1
               /* Solo materias de la carrera del alumno */
               AND m.CodCarrera = :carrera
               /* Solo materias de su año académico o anteriores (recursar libres), nunca más adelantadas */
               AND m.Anio <= :anioMax
               /* Excluye las materias que el alumno ya aprobó, está cursando o tiene como regular.
                * La exclusión es por MATERIA (no por curso): así una materia ya aprobada en un curso
                * de un año anterior no reaparece en el curso nuevo. Las materias en estado Libre no
                * se excluyen, para permitir recursarlas. */
               AND m.CodMateria NOT IN (
                   SELECT c2.CodMateria
                     FROM Inscripcion i3
                     JOIN Curso c2 ON c2.IDCurso = i3.IDCurso
                    WHERE i3.DNI = :dni
                      AND i3.Estado IN ('Aprobado', 'Activo', 'Regular')
               )
             ORDER BY m.Anio, m.NomMateria
        ");
        $stmt->execute([
            ':dni'     => $dni,
            ':dni2'    => $dni,
            ':dni3'    => $dni,
            ':carrera' => $codCarrera,
            ':anioMax' => $anioMax,
        ]);
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

    /**
     * Indica si el alumno ya aprobó, está cursando (Activo) o tiene como regular
     * la materia indicada. Sirve para impedir que se reinscriba a algo que no corresponde.
     * No considera el estado Libre (ese SÍ se puede recursar).
     */
    public function yaTieneMateria(int $dni, string $codMateria): bool {
        $stmt = $this->db->prepare("
            SELECT 1
              FROM Inscripcion i
              JOIN Curso c ON c.IDCurso = i.IDCurso
             WHERE i.DNI = :dni
               AND c.CodMateria = :cod
               AND i.Estado IN ('Aprobado', 'Activo', 'Regular')
             LIMIT 1
        ");
        $stmt->execute([':dni' => $dni, ':cod' => $codMateria]);
        return (bool)$stmt->fetch();
    }

    /** Inscribe al alumno en un curso. */
    public function inscribir(int $dni, int $idCurso): void {
        $stmt = $this->db->prepare("
            INSERT INTO Inscripcion (DNI, IDCurso, Estado, FechaInscripcion)
            VALUES (:dni, :idcurso, 'Activo', CURDATE())
        ");
        $stmt->execute([':dni' => $dni, ':idcurso' => $idCurso]);
    }

    /**
     * Da de baja al alumno de un curso.
     * Solo aplica si la inscripción está en estado 'Activo' y pertenece al DNI indicado,
     * lo que evita que un alumno pueda dar de baja una inscripción ajena manipulando el POST.
     */
    public function darDeBaja(int $idInscripcion, int $dni): bool {
        $stmt = $this->db->prepare("
            UPDATE Inscripcion
               SET Estado = 'Baja'
             WHERE IDInscripcion = :id
               AND DNI           = :dni
               AND Estado        = 'Activo'
        ");
        $stmt->execute([':id' => $idInscripcion, ':dni' => $dni]);
        /* Devuelve true si efectivamente se modificó una fila */
        return $stmt->rowCount() > 0;
    }

    /** Devuelve los datos completos de un curso por su ID, o [] si no existe.
     *  Incluye el año y la carrera de la materia para validar reglas de inscripción. */
    public function getCursoById(int $idCurso): array {
        $stmt = $this->db->prepare("
            SELECT c.*, m.NomMateria, m.Anio AS AnioMateria, m.CodCarrera,
                   au.Numero AS Aula,
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
