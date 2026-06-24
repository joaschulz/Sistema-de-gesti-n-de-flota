-- 1. CREACIÓN DE LA BASE DE DATOS
DROP DATABASE IF EXISTS celo_fleet;
CREATE DATABASE celo_fleet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE celo_fleet;

-- 2. TABLA PRINCIPAL: VEHÍCULOS
-- Almacena el estado en tiempo real para el tablero Kanban
CREATE TABLE vehiculos (
    patente VARCHAR(15) PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    kilometraje INT NOT NULL DEFAULT 0,
    estado ENUM('Operativo', 'Alerta', 'Taller') NOT NULL DEFAULT 'Operativo',
    novedades TEXT NULL DEFAULT NULL,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. TABLA DE HISTORIAL: INTERVENCIONES
-- Aquí es donde fallaba tu sistema. Esta tabla guarda el registro cada vez que un auto entra al taller.
CREATE TABLE intervenciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patente_vehiculo VARCHAR(15) NOT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'Correctivo', -- Ej: Preventivo, Correctivo
    costo DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    detalle TEXT NOT NULL,
    evidencias TEXT NULL, -- Guardará el string de nombres de archivo separados por comas
    fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Clave foránea: Vincula la intervención con el vehículo.
    -- Si se actualiza la patente del vehículo, se actualiza aquí (CASCADE)
    CONSTRAINT fk_vehiculo_intervencion 
        FOREIGN KEY (patente_vehiculo) 
        REFERENCES vehiculos(patente) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
);

-- ==========================================
-- DATOS DE PRUEBA (Opcional, para poblar tu Tablero Kanban)
-- ==========================================

INSERT INTO vehiculos (patente, marca, modelo, kilometraje, estado, novedades) VALUES 
('AB123CD', 'Toyota', 'Hilux 2.4 TDI', 45000, 'Operativo', NULL),
('EF456GH', 'Ford', 'Ranger XLT', 125000, 'Operativo', NULL),
('IJ789KL', 'Volkswagen', 'Amarok V6', 89000, 'Alerta', 'Ruido extraño en la suspensión delantera al girar'),
('MN012OP', 'Chevrolet', 'S10 High Country', 210000, 'Alerta', 'Testigo de check engine encendido'),
('QR345ST', 'Renault', 'Kangoo Stepway', 15000, 'Taller', NULL);

-- Simulamos que la Kangoo ya tiene una intervención registrada en el taller
INSERT INTO intervenciones (patente_vehiculo, tipo, costo, detalle, evidencias) VALUES 
('QR345ST', 'Correctivo', 45000.50, 'Cambio de bomba de agua y correa de distribución', 'foto_bomba.jpg,presupuesto.pdf');