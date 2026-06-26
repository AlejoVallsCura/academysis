<?php

/** Controlador del panel de administración. */
class AdminController extends Controller {

    private AdminModel $adminModel;

    public function __construct() {
        $this->adminModel = new AdminModel();
    }

    /** Dashboard con resumen de entidades. */
    public function dashboard(): void {
        requierePermiso('gestionar_materias');
        $resumen = $this->adminModel->getResumen();
        $this->render('admin/dashboard', compact('resumen'));
    }

    // ============================================================
    //  CARRERAS
    // ============================================================

    /** Lista todas las carreras. */
    public function carreras(): void {
        requierePermiso('gestionar_materias');
        $carreras = $this->adminModel->getCarreras();
        $this->render('admin/carreras', compact('carreras'));
    }

    /** Formulario de alta/edición de carrera (GET) y guardado (POST). */
    public function carrera(): void {
        requierePermiso('gestionar_materias');
        $isNew   = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['_isNew']) : !isset($_GET['cod']);
        $cod     = $_SERVER['REQUEST_METHOD'] === 'POST' ? trim($_POST['CodCarrera']) : ($_GET['cod'] ?? '');
        $error   = '';
        $carrera = $isNew ? [] : $this->adminModel->getCarreraById($cod);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->adminModel->saveCarrera([
                    'CodCarrera' => trim($_POST['CodCarrera']),
                    'NomCarrera' => trim($_POST['NomCarrera']),
                    'DurAnios'   => (int)$_POST['DurAnios'],
                ], $isNew);
                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Carrera guardada correctamente.'];
                $this->redirect('index.php?controller=admin&action=carreras');
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Error al guardar la carrera.';
            }
        }

        $this->render('admin/carrera_form', compact('carrera', 'isNew', 'error'));
    }

    // ============================================================
    //  MATERIAS
    // ============================================================

    /** Lista todas las materias. */
    public function materias(): void {
        requierePermiso('gestionar_materias');
        $materias = $this->adminModel->getMaterias();
        $this->render('admin/materias', compact('materias'));
    }

    /** Formulario de alta/edición de materia (GET) y guardado (POST). */
    public function materia(): void {
        requierePermiso('gestionar_materias');
        $isNew   = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['_isNew']) : !isset($_GET['cod']);
        $cod     = $_SERVER['REQUEST_METHOD'] === 'POST' ? trim($_POST['CodMateria']) : ($_GET['cod'] ?? '');
        $error   = '';
        $materia  = $isNew ? [] : $this->adminModel->getMateriaById($cod);
        $carreras = $this->adminModel->getCarrerasList();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codCarrera = trim($_POST['CodCarrera'] ?? '');
            $anio       = (int)($_POST['Anio'] ?? 0);
            if (!$codCarrera || $anio < 1) {
                $error = 'Debés seleccionar una carrera y un año de cursada.';
            } else {
                try {
                    $this->adminModel->saveMateria([
                        'CodMateria' => trim($_POST['CodMateria']),
                        'NomMateria' => trim($_POST['NomMateria']),
                        'CodCarrera' => $codCarrera,
                        'Anio'       => $anio,
                    ], $isNew);
                    $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Materia guardada correctamente.'];
                    $this->redirect('index.php?controller=admin&action=materias');
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $error = 'Error al guardar la materia.';
                }
            }
        }

        $this->render('admin/materia_form', compact('materia', 'isNew', 'error', 'carreras'));
    }

    /** Activa o desactiva una materia. */
    public function toggleMateria(): void {
        requierePermiso('gestionar_materias');
        $cod = $_POST['cod'] ?? '';
        if ($cod) $this->adminModel->toggleMateriaActivo($cod);
        $this->redirect('index.php?controller=admin&action=materias');
    }

    // ============================================================
    //  CURSOS
    // ============================================================

    /** Lista todos los cursos. */
    public function cursos(): void {
        requierePermiso('gestionar_cursos');
        $cursos = $this->adminModel->getCursos();
        $this->render('admin/cursos', compact('cursos'));
    }

    /** Formulario de alta/edición de curso (GET) y guardado (POST). */
    public function curso(): void {
        requierePermiso('gestionar_cursos');
        $isNew = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['_isNew']) : !isset($_GET['id']);
        $id    = (int)($_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['IDCurso'] ?? 0) : ($_GET['id'] ?? 0));
        $error    = '';
        $curso    = $isNew ? [] : $this->adminModel->getCursoById($id);
        $horarios = $isNew ? [] : $this->adminModel->getHorariosByCurso($id);
        $materias = $this->adminModel->getMateriasList();
        $docentes = $this->adminModel->getDocentesList();
        $aulas    = $this->adminModel->getAulas();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $idCurso = $this->adminModel->saveCurso([
                    'IDCurso'     => $id,
                    'AnioLectivo' => (int)$_POST['AnioLectivo'],
                    'CodMateria'  => $_POST['CodMateria'],
                    'Legajo'      => (int)$_POST['Legajo'],
                    'IDAula'      => (int)$_POST['IDAula'],
                ], $isNew);
                $this->adminModel->saveHorarios($idCurso, $_POST['horarios'] ?? []);
                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Curso guardado correctamente.'];
                $this->redirect('index.php?controller=admin&action=cursos');
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Error al guardar el curso.';
            }
        }

        $this->render('admin/curso_form', compact('curso', 'isNew', 'error', 'materias', 'docentes', 'aulas', 'horarios'));
    }

    /** Activa o desactiva un curso. */
    public function toggleCurso(): void {
        requierePermiso('gestionar_cursos');
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $this->adminModel->toggleCursoActivo($id);
        $this->redirect('index.php?controller=admin&action=cursos');
    }

    // ============================================================
    //  ALUMNOS
    // ============================================================

    /** Lista todos los alumnos. */
    public function alumnos(): void {
        requierePermiso('gestionar_alumnos');
        $alumnos = $this->adminModel->getAlumnos();
        $this->render('admin/alumnos', compact('alumnos'));
    }

    /** Formulario de alta/edición de alumno (GET) y guardado (POST). */
    public function alumno(): void {
        requierePermiso('gestionar_alumnos');
        $isNew    = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['_isNew']) : !isset($_GET['dni']);
        $dni      = (int)($_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['DNI'] ?? 0) : ($_GET['dni'] ?? 0));
        $error    = '';
        $alumno   = $isNew ? [] : $this->adminModel->getAlumnoByDni($dni);
        $carreras = $this->adminModel->getCarrerasList();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'DNI'             => (int)$_POST['DNI'],
                    'Nombre'          => trim($_POST['Nombre']),
                    'Apellido'        => trim($_POST['Apellido']),
                    'FechaNacimiento' => $_POST['FechaNacimiento'],
                    'Telefono'        => trim($_POST['Telefono'] ?? ''),
                    'Email'           => trim($_POST['Email']),
                    'FechaIngreso'    => $_POST['FechaIngreso'],
                    'CodCarrera'      => trim($_POST['CodCarrera'] ?? ''),
                    'Calle'           => trim($_POST['Calle']),
                    'DirNumero'       => trim($_POST['DirNumero']),
                    'Ciudad'          => trim($_POST['Ciudad']),
                    'Provincia'       => trim($_POST['Provincia']),
                    'CP'              => trim($_POST['CP'] ?? ''),
                    'Password'        => $_POST['Password'] ?? '',
                ];

                if ($isNew && $this->adminModel->existeAlumnoPorDni($data['DNI'])) {
                    $error = "Ya existe un alumno con el DNI {$data['DNI']}.";
                } elseif ($this->adminModel->existeAlumnoPorEmail($data['Email'], $isNew ? 0 : $data['DNI'])) {
                    $error = "El email {$data['Email']} ya está registrado en otro alumno.";
                } else {
                    if ($isNew) {
                        $this->adminModel->crearAlumno($data);
                    } else {
                        $this->adminModel->editarAlumno($data);
                    }
                    $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Alumno guardado correctamente.'];
                    $this->redirect('index.php?controller=admin&action=alumnos');
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Error al guardar el alumno.';
            }
        }

        $this->render('admin/alumno_form', compact('alumno', 'isNew', 'error', 'carreras'));
    }

    /** Activa o desactiva un alumno. */
    public function toggleAlumno(): void {
        requierePermiso('gestionar_alumnos');
        $dni = (int)($_POST['dni'] ?? 0);
        if ($dni) $this->adminModel->toggleAlumnoActivo($dni);
        $this->redirect('index.php?controller=admin&action=alumnos');
    }

    // ============================================================
    //  DOCENTES
    // ============================================================

    /** Lista todos los docentes. */
    public function docentes(): void {
        requierePermiso('gestionar_docentes');
        $docentes = $this->adminModel->getDocentes();
        $this->render('admin/docentes', compact('docentes'));
    }

    /** Formulario de alta/edición de docente (GET) y guardado (POST). */
    public function docente(): void {
        requierePermiso('gestionar_docentes');
        $isNew  = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['_isNew']) : !isset($_GET['legajo']);
        $legajo = (int)($_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['Legajo'] ?? 0) : ($_GET['legajo'] ?? 0));
        $error  = '';
        $docente = $isNew ? [] : $this->adminModel->getDocenteByLegajo($legajo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'Legajo'       => $legajo,
                    'Nombre'       => trim($_POST['Nombre']),
                    'Apellido'     => trim($_POST['Apellido']),
                    'DNI'          => (int)$_POST['DNI'],
                    'Titulo'       => trim($_POST['Titulo'] ?? ''),
                    'Especialidad' => trim($_POST['Especialidad'] ?? ''),
                    'Email'        => trim($_POST['Email']),
                    'Password'     => $_POST['Password'] ?? '',
                ];

                if ($isNew) {
                    $this->adminModel->crearDocente($data);
                } else {
                    $this->adminModel->editarDocente($data);
                }

                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Docente guardado correctamente.'];
                $this->redirect('index.php?controller=admin&action=docentes');
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Error al guardar el docente. Verificá que el DNI y email no estén duplicados.';
            }
        }

        $this->render('admin/docente_form', compact('docente', 'isNew', 'error'));
    }

    /** Activa o desactiva un docente. */
    public function toggleDocente(): void {
        requierePermiso('gestionar_docentes');
        $legajo = (int)($_POST['legajo'] ?? 0);
        if ($legajo) $this->adminModel->toggleDocenteActivo($legajo);
        $this->redirect('index.php?controller=admin&action=docentes');
    }

    // ============================================================
    //  ADMINISTRADORES
    // ============================================================

    /** Lista todos los administradores. */
    public function admins(): void {
        requierePermiso('gestionar_admins');
        $admins = $this->adminModel->getAdmins();
        $this->render('admin/admins', compact('admins'));
    }

    /** Formulario para crear un nuevo admin (GET) y guardado (POST). */
    public function nuevoAdmin(): void {
        requierePermiso('gestionar_admins');
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->adminModel->crearAdmin([
                    'Nombre'   => trim($_POST['Nombre']),
                    'Email'    => trim($_POST['Email']),
                    'Password' => $_POST['Password'],
                ]);
                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Administrador creado correctamente.'];
                $this->redirect('index.php?controller=admin&action=admins');
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Error al crear el administrador. El email puede estar duplicado.';
            }
        }

        $this->render('admin/admin_form', compact('error'));
    }

    /** Activa o desactiva un administrador. */
    public function toggleAdmin(): void {
        requierePermiso('gestionar_admins');
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $this->adminModel->toggleAdminActivo($id);
        $this->redirect('index.php?controller=admin&action=admins');
    }
}
