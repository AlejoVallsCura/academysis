<?php

/** Controlador del perfil Docente. */
class DocenteController extends Controller {

    private DocenteModel    $docenteModel;
    private CursoModel      $cursoModel;
    private EvaluacionModel $evaluacionModel;
    private AsistenciaModel $asistenciaModel;

    /** Instancia todos los modelos necesarios. */
    public function __construct() {
        $this->docenteModel    = new DocenteModel();
        $this->cursoModel      = new CursoModel();
        $this->evaluacionModel = new EvaluacionModel();
        $this->asistenciaModel = new AsistenciaModel();
    }

    /** Muestra la pantalla principal del docente con su resumen. */
    public function dashboard(): void {
        requierePermiso('ver_cursos_asignados');

        $legajo  = (int)$_SESSION['usuario']['Legajo'];
        $docente = $this->docenteModel->getByLegajo($legajo);
        $resumen = $this->docenteModel->getResumenDashboard($legajo);

        $this->render('docente/dashboard', compact('docente', 'resumen'));
    }

    /** Lista los cursos asignados al docente. */
    public function misCursos(): void {
        requierePermiso('ver_cursos_asignados');
        $legajo = (int)$_SESSION['usuario']['Legajo'];
        $cursos = $this->cursoModel->getCursosByDocente($legajo);
        $this->render('docente/mis_cursos', compact('cursos'));
    }

    /** Muestra los alumnos inscriptos en un curso específico. */
    public function alumnosCurso(): void {
        requierePermiso('ver_alumnos_curso');

        $idCurso = (int)($_GET['idCurso'] ?? 0);
        if (!$idCurso) {
            // Redirige si no se recibe un ID de curso válido
            $this->redirect('index.php?controller=docente&action=misCursos');
        }

        $curso   = $this->cursoModel->getCursoById($idCurso);
        $alumnos = $this->cursoModel->getAlumnosByCurso($idCurso);

        // Se pasa $idCurso a la vista para construir los enlaces de carga
        $this->render('docente/alumnos_curso', compact('curso', 'alumnos', 'idCurso'));
    }

    /** Muestra el formulario para marcar el estado final de cada alumno (GET) y lo guarda (POST). */
    public function cerrarCursada(): void {
        requierePermiso('ver_alumnos_curso');

        $idCurso = (int)($_GET['idCurso'] ?? $_POST['IDCurso'] ?? 0);
        if (!$idCurso) {
            $this->redirect('index.php?controller=docente&action=misCursos');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // $_POST['estado'] es un array [IDInscripcion => 'Regular'|'Libre'|'Baja']
                $estados = $_POST['estado'] ?? [];
                $estadosValidos = ['Regular', 'Libre', 'Baja', 'Activo', 'Aprobado'];

                // Filtra solo valores permitidos para evitar datos inválidos
                $estadosFiltrados = array_filter(
                    $estados,
                    fn($e) => in_array($e, $estadosValidos)
                );

                $this->cursoModel->actualizarEstados($estadosFiltrados);
                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Estados actualizados correctamente.'];
                $this->redirect("index.php?controller=docente&action=alumnosCurso&idCurso={$idCurso}");
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $_SESSION['mensaje'] = ['tipo' => 'danger', 'texto' => 'Error al actualizar los estados.'];
                $this->redirect("index.php?controller=docente&action=cerrarCursada&idCurso={$idCurso}");
            }
        }

