<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-check2-square me-2"></i>Cerrar Cursada —
        <?= htmlspecialchars($curso['NomMateria'] ?? '') ?>
        <small class="text-muted fs-6 fw-normal ms-2">
            <?= htmlspecialchars($curso['AnioLectivo'] ?? '') ?>
        </small>
    </h4>
    <a href="index.php?controller=docente&action=alumnosCurso&idCurso=<?= (int)$idCurso ?>"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (empty($alumnos)): ?>
    <div class="alert alert-info">No hay alumnos activos en este curso.</div>
<?php else: ?>

<form method="post" action="index.php?controller=docente&action=cerrarCursada">
    <input type="hidden" name="IDCurso" value="<?= (int)$idCurso ?>">

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-bordered mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Apellido y Nombre</th>
                        <th class="text-center">Asistencia</th>
                        <th class="text-center">Prom. Parciales</th>
                        <th class="text-center">Nota Final</th>
                        <th class="text-center">Estado actual</th>
                        <th class="text-center">Nuevo estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($alumnos as $a):
                    $pct      = $a['pct_asistencia'];
                    $prom     = $a['prom_parciales'];
                    $final    = $a['nota_final'];
                    $sugerido = $a['estado_sugerido'];
                    $colorPct   = $pct   === null ? 'secondary' : ($pct   >= 75 ? 'success' : 'danger');
                    $colorProm  = $prom  === null ? 'secondary' : ($prom  >= 6  ? 'success' : 'danger');
                    $colorFinal = $final === null ? 'secondary' : ($final >= 4  ? 'success' : 'danger');
                    $badges = ['Activo'=>'success','Regular'=>'primary','Libre'=>'warning','Baja'=>'danger','Aprobado'=>'info'];
                    $b = $badges[$a['Estado']] ?? 'secondary';
                    $bs = $badges[$sugerido] ?? 'secondary';
                ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($a['Apellido'] . ', ' . $a['Nombre']) ?></td>

                        <td class="text-center">
                            <?php if ($pct === null): ?>
                                <span class="text-muted">—</span>
                            <?php else: ?>
                                <span class="badge bg-<?= $colorPct ?>"><?= $pct ?>%</span>
                                <?php if ($pct < 75): ?><small class="d-block text-danger" style="font-size:.7rem">bajo 75%</small><?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <?php if ($prom === null): ?>
                                <span class="text-muted">—</span>
                            <?php else: ?>
                                <span class="badge bg-<?= $colorProm ?>"><?= $prom ?></span>
                                <?php if ($prom < 6): ?><small class="d-block text-danger" style="font-size:.7rem">bajo 6</small><?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <?php if ($final === null): ?>
                                <span class="text-muted">—</span>
                            <?php else: ?>
                                <span class="badge bg-<?= $colorFinal ?>"><?= $final ?></span>
                                <?php if ($final < 4): ?><small class="d-block text-danger" style="font-size:.7rem">desaprobado</small><?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-<?= $b ?>"><?= htmlspecialchars($a['Estado']) ?></span>
                        </td>

                        <td class="text-center">
                            <?php if ($a['Estado'] === 'Activo'): ?>
                                <span class="badge bg-<?= $bs ?> me-1" title="Sugerido por el sistema">
                                    <i class="bi bi-robot"></i> <?= $sugerido ?>
                                </span>
                            <?php endif; ?>
                            <select name="estado[<?= (int)$a['IDInscripcion'] ?>]"
                                    class="form-select form-select-sm w-auto mx-auto mt-1">
                                <option value="Activo"   <?= $sugerido === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                                <option value="Regular"  <?= $sugerido === 'Regular'  ? 'selected' : '' ?>>Regular</option>
                                <option value="Libre"    <?= $sugerido === 'Libre'    ? 'selected' : '' ?>>Libre</option>
                                <option value="Aprobado" <?= $sugerido === 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                                <option value="Baja"     <?= $sugerido === 'Baja'     ? 'selected' : '' ?>>Baja</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Guardar estados
        </button>
        <a href="index.php?controller=docente&action=alumnosCurso&idCurso=<?= (int)$idCurso ?>"
           class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<?php endif; ?>
