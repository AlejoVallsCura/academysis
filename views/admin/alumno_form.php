<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-person-badge me-2"></i><?= $isNew ? 'Nuevo Alumno' : 'Editar Alumno' ?>
    </h4>
    <a href="index.php?controller=admin&action=alumnos" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="index.php?controller=admin&action=alumno">
    <?php if ($isNew): ?>
        <input type="hidden" name="_isNew" value="1">
    <?php else: ?>
        <input type="hidden" name="DNI" value="<?= htmlspecialchars($alumno['DNI']) ?>">
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Datos personales</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">DNI</label>
                    <input type="text" name="DNI" class="form-control"
                           value="<?= htmlspecialchars($alumno['DNI'] ?? '') ?>"
                           <?= !$isNew ? 'disabled' : 'required' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" name="Nombre" class="form-control" required
                           value="<?= htmlspecialchars($alumno['Nombre'] ?? '') ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Apellido</label>
                    <input type="text" name="Apellido" class="form-control" required
                           value="<?= htmlspecialchars($alumno['Apellido'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Fecha de Nacimiento</label>
                    <input type="date" name="FechaNacimiento" class="form-control"
                           value="<?= htmlspecialchars($alumno['FechaNacimiento'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="Telefono" class="form-control"
                           value="<?= htmlspecialchars($alumno['Telefono'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="Email" class="form-control" required
                           value="<?= htmlspecialchars($alumno['Email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Fecha de Ingreso</label>
                    <input type="date" name="FechaIngreso" class="form-control" required
                           value="<?= htmlspecialchars($alumno['FechaIngreso'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Carrera</label>
                    <select name="CodCarrera" class="form-select" required>
                        <option value="">— Seleccioná —</option>
                        <?php foreach ($carreras as $c): ?>
                            <option value="<?= htmlspecialchars($c['CodCarrera']) ?>"
                                <?= ($alumno['CodCarrera'] ?? '') === $c['CodCarrera'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['NomCarrera']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Dirección</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Calle</label>
                    <input type="text" name="Calle" class="form-control"
                           value="<?= htmlspecialchars($alumno['Calle'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Número</label>
                    <input type="text" name="DirNumero" class="form-control"
                           value="<?= htmlspecialchars($alumno['DirNumero'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Ciudad</label>
                    <input type="text" name="Ciudad" class="form-control"
                           value="<?= htmlspecialchars($alumno['Ciudad'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Provincia</label>
                    <input type="text" name="Provincia" class="form-control"
                           value="<?= htmlspecialchars($alumno['Provincia'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">CP</label>
                    <input type="text" name="CP" class="form-control"
                           value="<?= htmlspecialchars($alumno['CP'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <?php if ($isNew): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Acceso</div>
        <div class="card-body">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Contraseña inicial</label>
                <input type="password" name="Password" class="form-control" required>
                <div class="form-text">El alumno deberá cambiarla al ingresar.</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy me-1"></i>Guardar
        </button>
        <a href="index.php?controller=admin&action=alumnos" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
