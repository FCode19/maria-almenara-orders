CREATE DATABASE maria_almenara_pedidos;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS tbl_notificacion;
DROP TABLE IF EXISTS tbl_historial_pedido;
DROP TABLE IF EXISTS tbl_pedido_detalle;
DROP TABLE IF EXISTS tbl_pedido;
DROP TABLE IF EXISTS tbl_estado_pedido;
DROP TABLE IF EXISTS tbl_sabor;
DROP TABLE IF EXISTS tbl_tamano;
DROP TABLE IF EXISTS tbl_torta;
DROP TABLE IF EXISTS tbl_sede;
DROP TABLE IF EXISTS tbl_cliente;
DROP TABLE IF EXISTS tbl_usuario;
DROP TABLE IF EXISTS tbl_rol;

CREATE TABLE tbl_rol (
    id_rol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_usuario (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol INT UNSIGNED NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    correo VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol
        FOREIGN KEY (id_rol)
        REFERENCES tbl_rol(id_rol)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_cliente (
    id_cliente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NULL,
    telefono VARCHAR(20) NOT NULL,
    correo VARCHAR(120) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cliente_telefono (telefono),
    INDEX idx_cliente_correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_sede (
    id_sede INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    direccion VARCHAR(200) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_torta (
    id_torta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_tamano (
    id_tamano INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_sabor (
    id_sabor INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_estado_pedido (
    id_estado INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE,
    orden INT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_estado_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_pedido (
    id_pedido BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_pedido VARCHAR(20) NOT NULL UNIQUE,
    codigo_seguimiento VARCHAR(32) NOT NULL UNIQUE,

    id_cliente INT UNSIGNED NOT NULL,
    id_sede INT UNSIGNED NOT NULL,
    id_estado_actual INT UNSIGNED NOT NULL,
    id_usuario_registro INT UNSIGNED NOT NULL,

    modalidad ENUM('RECOJO','DELIVERY') NOT NULL,
    direccion_entrega VARCHAR(250) NULL,

    fecha_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_requerida DATE NOT NULL,
    observaciones TEXT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_pedido_cliente (id_cliente),
    INDEX idx_pedido_estado (id_estado_actual),
    INDEX idx_pedido_fecha_requerida (fecha_requerida),
    INDEX idx_pedido_codigo_seguimiento (codigo_seguimiento),

    CONSTRAINT fk_pedido_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES tbl_cliente(id_cliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_sede
        FOREIGN KEY (id_sede)
        REFERENCES tbl_sede(id_sede)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_estado
        FOREIGN KEY (id_estado_actual)
        REFERENCES tbl_estado_pedido(id_estado)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_usuario
        FOREIGN KEY (id_usuario_registro)
        REFERENCES tbl_usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_pedido_delivery_direccion
        CHECK (
            modalidad = 'RECOJO'
            OR (
                modalidad = 'DELIVERY'
                AND direccion_entrega IS NOT NULL
                AND TRIM(direccion_entrega) <> ''
            )
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_pedido_detalle (
    id_detalle BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pedido BIGINT UNSIGNED NOT NULL,
    id_torta INT UNSIGNED NOT NULL,
    id_tamano INT UNSIGNED NOT NULL,
    id_sabor INT UNSIGNED NOT NULL,

    cantidad INT UNSIGNED NOT NULL,
    mensaje_personalizado VARCHAR(200) NULL,
    observaciones VARCHAR(250) NULL,

    INDEX idx_detalle_pedido (id_pedido),

    CONSTRAINT fk_detalle_pedido
        FOREIGN KEY (id_pedido)
        REFERENCES tbl_pedido(id_pedido)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_detalle_torta
        FOREIGN KEY (id_torta)
        REFERENCES tbl_torta(id_torta)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_detalle_tamano
        FOREIGN KEY (id_tamano)
        REFERENCES tbl_tamano(id_tamano)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_detalle_sabor
        FOREIGN KEY (id_sabor)
        REFERENCES tbl_sabor(id_sabor)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_detalle_cantidad
        CHECK (cantidad >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_historial_pedido (
    id_historial BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pedido BIGINT UNSIGNED NOT NULL,
    id_estado INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    observacion VARCHAR(250) NULL,

    INDEX idx_historial_pedido (id_pedido),
    INDEX idx_historial_estado (id_estado),
    INDEX idx_historial_fecha (fecha_hora),

    CONSTRAINT fk_historial_pedido
        FOREIGN KEY (id_pedido)
        REFERENCES tbl_pedido(id_pedido)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_historial_estado
        FOREIGN KEY (id_estado)
        REFERENCES tbl_estado_pedido(id_estado)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_historial_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES tbl_usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbl_notificacion (
    id_notificacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pedido BIGINT UNSIGNED NOT NULL,

    canal ENUM('TELEGRAM','EMAIL') NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    destinatario VARCHAR(150) NOT NULL,

    estado_envio ENUM('PENDIENTE','ENVIADO','ERROR') NOT NULL DEFAULT 'PENDIENTE',
    fecha_envio DATETIME NULL,
    detalle_error VARCHAR(500) NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_notificacion_pedido (id_pedido),
    INDEX idx_notificacion_estado (estado_envio),

    CONSTRAINT fk_notificacion_pedido
        FOREIGN KEY (id_pedido)
        REFERENCES tbl_pedido(id_pedido)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO tbl_rol (nombre) VALUES
('Administrador'),
('Encargado');

INSERT INTO tbl_estado_pedido (nombre, orden) VALUES
('Registrado', 1),
('Confirmado', 2),
('En produccion', 3),
('Terminado', 4),
('Entregado', 5),
('Cancelado', 99);

INSERT INTO tbl_tamano (nombre) VALUES
('Pequena'),
('Mediana'),
('Grande');

INSERT INTO tbl_sabor (nombre) VALUES
('Chocolate'),
('Vainilla'),
('Fresa'),
('Lúcuma');

INSERT INTO tbl_sede (nombre, direccion) VALUES
('Sede Principal', 'Direccion de demostracion');

INSERT INTO tbl_torta (nombre, descripcion) VALUES
('Torta de chocolate', 'Torta de chocolate para pedidos personalizados'),
('Torta de vainilla', 'Torta de vainilla para pedidos personalizados'),
('Torta de fresa', 'Torta de fresa para pedidos personalizados');

INSERT INTO tbl_usuario (
    id_rol,
    nombres,
    apellidos,
    usuario,
    correo,
    password_hash
) VALUES (
    1,
    'Usuario',
    'Administrador',
    'admin',
    'admin@demo.local',
    '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918'
);

INSERT INTO tbl_cliente (
    nombres,
    apellidos,
    telefono,
    correo
) VALUES (
    'Andrea',
    'Torres',
    '987654321',
    'andrea@demo.local'
);

INSERT INTO tbl_pedido (
    codigo_pedido,
    codigo_seguimiento,
    id_cliente,
    id_sede,
    id_estado_actual,
    id_usuario_registro,
    modalidad,
    fecha_requerida,
    observaciones
) VALUES (
    'PED-000001',
    'MA-7FH2K9Q4',
    1,
    1,
    1,
    1,
    'RECOJO',
    DATE_ADD(CURDATE(), INTERVAL 3 DAY),
    'Pedido de demostracion'
);

INSERT INTO tbl_pedido_detalle (
    id_pedido,
    id_torta,
    id_tamano,
    id_sabor,
    cantidad,
    mensaje_personalizado
) VALUES (
    1,
    1,
    2,
    1,
    1,
    'Feliz cumpleaños'
);

INSERT INTO tbl_historial_pedido (
    id_pedido,
    id_estado,
    id_usuario,
    observacion
) VALUES (
    1,
    1,
    1,
    'Pedido registrado'
);