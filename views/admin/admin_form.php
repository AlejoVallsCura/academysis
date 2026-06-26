<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2"></i>Nuevo Administrador</h4>
    <a href="index.php?controller=admin&action=admins" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:480px;">
    <div class="card-body">
        <form method="post" action="index.php?controller=admin&action=nuevoAdmin" id="adminForm">
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre</label>
                <input type="text" name="Nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="Email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Contraseña</label>
                <input type="password" name="Password" id="password" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Confirmar Contraseña</label>
                <input type="password" id="password2" class="form-control" required>
                <div class="invalid-feedback">Las contraseñas no coinciden.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Guardar
                </button>
                <a href="index.php?controller=admin&action=admins" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('adminForm').addEventListener('submit', function(e) {
    var p1 = document.getElementById('password');
    var p2 = document.getElementById('password2');
    if (p1.value !== p2.value) {
        e.preventDefault();
        p2.classList.add('is-invalid');
        p1.classList.add('is-invalid');
    } else {
        p2.classList.remove('is-invalid');
        p1.classList.remove('is-invalid');
    }
});
document.getElementById('password2').addEventListener('input', function() {
    this.classList.remove('is-invalid');
    document.getElementById('password').classList.remove('is-invalid');
});
</script>
