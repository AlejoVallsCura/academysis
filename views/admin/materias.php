<?php
$fCarrera  = $_GET['f_carrera'] ?? '';
$fAnio     = $_GET['f_anio']    ?? '';
$filtradas = array_filter($materias, function($m) use ($fCarrera, $fAnio) {
    if ($fCarrera && $m['CodCarrera'] !== $fCarrera) return false;
    if ($fAnio    && (string)$m['Anio'] !== $fAnio)  return false;
    return true;
});

$carreras = [];
foreach ($materias as $m) {
    if ($m['CodCarrera'] && !isset($carreras[$m['CodCarrera']])) {
        $carreras[$m['CodCarrera']] = $m['NomCarrera'];
    }
}
ksort($carreras);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-book me-2"></i>Materias</h4>
    <a href="index.php?controller=admin&action=materia" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nueva Materia
    </a>
</div>

<form method="get" action="index.php" class="d-flex gap-2 mb-3">
    <input type="hidden" name="controller" value="admin">
    <input type="hidden" name="action" value="materias">
    <select name="f_carrera" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
        <option value="">Todas las carreras</option>
        <?php foreach ($carreras as $cod => $nom): ?>
        <option value="<?= htmlspecialchars($cod) ?>" <?= $fCarrera === $cod ? 'selected' : '' ?>>
            <?= htmlspecialchars($cod) ?> – <?= htmlspecialchars($nom) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <select name="f_anio" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
        <option value="">Todos los años</option>
        <?php for ($a = 1; $a <= 5; $a++): ?>
        <option value="<?= $a ?>" <?= $fAnio === (string)$a ? 'selected' : '' ?>><?= $a ?>°</option>
        <?php endfor; ?>
    </select>
    <?php if ($fCarrera || $fAnio): ?>
    <a href="index.php?controller=admin&action=materias" class="btn btn-outline-secondary btn-sm">Limpiar</a>
    <?php endif; ?>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Carrera</th>
                    <th class="text-center">Año</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($filtradas)): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">No hay materias para los filtros seleccionados.</td></tr>
            <?php else: ?>
                <?php foreach ($filtradas as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['CodMateria']) ?></td>
                    <td><?= htmlspecialchars($m['NomMateria']) ?></td>
                    <td>
                        <?php if ($m['CodCarrera']): ?>
                            <span class="badge bg-primary me-1"><?= htmlspecialchars($m['CodCarrera']) ?></span>
                            <small class="text-muted"><?= htmlspecialchars($m['NomCarrera'] ?? '') ?></small>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?= $m['Anio'] ? $m['Anio'] . '°' : '<span class="text-muted">—</span>' ?>
                    </td>
                    <td>
                        <?php if ($m['Activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="index.php?controller=admin&action=materia&cod=<?= urlencode($m['CodMateria']) ?>"
                           class="btn btn-outline-primary btn-sm me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="post" action="index.php?controller=admin&action=toggleMateria" class="d-inline">
                            <input type="hidden" name="cod" value="<?= htmlspecialchars($m['CodMateria']) ?>">
                            <button type="submit" class="btn btn-sm <?= $m['Activo'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>">
                                <i class="bi <?= $m['Activo'] ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
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
