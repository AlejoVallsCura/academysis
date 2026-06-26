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
                    <th>Año</th>
                    <th>Horarios</th>
                    <th>Aula</th>
                    <th>Docente</th>
                    <th>Estado</th>
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
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
