<?php

/** Controlador del panel de administración. */
class AdminController extends Controller {

    private AdminModel     $adminModel;
    private AuditoriaModel $auditoriaModel;

    public function __construct() {
        $this->adminModel     = new AdminModel();
        $this->auditoriaModel = new AuditoriaModel();
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
                $codCarrera = trim($_POST['CodCarrera']);
                $this->adminModel->saveCarrera([
                    'CodCarrera' => $codCarrera,
                    'NomCarrera' => trim($_POST['NomCarrera']),
                    'DurAnios'   => (int)$_POST['DurAnios'],
                ], $isNew);
                /* Auditoría: alta o modificación de carrera según corresponda */
                Auditoria::registrar($isNew ? Auditoria::ALTA : Auditoria::MODIFICACION,
                    'Carrera', ($isNew ? 'Creó' : 'Editó') . " la carrera {$codCarrera}");
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
                    $codMateria = trim($_POST['CodMateria']);
                    $this->adminModel->saveMateria([
                        'CodMateria'  => $codMateria,
                        'NomMateria'  => trim($_POST['NomMateria']),
                        'CodCarrera'  => $codCarrera,
                        'Anio'        => $anio,
                        /* Contenidos mínimos: texto libre, puede estar vacío */
                        'ContMinimos' => trim($_POST['ContMinimos'] ?? ''),
                    ], $isNew);
                    /* Auditoría: alta o modificación de materia */
                    Auditoria::registrar($isNew ? Auditoria::ALTA : Auditoria::MODIFICACION,
                        'Materia', ($isNew ? 'Creó' : 'Editó') . " la materia {$codMateria}");
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
        if ($cod) {
            $this->adminModel->toggleMateriaActivo($cod);
            /* Auditoría: cambio de estado activo/inactivo de la materia */
            Auditoria::registrar(Auditoria::MODIFICACION, 'Materia', "Cambió el estado de la materia {$cod}");
        }
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
                /* Auditoría: alta o modificación de curso */
                Auditoria::registrar($isNew ? Auditoria::ALTA : Auditoria::MODIFICACION,
                    'Curso', ($isNew ? 'Creó' : 'Editó') . " el curso #{$idCurso} ({$_POST['CodMateria']})");
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
        if ($id) {
            $this->adminModel->toggleCursoActivo($id);
            /* Auditoría: cambio de estado activo/inactivo del curso */
            Auditoria::registrar(Auditoria::MODIFICACION, 'Curso', "Cambió el estado del curso #{$id}");
        }
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
                    /* Auditoría: alta o modificación de alumno */
                    Auditoria::registrar($isNew ? Auditoria::ALTA : Auditoria::MODIFICACION,
                        'Alumno', ($isNew ? 'Creó' : 'Editó') . " al alumno DNI {$data['DNI']} ({$data['Apellido']}, {$data['Nombre']})");
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
        if ($dni) {
            $this->adminModel->toggleAlumnoActivo($dni);
            /* Auditoría: cambio de estado activo/inactivo del alumno */
            Auditoria::registrar(Auditoria::MODIFICACION, 'Alumno', "Cambió el estado del alumno DNI {$dni}");
        }
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

                /* Auditoría: alta o modificación de docente */
                Auditoria::registrar($isNew ? Auditoria::ALTA : Auditoria::MODIFICACION,
                    'Docente', ($isNew ? 'Creó' : 'Editó') . " al docente {$data['Apellido']}, {$data['Nombre']} (DNI {$data['DNI']})");
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
        if ($legajo) {
            $this->adminModel->toggleDocenteActivo($legajo);
            /* Auditoría: cambio de estado activo/inactivo del docente */
            Auditoria::registrar(Auditoria::MODIFICACION, 'Docente', "Cambió el estado del docente legajo {$legajo}");
        }
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
                $emailAdmin = trim($_POST['Email']);
                $this->adminModel->crearAdmin([
                    'Nombre'   => trim($_POST['Nombre']),
                    'Email'    => $emailAdmin,
                    'Password' => $_POST['Password'],
                ]);
                /* Auditoría: alta de un nuevo administrador (acción sensible) */
                Auditoria::registrar(Auditoria::ALTA, 'Administrador', "Creó al administrador {$emailAdmin}");
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
        if ($id) {
            $this->adminModel->toggleAdminActivo($id);
            /* Auditoría: cambio de estado de un administrador (acción sensible) */
            Auditoria::registrar(Auditoria::MODIFICACION, 'Administrador', "Cambió el estado del administrador #{$id}");
        }
        $this->redirect('index.php?controller=admin&action=admins');
    }

    // ============================================================
    //  CORRELATIVAS
    // ============================================================

