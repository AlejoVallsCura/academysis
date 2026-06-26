<?php

/** Modelo para consultas relacionadas con el alumno. */
class AlumnoModel extends Model {

    /** Devuelve los datos del alumno por DNI (incluye NomCarrera), o [] si no existe. */
    public function getByDNI(int $dni): array {
        $stmt = $this->db->prepare("
            SELECT a.*, ca.NomCarrera
              FROM Alumno a
              LEFT JOIN Carrera ca ON ca.CodCarrera = a.CodCarrera
             WHERE a.DNI = :dni
        ");
        $stmt->execute([':dni' => $dni]);
        return $stmt->fetch() ?: [];
    }

    /** Devuelve el progreso académico del alumno dentro de su carrera.
     *  Incluye columna `bloqueada` (1/0): si la materia tiene correlativas sin aprobar. */
    public function getProgreso(int $dni, string $codCarrera): array {
        $stmt = $this->db->prepare("
            SELECT m.CodMateria, m.NomMateria, m.Anio,
                   (SELECT i.Estado
                      FROM Inscripcion i
                      JOIN Curso c ON c.IDCurso = i.IDCurso
                     WHERE i.DNI = :dni AND c.CodMateria = m.CodMateria
                     ORDER BY FIELD(i.Estado,'Aprobado','Regular','Activo','Libre','Baja') ASC
                     LIMIT 1) AS Estado,
                   EXISTS (
                       SELECT 1 FROM Correlativa co
                        WHERE co.CodMateria = m.CodMateria
                          AND NOT EXISTS (
                              SELECT 1 FROM Inscripcion i2
                                JOIN Curso c2 ON c2.IDCurso = i2.IDCurso
                               WHERE i2.DNI = :dni2
                                 AND c2.CodMateria = co.CodCorrelativa
                                 AND i2.Estado = 'Aprobado'
                          )
                   ) AS bloqueada
              FROM Materia m
             WHERE m.CodCarrera = :carrera AND m.Activo = 1
             ORDER BY m.Anio, m.NomMateria
        ");
        $stmt->execute([':dni' => $dni, ':dni2' => $dni, ':carrera' => $codCarrera]);
        return $stmt->fetchAll();
    }

    /** Devuelve cursos activos, promedio general y porcentaje de asistencia del alumno. */
    public function getResumenDashboard(int $dni): array {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total FROM Inscripcion WHERE DNI = :dni AND Estado = 'Activo'
        ");
        $stmt->execute([':dni' => $dni]);
        $cursosActivos = (int)$stmt->fetch()['total'];

        $stmt = $this->db->prepare("
            SELECT ROUND(AVG(e.Nota), 2) AS promedio
              FROM Evaluacion e
              JOIN Inscripcion i ON i.IDInscripcion = e.IDInscripcion
             WHERE i.DNI = :dni
        ");
        $stmt->execute([':dni' => $dni]);
        $promedio = $stmt->fetch()['promedio'] ?? 0;

        $stmt = $this->db->prepare("
            SELECT ROUND(AVG(a.Presente) * 100, 1) AS pct
              FROM Asistencia a
              JOIN Inscripcion i ON i.IDInscripcion = a.IDInscripcion
             WHERE i.DNI = :dni
        ");
        $stmt->execute([':dni' => $dni]);
        $asistencia = $stmt->fetch()['pct'] ?? 0;

        return [
            'cursos_activos'   => $cursosActivos,
            'promedio_general' => $promedio ?: 0,
            'pct_asistencia'   => $asistencia ?: 0,
        ];
    }
}
