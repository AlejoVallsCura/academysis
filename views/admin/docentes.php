<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-workspace me-2"></i>Docentes</h4>
    <a href="index.php?controller=admin&action=docente" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Docente
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Legajo</th>
                    <th>Apellido y Nombre</th>
                    <th>Email</th>
                    <th>Especialidad</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($docentes)): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">No hay docentes registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($docentes as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['Legajo']) ?></td>
                    <td><?= htmlspecialchars($d['Apellido'] . ', ' . $d['Nombre']) ?></td>
                    <td><?= htmlspecialchars($d['Email']) ?></td>
                    <td><?= htmlspecialchars($d['Especialidad']) ?></td>
                    <td>
                        <?php if ($d['Activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="index.php?controller=admin&action=docente&legajo=<?= urlencode($d['Legajo']) ?>"
                           class="btn btn-outline-primary btn-sm me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="post" action="index.php?controller=admin&action=toggleDocente" class="d-inline">
                            <input type="hidden" name="legajo" value="<?= htmlspecialchars($d['Legajo']) ?>">
                            <button type="submit" class="btn btn-sm <?= $d['Activo'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>">
                                <i class="bi <?= $d['Activo'] ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
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
