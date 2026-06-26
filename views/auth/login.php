<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AcademiSys — Iniciar sesión</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&display=swap">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:flex; font-family:'Nunito',sans-serif; }

        /* Panel izquierdo */
        .l-panel {
            width: 420px; flex-shrink: 0;
            background: #363D30;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 3rem 2.5rem;
            position: relative; overflow: hidden;
            color: #fff;
        }
        .l-panel::before {
            content:''; position:absolute;
            width:340px; height:340px; border-radius:50%;
            background:rgba(255,255,255,.04);
            top:-100px; right:-100px;
        }
        .l-panel::after {
            content:''; position:absolute;
            width:220px; height:220px; border-radius:50%;
            background:rgba(255,255,255,.04);
            bottom:-60px; left:-60px;
        }
        .l-panel .deco-ring {
            position:absolute; width:160px; height:160px;
            border-radius:50%; border:1px solid rgba(255,255,255,.08);
            bottom:120px; right:-40px;
        }
        .l-brand { position:relative; z-index:1; text-align:center; }
        .l-brand .icon-wrap {
            width:80px; height:80px; border-radius:20px;
            background:rgba(255,255,255,.12);
            display:flex; align-items:center; justify-content:center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }
        .l-brand h1 {
            font-family:'Nunito',sans-serif;
            font-size:2.1rem; font-weight:700;
            margin-bottom:.4rem;
            color:#fff;
        }
        .l-brand p { color:rgba(255,255,255,.55); font-size:.9rem; margin:0; }
        .l-divider {
            width:40px; height:2px;
            background:rgba(255,255,255,.2);
            margin:2rem auto;
        }
        .l-features { position:relative; z-index:1; width:100%; }
        .l-feature {
            display:flex; align-items:center; gap:.85rem;
            color:rgba(255,255,255,.65); font-size:.85rem;
            margin-bottom:.9rem;
        }
        .l-feature i { font-size:1rem; color:rgba(255,255,255,.4); flex-shrink:0; }

        /* Panel derecho */
        .r-panel {
            flex:1;
            background:#F3F5F0;
            display:flex; align-items:center; justify-content:center;
            padding:2rem;
        }
        .form-box { width:100%; max-width:380px; }
        .form-box h2 {
            font-family:'Nunito',sans-serif;
            font-size:1.75rem; font-weight:600;
            color:#363D30; margin-bottom:.3rem;
        }
        .form-box .sub { color:#849174; font-size:.88rem; margin-bottom:2rem; }
        .form-label { font-weight:500; color:#4A5240; font-size:.875rem; }
        .form-control {
            border-color:#DDE5D8; border-radius:7px;
            padding:.6rem .9rem; font-size:.93rem;
            background:#fff;
        }
        .form-control:focus {
            border-color:#849174;
            box-shadow:0 0 0 .2rem rgba(141,110,99,.2);
        }
        .btn-login {
            background:#65715A; border:none; color:#fff;
            border-radius:7px; padding:.7rem; font-weight:600;
            font-size:.95rem; letter-spacing:.2px;
            transition:background .18s;
        }
        .btn-login:hover { background:#4A5240; color:#fff; }
        .test-users {
            background:#fff; border:1px solid #ECF0E8;
            border-radius:8px; padding:1rem 1.1rem;
            font-size:.78rem; color:#849174; line-height:1.8;
        }
        .test-users strong { color:#4A5240; display:block; margin-bottom:.3rem; }
    </style>
</head>
<body>

<!-- Panel izquierdo: marca -->
<div class="l-panel">
    <div class="deco-ring"></div>
    <div class="l-brand">
        <div class="icon-wrap">
            <i class="bi bi-mortarboard-fill" style="color:rgba(255,255,255,.85)"></i>
        </div>
        <h1>AcademiSys</h1>
        <p>Sistema de Gestión Académica</p>
    </div>
    <div class="l-divider"></div>
    <div class="l-features">
        <div class="l-feature"><i class="bi bi-journal-check"></i> Seguimiento de cursadas y notas</div>
        <div class="l-feature"><i class="bi bi-people"></i> Gestión de alumnos y docentes</div>
        <div class="l-feature"><i class="bi bi-bar-chart-steps"></i> Progreso académico por carrera</div>
        <div class="l-feature"><i class="bi bi-shield-check"></i> Validación de correlativas</div>
    </div>
</div>

<!-- Panel derecho: formulario -->
<div class="r-panel">
    <div class="form-box">
        <h2>Bienvenido</h2>
        <p class="sub">Ingresá con tu cuenta institucional</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 mb-3" style="border-radius:7px; font-size:.88rem;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?controller=auth&action=login" novalidate>
            <div class="mb-3">
                <label class="form-label">Email institucional</label>
                <input type="email" name="email" class="form-control"
                       placeholder="usuario@mail.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-login w-100">
                Ingresar <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>

        <p style="text-align:center; margin-top:1.2rem; font-size:.8rem; color:#A3B090;">
            ¿Olvidaste tu contraseña? Contactá a
            <a href="mailto:secretaria@uch.edu.ar" style="color:#65715A; font-weight:600;">secretaria@uch.edu.ar</a>
        </p>

        <hr style="border-color:#ECF0E8; margin:1rem 0">

        <div class="test-users">
            <strong>Usuarios de prueba</strong>
            <span>Alumno: lucia.fernandez@mail.com / alumno123</span>
            <span>Docente: c.medina@academisys.edu.ar / docente123</span>
            <span>Admin: admin@academisys.edu.ar / Admin1234</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
