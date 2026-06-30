<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>Inscribirme a un Curso</h4>
    <a href="index.php?controller=alumno&action=miProgreso" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Nota: se muestran materias hasta el año académico del alumno (incluye recursar materias previas) -->
<div class="alert alert-light border small mb-3">
    <i class="bi bi-info-circle me-1"></i>
    Estás cursando <strong><?= (int)$anioMax ?>° año</strong>. Podés inscribirte a materias de tu año o de
    años anteriores que tengas pendientes. Las materias de años superiores se habilitan a medida que avanzás.
</div>

<?php if (empty($cursos)): ?>
    <div class="alert alert-info">No hay cursos disponibles para inscribirse en este momento.</div>
<?php else: ?>

<!-- Leyenda de estados -->
<div class="d-flex gap-3 mb-3 small text-muted">
    <span><i class="bi bi-check-circle-fill text-success me-1"></i>Disponible</span>
    <span><span class="badge bg-warning text-dark me-1" style="font-size:.6rem">Recursar</span>Ya la tenés Libre</span>
    <span><i class="bi bi-lock-fill text-danger me-1"></i>Correlativas pendientes</span>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Materia</th>
                    <th>Año (plan)</th>
                    <th>Horarios</th>
                    <th>Docente</th>
                    <th>Aula</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cursos as $c):
                /* Si tiene correlativas sin aprobar, la fila se muestra bloqueada */
                $bloqueada = (int)$c['CorrelativasPendientes'] > 0;
            ?>
                <tr class="<?= $bloqueada ? 'table-warning' : '' ?>">
                    <td class="fw-semibold">
                        <?= htmlspecialchars($c['NomMateria']) ?>
                        <?php if (!empty($c['EsRecursada'])): ?>
                            <!-- La tiene en estado Libre: se está reinscribiendo para recursarla -->
                            <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Recursar</span>
                        <?php endif; ?>
                        <?php if ($bloqueada): ?>
                            <!-- El title nativo del navegador muestra el motivo al pasar el mouse (sin JS) -->
                            <i class="bi bi-lock-fill text-danger ms-1"
                               title="Tenés correlativas pendientes para esta materia"></i>
                        <?php endif; ?>
                    </td>
                    <!-- Año de la materia en el plan de la carrera (no el año lectivo) -->
                    <td><span class="badge bg-secondary"><?= (int)$c['AnioMateria'] ?>°</span></td>
                    <td><small><?= htmlspecialchars($c['Horarios'] ?? '—') ?></small></td>
                    <td><?= htmlspecialchars($c['DocApellido'] . ', ' . $c['DocNombre']) ?></td>
                    <td><?= htmlspecialchars($c['Aula']) ?> <small class="text-muted">(<?= htmlspecialchars($c['Edificio']) ?>)</small></td>
                    <td class="text-end">
                        <?php if ($bloqueada): ?>
                            <!-- Botón deshabilitado visualmente; el servidor también rechaza la inscripción -->
                            <button type="button" class="btn btn-secondary btn-sm" disabled
                                    title="Aprobá las correlativas antes de inscribirte">
                                <i class="bi bi-lock me-1"></i>Bloqueado
                            </button>
                        <?php else: ?>
                            <form method="post" action="index.php?controller=alumno&action=inscribirCurso">
                                <input type="hidden" name="IDCurso" value="<?= (int)$c['IDCurso'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Inscribirme
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
