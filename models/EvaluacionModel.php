<?php

/** Modelo para consultas y operaciones sobre evaluaciones (notas). */
class EvaluacionModel extends Model {

    /** Devuelve los años lectivos en los que el alumno tiene evaluaciones. */
    public function getAniosDisponibles(int $dni): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT c.AnioLectivo
              FROM Evaluacion e
              JOIN Inscripcion i ON i.IDInscripcion = e.IDInscripcion
              JOIN Curso c       ON c.IDCurso        = i.IDCurso
             WHERE i.DNI = :dni
             ORDER BY c.AnioLectivo DESC
        ");
        $stmt->execute([':dni' => $dni]);
        return $stmt->fetchAll();
    }

    /** Devuelve las notas del alumno agrupadas por materia y cuatrimestre.
     *  Si $anio > 0 filtra solo ese año lectivo. */
    public function getNotasByAlumno(int $dni, int $anio = 0): array {
        $stmt = $this->db->prepare("
            SELECT m.NomMateria, m.CodMateria, m.Anio AS AnioMateria,
                   e.IDEvaluacion, e.Tipo, e.Nota, e.Fecha, e.Instancia,
                   c.AnioLectivo
              FROM Evaluacion e
              JOIN Inscripcion i ON i.IDInscripcion = e.IDInscripcion
              JOIN Curso c       ON c.IDCurso        = i.IDCurso
              JOIN Materia m     ON m.CodMateria      = c.CodMateria
             WHERE i.DNI = :dni
               AND (:anio = 0 OR c.AnioLectivo = :anio2)
             ORDER BY m.NomMateria, c.AnioLectivo DESC, e.Fecha DESC
        ");
        $stmt->execute([':dni' => $dni, ':anio' => $anio, ':anio2' => $anio]);

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $key = $row['CodMateria'] . '-' . $row['AnioLectivo'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'NomMateria'   => $row['NomMateria'],
                    'AnioMateria'  => $row['AnioMateria'],
                    'AnioLectivo'  => $row['AnioLectivo'],
                    'evaluaciones' => [],
                ];
            }
            $grouped[$key]['evaluaciones'][] = $row;
        }
        return array_values($grouped);
    }

    /** Devuelve el promedio y cantidad de evaluaciones por materia del alumno.
     *  Si $anio > 0 filtra solo ese año lectivo. */
    public function getPromedioByMateria(int $dni, int $anio = 0): array {
        $stmt = $this->db->prepare("
            SELECT m.NomMateria, m.Anio AS AnioMateria,
                   ROUND(AVG(e.Nota), 2) AS promedio,
                   COUNT(e.IDEvaluacion)  AS cantidad
              FROM Evaluacion e
              JOIN Inscripcion i ON i.IDInscripcion = e.IDInscripcion
              JOIN Curso c       ON c.IDCurso        = i.IDCurso
              JOIN Materia m     ON m.CodMateria      = c.CodMateria
             WHERE i.DNI = :dni
               AND (:anio = 0 OR c.AnioLectivo = :anio2)
             GROUP BY m.CodMateria, m.NomMateria, m.Anio
             ORDER BY m.Anio, m.NomMateria
        ");
        $stmt->execute([':dni' => $dni, ':anio' => $anio, ':anio2' => $anio]);
        return $stmt->fetchAll();
    }

    /** Inserta una nueva evaluación llamando al SP RegistrarNota. */
    public function guardarEvaluacion(array $d): bool {
        $stmt = $this->db->prepare("CALL RegistrarNota(:dni, :idcurso, :tipo, :nota, :fecha, :inst)");
        $ok = $stmt->execute([
            ':dni'    => $d['DNI'],
            ':idcurso'=> $d['IDCurso'],
            ':tipo'   => $d['Tipo'],
            ':nota'   => $d['Nota'],
            ':fecha'  => $d['Fecha'],
            ':inst'   => $d['Instancia'] ?? 1,
        ]);
        // El SP hace un SELECT final; closeCursor() libera ese resultado
        // para que PDO pueda ejecutar queries siguientes sin "Commands out of sync"
        $stmt->closeCursor();
        return $ok;
    }

    /** Devuelve true si el alumno ya tiene un Final cargado en ese curso. */
    public function tieneFinal(int $idCurso, int $dni): bool {
        $stmt = $this->db->prepare("
            SELECT 1 FROM Evaluacion e
              JOIN Inscripcion i ON i.IDInscripcion = e.IDInscripcion
             WHERE i.IDCurso = :idCurso AND i.DNI = :dni AND e.Tipo = 'Final'
             LIMIT 1
        ");
        $stmt->execute([':idCurso' => $idCurso, ':dni' => $dni]);
        return (bool)$stmt->fetch();
    }

    /** Devuelve todas las evaluaciones de un alumno en un curso. */
    public function getEvaluacionesByCursoAlumno(int $idCurso, int $dni): array {
        $stmt = $this->db->prepare("
            SELECT e.IDEvaluacion, e.Tipo, e.Nota, e.Fecha, e.Instancia,
                   a.Nombre, a.Apellido, a.DNI
              FROM Evaluacion e
              JOIN Inscripcion i ON i.IDInscripcion = e.IDInscripcion
              JOIN Alumno a      ON a.DNI = i.DNI
             WHERE i.IDCurso = :idCurso AND i.DNI = :dni
             ORDER BY e.Fecha DESC, e.Tipo
        ");
        $stmt->execute([':idCurso' => $idCurso, ':dni' => $dni]);
        return $stmt->fetchAll();
    }

    /** Actualiza los datos de una evaluación existente y devuelve true si fue exitosa. */
    public function editarEvaluacion(int $id, array $d): bool {
        $stmt = $this->db->prepare("
            UPDATE Evaluacion
               SET Tipo = :tipo, Nota = :nota, Fecha = :fecha, Instancia = :inst
             WHERE IDEvaluacion = :id
        ");
        return $stmt->execute([
            ':id'   => $id,
            ':tipo' => $d['Tipo'],
            ':nota' => $d['Nota'],
            ':fecha'=> $d['Fecha'],
            ':inst' => $d['Instancia'] ?? 1,
        ]);
    }
}
