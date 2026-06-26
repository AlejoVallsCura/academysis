<?php
$fCarrera  = $_GET['f_carrera'] ?? '';
$filtrados = array_filter($cursos, function($c) use ($fCarrera) {
    if ($fCarrera && $c['CodCarrera'] !== $fCarrera) return false;
    return true;
});

$carreras = [];
foreach ($cursos as $c) {
    if (!empty($c['CodCarrera']) && !isset($carreras[$c['CodCarrera']])) {
        $carreras[$c['CodCarrera']] = true;
    }
}
ksort($carreras);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2"></i>Cursos</h4>
    <a href="index.php?controller=admin&action=curso" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Curso
    </a>
</div>

<form method="get" action="index.php" class="d-flex gap-2 mb-3">
    <input type="hidden" name="controller" value="admin">
    <input type="hidden" name="action" value="cursos">
    <select name="f_carrera" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
        <option value="">Todas las carreras</option>
        <?php foreach (array_keys($carreras) as $cod): ?>
        <option value="<?= htmlspecialchars($cod) ?>" <?= $fCarrera === $cod ? 'selected' : '' ?>>
            <?= htmlspecialchars($cod) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <?php if ($fCarrera): ?>
    <a href="index.php?controller=admin&action=cursos" class="btn btn-outline-secondary btn-sm">Limpiar</a>
    <?php endif; ?>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Materia</th>
                    <th>Año</th>
                    <th>Horarios</th>
                    <th>Carga hs</th>
                    <th>Aula</th>
                    <th>Docente</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($filtrados)): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">No hay cursos para los filtros seleccionados.</td></tr>
            <?php else: ?>
                <?php foreach ($filtrados as $c): ?>
                <tr>
                    <td>
                        <?php if (!empty($c['CodCarrera'])): ?>
                            <span class="badge bg-primary me-1" style="font-size:.7rem"><?= htmlspecialchars($c['CodCarrera']) ?></span>
                        <?php endif; ?>
                        <?= htmlspecialchars($c['NomMateria']) ?>
                    </td>
                    <td><?= (int)$c['AnioLectivo'] ?></td>
                    <td><small><?= htmlspecialchars($c['Horarios'] ?? '—') ?></small></td>
                    <td class="text-center"><?= $c['CargaHoraria'] ? (int)$c['CargaHoraria'] . 'hs' : '—' ?></td>
                    <td><?= htmlspecialchars($c['Aula']) ?> <small class="text-muted">(<?= htmlspecialchars($c['Edificio']) ?>)</small></td>
                    <td><?= htmlspecialchars($c['DocApellido'] . ', ' . $c['DocNombre']) ?></td>
                    <td>
                        <?php if ($c['Activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="index.php?controller=admin&action=curso&id=<?= (int)$c['IDCurso'] ?>"
                           class="btn btn-outline-primary btn-sm me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="post" action="index.php?controller=admin&action=toggleCurso" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$c['IDCurso'] ?>">
                            <button type="submit" class="btn btn-sm <?= $c['Activo'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>">
                                <i class="bi <?= $c['Activo'] ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
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