    /**
     * Lista todas las correlativas actuales y provee los datos para el formulario de alta.
     * El formulario permite seleccionar la materia y la correlativa que la desbloquea.
     */
    public function correlativas(): void {
        requierePermiso('gestionar_materias');

        /* Las correlativas se gestionan por carrera. Selector con la carrera elegida;
         * por defecto, la primera de la lista. */
        $carreras   = $this->adminModel->getCarrerasList();
        $carreraSel = trim($_GET['carrera'] ?? '');
        if ($carreraSel === '' && !empty($carreras)) {
            $carreraSel = $carreras[0]['CodCarrera'];
        }

        /* Lista de correlativas y materias acotadas a la carrera seleccionada */
        $correlativas = $this->adminModel->getCorrelativasList($carreraSel);
        $materias     = $this->adminModel->getMateriasList($carreraSel);

        $this->render('admin/correlativas', compact('correlativas', 'materias', 'carreras', 'carreraSel'));
    }

    /** Agrega una correlativa nueva entre dos materias (siempre dentro de la misma carrera). */
    public function agregarCorrelativa(): void {
        requierePermiso('gestionar_materias');
        /* La carrera viene del form para volver a la misma vista filtrada */
        $carrera = trim($_POST['carrera'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cod  = trim($_POST['CodMateria']     ?? '');
            $corr = trim($_POST['CodCorrelativa'] ?? '');
            if (!$cod || !$corr || $cod === $corr) {
                $_SESSION['mensaje'] = ['tipo' => 'danger', 'texto' => 'Selección inválida. No podés usar la misma materia.'];
            } elseif (!$this->adminModel->mismaCarrera($cod, $corr)) {
                /* Seguridad: una correlativa debe ser entre materias de la misma carrera */
                $_SESSION['mensaje'] = ['tipo' => 'danger', 'texto' => 'Ambas materias deben ser de la misma carrera.'];
            } else {
                $this->adminModel->agregarCorrelativa($cod, $corr);
                Auditoria::registrar(Auditoria::ALTA, 'Correlativa', "Agregó: {$cod} requiere {$corr}");
                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Correlativa agregada correctamente.'];
            }
        }
        /* Vuelve a la vista de la carrera con la que se estaba trabajando */
        $this->redirect('index.php?controller=admin&action=correlativas&carrera=' . urlencode($carrera));
    }

    /** Elimina una correlativa específica entre dos materias. */
    public function eliminarCorrelativa(): void {
        requierePermiso('gestionar_materias');
        $carrera = trim($_POST['carrera'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cod  = trim($_POST['CodMateria']     ?? '');
            $corr = trim($_POST['CodCorrelativa'] ?? '');
            if ($cod && $corr) {
                $this->adminModel->eliminarCorrelativa($cod, $corr);
                Auditoria::registrar(Auditoria::BAJA, 'Correlativa', "Eliminó: {$cod} ya no requiere {$corr}");
                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Correlativa eliminada.'];
            }
        }
        $this->redirect('index.php?controller=admin&action=correlativas&carrera=' . urlencode($carrera));
    }

    // ============================================================
    //  AUDITORÍA
    // ============================================================

    /**
     * Muestra el registro de auditoría: log general de la aplicación (con filtros y
     * paginación) más la auditoría de inscripciones generada por triggers de la base.
     */
    public function auditoria(): void {
        requierePermiso('ver_auditoria');

        /* Filtros recibidos por GET (todos opcionales) */
        $filtros = [
            'accion' => trim($_GET['accion'] ?? ''),
            'rol'    => trim($_GET['rol']    ?? ''),
            'desde'  => trim($_GET['desde']  ?? ''),
            'hasta'  => trim($_GET['hasta']  ?? ''),
            'buscar' => trim($_GET['buscar'] ?? ''),
        ];

        /* Paginación: 25 registros por página */
        $porPagina = 25;
        $pagina    = max(1, (int)($_GET['pagina'] ?? 1));
        $offset    = ($pagina - 1) * $porPagina;

        /* Datos para la vista */
        $total       = $this->auditoriaModel->contarLog($filtros);
        $totalPaginas = max(1, (int)ceil($total / $porPagina));
        $registros   = $this->auditoriaModel->getLog($filtros, $porPagina, $offset);
        $acciones    = $this->auditoriaModel->getAccionesDistintas();
        $resumen     = $this->auditoriaModel->getResumenPorAccion();
        $inscAudit   = $this->auditoriaModel->getAuditInscripcion(50);

        $this->render('admin/auditoria', compact(
            'registros', 'filtros', 'acciones', 'resumen', 'inscAudit',
            'pagina', 'totalPaginas', 'total'
        ));
    }
}
