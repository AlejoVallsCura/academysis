<?php
/* Colores de badge según el tipo de acción, para identificarlas de un vistazo */
$colorAccion = [
    'LOGIN'        => 'success',
    'LOGOUT'       => 'secondary',
    'ALTA'         => 'primary',
    'MODIFICACION' => 'warning',
    'BAJA'         => 'danger',
    'CONSULTA'     => 'info',
    'ERROR'        => 'dark',
];
/* Conserva los filtros actuales al armar los links de paginación */
$qsBase = http_build_query(array_filter([
    'controller' => 'admin',
    'action'     => 'auditoria',
    'accion'     => $filtros['accion'],
    'rol'        => $filtros['rol'],
    'desde'      => $filtros['desde'],
    'hasta'      => $filtros['hasta'],
    'buscar'     => $filtros['buscar'],
]));
?>

<h4 class="fw-bold mb-3"><i class="bi bi-clipboard-check me-2"></i>Auditoría del Sistema</h4>

<!-- Resumen: cantidad de eventos por tipo de acción -->
<div class="row g-2 mb-4">
    <?php foreach ($resumen as $r):
        $col = $colorAccion[$r['Accion']] ?? 'secondary'; ?>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="h4 fw-bold mb-0 text-<?= $col ?>"><?= (int)$r['Total'] ?></div>
                <div class="text-muted" style="font-size:.7rem"><?= htmlspecialchars($r['Accion']) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Pestañas: log general de la app vs auditoría de inscripciones por triggers -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general" type="button">
            <i class="bi bi-list-ul me-1"></i>Log de la aplicación
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-inscripciones" type="button">
            <i class="bi bi-database me-1"></i>Inscripciones (triggers BBDD)
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- ============ TAB 1: LOG GENERAL ============ -->
    <div class="tab-pane fade show active" id="tab-general">

        <!-- Filtros -->
        <form method="get" action="index.php" class="row g-2 mb-3 align-items-end">
            <input type="hidden" name="controller" value="admin">
            <input type="hidden" name="action" value="auditoria">

            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Acción</label>
                <select name="accion" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($acciones as $a): ?>
                        <option value="<?= htmlspecialchars($a) ?>" <?= $filtros['accion'] === $a ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Rol</label>
                <select name="rol" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach (['admin','docente','alumno'] as $r): ?>
                        <option value="<?= $r ?>" <?= $filtros['rol'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Desde</label>
                <input type="date" name="desde" value="<?= htmlspecialchars($filtros['desde']) ?>" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Hasta</label>
                <input type="date" name="hasta" value="<?= htmlspecialchars($filtros['hasta']) ?>" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Buscar</label>
                <input type="text" name="buscar" value="<?= htmlspecialchars($filtros['buscar']) ?>"
                       placeholder="email o detalle" class="form-control form-control-sm">
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="index.php?controller=admin&action=auditoria" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>

        <div class="text-muted small mb-2"><?= (int)$total ?> registros encontrados</div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0" style="font-size:.85rem">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Acción</th>
                            <th>Entidad</th>
                            <th>Detalle</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($registros)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No hay registros para los filtros seleccionados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($registros as $reg):
                            $col = $colorAccion[$reg['Accion']] ?? 'secondary'; ?>
                        <tr>
                            <td class="text-nowrap"><?= htmlspecialchars($reg['Fecha']) ?></td>
                            <td><?= htmlspecialchars($reg['Email'] ?? '—') ?></td>
                            <td><span class="badge bg-light text-dark"><?= htmlspecialchars($reg['Rol'] ?? '—') ?></span></td>
                            <td><span class="badge bg-<?= $col ?>"><?= htmlspecialchars($reg['Accion']) ?></span></td>
                            <td><?= htmlspecialchars($reg['Entidad'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($reg['Detalle'] ?? '') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($reg['IP'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <nav class="mt-3">
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="index.php?<?= $qsBase ?>&pagina=<?= $pagina - 1 ?>">«</a>
                </li>
                <?php
                /* Muestra una ventana de páginas alrededor de la actual para no saturar */
                $ini = max(1, $pagina - 2);
                $fin = min($totalPaginas, $pagina + 2);
                for ($p = $ini; $p <= $fin; $p++): ?>
                    <li class="page-item <?= $p === $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="index.php?<?= $qsBase ?>&pagina=<?= $p ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="index.php?<?= $qsBase ?>&pagina=<?= $pagina + 1 ?>">»</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- ============ TAB 2: AUDITORÍA DE INSCRIPCIONES (TRIGGERS) ============ -->
    <div class="tab-pane fade" id="tab-inscripciones">
        <div class="alert alert-light border small">
            <i class="bi bi-info-circle me-1"></i>
            Esta tabla la mantiene automáticamente la base de datos mediante <strong>triggers</strong>
            sobre la tabla <code>inscripcion</code>. Registra cada cambio de estado de una inscripción
            independientemente de la aplicación. Se muestran los últimos 50 movimientos.
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0" style="font-size:.85rem">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Evento</th>
                            <th>Alumno</th>
                            <th>Materia</th>
                            <th>Estado anterior</th>
                            <th>Estado nuevo</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($inscAudit)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">Sin movimientos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($inscAudit as $ia): ?>
                        <tr>
                            <td class="text-nowrap"><?= htmlspecialchars($ia['Fecha']) ?></td>
                            <td>
                                <span class="badge bg-<?= $ia['Evento'] === 'INSERT' ? 'primary' : 'warning' ?>">
                                    <?= htmlspecialchars($ia['Evento']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($ia['Apellido'] . ', ' . $ia['Nombre']) ?>
                                <small class="text-muted">(<?= (int)$ia['DNI'] ?>)</small></td>
                            <td><?= htmlspecialchars($ia['NomMateria']) ?></td>
                            <td><?= $ia['EstadoAnterior'] ? htmlspecialchars($ia['EstadoAnterior']) : '<span class="text-muted">—</span>' ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($ia['EstadoNuevo']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
