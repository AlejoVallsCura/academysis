<?php

/** Modelo para consultas sobre materias y correlatividades. */
class MateriaModel extends Model {

    /**
     * Devuelve las materias con su lista de correlativas agrupada por materia.
     * Si se pasa $codCarrera, devuelve solo las materias de esa carrera
     * (para que el alumno vea únicamente el plan de la suya). Si es null, trae todas.
     * Ordena por año del plan y nombre.
     */
    public function getCorrelativas(?string $codCarrera = null): array {
        /* El filtro por carrera se aplica solo si se recibió un código */
        $where  = $codCarrera ? "WHERE m.CodCarrera = :carrera" : "";
        $params = $codCarrera ? [':carrera' => $codCarrera] : [];

        $stmt = $this->db->prepare("
            SELECT m.CodMateria, m.NomMateria, m.Anio,
                   co.CodCorrelativa, mp.NomMateria AS NomCorrelativa
              FROM Materia m
              LEFT JOIN Correlativa co ON co.CodMateria = m.CodMateria
              LEFT JOIN Materia mp     ON mp.CodMateria  = co.CodCorrelativa
              {$where}
             ORDER BY m.Anio, m.NomMateria, mp.NomMateria
        ");
        $stmt->execute($params);

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $key = $row['CodMateria'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'CodMateria'   => $row['CodMateria'],
                    'NomMateria'   => $row['NomMateria'],
                    'Anio'         => $row['Anio'],
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
}
