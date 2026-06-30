<?php
/* Por defecto se muestra el año lectivo actual; '' = todos */
$fAnio = $_GET['f_anio'] ?? (string)date('Y');

/* Junta los años lectivos en los que el docente tiene cursos, para el select */
$anios = [];
foreach ($cursos as $c) { $anios[(int)$c['AnioLectivo']] = true; }
krsort($anios); // más recientes primero

/* Aplica el filtro por año (si no es "Todos") */
$filtrados = array_filter($cursos, function ($c) use ($fAnio) {
    return $fAnio === '' || (string)$c['AnioLectivo'] === $fAnio;
});
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Mis Cursos</h4>
    <form method="get" action="index.php" class="d-flex align-items-center gap-2">
        <input type="hidden" name="controller" value="docente">
        <input type="hidden" name="action" value="misCursos">
        <label class="text-muted small mb-0">Año lectivo:</label>
        <select name="f_anio" class="form-select form-select-sm" style="width:auto">
            <option value="" <?= $fAnio === '' ? 'selected' : '' ?>>Todos</option>
            <?php foreach (array_keys($anios) as $a): ?>
            <option value="<?= $a ?>" <?= $fAnio === (string)$a ? 'selected' : '' ?>><?= $a ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
    </form>
</div>

<?php if (empty($cursos)): ?>
    <div class="alert alert-info">No tenés cursos asignados.</div>
<?php elseif (empty($filtrados)): ?>
    <div class="alert alert-info">No tenés cursos en el año lectivo seleccionado.</div>
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
            <?php foreach ($filtrados as $c): ?>
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
