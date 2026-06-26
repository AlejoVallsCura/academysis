<?php

/** Modelo para consultas sobre materias y correlatividades. */
class MateriaModel extends Model {

    /** Devuelve todas las materias con su lista de correlativas agrupada por materia. */
    public function getCorrelativas(): array {
        $stmt = $this->db->prepare("
            SELECT m.CodMateria, m.NomMateria,
                   co.CodCorrelativa, mp.NomMateria AS NomCorrelativa
              FROM Materia m
              LEFT JOIN Correlativa co ON co.CodMateria   = m.CodMateria
              LEFT JOIN Materia mp     ON mp.CodMateria    = co.CodCorrelativa
             ORDER BY m.NomMateria, mp.NomMateria
        ");
        $stmt->execute();

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $key = $row['CodMateria'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'CodMateria'   => $row['CodMateria'],
                    'NomMateria'   => $row['NomMateria'],
                    'correlativas' => [],
                ];
            }
            // Solo agrega correlativa si existe (LEFT JOIN puede devolver NULL)
            if ($row['CodCorrelativa']) {
                $grouped[$key]['correlativas'][] = [
                    'Cod'  => $row['CodCorrelativa'],
                    'Nom'  => $row['NomCorrelativa'],
                ];
            }
        }
        return array_values($grouped);
    }

    /** Devuelve todas las materias ordenadas alfabéticamente. */
    public function getAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM Materia ORDER BY NomMateria");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
