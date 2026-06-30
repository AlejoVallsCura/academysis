-- ============================================================
--  Migración: módulo de Auditoría
--  Ejecutar una sola vez sobre la base `academisys`.
--  Crea el log general de la aplicación y el permiso para verlo.
-- ============================================================

-- ------------------------------------------------------------
-- 1. Tabla de auditoría general (acciones a nivel aplicación)
-- ------------------------------------------------------------
-- Registra QUIÉN hizo QUÉ y CUÁNDO sobre la base de datos.
-- Complementa a `auditinscripcion`, que audita solo inscripciones a nivel trigger.
CREATE TABLE IF NOT EXISTS `auditoria` (
  `IDAuditoria` INT NOT NULL AUTO_INCREMENT,
  `Fecha`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,        -- Momento de la acción
  `IDUsuario`   INT          DEFAULT NULL,                              -- Usuario actor (NULL si no logueado, ej. login fallido)
  `Email`       VARCHAR(100) DEFAULT NULL,                             -- Snapshot del email del actor
  `Rol`         VARCHAR(30)  DEFAULT NULL,                             -- Rol del actor al momento de la acción
  `Accion`      VARCHAR(20)  NOT NULL,                                 -- LOGIN, LOGOUT, ALTA, MODIFICACION, BAJA, CONSULTA, ERROR
  `Entidad`     VARCHAR(50)  DEFAULT NULL,                             -- Tabla/entidad afectada (Alumno, Curso, Inscripcion...)
  `Detalle`     VARCHAR(255) DEFAULT NULL,                             -- Descripción legible de lo ocurrido
  `IP`          VARCHAR(45)  DEFAULT NULL,                             -- IP de origen (soporta IPv6)
  PRIMARY KEY (`IDAuditoria`),
  KEY `idx_aud_fecha`   (`Fecha`),
  KEY `idx_aud_usuario` (`IDUsuario`),
  KEY `idx_aud_accion`  (`Accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 2. Permiso para ver la auditoría
-- ------------------------------------------------------------
-- Se inserta con INSERT IGNORE para que sea reejecutable sin error si ya existe.
INSERT IGNORE INTO `permiso` (`IDPermiso`, `Codigo`, `Descripcion`, `Modulo`) VALUES
(20, 'ver_auditoria', 'Ver el registro de auditoría del sistema', 'auditoria');

-- ------------------------------------------------------------
-- 3. Asignar el permiso al rol admin (IDRol = 3)
-- ------------------------------------------------------------
INSERT IGNORE INTO `rol_permiso` (`IDRol`, `IDPermiso`) VALUES
(3, 20);
