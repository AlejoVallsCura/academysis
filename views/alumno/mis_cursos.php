<h4 class="mb-4 fw-bold">
    <i class="bi bi-journal-text me-2"></i>Mis Cursos
</h4>

<?php if (empty($cursos)): ?>
    <div class="alert alert-info">No tenés cursos registrados.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Materia</th>
                    <th>Año lectivo</th>
                    <th>Horarios</th>
                    <th>Aula</th>
                    <th>Docente</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cursos as $c):
                /* Inscripción superada: intento viejo de una materia que después recursó/aprobó.
                 * Se atenúa la fila para que se entienda que es historial, no el estado vigente. */
                $superada = !empty($c['Superada']);
            ?>
                <tr class="<?= $superada ? 'opacity-50' : '' ?>">
                    <td>
                        <?= htmlspecialchars($c['NomMateria']) ?>
                        <!-- Año de la materia en el plan de la carrera -->
                        <span class="badge bg-secondary ms-1" title="Año en el plan de estudios"><?= (int)$c['AnioMateria'] ?>°</span>
                        <br><small class="text-muted"><?= htmlspecialchars($c['CodMateria']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($c['AnioLectivo']) ?></td>
                    <td><small><?= htmlspecialchars($c['Horarios'] ?? '—') ?></small></td>
                    <td><?= htmlspecialchars($c['Aula']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($c['Edificio'] ?? '') ?></small></td>
                    <td><?= htmlspecialchars($c['DocNombre'] . ' ' . $c['DocApellido']) ?></td>
                    <td>
                        <?php
                        $badges = [
                            'Activo'   => 'success',
                            'Regular'  => 'primary',
                            'Aprobado' => 'info',
                            'Libre'    => 'warning',
                            'Baja'     => 'danger',
                        ];
                        $b = $badges[$c['EstadoInscripcion']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $b ?>"><?= htmlspecialchars($c['EstadoInscripcion']) ?></span>
                        <?php if ($superada): ?>
                            <!-- Aclaración: este intento ya fue recursado en un año posterior -->
                            <br><small class="text-muted fst-italic"><i class="bi bi-arrow-repeat me-1"></i>recursada después</small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($c['EstadoInscripcion'] === 'Activo'): ?>
                            <!-- Solo se puede dar de baja si el curso está Activo -->
                            <form method="post" action="index.php?controller=alumno&action=darDeBaja"
                                  onsubmit="return confirm('¿Confirmás que querés darte de baja de este curso?')">
                                <input type="hidden" name="IDInscripcion" value="<?= (int)$c['IDInscripcion'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-circle me-1"></i>Baja
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Estado no modificable desde el alumno -->
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
