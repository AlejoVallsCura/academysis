<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2"></i>Correlativas</h4>

    <!-- Selector de carrera: las correlativas se gestionan dentro de una carrera -->
    <form method="get" action="index.php" class="d-flex align-items-center gap-2">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="correlativas">
        <label class="text-muted small mb-0">Carrera:</label>
        <select name="carrera" class="form-select form-select-sm" style="width:auto">
            <?php foreach ($carreras as $c): ?>
            <option value="<?= htmlspecialchars($c['CodCarrera']) ?>" <?= $carreraSel === $c['CodCarrera'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['NomCarrera']) ?> (<?= htmlspecialchars($c['CodCarrera']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Ver</button>
    </form>
</div>

<!-- Formulario para agregar una correlativa nueva (solo materias de la carrera elegida) -->
<div class="card border-0 shadow-sm mb-4" style="max-width:640px">
    <div class="card-header fw-semibold bg-light">Agregar correlativa</div>
    <div class="card-body">
        <?php if (empty($materias)): ?>
            <p class="text-muted small mb-0">Esta carrera no tiene materias activas para relacionar.</p>
        <?php else: ?>
        <form method="post" action="index.php?controller=admin&action=agregarCorrelativa">
            <!-- Conserva la carrera para volver a la misma vista tras guardar -->
            <input type="hidden" name="carrera" value="<?= htmlspecialchars($carreraSel) ?>">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Materia</label>
                    <!-- La materia que requiere la correlativa -->
                    <select name="CodMateria" class="form-select form-select-sm" required>
                        <option value="">-- Seleccioná --</option>
                        <?php foreach ($materias as $m): ?>
                            <option value="<?= htmlspecialchars($m['CodMateria']) ?>">
                                <?= (int)$m['Anio'] ?>° · <?= htmlspecialchars($m['NomMateria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1 text-center pt-3">
                    <i class="bi bi-arrow-right text-muted"></i>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Requiere tener aprobada</label>
                    <select name="CodCorrelativa" class="form-select form-select-sm" required>
                        <option value="">-- Seleccioná --</option>
                        <?php foreach ($materias as $m): ?>
                            <option value="<?= htmlspecialchars($m['CodMateria']) ?>">
                                <?= (int)$m['Anio'] ?>° · <?= htmlspecialchars($m['NomMateria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- Tabla con las correlativas de la carrera seleccionada -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="text-center">Año</th>
                    <th>Materia</th>
                    <th>Requiere aprobar</th>
                    <th class="text-center">Eliminar</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($correlativas)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">Esta carrera no tiene correlativas cargadas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($correlativas as $co): ?>
                <tr>
                    <td class="text-center"><span class="badge bg-secondary"><?= (int)$co['Anio'] ?>°</span></td>
                    <td class="fw-semibold"><?= htmlspecialchars($co['NomMateria']) ?></td>
                    <td><?= htmlspecialchars($co['NomCorrelativa']) ?></td>
                    <td class="text-center">
                        <form method="post" action="index.php?controller=admin&action=eliminarCorrelativa"
                              onsubmit="return confirm('¿Eliminás esta correlativa?')">
                            <input type="hidden" name="carrera"        value="<?= htmlspecialchars($carreraSel) ?>">
                            <input type="hidden" name="CodMateria"     value="<?= htmlspecialchars($co['CodMateria']) ?>">
                            <input type="hidden" name="CodCorrelativa" value="<?= htmlspecialchars($co['CodCorrelativa']) ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i>
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
