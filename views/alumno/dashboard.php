<h4 class="mb-4 fw-bold">
    <i class="bi bi-speedometer2 me-2"></i>Dashboard
</h4>

<?php if (!($alumno['Activo'] ?? 1)): ?>
<div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <span>Tu cuenta está <strong>desactivada</strong>. Contactate con administración para más información.</span>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-amber h-100">
            <div class="card-body text-center py-4">
                <div class="stat-num"><?= (int)$resumen['cursos_activos'] ?></div>
                <div class="text-muted mt-1 small">Cursos activos</div>
                <i class="bi bi-journal-text stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-green h-100">
            <div class="card-body text-center py-4">
                <div class="stat-num"><?= number_format((float)$resumen['promedio_general'], 2) ?></div>
                <div class="text-muted mt-1 small">Promedio general</div>
                <i class="bi bi-clipboard-data stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-teal h-100">
            <div class="card-body text-center py-4">
                <div class="stat-num"><?= number_format((float)$resumen['pct_asistencia'], 1) ?>%</div>
                <div class="text-muted mt-1 small">Asistencia promedio</div>
                <i class="bi bi-calendar-check stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Datos personales</h6>
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Nombre</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($alumno['Nombre'] . ' ' . $alumno['Apellido']) ?></dd>
            <dt class="col-sm-3 text-muted">DNI</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($alumno['DNI']) ?></dd>
            <dt class="col-sm-3 text-muted">Email</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($alumno['Email']) ?></dd>
            <dt class="col-sm-3 text-muted">Ingreso</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($alumno['FechaIngreso']) ?></dd>
            <dt class="col-sm-3 text-muted">Carrera</dt>
            <dd class="col-sm-9">
                <?php if (!empty($alumno['NomCarrera'])): ?>
                    <span class="badge bg-primary"><?= htmlspecialchars($alumno['CodCarrera']) ?></span>
                    <?= htmlspecialchars($alumno['NomCarrera']) ?>
                <?php else: ?>
                    <span class="text-muted">Sin asignar — contactá a administración</span>
                <?php endif; ?>
            </dd>
        </dl>
    </div>
</div>
