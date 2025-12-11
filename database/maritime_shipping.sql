-- Crear base de datos
CREATE DATABASE IF NOT EXISTS maritime_shipping;
USE maritime_shipping;

-- Tabla de Rutas
CREATE TABLE IF NOT EXISTS rutas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    origen VARCHAR(100) NOT NULL,
    destino VARCHAR(100) NOT NULL,
    dias_totales INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Escalas
CREATE TABLE IF NOT EXISTS escalas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ruta_id INT NOT NULL,
    puerto VARCHAR(100) NOT NULL,
    dias INT NOT NULL,
    orden INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Contenedores
CREATE TABLE IF NOT EXISTS contenedores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(50) UNIQUE NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    capacidad DECIMAL(10, 2) NOT NULL,
    contenido VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Embarques
CREATE TABLE IF NOT EXISTS embarques (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    barco VARCHAR(100) NOT NULL,
    estado ENUM('activo', 'en-transito', 'completado') DEFAULT 'activo',
    fecha_salida DATE NOT NULL,
    fecha_llegada DATE NOT NULL,
    ruta_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de relación Embarques-Contenedores (muchos a muchos)
CREATE TABLE IF NOT EXISTS embarque_contenedor (
    id INT PRIMARY KEY AUTO_INCREMENT,
    embarque_id INT NOT NULL,
    contenedor_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (embarque_id) REFERENCES embarques(id) ON DELETE CASCADE,
    FOREIGN KEY (contenedor_id) REFERENCES contenedores(id) ON DELETE CASCADE,
    UNIQUE KEY unique_embarque_contenedor (embarque_id, contenedor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Documentos
CREATE TABLE IF NOT EXISTS documentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('BL', 'Invoice', 'Packing List', 'Certificate') NOT NULL,
    numero VARCHAR(50) UNIQUE NOT NULL,
    embarque_id INT NOT NULL,
    contenedor_id INT,
    descripcion TEXT,
    fecha_emision DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (embarque_id) REFERENCES embarques(id) ON DELETE CASCADE,
    FOREIGN KEY (contenedor_id) REFERENCES contenedores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar datos de ejemplo
INSERT INTO rutas (nombre, origen, destino, dias_totales) VALUES
('Ruta Asia-Europa', 'Shanghai', 'Rotterdam', 35),
('Ruta América-Europa', 'Los Ángeles', 'Hamburgo', 28);

INSERT INTO escalas (ruta_id, puerto, dias, orden) VALUES
(1, 'Singapur', 5, 1),
(1, 'Suez', 3, 2),
(1, 'Amberes', 2, 3),
(2, 'Panamá', 4, 1),
(2, 'Nueva York', 3, 2);

INSERT INTO contenedores (numero, tipo, capacidad, contenido) VALUES
('CONT-001', '40ft', 25.00, 'Electrónica'),
('CONT-002', '20ft', 15.00, 'Textiles'),
('CONT-003', '40ft', 25.00, 'Maquinaria'),
('CONT-004', '20ft', 15.00, 'Alimentos');

INSERT INTO embarques (nombre, barco, estado, fecha_salida, fecha_llegada, ruta_id) VALUES
('Embarque Shanghai-Rotterdam', 'MSC Gülsün', 'activo', '2025-12-15', '2026-01-20', 1),
('Embarque LA-Hamburgo', 'OOCL Hong Kong', 'en-transito', '2025-12-10', '2026-01-05', 2);

INSERT INTO embarque_contenedor (embarque_id, contenedor_id) VALUES
(1, 1),
(1, 2),
(2, 3);

INSERT INTO documentos (tipo, numero, embarque_id, contenedor_id, descripcion, fecha_emision) VALUES
('BL', 'BL-2025-001', 1, 1, 'Bill of Lading para electrónica', '2025-12-15'),
('Invoice', 'INV-2025-001', 1, 2, 'Factura comercial', '2025-12-15'),
('Packing List', 'PL-2025-001', 2, 3, 'Lista de empaque', '2025-12-10');
