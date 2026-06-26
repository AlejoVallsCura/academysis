<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-pencil-square me-2"></i>Editar Notas
        <small class="text-muted fs-6 fw-normal ms-2">
            <?= htmlspecialchars($alumno['Apellido'] . ', ' . $alumno['Nombre']) ?>
            — <?= htmlspecialchars($curso['NomMateria'] ?? '') ?>
        </small>
    </h4>
    <a href="index.php?controller=docente&action=alumnosCurso&idCurso=<?= (int)$idCurso ?>"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($notas)): ?>
    <div class="alert alert-info">Este alumno no tiene notas cargadas en este curso.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Tipo</th>
                    <th class="text-center">Instancia</th>
                    <th class="text-center">Nota</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($notas as $n):
                $c = (float)$n['Nota'] >= 6 ? 'success' : ((float)$n['Nota'] >= 4 ? 'warning' : 'danger');
            ?>
                <tr>
                    <form method="post" action="index.php?controller=docente&action=editarNota">
                    <input type="hidden" name="IDCurso"      value="<?= (int)$idCurso ?>">
                    <input type="hidden" name="DNI"          value="<?= (int)$dni ?>">
                    <input type="hidden" name="IDEvaluacion" value="<?= (int)$n['IDEvaluacion'] ?>">

                    <td>
                        <select name="Tipo" class="form-select form-select-sm" style="min-width:140px">
                            <?php foreach (['Trabajo Práctico','Parcial','Final'] as $t): ?>
                                <option <?= $n['Tipo'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="text-center">
                        <select name="Instancia" class="form-select form-select-sm" style="width:120px;margin:auto">
                            <option value="1" <?= (int)$n['Instancia'] === 1 ? 'selected' : '' ?>>1° instancia</option>
                            <option value="2" <?= (int)$n['Instancia'] === 2 ? 'selected' : '' ?>>2° instancia</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <span class="badge bg-<?= $c ?>"><?= number_format((float)$n['Nota'], 2) ?></span>
                            <input type="number" name="Nota" value="<?= htmlspecialchars($n['Nota']) ?>"
                                   min="0" max="10" step="0.25"
                                   class="form-control form-control-sm" style="width:80px">
                        </div>
                    </td>
                    <td>
                        <input type="date" name="Fecha" value="<?= htmlspecialchars($n['Fecha']) ?>"
                               class="form-control form-control-sm" style="width:140px">
                    </td>
                    <td class="text-end">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Guardar
                        </button>
                    </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