        $curso   = $this->cursoModel->getCursoById($idCurso);
        $alumnos = $this->cursoModel->getAlumnosConResumen($idCurso);
        $this->render('docente/cerrar_cursada', compact('curso', 'alumnos', 'idCurso'));
    }

    /** Marca el final de un alumno como Aprobado. */
    public function registrarFinal(): void {
        requierePermiso('ver_alumnos_curso');
        $idInscripcion = (int)($_POST['IDInscripcion'] ?? 0);
        $idCurso       = (int)($_POST['IDCurso'] ?? 0);
        if ($idInscripcion) {
            $this->cursoModel->registrarFinal($idInscripcion);
            $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Final registrado. Alumno aprobado.'];
        }
        $this->redirect("index.php?controller=docente&action=alumnosCurso&idCurso={$idCurso}");
    }

    /** Lista las notas de un alumno en un curso y permite editarlas. */
    public function editarNota(): void {
        requierePermiso('cargar_evaluacion');

        $idCurso = (int)($_GET['idCurso'] ?? $_POST['IDCurso'] ?? 0);
        $dni     = (int)($_GET['dni']     ?? $_POST['DNI']     ?? 0);
        $error   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idEval = (int)($_POST['IDEvaluacion'] ?? 0);
            $nota   = (float)$_POST['Nota'];
            if ($nota < 0 || $nota > 10) {
                $error = 'La nota debe estar entre 0 y 10.';
            } elseif ($idEval) {
                try {
                    $this->evaluacionModel->editarEvaluacion($idEval, [
                        'Tipo'      => $_POST['Tipo'],
                        'Nota'      => $nota,
                        'Fecha'     => $_POST['Fecha'],
                        'Instancia' => (int)($_POST['Instancia'] ?? 1),
                    ]);
                    $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Nota actualizada correctamente.'];
                    $this->redirect("index.php?controller=docente&action=editarNota&idCurso={$idCurso}&dni={$dni}");
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $error = 'Error al actualizar la nota.';
                }
            }
        }

        if (!$idCurso || !$dni) {
            $this->redirect('index.php?controller=docente&action=misCursos');
        }

        $curso  = $this->cursoModel->getCursoById($idCurso);
        $notas  = $this->evaluacionModel->getEvaluacionesByCursoAlumno($idCurso, $dni);
        $alumno = !empty($notas)
            ? ['Nombre' => $notas[0]['Nombre'], 'Apellido' => $notas[0]['Apellido'], 'DNI' => $dni]
            : ['Nombre' => '', 'Apellido' => 'Sin notas', 'DNI' => $dni];

        $this->render('docente/editar_nota', compact('curso', 'notas', 'alumno', 'idCurso', 'dni', 'error'));
    }

    /** Muestra el formulario de carga de nota (GET) y lo procesa (POST). */
    public function cargarNota(): void {
        requierePermiso('cargar_evaluacion');

        // El idCurso puede venir por GET o como campo oculto del POST
        $idCurso = (int)($_GET['idCurso'] ?? $_POST['IDCurso'] ?? 0);
        $error   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nota = (float)$_POST['Nota'];

                $tipo = $_POST['Tipo'] ?? '';
                if ($nota < 0 || $nota > 10) {
                    $error = 'La nota debe estar entre 0 y 10.';
                } elseif ($tipo === 'Final' && $this->evaluacionModel->tieneFinal($idCurso, (int)$_POST['DNI'])) {
                    $error = 'Este alumno ya tiene un Final registrado en este curso.';
                } else {
                    $this->evaluacionModel->guardarEvaluacion([
                        'DNI'       => (int)$_POST['DNI'],
                        'IDCurso'   => $idCurso,
                        'Tipo'      => $_POST['Tipo'],
                        'Nota'      => $nota,
                        'Fecha'     => $_POST['Fecha'],
                        'Instancia' => (int)($_POST['Instancia'] ?? 1),
                    ]);

                    // Guarda el mensaje de éxito en sesión antes de redirigir
                    $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Nota guardada correctamente.'];
                    $this->redirect("index.php?controller=docente&action=alumnosCurso&idCurso={$idCurso}");
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Error al guardar la nota. Revisá los datos e intentá de nuevo.';
            }
        }

        // Carga los datos del formulario (GET o POST con error)
        $alumnos = $idCurso ? $this->cursoModel->getAlumnosByCurso($idCurso) : [];
        $curso   = $idCurso ? $this->cursoModel->getCursoById($idCurso)       : [];
        $this->render('docente/cargar_nota', compact('alumnos', 'curso', 'idCurso', 'error'));
    }

    /** Muestra el formulario de carga de asistencia (GET) y lo procesa (POST). */
    public function cargarAsistencia(): void {
        requierePermiso('cargar_asistencia');

        // Leemos idCurso y fecha desde GET o POST según el flujo
        $idCurso = (int)($_GET['idCurso'] ?? $_POST['IDCurso'] ?? 0);
        $fecha   = $_GET['fecha'] ?? $_POST['fecha'] ?? date('Y-m-d');
        $error   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Array con los IDs de todos los alumnos del formulario
                $inscripciones = $_POST['IDInscripcion'] ?? [];

                // Array con solo los IDs de los alumnos marcados presentes
                $presentes     = $_POST['presente']      ?? [];

                $registros = [];
                foreach ($inscripciones as $idInsc) {
                    $registros[] = [
                        'IDInscripcion' => (int)$idInsc,
                        'Fecha'         => $fecha,
                        // 1 si está en $presentes, 0 si está ausente
                        'Presente'      => in_array($idInsc, $presentes) ? 1 : 0,
                        'Observaciones' => $_POST['obs'][$idInsc] ?? null,
                    ];
                }

                $this->asistenciaModel->guardarAsistencia($registros);

                // Guarda el mensaje de éxito en sesión antes de redirigir
                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Asistencia guardada correctamente.'];
                $this->redirect("index.php?controller=docente&action=alumnosCurso&idCurso={$idCurso}");
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Error al guardar la asistencia.';
            }
        }

        // Carga los alumnos con su estado de asistencia para la fecha indicada
        $alumnos = $idCurso ? $this->asistenciaModel->getAsistenciaByCurso($idCurso, $fecha) : [];
        $curso   = $idCurso ? $this->cursoModel->getCursoById($idCurso) : [];
        $this->render('docente/cargar_asistencia', compact('alumnos', 'curso', 'idCurso', 'fecha', 'error'));
    }
}
