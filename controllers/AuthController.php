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
                        $permisos = $this->usuarioModel->getPermisos((int)$usuario['IDRol']);
                        $this->usuarioModel->actualizarUltimoAcceso((int)$usuario['IDUsuario']);

                        // Guarda los datos del usuario en sesión
                        $_SESSION['usuario'] = [
                            'IDUsuario'      => $usuario['IDUsuario'],
                            'Email'          => $usuario['Email'],
                            'IDRol'          => $usuario['IDRol'],
                            'NombreRol'      => $usuario['NombreRol'],
                            'DNI'            => $usuario['DNI'],
                            'Legajo'         => $usuario['Legajo'],
                            'NombreCompleto' => $usuario['NombreCompleto'],
                        ];

                        $_SESSION['permisos']          = $permisos;
                        $_SESSION['ultima_actividad']  = time();

                        $rol = $usuario['NombreRol'];
                        $this->redirect("index.php?controller={$rol}&action=dashboard");
                    } else {
                        $error = 'Email o contraseña incorrectos.';
                    }
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $error = 'Error de conexión. Intentá de nuevo.';
                }
            }
        }

        require_once BASE_PATH . '/views/auth/login.php';
    }

    /** Destruye la sesión y redirige al login. */
    public function logout(): void {
        session_destroy();
        $this->redirect('index.php?controller=auth&action=login');
    }
}
