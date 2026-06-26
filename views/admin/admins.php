<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2"></i>Administradores</h4>
    <a href="index.php?controller=admin&action=nuevoAdmin" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Administrador
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Creado en</th>
                    <th>Último acceso</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($admins)): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">No hay administradores registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($admins as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['Nombre']) ?></td>
                    <td><?= htmlspecialchars($a['Email']) ?></td>
                    <td>
                        <?php if ($a['Estado']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($a['CreadoEn']) ?></td>
                    <td><?= $a['UltimoAcceso'] ? htmlspecialchars($a['UltimoAcceso']) : '<span class="text-muted">—</span>' ?></td>
                    <td class="text-center">
                        <form method="post" action="index.php?controller=admin&action=toggleAdmin" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$a['IDUsuario'] ?>">
                            <button type="submit" class="btn btn-sm <?= $a['Estado'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>">
                                <?= $a['Estado'] ? 'Desactivar' : 'Activar' ?>
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
