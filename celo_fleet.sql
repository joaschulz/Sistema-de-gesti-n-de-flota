CREATE DATABASE IF NOT EXISTS celo_fleet;
USE celo_fleet;

-- Tabla de Vehículos
CREATE TABLE IF NOT EXISTS vehiculos (
    patente VARCHAR(10) PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    kilometraje INT DEFAULT 0,
    estado ENUM('Operativo', 'Alerta', 'Taller') NOT NULL DEFAULT 'Operativo',
    novedades TEXT NULL
);

-- Tabla de Intervenciones (Mantenimientos)
CREATE TABLE IF NOT EXISTS intervenciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patente_vehiculo VARCHAR(10) NOT NULL,
    tipo ENUM('Preventivo', 'Correctivo') NOT NULL,
    costo DECIMAL(10,2) DEFAULT 0.00,
    detalle TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patente_vehiculo) REFERENCES vehiculos(patente) ON DELETE CASCADE
);

-- Inserción de datos de prueba idénticos al HTML estático
INSERT INTO vehiculos (patente, marca, modelo, kilometraje, estado, novedades) VALUES
('AE 452 CD', 'Toyota', 'Hilux', 145000, 'Operativo', NULL),
('AF 001 XX', 'Toyota', 'Hilux', 12000, 'Operativo', NULL),
('AD 123 AB', 'Ford', 'Ranger', 85000, 'Alerta', 'Deuda de patente, Ruido en motor'),
('AC 998 ZZ', 'Volkswagen', 'Amarok', 110000, 'Taller', 'VTV Vencida, Cambio de embrague'),
('AB 555 MM', 'Ford', 'Ranger', 98000, 'Taller', 'Póliza vencida');