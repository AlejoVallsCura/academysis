<?php
$fCarrera  = $_GET['f_carrera'] ?? '';
$filtrados = array_filter($alumnos, function($a) use ($fCarrera) {
    if ($fCarrera && $a['CodCarrera'] !== $fCarrera) return false;
    return true;
});

$carreras = [];
foreach ($alumnos as $a) {
    if (!empty($a['CodCarrera']) && !isset($carreras[$a['CodCarrera']])) {
        $carreras[$a['CodCarrera']] = true;
    }
}
ksort($carreras);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge me-2"></i>Alumnos</h4>
    <a href="index.php?controller=admin&action=alumno" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Alumno
    </a>
</div>

<form method="get" action="index.php" class="d-flex gap-2 mb-3">
    <input type="hidden" name="controller" value="admin">
    <input type="hidden" name="action" value="alumnos">
    <select name="f_carrera" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
        <option value="">Todas las carreras</option>
        <?php foreach (array_keys($carreras) as $cod): ?>
        <option value="<?= htmlspecialchars($cod) ?>" <?= $fCarrera === $cod ? 'selected' : '' ?>>
            <?= htmlspecialchars($cod) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <?php if ($fCarrera): ?>
    <a href="index.php?controller=admin&action=alumnos" class="btn btn-outline-secondary btn-sm">Limpiar</a>
    <?php endif; ?>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>DNI</th>
                    <th>Apellido y Nombre</th>
                    <th>Carrera</th>
                    <th>Email</th>
                    <th>Fecha Ingreso</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($filtrados)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">No hay alumnos para los filtros seleccionados.</td></tr>
            <?php else: ?>
                <?php foreach ($filtrados as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['DNI']) ?></td>
                    <td><?= htmlspecialchars($a['Apellido'] . ', ' . $a['Nombre']) ?></td>
                    <td>
                        <?php if ($a['NomCarrera']): ?>
                            <span class="badge bg-primary"><?= htmlspecialchars($a['CodCarrera']) ?></span>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($a['Email']) ?></td>
                    <td><?= htmlspecialchars($a['FechaIngreso']) ?></td>
                    <td>
                        <?php if ($a['Activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="index.php?controller=admin&action=alumno&dni=<?= urlencode($a['DNI']) ?>"
                           class="btn btn-outline-primary btn-sm me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="post" action="index.php?controller=admin&action=toggleAlumno" class="d-inline">
                            <input type="hidden" name="dni" value="<?= htmlspecialchars($a['DNI']) ?>">
                            <button type="submit" class="btn btn-sm <?= $a['Activo'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>">
                                <i class="bi <?= $a['Activo'] ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
