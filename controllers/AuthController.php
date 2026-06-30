<?php

/** Controlador de autenticación: inicio y cierre de sesión. */
class AuthController extends Controller {

    private UsuarioModel $usuarioModel;

    /** Instancia el modelo de usuario. */
    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    /** Muestra el formulario de login (GET) y procesa las credenciales (POST). */
    public function login(): void {

        // Si ya hay sesión activa, redirige al dashboard según el rol
        if (isset($_SESSION['usuario'])) {
            $rol = $_SESSION['usuario']['NombreRol'];
            $this->redirect("index.php?controller={$rol}&action=dashboard");
        }

        $error = ($_GET['timeout'] ?? false) ? 'Tu sesión expiró por inactividad. Ingresá nuevamente.' : '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password =      $_POST['password'] ?? '';

            // Valida que los campos no estén vacíos
            if ($email === '' || $password === '') {
                $error = 'Completá todos los campos.';
            } else {
                try {
                    $usuario = $this->usuarioModel->login($email, $password);

                    if ($usuario) {
                        /* Arma la sesión y registra la auditoría */
                        $this->iniciarSesion($usuario, 'Inicio de sesión correcto');
                        $this->redirect("index.php?controller={$usuario['NombreRol']}&action=dashboard");
                    } else {
                        $error = 'Email o contraseña incorrectos.';
                        /* Auditoría: intento fallido. No hay usuario en sesión, se deja constancia del email probado. */
                        Auditoria::registrar(Auditoria::ERROR, 'Usuario', "Intento de login fallido para: {$email}");
                    }
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $error = 'Error de conexión. Intentá de nuevo.';
                }
            }
        }

        require_once BASE_PATH . '/views/auth/login.php';
    }

    /**
     * Carga los datos del usuario en sesión y registra la auditoría del ingreso.
     * Centraliza el armado de sesión usado por el login.
     */
    private function iniciarSesion(array $usuario, string $detalleAuditoria): void {
        $permisos = $this->usuarioModel->getPermisos((int)$usuario['IDRol']);
        $this->usuarioModel->actualizarUltimoAcceso((int)$usuario['IDUsuario']);

        $_SESSION['usuario'] = [
            'IDUsuario'      => $usuario['IDUsuario'],
            'Email'          => $usuario['Email'],
            'IDRol'          => $usuario['IDRol'],
            'NombreRol'      => $usuario['NombreRol'],
            'DNI'            => $usuario['DNI'],
            'Legajo'         => $usuario['Legajo'],
            'NombreCompleto' => $usuario['NombreCompleto'],
        ];
        $_SESSION['permisos']         = $permisos;
        $_SESSION['ultima_actividad'] = time();

        Auditoria::registrar(Auditoria::LOGIN, 'Usuario', $detalleAuditoria);
    }

    /** Destruye la sesión y redirige al login. */
    public function logout(): void {
        /* Auditoría: registramos el cierre ANTES de destruir la sesión, para conservar los datos del usuario */
        Auditoria::registrar(Auditoria::LOGOUT, 'Usuario', "Cierre de sesión");
        session_destroy();
        $this->redirect('index.php?controller=auth&action=login');
    }
}
