<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-people me-2"></i>
        Alumnos — <?= htmlspecialchars($curso['NomMateria'] ?? '') ?>
        <small class="text-muted fs-6 fw-normal ms-2">
            <?= htmlspecialchars($curso['AnioLectivo'] ?? '') ?>
        </small>
    </h4>
    <div>
        <a href="index.php?controller=docente&action=cargarNota&idCurso=<?= (int)$idCurso ?>"
           class="btn btn-success btn-sm me-2">
            <i class="bi bi-pencil-square me-1"></i>Cargar Nota
        </a>
        <a href="index.php?controller=docente&action=cargarAsistencia&idCurso=<?= (int)$idCurso ?>"
           class="btn btn-warning btn-sm">
            <i class="bi bi-person-check me-1"></i>Cargar Asistencia
        </a>
        <a href="index.php?controller=docente&action=cerrarCursada&idCurso=<?= (int)$idCurso ?>"
           class="btn btn-secondary btn-sm">
            <i class="bi bi-check2-square me-1"></i>Cerrar Cursada
        </a>
    </div>
</div>

<?php if (empty($alumnos)): ?>
    <div class="alert alert-info">No hay alumnos inscriptos en este curso.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Apellido y Nombre</th>
                    <th>DNI</th>
                    <th>Email</th>
                    <th>Fecha Inscripción</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($alumnos as $a): ?>
                <?php
                $badges = ['Activo'=>'success','Regular'=>'primary','Libre'=>'warning','Baja'=>'danger','Aprobado'=>'info'];
                $b = $badges[$a['Estado']] ?? 'secondary';
                ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($a['Apellido'] . ', ' . $a['Nombre']) ?></td>
                    <td><?= htmlspecialchars($a['DNI']) ?></td>
                    <td><a href="mailto:<?= htmlspecialchars($a['Email']) ?>"><?= htmlspecialchars($a['Email']) ?></a></td>
                    <td><?= htmlspecialchars($a['FechaInscripcion']) ?></td>
                    <td class="text-center">
                        <span class="badge bg-<?= $b ?>"><?= htmlspecialchars($a['Estado']) ?></span>
                        <a href="index.php?controller=docente&action=editarNota&idCurso=<?= (int)$idCurso ?>&dni=<?= (int)$a['DNI'] ?>"
                           class="btn btn-outline-secondary btn-sm ms-1" title="Editar notas">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php if ($a['Estado'] === 'Regular'): ?>
                        <form method="post" action="index.php?controller=docente&action=registrarFinal" class="d-inline ms-1">
                            <input type="hidden" name="IDInscripcion" value="<?= (int)$a['IDInscripcion'] ?>">
                            <input type="hidden" name="IDCurso" value="<?= (int)$idCurso ?>">
                            <button type="submit" class="btn btn-info btn-sm"
                                    onclick="return confirm('¿Registrar final aprobado para <?= htmlspecialchars($a['Nombre'] . ' ' . $a['Apellido']) ?>?')">
                                <i class="bi bi-award"></i> Final
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="mt-3">
    <a href="index.php?controller=docente&action=misCursos" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver a Mis Cursos
    </a>
</div>
