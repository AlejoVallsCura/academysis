<?php
/* Colores por estado de inscripción (barra/dona y leyenda), de la paleta del sistema */
$colEstado = [
    'Aprobado' => '#00796B',  // teal
    'Activo'   => '#4A7C4E',  // green
    'Regular'  => '#3949AB',  // indigo
    'Libre'    => '#EF8C00',  // amber
    'Baja'     => '#BF360C',  // rose
];

/* Total de inscripciones para los porcentajes */
$totalInsc = array_sum($resumen['por_estado'] ?? []);

/* Arma los tramos de la dona (conic-gradient) acumulando grados.
 * Cada estado ocupa un arco proporcional a su porcentaje. */
$gradStops = [];
$acum = 0;
foreach (($resumen['por_estado'] ?? []) as $estado => $cant) {
    if ($totalInsc === 0) break;
    $col   = $colEstado[$estado] ?? '#849174';
    $desde = $acum / $totalInsc * 360;
    $acum += $cant;
    $hasta = $acum / $totalInsc * 360;
    $gradStops[] = "{$col} {$desde}deg {$hasta}deg";
}
$conic = $gradStops ? 'conic-gradient(' . implode(',', $gradStops) . ')' : '#eee';

/* Máximo de alumnos en una carrera para escalar las barras horizontales */
$maxCarrera = 1;
foreach ($resumen['por_carrera'] as $pc) $maxCarrera = max($maxCarrera, (int)$pc['Total']);

/* Indicadores: valor, etiqueta, ícono y color de acento */
$indicadores = [
    ['val' => number_format((float)$resumen['promedio_general'], 2), 'dec' => 2, 'lbl' => 'Promedio general', 'ico' => 'bi-clipboard-data',  'col' => '#3949AB'],
    ['val' => number_format((float)$resumen['asistencia_global'], 1), 'dec' => 1, 'suf' => '%', 'lbl' => 'Asistencia global', 'ico' => 'bi-calendar-check', 'col' => '#00796B'],
    ['val' => (int)$resumen['inscripciones_total'], 'dec' => 0, 'lbl' => 'Inscripciones',     'ico' => 'bi-pencil-square', 'col' => '#4A7C4E'],
    ['val' => (int)$resumen['titulos'],             'dec' => 0, 'lbl' => 'Títulos otorgados',  'ico' => 'bi-award',         'col' => '#EF8C00'],
    ['val' => (int)$resumen['cursos_inactivos'],    'dec' => 0, 'lbl' => 'Cursos cerrados',    'ico' => 'bi-archive',       'col' => '#BF360C'],
];
?>

<!-- Estilos del dashboard embebidos en la vista: así no dependen del CSS cacheado -->
<style>
#dash .dash-title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#849174; margin:1.5rem 0 .85rem; }

/* Tarjetas de indicadores */
#dash .ind-card { background:#fff; border:1px solid rgba(39,46,34,.08); border-radius:14px; padding:1.05rem 1.15rem;
                  display:flex; align-items:center; gap:.85rem; box-shadow:0 2px 8px rgba(39,46,34,.06);
                  transition:transform .2s ease, box-shadow .2s ease; height:100%; }
#dash .ind-card:hover { transform:translateY(-3px); box-shadow:0 8px 22px rgba(39,46,34,.13); }
#dash .ind-chip { width:42px; height:42px; flex-shrink:0; border-radius:11px; display:flex; align-items:center;
                  justify-content:center; font-size:1.25rem; color:#fff; }
#dash .ind-val  { font-family:'Nunito',sans-serif; font-size:1.7rem; font-weight:800; line-height:1; color:#272E22; }
#dash .ind-lbl  { font-size:.7rem; text-transform:uppercase; letter-spacing:.5px; color:#849174; margin-top:.3rem; }

/* Dona (conic-gradient) */
#dash .donut-wrap { display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap; }
#dash .donut { width:150px; height:150px; border-radius:50%; flex-shrink:0; position:relative;
               /* La dona se "rellena" girando desde 0 con una máscara animada */
               transition:background 1s ease; }
#dash .donut::after { content:''; position:absolute; inset:24px; background:#fff; border-radius:50%;
                      box-shadow:inset 0 1px 4px rgba(39,46,34,.12); }
#dash .donut-center { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center;
                      justify-content:center; z-index:2; }
