<?php
/**
 * ============================================================
 *  SEED COMPLETO — AcademiSys
 * ============================================================
 *  Vacía las tablas transaccionales y regenera TODO el cuerpo de datos
 *  de forma coherente con las reglas académicas del sistema:
 *
 *    - Cada alumno pertenece a una carrera y tiene un año de ingreso.
 *    - Su "año académico" se deriva del ingreso (igual que en la app).
 *    - Solo se inscribe a materias de su año o anteriores.
 *    - Se respetan las CORRELATIVAS: una materia se aprueba solo si sus
 *      correlativas ya están aprobadas.
 *    - Las materias desaprobadas quedan "Libre" y se recursan al año
 *      siguiente (segunda inscripción que termina Aprobada).
 *    - Las notas son consistentes con el estado (Aprobado/Libre/Activo).
 *    - La asistencia se genera solo para las cursadas en curso (2026).
 *    - Quien aprobó TODAS las materias de su carrera recibe el título.
 *
 *  El CATÁLOGO se conserva (carrera, materia, correlativa, docente, aula,
 *  rol, permiso, rol_permiso y los usuarios docente/admin). Solo se
 *  regenera lo transaccional.
 *
 *  CÓMO EJECUTAR (solo por consola, a propósito):
 *      php seed_completo.php
 *
 *  Es un script de desarrollo: BORRA datos. Por seguridad solo corre desde
 *  la línea de comandos, para no poder ejecutarlo por accidente desde el navegador.
 * ============================================================
 */

/* Candado de seguridad: este script borra datos, así que solo se permite
 * ejecutarlo desde la consola (CLI). Si alguien entra a la URL en el navegador,
 * no hace nada y muestra el aviso. */
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
    exit("Este script solo puede ejecutarse desde la consola:  php seed_completo.php\n");
}

require_once __DIR__ . '/config/database.php';

/* Semilla fija para que cada ejecución genere el mismo set (reproducible) */
mt_srand(42);

$db = Database::getConnection();

/* Año lectivo actual: define qué cursadas están "en curso" */
const ANIO_ACTUAL = 2026;

/* Contraseña única para todos los alumnos generados (hash bcrypt) */
const PASS_ALUMNOS = 'alumno123';
$hashAlumnos = password_hash(PASS_ALUMNOS, PASSWORD_BCRYPT);

/* Pools de nombres y apellidos para componer alumnos variados */
$NOMBRES   = ['Lucía','Mateo','Valentina','Benjamín','Martina','Thiago','Emma','Joaquín','Catalina','Bautista',
              'Renata','Lautaro','Isabella','Santino','Mía','Dante','Olivia','Felipe','Julieta','Tomás',
              'Victoria','Bruno','Delfina','Lorenzo','Camila','Gael','Pilar','Ramiro','Abril','Ignacio'];
$APELLIDOS = ['Gómez','Fernández','Rodríguez','López','Martínez','Pérez','García','Sánchez','Romero','Torres',
              'Ruiz','Díaz','Álvarez','Moreno','Muñoz','Suárez','Gutiérrez','Castro','Ortiz','Núñez',
              'Cabrera','Rojas','Molina','Silva','Vega','Acosta','Medina','Herrera','Aguirre','Ríos'];
$CIUDADES  = [['Buenos Aires','Buenos Aires','1000'],['La Plata','Buenos Aires','1900'],
              ['Córdoba','Córdoba','5000'],['Rosario','Santa Fe','2000'],['Mendoza','Mendoza','5500'],
              ['Mar del Plata','Buenos Aires','7600'],['San Miguel','Buenos Aires','1663']];
$CALLES    = ['Av. Rivadavia','Calle 50','San Martín','Belgrano','Mitre','Sarmiento','Av. Colón','Las Heras'];

echo "=== SEED COMPLETO AcademiSys ===\n\n";

