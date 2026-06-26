<h4 class="mb-4 fw-bold">
    <i class="bi bi-pencil-square me-2"></i>Cargar Nota
    <?php if (!empty($curso['NomMateria'])): ?>
        <small class="text-muted fs-6 fw-normal ms-2"><?= htmlspecialchars($curso['NomMateria']) ?></small>
    <?php endif; ?>
</h4>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($alumnos)): ?>
    <div class="alert alert-warning">
        Seleccioná un curso desde
        <a href="index.php?controller=docente&action=misCursos">Mis Cursos</a>.
    </div>
<?php else: ?>
<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="index.php?controller=docente&action=cargarNota" novalidate>
            <input type="hidden" name="IDCurso" value="<?= (int)$idCurso ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Alumno</label>
                <select name="DNI" class="form-select" required>
                    <option value="">— Seleccioná un alumno —</option>
                    <?php foreach ($alumnos as $a): ?>
                        <option value="<?= (int)$a['DNI'] ?>">
                            <?= htmlspecialchars($a['Apellido'] . ', ' . $a['Nombre']) ?>
                            (DNI <?= htmlspecialchars($a['DNI']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Tipo de evaluación</label>
                <select name="Tipo" class="form-select" required>
                    <option value="">— Seleccioná —</option>
                    <option>Trabajo Práctico</option>
                    <option>Parcial</option>
                    <option>Final</option>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Nota (0 – 10)</label>
                    <input type="number" name="Nota" class="form-control"
                           min="0" max="10" step="0.25" placeholder="Ej: 7.50" required>
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Instancia</label>
                    <select name="Instancia" class="form-select">
                        <option value="1">1° instancia</option>
                        <option value="2">2° instancia (Recup.)</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Fecha</label>
                <input type="date" name="Fecha" class="form-control"
                       value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>Guardar nota
                </button>
                <a href="index.php?controller=docente&action=alumnosCurso&idCurso=<?= (int)$idCurso ?>"
                   class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
