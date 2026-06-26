<h4 class="mb-4 fw-bold">
    <i class="bi bi-speedometer2 me-2"></i>Dashboard
</h4>

<div class="row g-4">
    <div class="col-md-3">
        <a href="index.php?controller=admin&action=carreras" class="text-decoration-none">
            <div class="card stat-indigo h-100">
                <div class="card-body text-center py-4">
                    <div class="stat-num"><?= (int)$resumen['carreras'] ?></div>
                    <div class="text-muted mt-1 small">Carreras</div>
                    <i class="bi bi-mortarboard stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2">
        <a href="index.php?controller=admin&action=materias" class="text-decoration-none">
            <div class="card stat-teal h-100">
                <div class="card-body text-center py-4">
                    <div class="stat-num"><?= (int)$resumen['materias'] ?></div>
                    <div class="text-muted mt-1 small">Materias</div>
                    <i class="bi bi-book stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2">
        <a href="index.php?controller=admin&action=cursos" class="text-decoration-none">
            <div class="card stat-green h-100">
                <div class="card-body text-center py-4">
                    <div class="stat-num"><?= (int)$resumen['cursos'] ?></div>
                    <div class="text-muted mt-1 small">Cursos</div>
                    <i class="bi bi-calendar3 stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2">
        <a href="index.php?controller=admin&action=alumnos" class="text-decoration-none">
            <div class="card stat-amber h-100">
                <div class="card-body text-center py-4">
                    <div class="stat-num"><?= (int)$resumen['alumnos'] ?></div>
                    <div class="text-muted mt-1 small">Alumnos</div>
                    <i class="bi bi-person-badge stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2">
        <a href="index.php?controller=admin&action=docentes" class="text-decoration-none">
            <div class="card stat-rose h-100">
                <div class="card-body text-center py-4">
                    <div class="stat-num"><?= (int)$resumen['docentes'] ?></div>
                    <div class="text-muted mt-1 small">Docentes</div>
                    <i class="bi bi-person-workspace stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
</div>
