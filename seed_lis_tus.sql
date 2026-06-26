-- ============================================================
-- SEED: Materias y Cursos 2026 para LIS y TUS
-- Ejecutar en phpMyAdmin sobre la base academisys
-- PREREQUISITO: seed_carreras_materias_cursos.sql ya ejecutado
-- ============================================================
USE academisys;

-- ============================================================
-- 1. MATERIAS LIS — 5 años (22 materias)
-- ============================================================
INSERT IGNORE INTO Materia (CodMateria, NomMateria, CodCarrera, Anio, Activo) VALUES
  ('LIS-101', 'Cálculo I',                            'LIS', 1, 1),
  ('LIS-102', 'Álgebra Lineal',                       'LIS', 1, 1),
  ('LIS-103', 'Fundamentos de Programación',          'LIS', 1, 1),
  ('LIS-104', 'Arquitectura de Computadoras',         'LIS', 1, 1),
  ('LIS-201', 'Cálculo II',                           'LIS', 2, 1),
  ('LIS-202', 'Probabilidad y Estadística',           'LIS', 2, 1),
  ('LIS-203', 'Programación Orientada a Objetos',     'LIS', 2, 1),
  ('LIS-204', 'Sistemas Operativos',                  'LIS', 2, 1),
  ('LIS-205', 'Bases de Datos I',                     'LIS', 2, 1),
  ('LIS-301', 'Ingeniería de Software I',             'LIS', 3, 1),
  ('LIS-302', 'Redes de Computadoras',                'LIS', 3, 1),
  ('LIS-303', 'Bases de Datos II',                    'LIS', 3, 1),
  ('LIS-304', 'Lenguajes Formales y Autómatas',       'LIS', 3, 1),
  ('LIS-305', 'Análisis de Sistemas',                 'LIS', 3, 1),
  ('LIS-401', 'Inteligencia Artificial',              'LIS', 4, 1),
  ('LIS-402', 'Gestión de Proyectos de Software',     'LIS', 4, 1),
  ('LIS-403', 'Seguridad en Sistemas',                'LIS', 4, 1),
  ('LIS-404', 'Sistemas Distribuidos',                'LIS', 4, 1),
  ('LIS-501', 'Ingeniería de Software II',            'LIS', 5, 1),
  ('LIS-502', 'Legislación y Ética Informática',      'LIS', 5, 1),
  ('LIS-503', 'Emprendimiento en Tecnología',         'LIS', 5, 1),
  ('LIS-504', 'Trabajo Final de Licenciatura',        'LIS', 5, 1);

-- ============================================================
-- 2. MATERIAS TUS — 3 años (13 materias)
-- ============================================================
INSERT IGNORE INTO Materia (CodMateria, NomMateria, CodCarrera, Anio, Activo) VALUES
  ('TUS-101', 'Introducción a la Informática',        'TUS', 1, 1),
  ('TUS-102', 'Programación I',                       'TUS', 1, 1),
  ('TUS-103', 'Matemática para Sistemas',             'TUS', 1, 1),
  ('TUS-104', 'Introducción a las Redes',             'TUS', 1, 1),
  ('TUS-201', 'Programación II',                      'TUS', 2, 1),
  ('TUS-202', 'Bases de Datos',                       'TUS', 2, 1),
  ('TUS-203', 'Sistemas Operativos',                  'TUS', 2, 1),
  ('TUS-204', 'Redes y Comunicaciones',               'TUS', 2, 1),
  ('TUS-205', 'Programación Web',                     'TUS', 2, 1),
  ('TUS-301', 'Seguridad Informática',                'TUS', 3, 1),
  ('TUS-302', 'Administración de Sistemas',           'TUS', 3, 1),
  ('TUS-303', 'Gestión de Proyectos IT',              'TUS', 3, 1),
  ('TUS-304', 'Práctica Profesional Integradora',     'TUS', 3, 1);

