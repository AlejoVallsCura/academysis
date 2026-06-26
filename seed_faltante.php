<?php
/**
 * Seed: completa la BD después de importar el dump + seed_lis_tus.sql
 *
 * PREREQUISITOS:
 *   1. Importar academisys (4).sql en phpMyAdmin
 *   2. Importar seed_lis_tus.sql en phpMyAdmin
 * EJECUTAR: http://localhost/academisys/seed_faltante.php
 */

$pdo = new PDO('mysql:host=localhost;dbname=academisys;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

if ($pdo->query("SELECT 1 FROM Alumno WHERE DNI = 71000001")->fetchColumn()) {
    die('<pre>Ya ejecutado. Eliminá este archivo.</pre>');
}

$passHash    = password_hash('alumno123', PASSWORD_BCRYPT);
$idRolAlumno = $pdo->query("SELECT IDRol FROM Rol WHERE NombreRol = 'alumno' LIMIT 1")->fetchColumn();
if (!$idRolAlumno) die('<pre>Error: rol alumno no encontrado.</pre>');

// ──────────────────────────────────────────────────────────
//  Alumnos nuevos: ISI, LAD, CP
// ──────────────────────────────────────────────────────────
$nuevos = [
    // ISI
    ['DNI'=>71000001,'Nombre'=>'Lucas',  'Apellido'=>'Herrera',    'Nac'=>'2001-04-10','Tel'=>'1134000001','Email'=>'l.herrera@mail.com',   'Ingreso'=>'2024-03-01','Carrera'=>'ISI','Anio'=>3,'Calle'=>'Lavalle',    'Num'=>'1200','Ciudad'=>'Buenos Aires','Prov'=>'CABA','CP'=>'C1048'],
    ['DNI'=>71000002,'Nombre'=>'Ana',    'Apellido'=>'Molina',     'Nac'=>'2005-08-22','Tel'=>'1134000002','Email'=>'a.molina@mail.com',    'Ingreso'=>'2026-03-01','Carrera'=>'ISI','Anio'=>1,'Calle'=>'Corrientes',  'Num'=>'3400','Ciudad'=>'Buenos Aires','Prov'=>'CABA','CP'=>'C1193'],
    ['DNI'=>71000003,'Nombre'=>'Carlos', 'Apellido'=>'Benítez',    'Nac'=>'2000-11-05','Tel'=>'1134000003','Email'=>'c.benitez@mail.com',   'Ingreso'=>'2023-03-01','Carrera'=>'ISI','Anio'=>4,'Calle'=>'San Martín', 'Num'=>'560', 'Ciudad'=>'Buenos Aires','Prov'=>'CABA','CP'=>'C1004'],
    // LAD
    ['DNI'=>72000001,'Nombre'=>'María',  'Apellido'=>'Soto',       'Nac'=>'2002-06-15','Tel'=>'1145000001','Email'=>'m.soto@mail.com',     'Ingreso'=>'2025-03-01','Carrera'=>'LAD','Anio'=>2,'Calle'=>'Florida',      'Num'=>'890', 'Ciudad'=>'Buenos Aires','Prov'=>'CABA','CP'=>'C1005'],
    ['DNI'=>72000002,'Nombre'=>'Jorge',  'Apellido'=>'Palma',      'Nac'=>'2004-03-28','Tel'=>'1145000002','Email'=>'j.palma@mail.com',    'Ingreso'=>'2026-03-01','Carrera'=>'LAD','Anio'=>1,'Calle'=>'Tucumán',     'Num'=>'2100','Ciudad'=>'Buenos Aires','Prov'=>'CABA','CP'=>'C1049'],
    ['DNI'=>72000003,'Nombre'=>'Paula',  'Apellido'=>'Torres',     'Nac'=>'2000-09-03','Tel'=>'1145000003','Email'=>'p.torres@mail.com',   'Ingreso'=>'2023-03-01','Carrera'=>'LAD','Anio'=>4,'Calle'=>'Chacabuco',   'Num'=>'765', 'Ciudad'=>'Buenos Aires','Prov'=>'CABA','CP'=>'C1069'],
    // CP
    ['DNI'=>73000001,'Nombre'=>'Diego',  'Apellido'=>'Fernández',  'Nac'=>'2001-07-19','Tel'=>'1156000001','Email'=>'d.fernandez@mail.com','Ingreso'=>'2024-03-01','Carrera'=>'CP', 'Anio'=>3,'Calle'=>'Maipú',       'Num'=>'450', 'Ciudad'=>'Buenos Aires','Prov'=>'CABA','CP'=>'C1006'],
    ['DNI'=>73000002,'Nombre'=>'Laura',  'Apellido'=>'Ríos',       'Nac'=>'2005-01-11','Tel'=>'1156000002','Email'=>'l.rios@mail.com',     'Ingreso'=>'2026-03-01','Carrera'=>'CP', 'Anio'=>1,'Calle'=>'Perón',        'Num'=>'1750','Ciudad'=>'Buenos Aires','Prov'=>'CABA','CP'=>'C1038'],
    ['DNI'=>73000003,'Nombre'=>'Alberto','Apellido'=>'Ruiz',       'Nac'=>'1999-12-25','Tel'=>'1156000003','Email'=>'a.ruiz@mail.com',     'Ingreso'=>'2023-03-01','Carrera'=>'CP', 'Anio'=>4,'Calle'=>'Suipacha',    'Num'=>'330', 'Ciudad'=>'Buenos Aires','Prov'=>'CABA','CP'=>'C1008'],
];

$stmtDir   = $pdo->prepare("INSERT INTO Direccion (Calle,Numero,Ciudad,Provincia,CP) VALUES (?,?,?,?,?)");
$stmtAlum  = $pdo->prepare("INSERT IGNORE INTO Alumno (DNI,Nombre,Apellido,FechaNacimiento,Telefono,Email,FechaIngreso,IDDireccion,CodCarrera,Activo) VALUES (?,?,?,?,?,?,?,?,?,1)");
$stmtUsr   = $pdo->prepare("INSERT IGNORE INTO Usuario (Email,Password,IDRol,DNI,Estado) VALUES (?,?,?,?,1)");
$stmtInsc  = $pdo->prepare("INSERT IGNORE INTO Inscripcion (DNI,IDCurso,Estado,FechaInscripcion) VALUES (?,?,?,?)");
$stmtEval  = $pdo->prepare("INSERT INTO Evaluacion (IDInscripcion,Tipo,Nota,Fecha) VALUES (?,?,?,?)");
$stmtAsist = $pdo->prepare("INSERT IGNORE INTO Asistencia (IDInscripcion,Fecha,Presente,Observaciones) VALUES (?,?,?,NULL)");
$stmtCurso = $pdo->prepare("
    SELECT c.IDCurso, m.Anio
      FROM Curso c
      JOIN Materia m ON m.CodMateria = c.CodMateria
     WHERE c.AnioLectivo = 2026
       AND m.CodCarrera  = ?
       AND m.Anio        <= ?
     ORDER BY m.Anio, m.NomMateria
");

$creadosAlumnos = 0;
$totalInsc      = 0;

// ── Crear alumnos nuevos e inscribirlos ───────────────────
foreach ($nuevos as $a) {
    $stmtDir->execute([$a['Calle'],$a['Num'],$a['Ciudad'],$a['Prov'],$a['CP']]);
    $idDir = $pdo->lastInsertId();
    $stmtAlum->execute([$a['DNI'],$a['Nombre'],$a['Apellido'],$a['Nac'],$a['Tel'],$a['Email'],$a['Ingreso'],$idDir,$a['Carrera']]);
    $stmtUsr->execute([$a['Email'],$passHash,$idRolAlumno,$a['DNI']]);
    $creadosAlumnos++;

    $stmtCurso->execute([$a['Carrera'], $a['Anio']]);
    foreach ($stmtCurso->fetchAll() as $c) {
        $totalInsc += inscribir($pdo, $stmtInsc, $stmtEval, $stmtAsist, $a['DNI'], $c['IDCurso'], (int)$c['Anio'], $a['Anio']);
    }
}

// ── Inscribir alumnos LIS existentes en cursos LIS 2026 ──
$lisAlumnos = $pdo->query("SELECT DNI, FechaIngreso FROM Alumno WHERE CodCarrera = 'LIS'")->fetchAll();
foreach ($lisAlumnos as $a) {
    $anioActual = min(5, 2026 - (int)date('Y', strtotime($a['FechaIngreso'])) + 1);
    $stmtCurso->execute(['LIS', $anioActual]);
    foreach ($stmtCurso->fetchAll() as $c) {
        $totalInsc += inscribir($pdo, $stmtInsc, $stmtEval, $stmtAsist, $a['DNI'], $c['IDCurso'], (int)$c['Anio'], $anioActual);
    }
}

// ── Inscribir alumnos TUS existentes en cursos TUS 2026 ──
$tusAlumnos = $pdo->query("SELECT DNI, FechaIngreso FROM Alumno WHERE CodCarrera = 'TUS'")->fetchAll();
foreach ($tusAlumnos as $a) {
    $anioActual = min(3, 2026 - (int)date('Y', strtotime($a['FechaIngreso'])) + 1);
    $stmtCurso->execute(['TUS', $anioActual]);
    foreach ($stmtCurso->fetchAll() as $c) {
        $totalInsc += inscribir($pdo, $stmtInsc, $stmtEval, $stmtAsist, $a['DNI'], $c['IDCurso'], (int)$c['Anio'], $anioActual);
    }
}

echo "<pre style='font-family:monospace;padding:2rem;line-height:1.8'>
Seed completado.

  Alumnos creados  : $creadosAlumnos  (ISI x3, LAD x3, CP x3)
  Inscripciones    : $totalInsc
  Contraseña nueva : alumno123

  LIS/TUS alumnos inscriptos en sus cursos 2026.
  Podés eliminar este archivo ahora.
</pre>";

// ──────────────────────────────────────────────────────────
function inscribir(PDO $pdo, $stmtInsc, $stmtEval, $stmtAsist,
                   int $dni, int $idCurso, int $anioCurso, int $anioMax): int
{
    $esActual  = ($anioCurso === $anioMax);
    $estado    = $esActual ? 'Activo' : 'Aprobado';
    $anioCal   = 2026 - ($anioMax - $anioCurso);
    $fechaInsc = "$anioCal-03-01";

    $stmtInsc->execute([$dni, $idCurso, $estado, $fechaInsc]);
    $idInsc = (int)$pdo->lastInsertId();
    if ($idInsc === 0) return 0;

    if (!$esActual) {
        $stmtEval->execute([$idInsc, 'Parcial', rand(6,10), "$anioCal-04-20"]);
        $stmtEval->execute([$idInsc, 'Parcial', rand(6,10), "$anioCal-06-10"]);
        $stmtEval->execute([$idInsc, 'Final',   rand(6,10), "$anioCal-07-15"]);
    } else {
        $stmtEval->execute([$idInsc, 'Parcial', rand(4,10), '2026-04-20']);
        if (rand(0,1)) {
            $stmtEval->execute([$idInsc, 'Parcial', rand(4,10), '2026-06-10']);
        }
    }

    $fecha = new DateTime("$anioCal-03-10");
    for ($i = 0; $i < 10; $i++) {
        $presente = (rand(1,10) <= 8) ? 1 : 0;
        $stmtAsist->execute([$idInsc, $fecha->format('Y-m-d'), $presente]);
        $fecha->modify('+7 days');
    }

    return 1;
}
