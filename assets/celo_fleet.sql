-- 1. CREACIÓN DE LA BASE DE DATOS
DROP DATABASE IF EXISTS celo_fleet;
CREATE DATABASE celo_fleet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE celo_fleet;

-- 2. TABLA: USUARIOS (Control de Acceso y RBAC)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('JefeTaller', 'PersonalCampo', 'Admin', 'PersonalIT') NOT NULL DEFAULT 'PersonalCampo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. TABLA PRINCIPAL: VEHÍCULOS
CREATE TABLE vehiculos (
    patente VARCHAR(15) PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    kilometraje INT NOT NULL DEFAULT 0,
    estado ENUM('Operativo', 'Alerta', 'Taller') NOT NULL DEFAULT 'Operativo',
    novedades TEXT NULL DEFAULT NULL,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. TABLA DE HISTORIAL: INTERVENCIONES
CREATE TABLE intervenciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patente_vehiculo VARCHAR(15) NOT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'Correctivo', -- Ej: Preventivo, Correctivo, Siniestro
    costo DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    detalle TEXT NOT NULL,
    evidencias TEXT NULL, -- Guardará el string de nombres de archivo separados por comas
    fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_vehiculo_intervencion 
        FOREIGN KEY (patente_vehiculo) 
        REFERENCES vehiculos(patente) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
);

-- ==========================================
-- DATOS DE PRUEBA INICIALES
-- ==========================================

-- Usuarios de prueba. La contraseña para todos es: password123
INSERT INTO usuarios (usuario, password, rol) VALUES 
('chofer_juan', '$2y$10$N2V9Q4V3wVdYx.s720P9HObVXX4TXYn4P/b.2Z8L2o9910H1r3G6O', 'PersonalCampo'),
('jefe_pedro', '$2y$10$N2V9Q4V3wVdYx.s720P9HObVXX4TXYn4P/b.2Z8L2o9910H1r3G6O', 'JefeTaller'),
('admin_celo', '$2y$10$N2V9Q4V3wVdYx.s720P9HObVXX4TXYn4P/b.2Z8L2o9910H1r3G6O', 'Admin'),
('it_soporte', '$2y$10$N2V9Q4V3wVdYx.s720P9HObVXX4TXYn4P/b.2Z8L2o9910H1r3G6O', 'PersonalIT');

INSERT INTO vehiculos (patente, marca, modelo, kilometraje, estado, novedades) VALUES 
('AB123CD', 'Toyota', 'Hilux 2.4 TDI', 45000, 'Operativo', NULL),
('EF456GH', 'Ford', 'Ranger XLT', 125000, 'Operativo', NULL),
('IJ789KL', 'Volkswagen', 'Amarok V6', 89000, 'Alerta', 'Ruido extraño en la suspensión delantera al girar'),
('MN012OP', 'Chevrolet', 'S10 High Country', 210000, 'Alerta', 'Testigo de check engine encendido'),
('QR345ST', 'Renault', 'Alaskan', 32000, 'Taller', 'Cambio de correa de distribución pendiente');

-- 1. Alteración de la estructura física del modelo para soportar auditoría y control de estados
ALTER TABLE usuarios 
ADD COLUMN estado ENUM('Activo', 'Suspendido') NOT NULL DEFAULT 'Activo',
ADD COLUMN ultimo_acceso DATETIME NULL DEFAULT NULL;

-- 2. Sincronización de los usuarios de prueba para garantizar consistencia en la grilla
UPDATE usuarios SET ultimo_acceso = NOW() WHERE usuario = 'it_soporte';
UPDATE usuarios SET ultimo_acceso = '2026-06-25 18:34:12' WHERE usuario = 'jefe_pedro';
UPDATE usuarios SET ultimo_acceso = '2026-06-26 09:15:00' WHERE usuario = 'chofer_juan';