try {
    // ============================================================
    // 1. VACIAR TABLAS TRANSACCIONALES
    // ============================================================
    // Nota: TRUNCATE es DDL y provoca un commit implícito en MySQL, por eso
    // el vaciado va FUERA de la transacción. La transacción se abre después,
    // solo para los INSERT de datos (que sí deben ser atómicos).
    echo "Vaciando tablas transaccionales...\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    /* TRUNCATE resetea el AUTO_INCREMENT (deja IDs limpios desde 1) */
    foreach (['asistencia','evaluacion','inscripcion','cursohorario','curso',
              'tituloobtenido','auditinscripcion','auditoria','alumno','direccion'] as $t) {
        $db->exec("TRUNCATE TABLE `{$t}`");
    }
    /* De usuario borramos SOLO los alumnos (IDRol=1); docentes y admin se conservan */
    $db->exec("DELETE FROM usuario WHERE IDRol = 1");

    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    // ============================================================
    // 2. LEER EL CATÁLOGO EXISTENTE
    // ============================================================
    echo "Leyendo catálogo (carreras, materias, correlativas, docentes, aulas)...\n";

    /* Carreras con su duración en años */
    $carreras = [];
    foreach ($db->query("SELECT CodCarrera, NomCarrera, DurAnios FROM Carrera") as $c) {
        $carreras[$c['CodCarrera']] = $c;
    }

    /* Materias activas agrupadas por carrera y año: $materiasPorCarrera[cod][anio] = [materias...] */
    $materiasPorCarrera = [];
    $todasLasMaterias   = [];
    $sqlMat = "SELECT CodMateria, NomMateria, CodCarrera, Anio
                 FROM Materia
                WHERE Activo = 1 AND CodCarrera IS NOT NULL AND Anio IS NOT NULL
                ORDER BY CodCarrera, Anio, CodMateria";
    foreach ($db->query($sqlMat) as $m) {
        $materiasPorCarrera[$m['CodCarrera']][(int)$m['Anio']][] = $m;
        $todasLasMaterias[$m['CodMateria']] = $m;
    }

    /* ----------------------------------------------------------------
     * Correlativas "con sentido" para las carreras con plan completo.
     * Cada par [Materia, Requiere] significa que Materia necesita tener
     * aprobada Requiere. Todas apuntan a materias de un año anterior,
     * así el régimen de correlatividades es siempre cumplible en orden.
     * Se insertan con INSERT IGNORE: no duplican las que ya existan
     * (p. ej. las de ING que ya venían en el catálogo).
     * ---------------------------------------------------------------- */
    $correlativasPlan = [
        // ISI — Ingeniería en Sistemas de Información
        ['ISI-201','ISI-101'], ['ISI-203','ISI-103'], ['ISI-204','ISI-103'],
        ['ISI-301','ISI-204'], ['ISI-303','ISI-203'], ['ISI-304','ISI-203'],
        ['ISI-305','ISI-203'], ['ISI-401','ISI-202'], ['ISI-401','ISI-203'],
        ['ISI-404','ISI-303'], ['ISI-405','ISI-205'], ['ISI-405','ISI-302'],
        ['ISI-501','ISI-404'], ['ISI-504','ISI-404'], ['ISI-504','ISI-403'],
        // LIS — Licenciatura en Sistemas
        ['LIS-201','LIS-101'], ['LIS-203','LIS-103'], ['LIS-205','LIS-103'],
        ['LIS-303','LIS-205'], ['LIS-301','LIS-203'], ['LIS-305','LIS-203'],
        ['LIS-401','LIS-202'], ['LIS-404','LIS-204'], ['LIS-404','LIS-302'],
        ['LIS-501','LIS-301'], ['LIS-504','LIS-501'], ['LIS-504','LIS-402'],
        // TUS — Tecnicatura Universitaria en Sistemas
        ['TUS-201','TUS-102'], ['TUS-202','TUS-102'], ['TUS-204','TUS-104'],
        ['TUS-205','TUS-102'], ['TUS-301','TUS-204'], ['TUS-302','TUS-203'],
        ['TUS-303','TUS-201'], ['TUS-304','TUS-202'], ['TUS-304','TUS-205'],
        // LAD — Licenciatura en Administración
        ['LAD-201','LAD-101'], ['LAD-202','LAD-102'], ['LAD-301','LAD-104'],
        ['LAD-303','LAD-202'], ['LAD-401','LAD-104'], ['LAD-402','LAD-201'],
        ['LAD-404','LAD-401'], ['LAD-404','LAD-302'],
        // CP — Contador Público (complementa la que ya existía)
        ['CP-201','CP-101'], ['CP-202','CP-102'], ['CP-301','CP-201'],
        ['CP-303','CP-301'], ['CP-402','CP-303'], ['CP-503','CP-402'],
    ];
    $insCorr = $db->prepare("INSERT IGNORE INTO Correlativa (CodMateria, CodCorrelativa) VALUES (?,?)");
    foreach ($correlativasPlan as $cc) $insCorr->execute($cc);

    /* Limpieza de catálogo: elimina correlativas IMPOSIBLES, donde la materia requerida
     * es de un año POSTERIOR a la que la exige (nunca se podrían cumplir y dejarían
     * materias inaccesibles). Corrige inconsistencias heredadas del dump original. */
    $db->exec("
        DELETE co FROM Correlativa co
        JOIN Materia m1 ON m1.CodMateria = co.CodMateria
        JOIN Materia m2 ON m2.CodMateria = co.CodCorrelativa
        WHERE m2.Anio > m1.Anio
    ");

    /* Mapa de correlativas: $correl[CodMateria] = [CodCorrelativa, ...] (ya incluye las recién insertadas) */
    $correl = [];
    /* Conjunto de materias que SON correlativa de otra (prerrequisitos) */
    $esPrerequisito = [];
    foreach ($db->query("SELECT CodMateria, CodCorrelativa FROM Correlativa") as $co) {
        $correl[$co['CodMateria']][] = $co['CodCorrelativa'];
        $esPrerequisito[$co['CodCorrelativa']] = true;
    }

    /* Legajos de docentes y IDs de aulas para asignar a los cursos */
    $docentes = $db->query("SELECT Legajo FROM Docente WHERE Activo = 1")->fetchAll(PDO::FETCH_COLUMN);
    $aulas    = $db->query("SELECT IDAula FROM Aula")->fetchAll(PDO::FETCH_COLUMN);

    /* Solo trabajamos con carreras que tengan al menos una materia cargada */
    $carrerasConPlan = array_keys($materiasPorCarrera);

    // ============================================================
    // 3. PREPARAR SENTENCIAS (se reutilizan en los bucles)
    // ============================================================
    /* Recién acá abrimos la transacción: de aquí en más son todos INSERT.
     * Si algo falla, se revierte todo el cuerpo de datos de un saque. */
    $db->beginTransaction();

    $insDireccion  = $db->prepare("INSERT INTO Direccion (Calle, Numero, Ciudad, Provincia, CP) VALUES (?,?,?,?,?)");
    $insAlumno     = $db->prepare("INSERT INTO Alumno (DNI, Nombre, Apellido, FechaNacimiento, Telefono, Email, FechaIngreso, IDDireccion, Activo, CodCarrera) VALUES (?,?,?,?,?,?,?,?,1,?)");
    $insUsuario    = $db->prepare("INSERT INTO Usuario (Email, Password, IDRol, DNI, Estado, CreadoEn) VALUES (?,?,1,?,1,?)");
    $insCurso      = $db->prepare("INSERT INTO Curso (AnioLectivo, CodMateria, Legajo, IDAula, Activo) VALUES (?,?,?,?,?)");
    $insHorario    = $db->prepare("INSERT INTO CursoHorario (IDCurso, Dia, HoraInicio, HoraFin) VALUES (?,?,?,?)");
    $insInscrip    = $db->prepare("INSERT INTO Inscripcion (FechaInscripcion, Estado, DNI, IDCurso) VALUES (?,?,?,?)");
    $insEval       = $db->prepare("INSERT INTO Evaluacion (IDInscripcion, Tipo, Nota, Fecha, Instancia) VALUES (?,?,?,?,?)");
    $insAsist      = $db->prepare("INSERT INTO Asistencia (IDInscripcion, Fecha, Presente, Observaciones) VALUES (?,?,?,?)");
    $insTitulo     = $db->prepare("INSERT INTO TituloObtenido (DNI, CodCarrera, FechaEgreso, PromedioFinal, LibroTitulo, FolioTitulo) VALUES (?,?,?,?,?,?)");

    // ============================================================
    // 4. CREAR CURSOS (catálogo de comisiones)
    // ============================================================
    // getCurso() devuelve el IDCurso de una materia en un año lectivo dado,
    // creándolo (con docente, aula y horario) la primera vez que se pide.
    // Así solo se generan los cursos que realmente se usan + los de 2026.
    $cursoCache = [];   // $cursoCache["MAT001-2026"] = IDCurso
    $cnt = 0;           // contador para rotar docentes/aulas
    $DIAS    = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    $INICIOS = ['08:00', '10:00', '13:00', '15:00', '17:00'];   // horas de inicio posibles
    /* Patrones de carga semanal: cada número es un encuentro de N horas.
     * Ej: [2,2] = dos encuentros de 2hs (4hs/semana). La carga horaria total
     * la calcula la app multiplicando las horas semanales por las semanas del año. */
    $PATRONES = [[2], [3], [4], [2, 2], [3, 2], [2, 3]];

    $getCurso = function(string $codMateria, int $lectivo)
                use (&$cursoCache, &$cnt, $insCurso, $insHorario, $docentes, $aulas, $DIAS, $INICIOS, $PATRONES, $db): int {
        $key = "{$codMateria}-{$lectivo}";
        if (isset($cursoCache[$key])) return $cursoCache[$key];

        /* Activo solo si es del año lectivo actual (los pasados quedan cerrados) */
        $activo  = ($lectivo === ANIO_ACTUAL) ? 1 : 0;
        $legajo  = $docentes[$cnt % count($docentes)];
        $idAula  = $aulas[$cnt % count($aulas)];
        $insCurso->execute([$lectivo, $codMateria, $legajo, $idAula, $activo]);
        $idCurso = (int)$db->lastInsertId();

        /* El horario depende de la MATERIA (no del curso), así la misma materia
         * tiene siempre la misma carga horaria aunque se dicte en años distintos.
         * Se elige de forma determinística con un hash del código de materia. */
        $h       = abs(crc32($codMateria));
        $patron  = $PATRONES[$h % count($PATRONES)];          // cuántos encuentros y de cuántas horas
        $diaBase = $h % count($DIAS);                          // día de inicio
        $iniBase = intdiv($h, 8) % count($INICIOS);            // franja de inicio

        foreach ($patron as $idx => $horas) {
            /* Cada encuentro va en un día distinto (separados para no superponerse) */
            $dia   = $DIAS[($diaBase + $idx * 2) % count($DIAS)];
            $ini   = $INICIOS[($iniBase + $idx) % count($INICIOS)];
            $hIni  = $ini . ':00';
            /* Hora de fin = inicio + duración del encuentro */
            $hFin  = sprintf('%02d:%s:00', (int)substr($ini, 0, 2) + $horas, substr($ini, 3, 2));
            $insHorario->execute([$idCurso, $dia, $hIni, $hFin]);
        }

        $cnt++;
        $cursoCache[$key] = $idCurso;
        return $idCurso;
    };

    /* Pre-creamos un curso 2026 (activo) para CADA materia, así la pantalla
     * "Inscribirme" siempre tiene comisiones disponibles para el alumno. */
    foreach ($todasLasMaterias as $cod => $m) {
        $getCurso($cod, ANIO_ACTUAL);
    }

    // ============================================================
    // 5. GENERAR ALUMNOS Y SU HISTORIA ACADÉMICA
    // ============================================================
    echo "Generando alumnos e historia académica...\n";

    $dniSeq   = 45000000;   // DNIs de alumnos a partir de acá (no chocan con docentes)
    $totAlum  = 0; $totInsc = 0; $totEval = 0; $totAsist = 0; $totTit = 0;

    /* Cuántos alumnos por carrera (más en las carreras con plan completo) */
    // Alumnos por cohorte (año de ingreso). Cuanto más alto, más poblados quedan los cursos.
    // Con ~6 cohortes por carrera, esto da aprox. este número de alumnos por año cursando
    // cada materia, así las comisiones del docente dejan de verse casi vacías.
    $alumnosPorCohorte = 7;

    $alumnosPorCarrera = [];
    foreach ($carrerasConPlan as $cod) {
        /* Proporcional a la cantidad de años con materias del plan, por la cantidad por cohorte */
        $aniosConMaterias = count($materiasPorCarrera[$cod]);
        $alumnosPorCarrera[$cod] = $aniosConMaterias * $alumnosPorCohorte;
    }

    foreach ($carrerasConPlan as $codCarrera) {
        $durCarrera = (int)$carreras[$codCarrera]['DurAnios'];

        for ($i = 0; $i < $alumnosPorCarrera[$codCarrera]; $i++) {

            // ---- Datos personales del alumno ----
            $dni     = $dniSeq++;
            $nombre  = $NOMBRES[mt_rand(0, count($NOMBRES) - 1)];
            $apellido= $APELLIDOS[mt_rand(0, count($APELLIDOS) - 1)];
            /* Email único usando el DNI como sufijo para no duplicar */
            $email   = strtolower(iconv('UTF-8','ASCII//TRANSLIT', "{$nombre}.{$apellido}")) ;
            $email   = preg_replace('/[^a-z.]/', '', $email) . substr((string)$dni, -3) . '@mail.com';

            /* Año de ingreso: repartido para tener alumnos de todos los años y egresados.
             * Rango: desde (actual - duración) hasta actual, con algunos ya egresados. */
            $ingreso = ANIO_ACTUAL - mt_rand(0, $durCarrera);    // 0..durCarrera años de antigüedad
            $fechaIngreso = "{$ingreso}-03-01";
            /* Fecha de nacimiento ~18-20 años antes del ingreso */
            $anioNac = $ingreso - mt_rand(18, 23);
            $fechaNac = sprintf('%d-%02d-%02d', $anioNac, mt_rand(1,12), mt_rand(1,28));
            $tel = '11' . mt_rand(30000000, 69999999);

            /* Dirección */
            $ciudad = $CIUDADES[mt_rand(0, count($CIUDADES) - 1)];
            $calle  = $CALLES[mt_rand(0, count($CALLES) - 1)];
            $insDireccion->execute([$calle, (string)mt_rand(100, 4999), $ciudad[0], $ciudad[1], $ciudad[2]]);
            $idDir = (int)$db->lastInsertId();

            /* Alumno + su usuario de acceso */
            $insAlumno->execute([$dni, $nombre, $apellido, $fechaNac, $tel, $email, $fechaIngreso, $idDir, $codCarrera]);
            $insUsuario->execute([$email, $hashAlumnos, $dni, "{$ingreso}-03-01 09:00:00"]);
            $totAlum++;

            // ---- Determinar año académico / egreso ----
            $aniosCursados = ANIO_ACTUAL - $ingreso;       // 0 = recién ingresa
            $egresado      = ($aniosCursados >= $durCarrera); // ya completó la carrera
            /* Año en curso (si no egresó): su año académico actual, topeado a la duración */
            $anioActualAcad = min($aniosCursados + 1, $durCarrera);

            /* Conjunto de materias aprobadas por este alumno (para chequear correlativas) */
            $aprobadas = [];
            /* Acumulador de notas finales para el promedio del título */
            $finales   = [];

            /* Hasta qué año procesar: si egresó, toda la carrera; si no, hasta su año actual */
            $anioTope = $egresado ? $durCarrera : $anioActualAcad;

            for ($anio = 1; $anio <= $anioTope; $anio++) {
                $lectivo   = $ingreso + $anio - 1;                 // año calendario en que cursó ese año
                $esActual  = (!$egresado && $anio === $anioActualAcad); // año que está cursando ahora
                $materias  = $materiasPorCarrera[$codCarrera][$anio] ?? [];

                foreach ($materias as $mat) {
                    $cod = $mat['CodMateria'];

                    /* Chequeo de correlativas: todas deben estar aprobadas */
                    $correlOK = true;
                    foreach ($correl[$cod] ?? [] as $req) {
                        if (!isset($aprobadas[$req])) { $correlOK = false; break; }
                    }
                    if (!$correlOK) {
                        /* No puede cursarla todavía: queda pendiente (sin inscripción) */
                        continue;
                    }

                    if ($esActual) {
                        /* ---- Materia del año en curso: la está cursando (Activo) ----
                         * Un 30% se deja SIN inscribir para que el alumno tenga
                         * cursos disponibles en la pantalla "Inscribirme". */
                        if (mt_rand(1, 100) <= 30) continue;

                        $idCurso = $getCurso($cod, $lectivo);     // curso 2026 activo
                        $insInscrip->execute(["{$lectivo}-03-10", 'Activo', $dni, $idCurso]);
                        $idInsc = (int)$db->lastInsertId();
                        $totInsc++;

                        /* Evaluaciones parciales ya rendidas a mitad de año */
                        crearEvaluacion($insEval, $idInsc, 'Trabajo Práctico', mt_rand(6,9),  "{$lectivo}-04-20", 1); $totEval++;
                        crearEvaluacion($insEval, $idInsc, 'Parcial',          mt_rand(5,9),  "{$lectivo}-05-22", 1); $totEval++;

                        /* Asistencia semanal (marzo a junio), ~85% presente */
                        $totAsist += generarAsistencia($insAsist, $idInsc, $lectivo);

                    } else {
                        /* ---- Materia de un año ya pasado: resultado final ----
                         * 80% aprobada directo / 15% libre-y-recursa / 5% según prerrequisito. */
                        $roll      = mt_rand(1, 100);
                        $idCurso   = $getCurso($cod, $lectivo);

                        if ($roll <= 80 || $egresado) {
                            /* Aprobada en el primer intento (los egresados aprueban todo) */
                            $insInscrip->execute(["{$lectivo}-03-10", 'Aprobado', $dni, $idCurso]);
                            $idInsc = (int)$db->lastInsertId();
                            $totInsc++;
                            crearEvaluacion($insEval, $idInsc, 'Parcial', mt_rand(6,9), "{$lectivo}-05-20", 1); $totEval++;
                            crearEvaluacion($insEval, $idInsc, 'Parcial', mt_rand(6,9), "{$lectivo}-09-18", 1); $totEval++;
                            $notaFinal = mt_rand(6,10);
                            crearEvaluacion($insEval, $idInsc, 'Final', $notaFinal, "{$lectivo}-11-25", 1); $totEval++;
                            $aprobadas[$cod] = true;
                            $finales[] = $notaFinal;

                        } elseif ($roll <= 95) {
                            /* Quedó Libre y RECURSA al año siguiente (si ese año existe) */
                            $insInscrip->execute(["{$lectivo}-03-10", 'Libre', $dni, $idCurso]);
                            $idInsc = (int)$db->lastInsertId();
                            $totInsc++;
                            crearEvaluacion($insEval, $idInsc, 'Parcial', mt_rand(2,5), "{$lectivo}-05-20", 1); $totEval++;
                            crearEvaluacion($insEval, $idInsc, 'Parcial', mt_rand(3,5), "{$lectivo}-09-18", 2); $totEval++;

                            /* Recursada al año lectivo siguiente */
                            $lectivoRec = $lectivo + 1;
                            $idCursoRec = $getCurso($cod, $lectivoRec);
                            if ($lectivoRec === ANIO_ACTUAL && !$egresado) {
                                /* La recursada cae en el año actual: la está cursando ahora */
                                $insInscrip->execute(["{$lectivoRec}-03-10", 'Activo', $dni, $idCursoRec]);
                                $idInscRec = (int)$db->lastInsertId();
                                $totInsc++;
                                crearEvaluacion($insEval, $idInscRec, 'Trabajo Práctico', mt_rand(6,8), "{$lectivoRec}-04-20", 1); $totEval++;
                                $totAsist += generarAsistencia($insAsist, $idInscRec, $lectivoRec);
                            } else {
                                /* Recursada ya cerrada: la aprobó en el segundo intento */
                                $insInscrip->execute(["{$lectivoRec}-03-10", 'Aprobado', $dni, $idCursoRec]);
                                $idInscRec = (int)$db->lastInsertId();
                                $totInsc++;
                                crearEvaluacion($insEval, $idInscRec, 'Parcial', mt_rand(6,8), "{$lectivoRec}-05-20", 1); $totEval++;
                                $notaFinal = mt_rand(6,9);
                                crearEvaluacion($insEval, $idInscRec, 'Final', $notaFinal, "{$lectivoRec}-11-25", 1); $totEval++;
                                $aprobadas[$cod] = true;
                                $finales[] = $notaFinal;
                            }

                        } else {
                            /* 5% restante */
                            if (isset($esPrerequisito[$cod])) {
                                /* Si es prerrequisito de otras, la aprueba (para no bloquear el avance) */
                                $insInscrip->execute(["{$lectivo}-03-10", 'Aprobado', $dni, $idCurso]);
                                $idInsc = (int)$db->lastInsertId();
                                $totInsc++;
                                $notaFinal = mt_rand(6,9);
                                crearEvaluacion($insEval, $idInsc, 'Final', $notaFinal, "{$lectivo}-11-25", 1); $totEval++;
                                $aprobadas[$cod] = true;
                                $finales[] = $notaFinal;
                            } else {
                                /* Materia que no traba a otras: queda genuinamente Libre/pendiente */
                                $insInscrip->execute(["{$lectivo}-03-10", 'Libre', $dni, $idCurso]);
                                $totInsc++;
                            }
                        }
                    }
                }
            }

            // ---- Título: si aprobó TODAS las materias de la carrera ----
            $totalMateriasCarrera = count(todasLasMateriasDeCarrera($materiasPorCarrera, $codCarrera));
            if ($egresado && count($aprobadas) >= $totalMateriasCarrera && $totalMateriasCarrera > 0) {
                $promedio   = $finales ? round(array_sum($finales) / count($finales), 2) : 7.00;
                $fechaEgreso = ($ingreso + $durCarrera) . "-12-15";
                $insTitulo->execute([$dni, $codCarrera, $fechaEgreso, $promedio,
                                     'L' . mt_rand(1,50), 'F' . mt_rand(1,300)]);
                $totTit++;
            }
        }
    }

    /* Registro de auditoría: dejamos constancia de la carga masiva */
    $db->prepare("INSERT INTO Auditoria (Accion, Entidad, Detalle, IP) VALUES ('ALTA','Sistema',?, ?)")
       ->execute(['Carga masiva de datos (seed_completo.php)', '127.0.0.1']);

    $db->commit();

    // ============================================================
    // 6. RESUMEN
    // ============================================================
    echo "\n=== CARGA FINALIZADA ===\n";
    echo "Alumnos creados......: {$totAlum}\n";
    echo "Cursos (comisiones)..: " . count($cursoCache) . "\n";
    echo "Inscripciones........: {$totInsc}\n";
    echo "Evaluaciones.........: {$totEval}\n";
    echo "Registros asistencia.: {$totAsist}\n";
    echo "Títulos otorgados....: {$totTit}\n";
    echo "\nAcceso de alumnos -> email del alumno + contraseña: '" . PASS_ALUMNOS . "'\n";
    echo "(Los usuarios docente y admin se mantuvieron sin cambios.)\n";

} catch (Throwable $e) {
    /* Si algo falló, revertimos todo para no dejar datos inconsistentes */
    if ($db->inTransaction()) $db->rollBack();
    echo "\nERROR: la carga se canceló y se revirtió.\n";
    echo $e->getMessage() . "\n";
}

// ============================================================
//  FUNCIONES AUXILIARES
// ============================================================

/** Inserta una evaluación reutilizando el statement preparado. */
function crearEvaluacion(PDOStatement $stmt, int $idInsc, string $tipo, $nota, string $fecha, int $instancia): void {
    $stmt->execute([$idInsc, $tipo, $nota, $fecha, $instancia]);
}

/**
 * Genera asistencia semanal (lunes) entre marzo y junio del año lectivo.
 * Marca ~85% de presentes. Devuelve cuántos registros insertó.
 */
function generarAsistencia(PDOStatement $stmt, int $idInsc, int $lectivo): int {
    $n = 0;
    /* Arranca el primer lunes de marzo y avanza de a 7 días, ~14 clases */
    $fecha = new DateTime("{$lectivo}-03-02");
    for ($semana = 0; $semana < 14; $semana++) {
        $presente = (mt_rand(1, 100) <= 85) ? 1 : 0;        // 85% presente
        $obs      = $presente ? null : (mt_rand(0,1) ? 'Justificada' : null);
        $stmt->execute([$idInsc, $fecha->format('Y-m-d'), $presente, $obs]);
        $fecha->modify('+7 days');
        $n++;
    }
    return $n;
}

/** Devuelve, en un único array plano, todas las materias de una carrera. */
function todasLasMateriasDeCarrera(array $materiasPorCarrera, string $codCarrera): array {
    $out = [];
    foreach ($materiasPorCarrera[$codCarrera] ?? [] as $anio => $lista) {
        foreach ($lista as $m) $out[$m['CodMateria']] = $m;
    }
    return $out;
}