#dash .donut-total { font-family:'Nunito',sans-serif; font-size:1.6rem; font-weight:800; color:#272E22; line-height:1; }
#dash .donut-sub   { font-size:.62rem; text-transform:uppercase; letter-spacing:.6px; color:#849174; }
#dash .donut-legend { flex:1; min-width:160px; }
#dash .leg-row { display:flex; align-items:center; gap:.55rem; padding:.28rem 0; font-size:.85rem; }
#dash .leg-dot { width:12px; height:12px; border-radius:3px; flex-shrink:0; }
#dash .leg-name { flex:1; color:#4A5240; }
#dash .leg-cant { font-weight:700; color:#272E22; }
#dash .leg-pct  { color:#849174; font-size:.78rem; width:44px; text-align:right; }

/* Barras horizontales de alumnos por carrera */
#dash .hbar-row { display:flex; align-items:center; gap:.7rem; margin-bottom:.62rem; }
#dash .hbar-lbl { width:46px; font-size:.82rem; font-weight:700; color:#4A5240; flex-shrink:0; }
#dash .hbar-track { flex:1; background:#ECF0E8; border-radius:6px; height:20px; overflow:hidden; }
#dash .hbar-fill { height:100%; width:0; border-radius:6px;
                   background:linear-gradient(90deg,#849174,#65715A);
                   transition:width 1.1s cubic-bezier(.22,1,.36,1); }
#dash .hbar-val { width:26px; text-align:right; font-size:.82rem; font-weight:700; color:#4A5240; }
</style>

<div id="dash">

<h4 class="mb-4 fw-bold"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>

<!-- ============ FILA 1: TOTALES PRINCIPALES ============ -->
<div class="row g-3">
    <div class="col-6 col-md">
        <a href="index.php?controller=admin&action=carreras" class="text-decoration-none">
            <div class="card stat-indigo h-100"><div class="card-body text-center py-4">
                <div class="stat-num dash-count" data-to="<?= (int)$resumen['carreras'] ?>">0</div>
                <div class="text-muted mt-1 small">Carreras</div>
                <i class="bi bi-mortarboard stat-icon"></i>
            </div></div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="index.php?controller=admin&action=materias" class="text-decoration-none">
            <div class="card stat-teal h-100"><div class="card-body text-center py-4">
                <div class="stat-num dash-count" data-to="<?= (int)$resumen['materias'] ?>">0</div>
                <div class="text-muted mt-1 small">Materias</div>
                <i class="bi bi-book stat-icon"></i>
            </div></div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="index.php?controller=admin&action=cursos" class="text-decoration-none">
            <div class="card stat-green h-100"><div class="card-body text-center py-4">
                <div class="stat-num dash-count" data-to="<?= (int)$resumen['cursos'] ?>">0</div>
                <div class="text-muted mt-1 small">Cursos activos</div>
                <i class="bi bi-calendar3 stat-icon"></i>
            </div></div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="index.php?controller=admin&action=alumnos" class="text-decoration-none">
            <div class="card stat-amber h-100"><div class="card-body text-center py-4">
                <div class="stat-num dash-count" data-to="<?= (int)$resumen['alumnos'] ?>">0</div>
                <div class="text-muted mt-1 small">Alumnos</div>
                <i class="bi bi-person-badge stat-icon"></i>
            </div></div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="index.php?controller=admin&action=docentes" class="text-decoration-none">
            <div class="card stat-rose h-100"><div class="card-body text-center py-4">
                <div class="stat-num dash-count" data-to="<?= (int)$resumen['docentes'] ?>">0</div>
                <div class="text-muted mt-1 small">Docentes</div>
                <i class="bi bi-person-workspace stat-icon"></i>
            </div></div>
        </a>
    </div>
</div>

<!-- ============ FILA 2: INDICADORES ACADÉMICOS ============ -->
<div class="dash-title"><i class="bi bi-graph-up me-1"></i>Indicadores</div>
<div class="row g-3">
    <?php foreach ($indicadores as $ind): ?>
    <div class="col-6 col-md-4 col-lg">
        <div class="ind-card">
            <div class="ind-chip" style="background: <?= $ind['col'] ?>;"><i class="bi <?= $ind['ico'] ?>"></i></div>
            <div>
                <div class="ind-val dash-count" data-to="<?= (float)str_replace(',', '.', (string)$ind['val']) ?>"
                     data-dec="<?= $ind['dec'] ?>" data-suf="<?= $ind['suf'] ?? '' ?>">0</div>
                <div class="ind-lbl"><?= htmlspecialchars($ind['lbl']) ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ============ FILA 3: GRÁFICOS ============ -->
