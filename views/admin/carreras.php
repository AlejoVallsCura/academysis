<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-mortarboard me-2"></i>Carreras</h4>
    <a href="index.php?controller=admin&action=carrera" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nueva Carrera
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th class="text-center">Duración</th>
                    <th class="text-center">Materias</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($carreras)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">No hay carreras registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($carreras as $c): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($c['CodCarrera']) ?></span></td>
                    <td><?= htmlspecialchars($c['NomCarrera']) ?></td>
                    <td class="text-center"><?= (int)$c['DurAnios'] ?> años</td>
                    <td class="text-center"><?= (int)$c['totalMaterias'] ?></td>
                    <td class="text-center">
                        <a href="index.php?controller=admin&action=carrera&cod=<?= urlencode($c['CodCarrera']) ?>"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