-- ============================================================
-- 3. CURSOS 2026 para LIS y TUS
-- ============================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS SeedCursosLISTUS$$

CREATE PROCEDURE SeedCursosLISTUS()
BEGIN
    DECLARE done      INT DEFAULT 0;
    DECLARE v_cod     VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_idx     INT DEFAULT 0;
    DECLARE v_ndoc    INT;
    DECLARE v_naula   INT;
    DECLARE v_leg     INT;
    DECLARE v_aula    INT;
    DECLARE v_idcurso INT;
    DECLARE v_turno   INT;
    DECLARE v_dia1    VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_dia2    VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_hi      TIME;
    DECLARE v_hf      TIME;
    DECLARE v_off_doc  INT;
    DECLARE v_off_aula INT;

    SELECT COUNT(*) INTO v_ndoc  FROM Docente WHERE Activo = 1;
    SELECT COUNT(*) INTO v_naula FROM Aula;

    IF v_ndoc = 0 OR v_naula = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cargá al menos un docente y un aula antes de ejecutar este seed.';
    END IF;

    BEGIN
        DECLARE cur CURSOR FOR
            SELECT CodMateria FROM Materia
             WHERE Activo = 1 AND CodCarrera IN ('LIS', 'TUS')
             ORDER BY CodCarrera, Anio, NomMateria;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

        OPEN cur;
        loop_mat: LOOP
            FETCH cur INTO v_cod;
            IF done THEN LEAVE loop_mat; END IF;

            IF NOT EXISTS (
                SELECT 1 FROM Curso
                 WHERE CodMateria COLLATE utf8mb4_general_ci = v_cod COLLATE utf8mb4_general_ci
                   AND AnioLectivo = 2026
            ) THEN
                SET v_off_doc  = v_idx MOD v_ndoc;
                SET v_off_aula = v_idx MOD v_naula;

                SELECT Legajo INTO v_leg
                  FROM Docente WHERE Activo = 1
                  ORDER BY Legajo LIMIT 1 OFFSET v_off_doc;

                SELECT IDAula INTO v_aula
                  FROM Aula
                  ORDER BY IDAula LIMIT 1 OFFSET v_off_aula;

                INSERT INTO Curso (AnioLectivo, CodMateria, Legajo, IDAula, Activo)
                VALUES (2026, v_cod, v_leg, v_aula, 1);

                SET v_idcurso = LAST_INSERT_ID();
                SET v_turno   = v_idx MOD 6;

                IF    v_turno = 0 THEN SET v_dia1='Lunes',     v_dia2='Miércoles', v_hi='18:00:00', v_hf='20:00:00';
                ELSEIF v_turno = 1 THEN SET v_dia1='Martes',   v_dia2='Jueves',    v_hi='18:00:00', v_hf='20:00:00';
                ELSEIF v_turno = 2 THEN SET v_dia1='Lunes',    v_dia2='Miércoles', v_hi='20:00:00', v_hf='22:00:00';
                ELSEIF v_turno = 3 THEN SET v_dia1='Martes',   v_dia2='Jueves',    v_hi='20:00:00', v_hf='22:00:00';
                ELSEIF v_turno = 4 THEN SET v_dia1='Miércoles',v_dia2='Viernes',   v_hi='18:00:00', v_hf='20:00:00';
                ELSE                    SET v_dia1='Miércoles',v_dia2='Viernes',   v_hi='20:00:00', v_hf='22:00:00';
                END IF;

                INSERT INTO CursoHorario (IDCurso, Dia, HoraInicio, HoraFin) VALUES
                    (v_idcurso, v_dia1, v_hi, v_hf),
                    (v_idcurso, v_dia2, v_hi, v_hf);

                SET v_idx = v_idx + 1;
            END IF;
        END LOOP loop_mat;

        CLOSE cur;
    END;
END$$

DELIMITER ;

CALL SeedCursosLISTUS();
DROP PROCEDURE SeedCursosLISTUS;
