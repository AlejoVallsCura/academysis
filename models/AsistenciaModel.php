<?php

/** Modelo para consultas y operaciones sobre asistencia. */
class AsistenciaModel extends Model {

    /** Devuelve los años lectivos en los que el alumno tiene registros de asistencia. */
    public function getAniosDisponibles(int $dni): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT c.AnioLectivo
              FROM Asistencia a
              JOIN Inscripcion i ON i.IDInscripcion = a.IDInscripcion
              JOIN Curso c       ON c.IDCurso        = i.IDCurso
             WHERE i.DNI = :dni
             ORDER BY c.AnioLectivo DESC
        ");
        $stmt->execute([':dni' => $dni]);
        return $stmt->fetchAll();
    }

    /** Devuelve el historial de asistencia del alumno agrupado por curso, con porcentaje calculado.
     *  Si $anio > 0 filtra solo ese año lectivo. */
    public function getAsistenciaByAlumno(int $dni, int $anio = 0): array {
        $stmt = $this->db->prepare("
            SELECT m.NomMateria,
                   c.IDCurso, c.AnioLectivo,
                   (SELECT GROUP_CONCAT(ch.Dia
                               ORDER BY FIELD(ch.Dia,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado')
                               SEPARATOR ', ')
                    FROM CursoHorario ch WHERE ch.IDCurso = c.IDCurso) AS Horarios,
                   a.Fecha, a.Presente, a.Observaciones
              FROM Asistencia a
              JOIN Inscripcion i ON i.IDInscripcion = a.IDInscripcion
              JOIN Curso c       ON c.IDCurso        = i.IDCurso
              JOIN Materia m     ON m.CodMateria      = c.CodMateria
             WHERE i.DNI = :dni
               AND (:anio = 0 OR c.AnioLectivo = :anio2)
             ORDER BY m.NomMateria, a.Fecha DESC
        ");
        $stmt->execute([':dni' => $dni, ':anio' => $anio, ':anio2' => $anio]);

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $key = $row['IDCurso'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'NomMateria'   => $row['NomMateria'],
                    'IDCurso'      => $row['IDCurso'],
                    'Horarios'     => $row['Horarios'],
                    'AnioLectivo'  => $row['AnioLectivo'],
                    'clases'       => [],
                    'presentes'    => 0,
                    'total'        => 0,
                ];
            }
            $grouped[$key]['clases'][] = $row;
            $grouped[$key]['total']++;
            if ($row['Presente']) $grouped[$key]['presentes']++;
        }
        // Calcula el porcentaje por curso una vez agrupados todos los registros
        foreach ($grouped as &$g) {
            $g['pct'] = $g['total'] > 0 ? round($g['presentes'] / $g['total'] * 100, 1) : 0;
        }
        return array_values($grouped);
    }

    /** Devuelve la lista de alumnos de un curso con su estado de asistencia en una fecha dada. */
    public function getAsistenciaByCurso(int $idCurso, string $fecha): array {
        $stmt = $this->db->prepare("
            SELECT al.DNI, al.Nombre, al.Apellido,
                   i.IDInscripcion,
                   COALESCE(a.Presente, -1) AS Presente,
                   a.Observaciones
              FROM Inscripcion i
              JOIN Alumno al ON al.DNI = i.DNI
              LEFT JOIN Asistencia a
                     ON a.IDInscripcion = i.IDInscripcion AND a.Fecha = :fecha
             WHERE i.IDCurso = :idCurso AND i.Estado != 'Baja'
             ORDER BY al.Apellido, al.Nombre
        ");
        $stmt->execute([':idCurso' => $idCurso, ':fecha' => $fecha]);
        return $stmt->fetchAll();
    }

    /** Guarda o actualiza los registros de asistencia de una clase completa. */
    public function guardarAsistencia(array $registros): bool {
        // El statement se prepara una sola vez y se reutiliza para cada alumno
        $stmt = $this->db->prepare("
            INSERT INTO Asistencia (IDInscripcion, Fecha, Presente, Observaciones)
            VALUES (:idinsc, :fecha, :presente, :obs)
            ON DUPLICATE KEY UPDATE
                Presente      = VALUES(Presente),
                Observaciones = VALUES(Observaciones)
        ");
        foreach ($registros as $r) {
            $stmt->execute([
                ':idinsc'   => $r['IDInscripcion'],
                ':fecha'    => $r['Fecha'],
                ':presente' => $r['Presente'],
                ':obs'      => $r['Observaciones'] ?? null,
            ]);
        }
        return true;
    }
}
