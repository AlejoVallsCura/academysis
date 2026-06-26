<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-clipboard-data me-2"></i>Mis Notas</h4>
    <form method="get" action="index.php" class="d-flex align-items-center gap-2">
        <input type="hidden" name="controller" value="alumno">
        <input type="hidden" name="action" value="misNotas">
        <label class="text-muted small me-1 mb-0">Año:</label>
        <select name="anio" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <option value="0" <?= $anio === 0 ? 'selected' : '' ?>>Todos</option>
            <?php foreach ($anios as $a): ?>
                <option value="<?= (int)$a['AnioLectivo'] ?>" <?= $anio === (int)$a['AnioLectivo'] ? 'selected' : '' ?>>
                    <?= (int)$a['AnioLectivo'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (!empty($promedios)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Promedios por materia</div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr><th>Materia</th><th class="text-center">Evaluaciones</th><th class="text-center">Promedio</th></tr>
            </thead>
            <tbody>
            <?php foreach ($promedios as $p): ?>
                <?php $pr = (float)$p['promedio']; $color = $pr >= 7 ? 'success' : ($pr >= 4 ? 'warning' : 'danger'); ?>
                <tr>
                    <td><?= htmlspecialchars($p['NomMateria']) ?></td>
                    <td class="text-center"><?= (int)$p['cantidad'] ?></td>
                    <td class="text-center"><span class="badge bg-<?= $color ?> fs-6"><?= number_format($pr,2) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (empty($notas)): ?>
    <div class="alert alert-info">No hay evaluaciones registradas.</div>
<?php else: ?>
    <?php foreach ($notas as $grupo): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-primary bg-opacity-10 fw-semibold">
            <?= htmlspecialchars($grupo['NomMateria']) ?>
            <small class="text-muted fw-normal ms-2"><?= htmlspecialchars($grupo['AnioLectivo']) ?></small>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr><th>Tipo</th><th>Instancia</th><th>Fecha</th><th class="text-center">Nota</th></tr>
                </thead>
                <tbody>
                <?php foreach ($grupo['evaluaciones'] as $e): ?>
                    <?php $n = (float)$e['Nota']; $c = $n >= 7 ? 'success' : ($n >= 4 ? 'warning' : 'danger'); ?>
                    <tr>
                        <td><?= htmlspecialchars($e['Tipo']) ?></td>
                        <td><?= (int)$e['Instancia'] ?>°</td>
                        <td><?= htmlspecialchars($e['Fecha']) ?></td>
                        <td class="text-center"><span class="badge bg-<?= $c ?>"><?= number_format($n,2) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
