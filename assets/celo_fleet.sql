-- 1. CREACIÓN DE LA BASE DE DATOS
DROP DATABASE IF EXISTS celo_fleet;
CREATE DATABASE celo_fleet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE celo_fleet;

-- 2. TABLA: ROL
CREATE TABLE ROL (
  ID_rol INT AUTO_INCREMENT NOT NULL,
  nombre_rol VARCHAR(50) NOT NULL,
  permisos TEXT NOT NULL,
  PRIMARY KEY (ID_rol)
);

-- 3. TABLA: VEHICULO
CREATE TABLE VEHICULO (
  patente VARCHAR(15) NOT NULL,
  marca VARCHAR(50) NOT NULL,
  modelo VARCHAR(50) NOT NULL,
  estado ENUM('Operativo', 'Alerta', 'Taller') NOT NULL DEFAULT 'Operativo',
  kilometraje INT NOT NULL DEFAULT 0,
  novedades TEXT NULL DEFAULT NULL,
  PRIMARY KEY (patente)
);

-- 4. TABLA: USUARIO
CREATE TABLE USUARIO (
  ID_usuario INT AUTO_INCREMENT NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  legajo INT NOT NULL UNIQUE,
  nombre VARCHAR(50) NOT NULL,
  apellido VARCHAR(50) NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  estado ENUM('Activo', 'Suspendido') NOT NULL DEFAULT 'Activo',
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultimo_acceso DATETIME NULL DEFAULT NULL,
  ID_rol INT NOT NULL,
  PRIMARY KEY (ID_usuario),
  FOREIGN KEY (ID_rol) REFERENCES ROL(ID_rol)
);

-- 5. TABLA: INTERVENCION_TECNICA
CREATE TABLE INTERVENCION_TECNICA (
  ID_intervencion INT AUTO_INCREMENT NOT NULL,
  fecha_inicio DATETIME NOT NULL,
  costo DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  fecha_fin DATETIME NULL,
  tipo_mantenimiento ENUM('Preventivo', 'Correctivo', 'Siniestro') NOT NULL,
  tipo_falla VARCHAR(100) NOT NULL,
  detalles TEXT NOT NULL,
  ID_usuario INT NOT NULL,
  patente VARCHAR(15) NOT NULL,
  PRIMARY KEY (ID_intervencion),
  FOREIGN KEY (ID_usuario) REFERENCES USUARIO(ID_usuario),
  FOREIGN KEY (patente) REFERENCES VEHICULO(patente)
);

-- 6. TABLA: EVIDENCIA_DIGITAL
CREATE TABLE EVIDENCIA_DIGITAL (
  ID_evidencia INT AUTO_INCREMENT NOT NULL,
  url_archivo VARCHAR(255) NOT NULL,
  tipo_archivo VARCHAR(50) NOT NULL,
  ID_intervencion INT NULL,
  ID_carga INT NULL,
  PRIMARY KEY (ID_evidencia),
  FOREIGN KEY (ID_intervencion) REFERENCES INTERVENCION_TECNICA(ID_intervencion)
  -- Descomentar las siguientes líneas cuando se creen las tablas respectivas:
  -- FOREIGN KEY (ID_reporte) REFERENCES REPORTE_NOVEDAD(ID_reporte),
  -- FOREIGN KEY (ID_carga) REFERENCES CARGA_COMBUSTIBLE(ID_carga)
);


-- ==========================================
-- DATOS DE PRUEBA INICIALES (MOCKS)
-- ==========================================

-- Roles
INSERT INTO ROL (nombre_rol, permisos) VALUES 
('JefeTaller', 'ALL_MAINTENANCE'),
('Chofer', 'READ_VEHICLES, WRITE_REPORTS'),
('Admin', 'ALL_SYSTEM'),
('PersonalIT', 'SYSTEM_SUPPORT');

-- Vehículos
INSERT INTO VEHICULO (patente, marca, modelo, estado, kilometraje, novedades) VALUES 
('AA001BB', 'Ford', 'Ranger XL 2.2', 'Operativo', 125000, NULL),
('AC222DD', 'Chevrolet', 'S10 LS', 'Alerta', 158000, 'Ruido en la suspensión delantera derecha y dirección desviada'),
('AE333FF', 'Mercedes-Benz', 'Sprinter 311 CDI', 'Operativo', 210000, NULL),
('AG444HH', 'Toyota', 'Hilux SW4', 'Taller', 95000, 'Pérdida de líquido refrigerante e indicador de temperatura al límite'),
('OQ555PP', 'Peugeot', 'Partner Furgón', 'Operativo', 88000, NULL),
('AF666ZZ', 'Volkswagen', 'Saveiro Cabina Simple', 'Alerta', 132000, 'Luz de check engine encendida y pérdida de potencia en baja'),
('AD777XX', 'Fiat', 'Fiorino', 'Operativo', 112000, NULL),
('AB888YY', 'Ford', 'Transit Furgón Largo', 'Taller', 185000, 'Desgaste excesivo en pastillas de freno traseras y vibración'),
('AA999WW', 'Renault', 'Kangoo Express', 'Operativo', 143000, NULL),
('AE111QQ', 'Toyota', 'Hiace Furgón', 'Operativo', 76000, NULL);


-- Usuarios
INSERT INTO USUARIO (usuario, legajo, nombre, apellido, contrasena, estado, ID_rol) VALUES 
('jefe_pedro', 1001, 'Pedro', 'Gómez', '$2y$10$N2V9Q4V3wVdYx.s720P9HObVXX4TXYn4P/b.2Z8L2o9910H1r3G6O', 'Activo', 1), -- JefeTaller
('chofer_juan', 1002, 'Juan', 'Pérez', '$2y$10$N2V9Q4V3wVdYx.s720P9HObVXX4TXYn4P/b.2Z8L2o9910H1r3G6O', 'Activo', 2), -- Chofer
('admin_celo', 1003, 'Ana', 'López', '$2y$10$N2V9Q4V3wVdYx.s720P9HObVXX4TXYn4P/b.2Z8L2o9910H1r3G6O', 'Activo', 3), -- Admin
('it_soporte', 1004, 'Laura', 'Martinez', '$2y$10$N2V9Q4V3wVdYx.s720P9HObVXX4TXYn4P/b.2Z8L2o9910H1r3G6O', 'Activo', 4); -- IT

-- Intervenciones Técnicas
INSERT INTO INTERVENCION_TECNICA (fecha_inicio, costo, fecha_fin, tipo_mantenimiento, tipo_falla, detalles, ID_usuario, patente) VALUES 
('2026-06-25 08:30:00', 45000.50, '2026-06-25 14:00:00', 'Correctivo', 'Mecánica - Frenos', 'Cambio de pastillas de freno delanteras', 1, 'AG444HH');

-- Evidencia Digital
INSERT INTO EVIDENCIA_DIGITAL (url_archivo, tipo_archivo, ID_intervencion) VALUES 
('assets/uploads/factura_frenos_001.jpg', 'image/jpeg', 1),
('assets/uploads/foto_reparacion_ag444hh.png', 'image/png', 1);