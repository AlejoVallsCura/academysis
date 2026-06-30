<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2"></i>Mi Asistencia</h4>
    <form method="get" action="index.php" class="d-flex align-items-center gap-2">
        <input type="hidden" name="controller" value="alumno">
        <input type="hidden" name="action" value="miAsistencia">
        <label class="text-muted small me-1 mb-0">Año:</label>
        <select name="anio" class="form-select form-select-sm" style="width:auto">
            <option value="0" <?= $anio === 0 ? 'selected' : '' ?>>Todos</option>
            <?php foreach ($anios as $a): ?>
                <option value="<?= (int)$a['AnioLectivo'] ?>" <?= $anio === (int)$a['AnioLectivo'] ? 'selected' : '' ?>>
                    <?= (int)$a['AnioLectivo'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
    </form>
</div>

<?php if (empty($asistencia)): ?>
    <div class="alert alert-info">No hay registros de asistencia.</div>
<?php else: ?>
    <?php foreach ($asistencia as $curso): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <span class="fw-semibold">
                <?= htmlspecialchars($curso['NomMateria']) ?>
                <span class="badge bg-secondary ms-1" title="Año en el plan"><?= (int)$curso['AnioMateria'] ?>°</span>
                <small class="text-muted fw-normal ms-2">
                    Lectivo <?= htmlspecialchars($curso['AnioLectivo']) ?> — <?= htmlspecialchars($curso['Horarios'] ?? '') ?>
                </small>
            </span>
            <?php $pct = (float)$curso['pct']; $color = $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger'); ?>
            <span class="badge bg-<?= $color ?> fs-6"><?= number_format($pct,1) ?>% asistencia</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th class="text-center">Asistencia</th><th>Observaciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($curso['clases'] as $cl): ?>
                    <tr>
                        <td><?= htmlspecialchars($cl['Fecha']) ?></td>
                        <td class="text-center">
                            <?php if ($cl['Presente']): ?>
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Presente</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Ausente</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($cl['Observaciones'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
