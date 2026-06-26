<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AcademiSys</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(BASE_PATH . '/assets/css/style.css') ?>">
    <style>
        .navbar-brand { font-weight:700; letter-spacing:.5px; font-family:'Nunito',sans-serif; font-size:1.3rem; }
        #sidebar {
            width:230px; min-height:calc(100vh - 56px);
            background:#363D30; position:fixed; top:56px; left:0;
            padding-top:1rem; z-index:100;
            border-right:1px solid rgba(255,255,255,.06);
        }
        #sidebar .nav-link {
            color:rgba(255,255,255,.78); padding:.55rem 1.25rem;
            font-size:.9rem; border-radius:0;
        }
        #sidebar .nav-link:hover { color:#fff; background:rgba(255,255,255,.1); }
        #sidebar .nav-section {
            color:rgba(255,255,255,.38); font-size:.68rem;
            text-transform:uppercase; letter-spacing:1.5px;
            padding:.75rem 1.25rem .25rem;
        }
        #main-content { margin-left:230px; padding:1.75rem; margin-top:56px; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark fixed-top px-3" style="background:#363D30; height:56px; border-bottom:1px solid rgba(255,255,255,.08)">
    <a class="navbar-brand" href="index.php">
        <i class="bi bi-mortarboard-fill me-2"></i>AcademiSys
    </a>
    <div class="d-flex align-items-center gap-3 ms-auto">
        <span class="text-white-50 small">
            <?= htmlspecialchars($_SESSION['usuario']['NombreCompleto'] ?? '') ?>
        </span>
        <span class="badge bg-light text-dark text-uppercase" style="font-size:.7rem;">
            <?= htmlspecialchars($_SESSION['usuario']['NombreRol'] ?? '') ?>
        </span>
        <a href="index.php?controller=auth&action=logout"
           class="btn btn-sm btn-outline-light">
            <i class="bi bi-box-arrow-right"></i> Salir
        </a>
    </div>
</nav>

<!-- SIDEBAR -->
<div id="sidebar">
<?php if (esAlumno()): ?>
    <div class="nav-section">Alumno</div>
    <a href="index.php?controller=alumno&action=dashboard"     class="nav-link d-flex align-items-center gap-2"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="index.php?controller=alumno&action=misCursos"     class="nav-link d-flex align-items-center gap-2"><i class="bi bi-journal-text"></i> Mis Cursos</a>
    <a href="index.php?controller=alumno&action=misNotas"      class="nav-link d-flex align-items-center gap-2"><i class="bi bi-clipboard-data"></i> Mis Notas</a>
    <a href="index.php?controller=alumno&action=miAsistencia"  class="nav-link d-flex align-items-center gap-2"><i class="bi bi-calendar-check"></i> Mi Asistencia</a>
    <a href="index.php?controller=alumno&action=correlativas"  class="nav-link d-flex align-items-center gap-2"><i class="bi bi-diagram-3"></i> Correlativas</a>
    <a href="index.php?controller=alumno&action=miProgreso"    class="nav-link d-flex align-items-center gap-2"><i class="bi bi-bar-chart-steps"></i> Mi Progreso</a>
    <a href="index.php?controller=alumno&action=inscribirCurso" class="nav-link d-flex align-items-center gap-2"><i class="bi bi-plus-circle"></i> Inscribirme</a>
    <a href="index.php?controller=alumno&action=miTitulo"      class="nav-link d-flex align-items-center gap-2"><i class="bi bi-award"></i> Mi Título</a>
<?php elseif (esAdmin()): ?>
    <div class="nav-section">Admin</div>
    <a href="index.php?controller=admin&action=dashboard"  class="nav-link d-flex align-items-center gap-2"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="index.php?controller=admin&action=carreras"   class="nav-link d-flex align-items-center gap-2"><i class="bi bi-mortarboard"></i> Carreras</a>
    <a href="index.php?controller=admin&action=materias"   class="nav-link d-flex align-items-center gap-2"><i class="bi bi-book"></i> Materias</a>
    <a href="index.php?controller=admin&action=cursos"     class="nav-link d-flex align-items-center gap-2"><i class="bi bi-calendar3"></i> Cursos</a>
    <a href="index.php?controller=admin&action=alumnos"    class="nav-link d-flex align-items-center gap-2"><i class="bi bi-person-badge"></i> Alumnos</a>
    <a href="index.php?controller=admin&action=docentes"   class="nav-link d-flex align-items-center gap-2"><i class="bi bi-person-workspace"></i> Docentes</a>
    <a href="index.php?controller=admin&action=admins"     class="nav-link d-flex align-items-center gap-2"><i class="bi bi-shield-lock"></i> Administradores</a>
<?php elseif (esDocente()): ?>
    <div class="nav-section">Docente</div>
    <a href="index.php?controller=docente&action=dashboard"  class="nav-link d-flex align-items-center gap-2"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="index.php?controller=docente&action=misCursos"  class="nav-link d-flex align-items-center gap-2"><i class="bi bi-journal-text"></i> Mis Cursos</a>
<?php endif; ?>
</div>

<!-- CONTENT -->
<div id="main-content">

<?php
// Mostrar mensaje de sesión si existe
if (!empty($_SESSION['mensaje'])):
    $tipo  = htmlspecialchars($_SESSION['mensaje']['tipo']);
    $texto = htmlspecialchars($_SESSION['mensaje']['texto']);
    unset($_SESSION['mensaje']);
?>
<div class="alert alert-<?= $tipo ?> alert-dismissible fade show" role="alert">
    <?= $texto ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
