<h4 class="mb-4 fw-bold">
    <i class="bi bi-speedometer2 me-2"></i>Dashboard
</h4>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card stat-green h-100">
            <div class="card-body text-center py-4">
                <div class="stat-num"><?= (int)$resumen['total_cursos'] ?></div>
                <div class="text-muted mt-1 small">Cursos asignados</div>
                <i class="bi bi-journal-text stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-teal h-100">
            <div class="card-body text-center py-4">
                <div class="stat-num"><?= (int)$resumen['total_alumnos'] ?></div>
                <div class="text-muted mt-1 small">Alumnos a cargo</div>
                <i class="bi bi-people stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Datos del docente</h6>
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Nombre</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($docente['Nombre'] . ' ' . $docente['Apellido']) ?></dd>
            <dt class="col-sm-3 text-muted">Legajo</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($docente['Legajo']) ?></dd>
            <dt class="col-sm-3 text-muted">Título</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($docente['Titulo'] ?? '—') ?></dd>
            <dt class="col-sm-3 text-muted">Especialidad</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($docente['Especialidad'] ?? '—') ?></dd>
            <dt class="col-sm-3 text-muted">Email</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($docente['Email']) ?></dd>
        </dl>
    </div>
</div>
