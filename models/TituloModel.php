<?php

/** Modelo para consultas sobre títulos obtenidos por alumnos. */
class TituloModel extends Model {

    /** Devuelve el título más reciente del alumno con datos de la carrera, o null si no egresó. */
    public function getTituloByAlumno(int $dni): ?array {
        $stmt = $this->db->prepare("
            SELECT t.*, c.NomCarrera, c.DurAnios
              FROM TituloObtenido t
              JOIN Carrera c ON c.CodCarrera = t.CodCarrera
             WHERE t.DNI = :dni
             ORDER BY t.FechaEgreso DESC
             LIMIT 1
        ");
        $stmt->execute([':dni' => $dni]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
