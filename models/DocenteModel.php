<?php

/** Modelo para consultas relacionadas con el docente. */
class DocenteModel extends Model {

    /** Devuelve los datos del docente por legajo, o [] si no existe. */
    public function getByLegajo(int $legajo): array {
        $stmt = $this->db->prepare("SELECT * FROM Docente WHERE Legajo = :legajo");
        $stmt->execute([':legajo' => $legajo]);
        return $stmt->fetch() ?: [];
    }

    /** Devuelve la cantidad de cursos del año actual y alumnos activos a cargo del docente. */
    public function getResumenDashboard(int $legajo): array {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total FROM Curso
             WHERE Legajo = :legajo AND AnioLectivo = YEAR(CURDATE())
        ");
        $stmt->execute([':legajo' => $legajo]);
        $cursos = (int)$stmt->fetch()['total'];

        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT i.DNI) AS total
              FROM Inscripcion i
              JOIN Curso c ON c.IDCurso = i.IDCurso
             WHERE c.Legajo = :legajo
               AND i.Estado != 'Baja'
               AND c.AnioLectivo = YEAR(CURDATE())
        ");
        $stmt->execute([':legajo' => $legajo]);
        $alumnos = (int)$stmt->fetch()['total'];

        return [
            'total_cursos'  => $cursos,
            'total_alumnos' => $alumnos,
        ];
    }
}
