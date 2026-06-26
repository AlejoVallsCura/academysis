<h4 class="mb-4 fw-bold">
    <i class="bi bi-diagram-3 me-2"></i>Correlativas
</h4>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Materia</th>
                    <th>Requiere aprobar previamente</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($materias as $m): ?>
                <tr>
                    <td><code><?= htmlspecialchars($m['CodMateria']) ?></code></td>
                    <td class="fw-semibold"><?= htmlspecialchars($m['NomMateria']) ?></td>
                    <td>
                        <?php if (empty($m['correlativas'])): ?>
                            <span class="text-muted fst-italic">Sin correlativas</span>
                        <?php else: ?>
                            <?php foreach ($m['correlativas'] as $c): ?>
                                <span class="badge bg-info text-dark me-1">
                                    <?= htmlspecialchars($c['Nom']) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
