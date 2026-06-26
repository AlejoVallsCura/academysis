<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-person-workspace me-2"></i><?= $isNew ? 'Nuevo Docente' : 'Editar Docente' ?>
    </h4>
    <a href="index.php?controller=admin&action=docentes" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="index.php?controller=admin&action=docente">
    <?php if ($isNew): ?>
        <input type="hidden" name="_isNew" value="1">
    <?php else: ?>
        <input type="hidden" name="Legajo" value="<?= htmlspecialchars($docente['Legajo']) ?>">
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-3" style="max-width:640px;">
        <div class="card-header bg-light fw-semibold">Datos del docente</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" name="Nombre" class="form-control" required
                           value="<?= htmlspecialchars($docente['Nombre'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Apellido</label>
                    <input type="text" name="Apellido" class="form-control" required
                           value="<?= htmlspecialchars($docente['Apellido'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">DNI</label>
                    <input type="number" name="DNI" class="form-control" required
                           value="<?= htmlspecialchars($docente['DNI'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Título</label>
                    <input type="text" name="Titulo" class="form-control"
                           value="<?= htmlspecialchars($docente['Titulo'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Especialidad</label>
                    <input type="text" name="Especialidad" class="form-control"
                           value="<?= htmlspecialchars($docente['Especialidad'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="Email" class="form-control" required
                           value="<?= htmlspecialchars($docente['Email'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <?php if ($isNew): ?>
    <div class="card border-0 shadow-sm mb-3" style="max-width:640px;">
        <div class="card-header bg-light fw-semibold">Acceso</div>
        <div class="card-body">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Contraseña inicial</label>
                <input type="password" name="Password" class="form-control" required>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy me-1"></i>Guardar
        </button>
        <a href="index.php?controller=admin&action=docentes" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
