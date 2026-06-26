<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>Inscribirme a un Curso</h4>
    <a href="index.php?controller=alumno&action=miProgreso" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($cursos)): ?>
    <div class="alert alert-info">No hay cursos disponibles para inscribirse en este momento.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Materia</th>
                    <th>Año</th>
                    <th>Horarios</th>
                    <th>Docente</th>
                    <th>Aula</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cursos as $c): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($c['NomMateria']) ?></td>
                    <td><?= (int)$c['AnioLectivo'] ?></td>
                    <td><small><?= htmlspecialchars($c['Horarios'] ?? '—') ?></small></td>
                    <td><?= htmlspecialchars($c['DocApellido'] . ', ' . $c['DocNombre']) ?></td>
                    <td><?= htmlspecialchars($c['Aula']) ?> (<?= htmlspecialchars($c['Edificio']) ?>)</td>
                    <td class="text-end">
                        <form method="post" action="index.php?controller=alumno&action=inscribirCurso">
                            <input type="hidden" name="IDCurso" value="<?= (int)$c['IDCurso'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>Inscribirme
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
