<?php

/** Controlador del perfil Alumno. */
class AlumnoController extends Controller {

    private AlumnoModel     $alumnoModel;
    private CursoModel      $cursoModel;
    private EvaluacionModel $evaluacionModel;
    private AsistenciaModel $asistenciaModel;
    private MateriaModel    $materiaModel;
    private TituloModel     $tituloModel;

    /** Instancia todos los modelos necesarios. */
    public function __construct() {
        $this->alumnoModel     = new AlumnoModel();
        $this->cursoModel      = new CursoModel();
        $this->evaluacionModel = new EvaluacionModel();
        $this->asistenciaModel = new AsistenciaModel();
        $this->materiaModel    = new MateriaModel();
        $this->tituloModel     = new TituloModel();
    }

    /** Muestra la pantalla principal del alumno con su resumen. */
    public function dashboard(): void {
        requierePermiso('ver_cursos_propios');

        // El DNI viene de sesión para evitar que un alumno vea datos de otro
        $dni     = (int)$_SESSION['usuario']['DNI'];
        $alumno  = $this->alumnoModel->getByDNI($dni);
        $resumen = $this->alumnoModel->getResumenDashboard($dni);

        $this->render('alumno/dashboard', compact('alumno', 'resumen'));
    }

    /** Lista los cursos en los que está inscripto el alumno. */
    public function misCursos(): void {
        requierePermiso('ver_cursos_propios');
        $dni    = (int)$_SESSION['usuario']['DNI'];
        $cursos = $this->cursoModel->getCursosByAlumno($dni);
        $this->render('alumno/mis_cursos', compact('cursos'));
    }

    /** Muestra las evaluaciones y promedios por materia del alumno, con filtro por año. */
    public function misNotas(): void {
        requierePermiso('ver_notas_propias');
        $dni       = (int)$_SESSION['usuario']['DNI'];
        $anio      = (int)($_GET['anio'] ?? date('Y'));
        $anios     = $this->evaluacionModel->getAniosDisponibles($dni);
        $notas     = $this->evaluacionModel->getNotasByAlumno($dni, $anio);
        $promedios = $this->evaluacionModel->getPromedioByMateria($dni, $anio);
        $this->render('alumno/mis_notas', compact('notas', 'promedios', 'anios', 'anio'));
    }

    /** Muestra el registro de asistencia del alumno, con filtro por año. */
    public function miAsistencia(): void {
        requierePermiso('ver_asistencia_propia');
        $dni        = (int)$_SESSION['usuario']['DNI'];
        $anio       = (int)($_GET['anio'] ?? date('Y'));
        $anios      = $this->asistenciaModel->getAniosDisponibles($dni);
        $asistencia = $this->asistenciaModel->getAsistenciaByAlumno($dni, $anio);
        $this->render('alumno/mi_asistencia', compact('asistencia', 'anios', 'anio'));
    }

    /** Muestra el mapa de correlativas de las materias de la carrera del alumno. */
    public function correlativas(): void {
        requierePermiso('ver_correlativas');
        $dni    = (int)$_SESSION['usuario']['DNI'];
        $alumno = $this->alumnoModel->getByDNI($dni);
        /* Solo las correlativas de la carrera del alumno (no las de todas las carreras) */
        $materias = $this->materiaModel->getCorrelativas($alumno['CodCarrera'] ?? null);
        $this->render('alumno/correlativas', compact('materias'));
    }

    /** Muestra el estado del título del alumno. */
    public function miTitulo(): void {
        requierePermiso('ver_titulo_propio');
        $dni = (int)$_SESSION['usuario']['DNI'];
        /* Red de seguridad: si ya completó la carrera y aún no tiene título, se emite ahora */
        if ($this->tituloModel->emitirTituloSiCompleto($dni)) {
            Auditoria::registrar(Auditoria::ALTA, 'Titulo', 'Título emitido automáticamente (al consultar Mi Título)');
        }
        $titulo = $this->tituloModel->getTituloByAlumno($dni);
        $this->render('alumno/mi_titulo', compact('titulo'));
    }

    /** Muestra el progreso académico del alumno en su carrera. */
    public function miProgreso(): void {
        requierePermiso('ver_cursos_propios');
        $dni    = (int)$_SESSION['usuario']['DNI'];
        $alumno = $this->alumnoModel->getByDNI($dni);
        $codCarrera = $alumno['CodCarrera'] ?? null;
        if (!$codCarrera) {
            $_SESSION['mensaje'] = ['tipo' => 'warning', 'texto' => 'No tenés carrera asignada. Contactá a administración.'];
            $this->redirect('index.php?controller=alumno&action=dashboard');
        }
        $materias = $this->alumnoModel->getProgreso($dni, $codCarrera);
        $this->render('alumno/mi_progreso', compact('materias', 'alumno'));
    }

