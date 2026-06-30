<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-book me-2"></i><?= $isNew ? 'Nueva Materia' : 'Editar Materia' ?>
    </h4>
    <a href="index.php?controller=admin&action=materias" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:540px;">
    <div class="card-body">
        <form method="post" action="index.php?controller=admin&action=materia">
            <?php if ($isNew): ?>
                <input type="hidden" name="_isNew" value="1">
            <?php else: ?>
                <input type="hidden" name="CodMateria" value="<?= htmlspecialchars($materia['CodMateria']) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Código</label>
                <input type="text" name="CodMateria" class="form-control"
                       value="<?= htmlspecialchars($materia['CodMateria'] ?? '') ?>"
                       <?= !$isNew ? 'disabled' : 'required' ?> maxlength="10">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre</label>
                <input type="text" name="NomMateria" class="form-control" required
                       value="<?= htmlspecialchars($materia['NomMateria'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Carrera <span class="text-danger">*</span></label>
                <select name="CodCarrera" id="codCarrera" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($carreras as $c): ?>
                        <option value="<?= htmlspecialchars($c['CodCarrera']) ?>"
                                data-anios="<?= (int)$c['DurAnios'] ?>"
                                <?= ($materia['CodCarrera'] ?? '') === $c['CodCarrera'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['NomCarrera']) ?> (<?= (int)$c['DurAnios'] ?> años)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Año de cursada <span class="text-danger">*</span></label>
                <select name="Anio" id="anioSelect" class="form-select" required>
                    <option value="">-- Primero seleccioná una carrera --</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Contenidos mínimos</label>
                <!-- Campo opcional: descripción resumida de los temas de la materia -->
                <textarea name="ContMinimos" class="form-control" rows="3"
                          placeholder="Ej: Modelo relacional, SQL, normalización..."><?= htmlspecialchars($materia['ContMinimos'] ?? '') ?></textarea>
                <div class="form-text">Opcional. Descripción breve de los temas que cubre la materia.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Guardar
                </button>
                <a href="index.php?controller=admin&action=materias" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const carreraSelect = document.getElementById('codCarrera');
    const anioSelect    = document.getElementById('anioSelect');
    const savedAnio     = <?= (int)($materia['Anio'] ?? 0) ?>;

    function updateAnios() {
        const opt    = carreraSelect.selectedOptions[0];
        const maxAnios = opt ? parseInt(opt.dataset.anios || 0) : 0;
        anioSelect.innerHTML = '';
        if (!maxAnios) {
            anioSelect.innerHTML = '<option value="">-- Primero seleccioná una carrera --</option>';
            return;
        }
        for (let i = 1; i <= maxAnios; i++) {
            const o = document.createElement('option');
            o.value = i;
            o.textContent = i + '° año';
            if (i === savedAnio) o.selected = true;
            anioSelect.appendChild(o);
        }
    }

    carreraSelect.addEventListener('change', updateAnios);
    updateAnios();
})();
</script>
