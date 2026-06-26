<h4 class="mb-4 fw-bold">
    <i class="bi bi-person-check me-2"></i>Cargar Asistencia
    <?php if (!empty($curso['NomMateria'])): ?>
        <small class="text-muted fs-6 fw-normal ms-2"><?= htmlspecialchars($curso['NomMateria']) ?></small>
    <?php endif; ?>
</h4>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!$idCurso): ?>
    <div class="alert alert-warning">
        Seleccioná un curso desde
        <a href="index.php?controller=docente&action=misCursos">Mis Cursos</a>.
    </div>
<?php else: ?>

<!-- Selector de fecha -->
<form method="GET" action="index.php" class="mb-4 d-flex align-items-end gap-3">
    <input type="hidden" name="controller" value="docente">
    <input type="hidden" name="action" value="cargarAsistencia">
    <input type="hidden" name="idCurso" value="<?= (int)$idCurso ?>">
    <div>
        <label class="form-label fw-semibold mb-1">Fecha de clase</label>
        <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($fecha) ?>">
    </div>
    <button type="submit" class="btn btn-outline-primary">
        <i class="bi bi-search me-1"></i>Cargar para esta fecha
    </button>
</form>

<?php if (empty($alumnos)): ?>
    <div class="alert alert-info">No hay alumnos activos en este curso.</div>
<?php else: ?>
<form method="POST" action="index.php?controller=docente&action=cargarAsistencia">
    <input type="hidden" name="IDCurso" value="<?= (int)$idCurso ?>">
    <input type="hidden" name="fecha"   value="<?= htmlspecialchars($fecha) ?>">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            Clase del <?= htmlspecialchars($fecha) ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Apellido y Nombre</th>
                        <th>DNI</th>
                        <th class="text-center" style="width:130px">Presente</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($alumnos as $a): ?>
                    <input type="hidden" name="IDInscripcion[]" value="<?= (int)$a['IDInscripcion'] ?>">
                    <tr>
                        <td><?= htmlspecialchars($a['Apellido'] . ', ' . $a['Nombre']) ?></td>
                        <td><?= htmlspecialchars($a['DNI']) ?></td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox"
                                       name="presente[]"
                                       value="<?= (int)$a['IDInscripcion'] ?>"
                                       <?= ($a['Presente'] !== -1 && $a['Presente']) ? 'checked' : '' ?>
                                       style="width:2.5rem; height:1.3rem;">
                            </div>
                        </td>
                        <td>
                            <input type="text" name="obs[<?= (int)$a['IDInscripcion'] ?>]"
                                   class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($a['Observaciones'] ?? '') ?>"
                                   placeholder="Opcional">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-warning fw-semibold">
            <i class="bi bi-save me-1"></i>Guardar asistencia
        </button>
        <a href="index.php?controller=docente&action=alumnosCurso&idCurso=<?= (int)$idCurso ?>"
           class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?php endif; ?>
<?php endif; ?>
