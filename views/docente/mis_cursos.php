<h4 class="mb-4 fw-bold">
    <i class="bi bi-journal-text me-2"></i>Mis Cursos
</h4>

<?php if (empty($cursos)): ?>
    <div class="alert alert-info">No tenés cursos asignados.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Materia</th>
                    <th>Año</th>
                    <th>Horarios</th>
                    <th>Aula</th>
                    <th class="text-center">Alumnos</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cursos as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['NomMateria']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($c['CodMateria']) ?></small></td>
                    <td><?= htmlspecialchars($c['AnioLectivo']) ?></td>
                    <td><small><?= htmlspecialchars($c['Horarios'] ?? '—') ?></small></td>
                    <td><?= htmlspecialchars($c['Aula']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($c['Edificio'] ?? '') ?></small></td>
                    <td class="text-center">
                        <span class="badge bg-secondary"><?= (int)$c['CantAlumnos'] ?></span>
                    </td>
                    <td class="text-center">
                        <a href="index.php?controller=docente&action=alumnosCurso&idCurso=<?= (int)$c['IDCurso'] ?>"
                           class="btn btn-sm btn-outline-primary me-1" title="Ver alumnos">
                            <i class="bi bi-people"></i>
                        </a>
                        <a href="index.php?controller=docente&action=cargarNota&idCurso=<?= (int)$c['IDCurso'] ?>"
                           class="btn btn-sm btn-outline-success me-1" title="Cargar nota">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href="index.php?controller=docente&action=cargarAsistencia&idCurso=<?= (int)$c['IDCurso'] ?>"
                           class="btn btn-sm btn-outline-warning" title="Cargar asistencia">
                            <i class="bi bi-person-check"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
