<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-mortarboard me-2"></i><?= $isNew ? 'Nueva Carrera' : 'Editar Carrera' ?>
    </h4>
    <a href="index.php?controller=admin&action=carreras" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:480px;">
    <div class="card-body">
        <form method="post" action="index.php?controller=admin&action=carrera">
            <?php if ($isNew): ?>
                <input type="hidden" name="_isNew" value="1">
            <?php else: ?>
                <input type="hidden" name="CodCarrera" value="<?= htmlspecialchars($carrera['CodCarrera']) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Código</label>
                <input type="text" name="CodCarrera" class="form-control text-uppercase"
                       value="<?= htmlspecialchars($carrera['CodCarrera'] ?? '') ?>"
                       <?= !$isNew ? 'disabled' : 'required' ?> maxlength="10"
                       placeholder="Ej: ING, LIS, TUS">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre</label>
                <input type="text" name="NomCarrera" class="form-control" required
                       value="<?= htmlspecialchars($carrera['NomCarrera'] ?? '') ?>"
                       placeholder="Ej: Ingeniería en Informática">
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Duración (años)</label>
                <input type="number" name="DurAnios" class="form-control" required min="1" max="10"
                       value="<?= htmlspecialchars($carrera['DurAnios'] ?? '') ?>">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Guardar
                </button>
                <a href="index.php?controller=admin&action=carreras" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
