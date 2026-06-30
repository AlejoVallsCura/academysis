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

    /**
     * Emite el título del alumno automáticamente si completó la carrera:
     * tiene TODAS las materias activas de su carrera en estado Aprobado y todavía no tiene título.
     * Devuelve true si lo emitió (recién en esta llamada), false si no corresponde o ya lo tenía.
     *
     * El promedio final se calcula con las notas de los finales. Se usa CURDATE() como fecha
     * de egreso y un libro/folio correlativo simple.
     */
    public function emitirTituloSiCompleto(int $dni): bool {
        /* 1. Si ya tiene título, no hace nada */
        $stmt = $this->db->prepare("SELECT 1 FROM TituloObtenido WHERE DNI = :dni LIMIT 1");
        $stmt->execute([':dni' => $dni]);
        if ($stmt->fetch()) return false;

        /* 2. Carrera del alumno */
        $stmt = $this->db->prepare("SELECT CodCarrera FROM Alumno WHERE DNI = :dni");
        $stmt->execute([':dni' => $dni]);
        $carrera = $stmt->fetchColumn();
        if (!$carrera) return false;

        /* 3. Cantidad de materias activas que tiene la carrera */
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM Materia WHERE CodCarrera = :c AND Activo = 1");
        $stmt->execute([':c' => $carrera]);
        $totalMaterias = (int)$stmt->fetchColumn();
        if ($totalMaterias === 0) return false;

        /* 4. Materias distintas de la carrera que el alumno tiene aprobadas */
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT c.CodMateria)
              FROM Inscripcion i
              JOIN Curso c   ON c.IDCurso     = i.IDCurso
              JOIN Materia m ON m.CodMateria  = c.CodMateria
             WHERE i.DNI = :dni
               AND i.Estado = 'Aprobado'
               AND m.CodCarrera = :c
               AND m.Activo = 1
        ");
        $stmt->execute([':dni' => $dni, ':c' => $carrera]);
        $aprobadas = (int)$stmt->fetchColumn();

        /* 5. Si todavía le faltan materias, no corresponde el título */
        if ($aprobadas < $totalMaterias) return false;

        /* 6. Promedio final con las notas de los finales (si no hubiera, queda 7.00 por defecto) */
        $stmt = $this->db->prepare("
            SELECT ROUND(AVG(e.Nota), 2)
              FROM Evaluacion e
              JOIN Inscripcion i ON i.IDInscripcion = e.IDInscripcion
             WHERE i.DNI = :dni AND e.Tipo = 'Final'
        ");
        $stmt->execute([':dni' => $dni]);
        $prom = $stmt->fetchColumn();
        $promedio = $prom !== null ? (float)$prom : 7.00;

        /* 7. Libro/folio correlativo simple según cuántos títulos hay emitidos */
        $n     = (int)$this->db->query("SELECT COUNT(*) FROM TituloObtenido")->fetchColumn() + 1;
        $libro = 'L' . (1 + intdiv($n, 100));
        $folio = 'F' . $n;

        /* 8. Emite el título */
        $stmt = $this->db->prepare("
            INSERT INTO TituloObtenido (DNI, CodCarrera, FechaEgreso, PromedioFinal, LibroTitulo, FolioTitulo)
            VALUES (:dni, :c, CURDATE(), :prom, :libro, :folio)
        ");
        $stmt->execute([
            ':dni'   => $dni,
            ':c'     => $carrera,
            ':prom'  => $promedio,
            ':libro' => $libro,
            ':folio' => $folio,
        ]);
        return true;
    }
}
