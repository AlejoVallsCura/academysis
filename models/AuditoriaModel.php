<?php

/**
 * Modelo de lectura de la auditoría para el panel de administración.
 * La escritura del log la hace el helper estático Auditoria; este modelo
 * solo consulta y arma estadísticas para mostrarlas.
 */
class AuditoriaModel extends Model {

    /**
     * Devuelve el log general filtrado y paginado.
     * Los filtros son opcionales; si vienen vacíos no se aplican.
     *
     * @param array $f  Filtros: ['accion'=>?, 'rol'=>?, 'desde'=>?, 'hasta'=>?, 'buscar'=>?]
     * @param int   $limit   Cantidad de filas por página.
     * @param int   $offset  Desplazamiento para la paginación.
     */
    public function getLog(array $f, int $limit, int $offset): array {
        /* Construimos el WHERE dinámicamente según los filtros presentes */
        $where  = [];
        $params = [];

        if (!empty($f['accion'])) {
            $where[]            = "Accion = :accion";
            $params[':accion']  = $f['accion'];
        }
        if (!empty($f['rol'])) {
            $where[]         = "Rol = :rol";
            $params[':rol']  = $f['rol'];
        }
        if (!empty($f['desde'])) {
            $where[]           = "Fecha >= :desde";
            $params[':desde']  = $f['desde'] . ' 00:00:00';
        }
        if (!empty($f['hasta'])) {
            $where[]           = "Fecha <= :hasta";
            $params[':hasta']  = $f['hasta'] . ' 23:59:59';
        }
        if (!empty($f['buscar'])) {
            /* Busca el texto tanto en el email del actor como en el detalle */
            $where[]            = "(Email LIKE :buscar OR Detalle LIKE :buscar)";
            $params[':buscar']  = '%' . $f['buscar'] . '%';
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        /* LIMIT/OFFSET se interpolan como enteros ya saneados (PDO no los acepta como named param con emulación off) */
        $limit  = max(1, $limit);
        $offset = max(0, $offset);

        $stmt = $this->db->prepare("
            SELECT IDAuditoria, Fecha, IDUsuario, Email, Rol, Accion, Entidad, Detalle, IP
              FROM Auditoria
              {$sqlWhere}
             ORDER BY Fecha DESC, IDAuditoria DESC
             LIMIT {$limit} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta el total de filas que cumplen los filtros (para paginar).
     * Reutiliza la misma lógica de WHERE que getLog().
     */
    public function contarLog(array $f): int {
        $where  = [];
        $params = [];

        if (!empty($f['accion'])) { $where[] = "Accion = :accion"; $params[':accion'] = $f['accion']; }
        if (!empty($f['rol']))    { $where[] = "Rol = :rol";       $params[':rol']    = $f['rol']; }
        if (!empty($f['desde']))  { $where[] = "Fecha >= :desde";  $params[':desde']  = $f['desde'] . ' 00:00:00'; }
        if (!empty($f['hasta']))  { $where[] = "Fecha <= :hasta";  $params[':hasta']  = $f['hasta'] . ' 23:59:59'; }
        if (!empty($f['buscar'])) { $where[] = "(Email LIKE :buscar OR Detalle LIKE :buscar)"; $params[':buscar'] = '%' . $f['buscar'] . '%'; }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM Auditoria {$sqlWhere}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** Devuelve la lista de tipos de acción distintos presentes en el log (para el filtro). */
    public function getAccionesDistintas(): array {
        $stmt = $this->db->query("SELECT DISTINCT Accion FROM Auditoria ORDER BY Accion");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Conteo de acciones por tipo, para las tarjetas de resumen del dashboard de auditoría. */
    public function getResumenPorAccion(): array {
        $stmt = $this->db->query("
            SELECT Accion, COUNT(*) AS Total
              FROM Auditoria
             GROUP BY Accion
             ORDER BY Total DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Devuelve la auditoría de inscripciones generada por los triggers de la base
     * (tabla auditinscripcion). Es auditoría a nivel BBDD, complementaria al log de la app.
     */
    public function getAuditInscripcion(int $limit = 50): array {
        $limit = max(1, $limit);
        $stmt = $this->db->query("
            SELECT ai.IDAudit, ai.Fecha AS Fecha, ai.Evento,
                   ai.EstadoAnterior, ai.EstadoNuevo,
                   ai.DNI, al.Nombre, al.Apellido,
                   m.NomMateria
              FROM (
                   SELECT IDAudit, IDInscripcion, DNI, IDCurso,
                          EstadoAnterior, EstadoNuevo, Evento, FechaCambio AS Fecha
                     FROM AuditInscripcion
              ) ai
              JOIN Alumno al ON al.DNI = ai.DNI
              JOIN Curso  c  ON c.IDCurso = ai.IDCurso
              JOIN Materia m ON m.CodMateria = c.CodMateria
             ORDER BY ai.Fecha DESC, ai.IDAudit DESC
             LIMIT {$limit}
        ");
        return $stmt->fetchAll();
    }
}