    /** Muestra los cursos disponibles y procesa la inscripción del alumno. */
    public function inscribirCurso(): void {
        requierePermiso('ver_cursos_propios');
        $dni    = (int)$_SESSION['usuario']['DNI'];
        $alumno = $this->alumnoModel->getByDNI($dni);
        /* La carrera es necesaria para filtrar los cursos disponibles */
        $codCarrera = $alumno['CodCarrera'] ?? '';
        $error = '';

        if (!$codCarrera) {
            /* Sin carrera asignada no tiene sentido mostrar cursos */
            $_SESSION['mensaje'] = ['tipo' => 'warning', 'texto' => 'No tenés carrera asignada. Contactá a administración.'];
            $this->redirect('index.php?controller=alumno&action=dashboard');
        }

        /* Año académico del alumno: define hasta qué año de materias puede inscribirse */
        $anioMax = $this->alumnoModel->getAnioAcademico($alumno);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCurso = (int)($_POST['IDCurso'] ?? 0);
            $curso   = $this->cursoModel->getCursoById($idCurso);

            if ($curso) {
                /* Validaciones de seguridad del lado servidor (no confiar solo en la vista):
                 * 1. El curso debe ser de la carrera del alumno.
                 * 2. La materia no puede ser de un año más adelantado que el del alumno. */
                if ($curso['CodCarrera'] !== $codCarrera) {
                    $error = 'Ese curso no pertenece a tu carrera.';
                } elseif ((int)$curso['AnioMateria'] > $anioMax) {
                    $error = "No podés inscribirte a una materia de {$curso['AnioMateria']}° año todavía.";
                } elseif ($this->cursoModel->yaTieneMateria($dni, $curso['CodMateria'])) {
                    /* Ya la aprobó, la está cursando o es regular: no corresponde reinscribirse */
                    $error = 'Ya aprobaste o estás cursando esa materia.';
                } else {
                    /* Verificamos correlativas pendientes antes de inscribir */
                    $pendientes = $this->cursoModel->getCorrelativasPendientes($dni, $curso['CodMateria']);
                    if (!empty($pendientes)) {
                        $nombres = implode(', ', array_column($pendientes, 'NomMateria'));
                        $error   = "No podés inscribirte: primero debés aprobar: {$nombres}";
                    } else {
                        try {
                            $this->cursoModel->inscribir($dni, $idCurso);
                            /* Auditoría: inscripción a un curso */
                            Auditoria::registrar(Auditoria::ALTA, 'Inscripcion',
                                "Se inscribió a {$curso['NomMateria']} (curso #{$idCurso})");
                            $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Inscripción realizada correctamente.'];
                            $this->redirect('index.php?controller=alumno&action=misCursos');
                        } catch (PDOException $e) {
                            $error = 'Ya estás inscripto en este curso o hubo un error.';
                        }
                    }
                }
            }
        }

        /* Carga solo cursos de la carrera y año del alumno, con el conteo de correlativas pendientes */
        $cursos = $this->cursoModel->getCursosDisponibles($dni, $codCarrera, $anioMax);
        $this->render('alumno/inscribir_curso', compact('cursos', 'error', 'anioMax'));
    }

    /**
     * Da de baja al alumno de un curso en estado Activo.
     * Solo acepta POST para evitar bajas accidentales por GET.
     */
    public function darDeBaja(): void {
        requierePermiso('ver_cursos_propios');
        $dni           = (int)$_SESSION['usuario']['DNI'];
        $idInscripcion = (int)($_POST['IDInscripcion'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $idInscripcion) {
            $ok = $this->cursoModel->darDeBaja($idInscripcion, $dni);
            if ($ok) {
                /* Auditoría: baja de una inscripción por parte del alumno */
                Auditoria::registrar(Auditoria::BAJA, 'Inscripcion',
                    "Se dio de baja de la inscripción #{$idInscripcion}");
                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Te diste de baja del curso correctamente.'];
            } else {
                /* No se actualizó ninguna fila: inscripción no encontrada, no es Activo, o no es del alumno */
                $_SESSION['mensaje'] = ['tipo' => 'warning', 'texto' => 'No se pudo dar de baja. El curso puede no estar activo.'];
            }
        }

        $this->redirect('index.php?controller=alumno&action=misCursos');
    }
}
