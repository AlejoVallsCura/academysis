<h4 class="mb-4 fw-bold">
    <i class="bi bi-bar-chart-steps me-2"></i>Mi Progreso Académico
    <small class="text-muted fs-6 fw-normal ms-2"><?= htmlspecialchars($alumno['CodCarrera'] ?? '') ?></small>
</h4>

<?php
$badges = ['Aprobado'=>'info','Regular'=>'primary','Activo'=>'success','Libre'=>'warning','Baja'=>'danger'];
$anioActual = null;
$totales = ['Aprobado'=>0,'Regular'=>0,'Activo'=>0,'Pendiente'=>0];
foreach ($materias as $m) {
    $estado = $m['Estado'] ?? 'Pendiente';
    $totales[$estado] = ($totales[$estado] ?? 0) + 1;
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="display-5 fw-bold text-info"><?= $totales['Aprobado'] ?></div>
            <div class="text-muted small">Aprobadas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="display-5 fw-bold text-primary"><?= $totales['Regular'] ?></div>
            <div class="text-muted small">Regulares</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="display-5 fw-bold text-success"><?= $totales['Activo'] ?></div>
            <div class="text-muted small">Cursando</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="display-5 fw-bold text-secondary"><?= $totales['Pendiente'] ?></div>
            <div class="text-muted small">Pendientes</div>
        </div>
    </div>
</div>

<?php foreach ($materias as $m): ?>
    <?php if ($m['Anio'] !== $anioActual): $anioActual = $m['Anio']; ?>
        <h6 class="fw-bold text-uppercase text-muted mt-4 mb-2" style="font-size:.75rem;letter-spacing:1px;">
            <i class="bi bi-calendar3 me-1"></i><?= (int)$m['Anio'] ?>° Año
        </h6>
    <?php endif; ?>
    <?php
    $estado = $m['Estado'] ?? 'Pendiente';
    $b = $badges[$estado] ?? 'secondary';
    $bloqueada = ($estado === 'Pendiente' && !empty($m['bloqueada']));
    $icon = match(true) {
        $estado === 'Aprobado'  => 'bi-check-circle-fill',
        $estado === 'Regular'   => 'bi-clock-history',
        $estado === 'Activo'    => 'bi-play-circle',
        $estado === 'Libre'     => 'bi-x-circle',
        $bloqueada              => 'bi-lock-fill',
        default                 => 'bi-circle',
    };
    $iconColor = $bloqueada ? 'danger' : $b;
    ?>
    <div class="d-flex align-items-center gap-3 py-2 border-bottom">
        <i class="bi <?= $icon ?> text-<?= $iconColor ?> fs-5" style="width:22px"
           <?= $bloqueada ? 'title="Correlativas pendientes" data-bs-toggle="tooltip"' : '' ?>></i>
        <div class="flex-grow-1">
            <span class="fw-semibold"><?= htmlspecialchars($m['NomMateria']) ?></span>
            <small class="text-muted ms-2">Año <?= (int)$m['Anio'] ?></small>
            <?php if ($bloqueada): ?>
                <small class="text-danger ms-2"><i class="bi bi-lock me-1"></i>Correlativas pendientes</small>
            <?php endif; ?>
        </div>
        <span class="badge bg-<?= $b ?>"><?= $estado ?></span>
    </div>
<?php endforeach; ?>

<div class="mt-4">
    <a href="index.php?controller=alumno&action=inscribirCurso" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Inscribirme a un curso
    </a>
</div>
