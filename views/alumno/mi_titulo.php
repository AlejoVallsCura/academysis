<h4 class="mb-4 fw-bold">
    <i class="bi bi-award me-2"></i>Mi Título
</h4>

<?php if ($titulo === null): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-hourglass-split display-2 text-muted"></i>
            <h5 class="mt-3 text-muted">Título en proceso</h5>
            <p class="text-muted">Aún no tenés un título registrado en el sistema.<br>
               Al completar todos los requisitos de tu carrera, aparecerá aquí.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm" style="max-width:600px">
        <div class="card-header text-white fw-bold fs-5 text-center" style="background:#1A237E;">
            <i class="bi bi-award-fill me-2"></i>Título Obtenido
        </div>
        <div class="card-body py-4">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Carrera</dt>
                <dd class="col-sm-8 fw-semibold"><?= htmlspecialchars($titulo['NomCarrera']) ?></dd>

                <dt class="col-sm-4 text-muted">Duración</dt>
                <dd class="col-sm-8"><?= (int)$titulo['DurAnios'] ?> años</dd>

                <dt class="col-sm-4 text-muted">Fecha de egreso</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($titulo['FechaEgreso']) ?></dd>

                <dt class="col-sm-4 text-muted">Promedio final</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-success fs-6"><?= number_format((float)$titulo['PromedioFinal'],2) ?></span>
                </dd>

                <?php if (!empty($titulo['LibroTitulo'])): ?>
                <dt class="col-sm-4 text-muted">Libro / Folio</dt>
                <dd class="col-sm-8">
                    <?= htmlspecialchars($titulo['LibroTitulo']) ?> — <?= htmlspecialchars($titulo['FolioTitulo'] ?? '') ?>
                </dd>
                <?php endif; ?>
            </dl>
        </div>
    </div>
<?php endif; ?>