<div class="row g-4 mt-1">

    <!-- Distribución de inscripciones por estado (dona) -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart me-1"></i>Inscripciones por estado</div>
            <div class="card-body">
                <?php if ($totalInsc === 0): ?>
                    <p class="text-muted small mb-0">Sin inscripciones registradas.</p>
                <?php else: ?>
                <div class="donut-wrap">
                    <div class="donut" style="background: <?= $conic ?>;">
                        <div class="donut-center">
                            <span class="donut-total dash-count" data-to="<?= $totalInsc ?>">0</span>
                            <span class="donut-sub">inscrip.</span>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <?php foreach ($resumen['por_estado'] as $estado => $cant):
                            $col = $colEstado[$estado] ?? '#849174';
                            $pct = round($cant / $totalInsc * 100, 1); ?>
                        <div class="leg-row">
                            <span class="leg-dot" style="background: <?= $col ?>;"></span>
                            <span class="leg-name"><?= htmlspecialchars($estado) ?></span>
                            <span class="leg-cant"><?= (int)$cant ?></span>
                            <span class="leg-pct"><?= $pct ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alumnos por carrera (barras) -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-people me-1"></i>Alumnos por carrera</div>
            <div class="card-body">
                <?php if (empty($resumen['por_carrera'])): ?>
                    <p class="text-muted small mb-0">Sin datos de alumnos.</p>
                <?php else: ?>
                    <?php foreach ($resumen['por_carrera'] as $pc):
                        $total = (int)$pc['Total'];
                        $pct   = round($total / $maxCarrera * 100, 1); ?>
                        <div class="hbar-row">
                            <div class="hbar-lbl" title="<?= htmlspecialchars($pc['NomCarrera']) ?>"><?= htmlspecialchars($pc['CodCarrera']) ?></div>
                            <div class="hbar-track">
                                <!-- data-w guarda el ancho final; el JS lo aplica para animar -->
                                <div class="hbar-fill" data-w="<?= $pct ?>"></div>
                            </div>
                            <div class="hbar-val"><?= $total ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============ FILA 4: ACTIVIDAD RECIENTE ============ -->
<div class="card mt-4 mb-2">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-1"></i>Actividad reciente</span>
        <a href="index.php?controller=admin&action=auditoria" class="small">Ver auditoría completa →</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($resumen['actividad'])): ?>
            <p class="text-muted small p-3 mb-0">Sin actividad registrada todavía.</p>
        <?php else: ?>
            <table class="table table-hover mb-0" style="font-size:.85rem">
                <tbody>
                <?php
                $colAccion = ['LOGIN'=>'success','LOGOUT'=>'secondary','ALTA'=>'primary',
                              'MODIFICACION'=>'warning','BAJA'=>'danger','CONSULTA'=>'info','ERROR'=>'dark'];
                foreach ($resumen['actividad'] as $act):
                    $c = $colAccion[$act['Accion']] ?? 'secondary'; ?>
                    <tr>
                        <td class="text-nowrap text-muted" style="width:140px"><?= htmlspecialchars($act['Fecha']) ?></td>
                        <td style="width:120px"><span class="badge bg-<?= $c ?>"><?= htmlspecialchars($act['Accion']) ?></span></td>
                        <td><?= htmlspecialchars($act['Detalle'] ?? '') ?></td>
                        <td class="text-muted text-end"><?= htmlspecialchars($act['Email'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</div><!-- /#dash -->

<script>
/* Animaciones del dashboard (JS mínimo y nativo, sin librerías) */
(function () {
    /* 1) Conteo animado de números (totales, indicadores, total de la dona) */
    document.querySelectorAll('#dash .dash-count').forEach(function (el) {
        var destino = parseFloat(el.dataset.to) || 0;
        var dec     = parseInt(el.dataset.dec || '0', 10);
        var suf     = el.dataset.suf || '';
        var inicio  = null;
        var dur     = 900; // ms
        function paso(ts) {
            if (!inicio) inicio = ts;
            var p = Math.min((ts - inicio) / dur, 1);
            /* easing suave (easeOutCubic) */
            var val = destino * (1 - Math.pow(1 - p, 3));
            el.textContent = val.toFixed(dec) + suf;
            if (p < 1) requestAnimationFrame(paso);
            else el.textContent = destino.toFixed(dec) + suf;
        }
        requestAnimationFrame(paso);
    });

    /* 2) Relleno animado de las barras de carreras */
    requestAnimationFrame(function () {
        document.querySelectorAll('#dash .hbar-fill').forEach(function (el) {
            el.style.width = (el.dataset.w || 0) + '%';
        });
    });
})();
</script>
