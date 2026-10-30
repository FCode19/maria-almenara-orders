-- Carga operativa para noviembre de 2026.
-- Reemplaza pedidos, detalles, historial y notificaciones.
-- Conserva clientes, usuarios, sedes y catálogos existentes.
-- Ejecutar únicamente sobre la base elegida para la presentación.

START TRANSACTION;

DELETE FROM tbl_notificacion;
DELETE FROM tbl_historial_pedido;
DELETE FROM tbl_pedido_detalle;
DELETE FROM tbl_pedido;

CREATE TEMPORARY TABLE tmp_secuencia_noviembre (
    numero INT UNSIGNED NOT NULL PRIMARY KEY
);

INSERT INTO tmp_secuencia_noviembre (numero) VALUES
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9),
(10),
(11),
(12),
(13),
(14),
(15),
(16),
(17),
(18),
(19),
(20),
(21),
(22),
(23),
(24),
(25),
(26),
(27),
(28),
(29),
(30),
(31),
(32),
(33),
(34),
(35),
(36),
(37),
(38),
(39),
(40),
(41),
(42),
(43),
(44),
(45),
(46),
(47),
(48),
(49),
(50),
(51),
(52),
(53),
(54),
(55),
(56),
(57),
(58),
(59),
(60);

-- Mapas internos para utilizar los identificadores reales de cada catálogo.
CREATE TEMPORARY TABLE tmp_clientes (
    posicion INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT UNSIGNED NOT NULL
);
INSERT INTO tmp_clientes (id_cliente)
SELECT id_cliente FROM tbl_cliente WHERE activo = 1 ORDER BY id_cliente;

CREATE TEMPORARY TABLE tmp_sedes (
    posicion INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_sede INT UNSIGNED NOT NULL
);
INSERT INTO tmp_sedes (id_sede)
SELECT id_sede FROM tbl_sede WHERE activo = 1 ORDER BY id_sede;

CREATE TEMPORARY TABLE tmp_usuarios (
    posicion INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL
);
INSERT INTO tmp_usuarios (id_usuario)
SELECT id_usuario FROM tbl_usuario WHERE activo = 1 ORDER BY id_usuario;

CREATE TEMPORARY TABLE tmp_tortas (
    posicion INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_torta INT UNSIGNED NOT NULL
);
INSERT INTO tmp_tortas (id_torta)
SELECT id_torta FROM tbl_torta WHERE activo = 1 ORDER BY id_torta;

CREATE TEMPORARY TABLE tmp_tamanos (
    posicion INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_tamano INT UNSIGNED NOT NULL
);
INSERT INTO tmp_tamanos (id_tamano)
SELECT id_tamano FROM tbl_tamano WHERE activo = 1 ORDER BY id_tamano;

CREATE TEMPORARY TABLE tmp_sabores (
    posicion INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_sabor INT UNSIGNED NOT NULL
);
INSERT INTO tmp_sabores (id_sabor)
SELECT id_sabor FROM tbl_sabor WHERE activo = 1 ORDER BY id_sabor;

INSERT INTO tbl_pedido (
    codigo_pedido,
    codigo_seguimiento,
    id_cliente,
    id_sede,
    id_estado_actual,
    id_usuario_registro,
    modalidad,
    direccion_entrega,
    fecha_pedido,
    fecha_requerida,
    observaciones
)
SELECT
    CONCAT('PED-202611-', LPAD(s.numero, 3, '0')),
    CONCAT('MA-NOV26-', LPAD(s.numero, 4, '0')),
    clientes.id_cliente,
    sedes.id_sede,
    e.id_estado,
    usuarios.id_usuario,
    CASE WHEN MOD(s.numero, 3) = 0 THEN 'DELIVERY' ELSE 'RECOJO' END,
    CASE
        WHEN MOD(s.numero, 3) <> 0 THEN NULL
        WHEN MOD(s.numero, 5) = 0 THEN 'Av. Primavera 1350, Santiago de Surco'
        WHEN MOD(s.numero, 5) = 1 THEN 'Av. José Pardo 620, Miraflores'
        WHEN MOD(s.numero, 5) = 2 THEN 'Av. San Luis 2450, San Borja'
        WHEN MOD(s.numero, 5) = 3 THEN 'Av. Grau 410, Barranco'
        ELSE 'Av. Javier Prado 1680, San Isidro'
    END,
    TIMESTAMP(
        DATE_ADD('2026-11-01', INTERVAL FLOOR((s.numero - 1) / 2) DAY),
        CASE WHEN MOD(s.numero, 2) = 1 THEN '09:15:00' ELSE '15:40:00' END
    ),
    DATE_ADD(
        DATE_ADD('2026-11-01', INTERVAL FLOOR((s.numero - 1) / 2) DAY),
        INTERVAL (CASE WHEN s.numero <= 4 THEN 1 ELSE 1 + MOD(s.numero, 3) END) DAY
    ),
    CASE
        WHEN MOD(s.numero, 6) = 0 THEN 'Entrega coordinada con recepción'
        WHEN MOD(s.numero, 6) = 1 THEN 'Confirmar mensaje personalizado antes de producción'
        WHEN MOD(s.numero, 6) = 2 THEN 'Cliente solicita comunicación por teléfono'
        WHEN MOD(s.numero, 6) = 3 THEN 'Decoración en tonos claros'
        WHEN MOD(s.numero, 6) = 4 THEN 'Recojo coordinado durante la tarde'
        ELSE 'Mantener refrigerado hasta la entrega'
    END
FROM tmp_secuencia_noviembre s
INNER JOIN tmp_clientes clientes
    ON clientes.posicion = 1 + MOD(s.numero - 1, (SELECT COUNT(*) FROM tmp_clientes))
INNER JOIN tmp_sedes sedes
    ON sedes.posicion = 1 + MOD(s.numero - 1, (SELECT COUNT(*) FROM tmp_sedes))
INNER JOIN tmp_usuarios usuarios
    ON usuarios.posicion = 1 + MOD(s.numero - 1, (SELECT COUNT(*) FROM tmp_usuarios))
INNER JOIN tbl_estado_pedido e
    ON e.nombre = CASE
        WHEN s.numero = 1 THEN 'En produccion'
        WHEN s.numero = 2 THEN 'Terminado'
        WHEN s.numero = 3 THEN 'Confirmado'
        WHEN s.numero = 4 THEN 'Registrado'
        WHEN s.numero BETWEEN 5 AND 16 THEN 'Entregado'
        WHEN s.numero BETWEEN 17 AND 20 THEN 'Cancelado'
        WHEN s.numero BETWEEN 21 AND 30 THEN 'Entregado'
        WHEN s.numero BETWEEN 31 AND 36 THEN 'Terminado'
        WHEN s.numero BETWEEN 37 AND 44 THEN 'En produccion'
        WHEN s.numero BETWEEN 45 AND 52 THEN 'Confirmado'
        ELSE 'Registrado'
    END;

INSERT INTO tbl_pedido_detalle (
    id_pedido,
    id_torta,
    id_tamano,
    id_sabor,
    cantidad,
    mensaje_personalizado,
    observaciones
)
SELECT
    p.id_pedido,
    tortas.id_torta,
    tamanos.id_tamano,
    sabores.id_sabor,
    CASE WHEN MOD(s.numero, 11) = 0 THEN 2 ELSE 1 END,
    CASE
        WHEN MOD(s.numero, 5) = 0 THEN 'Feliz cumpleaños'
        WHEN MOD(s.numero, 5) = 1 THEN 'Con mucho cariño'
        WHEN MOD(s.numero, 5) = 2 THEN 'Feliz aniversario'
        WHEN MOD(s.numero, 5) = 3 THEN NULL
        ELSE 'Que disfrutes tu día'
    END,
    CASE
        WHEN MOD(s.numero, 4) = 0 THEN 'Decoración sobria'
        WHEN MOD(s.numero, 4) = 1 THEN 'Agregar placa de chocolate'
        WHEN MOD(s.numero, 4) = 2 THEN NULL
        ELSE 'Presentación para regalo'
    END
FROM tmp_secuencia_noviembre s
INNER JOIN tbl_pedido p
    ON p.codigo_pedido = CONCAT('PED-202611-', LPAD(s.numero, 3, '0'))
INNER JOIN tmp_tortas tortas
    ON tortas.posicion = 1 + MOD(s.numero - 1, (SELECT COUNT(*) FROM tmp_tortas))
INNER JOIN tmp_tamanos tamanos
    ON tamanos.posicion = 1 + MOD(s.numero - 1, (SELECT COUNT(*) FROM tmp_tamanos))
INNER JOIN tmp_sabores sabores
    ON sabores.posicion = 1 + MOD(s.numero - 1, (SELECT COUNT(*) FROM tmp_sabores));

INSERT INTO tbl_pedido_detalle (
    id_pedido,
    id_torta,
    id_tamano,
    id_sabor,
    cantidad,
    mensaje_personalizado,
    observaciones
)
SELECT
    p.id_pedido,
    tortas.id_torta,
    tamanos.id_tamano,
    sabores.id_sabor,
    1,
    NULL,
    'Producto adicional del mismo pedido'
FROM tmp_secuencia_noviembre s
INNER JOIN tbl_pedido p
    ON p.codigo_pedido = CONCAT('PED-202611-', LPAD(s.numero, 3, '0'))
INNER JOIN tmp_tortas tortas
    ON tortas.posicion = 1 + MOD(s.numero + 2, (SELECT COUNT(*) FROM tmp_tortas))
INNER JOIN tmp_tamanos tamanos
    ON tamanos.posicion = 1 + MOD(s.numero, (SELECT COUNT(*) FROM tmp_tamanos))
INNER JOIN tmp_sabores sabores
    ON sabores.posicion = 1 + MOD(s.numero + 1, (SELECT COUNT(*) FROM tmp_sabores))
WHERE MOD(s.numero, 6) = 0;

-- Registro inicial de todos los pedidos.
INSERT INTO tbl_historial_pedido (
    id_pedido, id_estado, id_usuario, fecha_hora, observacion
)
SELECT
    p.id_pedido,
    e.id_estado,
    p.id_usuario_registro,
    p.fecha_pedido,
    'Pedido registrado en el sistema'
FROM tbl_pedido p
INNER JOIN tbl_estado_pedido e ON e.nombre = 'Registrado';

-- Confirmación con el cliente.
INSERT INTO tbl_historial_pedido (
    id_pedido, id_estado, id_usuario, fecha_hora, observacion
)
SELECT
    p.id_pedido,
    confirmado.id_estado,
    p.id_usuario_registro,
    DATE_ADD(p.fecha_pedido, INTERVAL 2 HOUR),
    'Datos y fecha confirmados con el cliente'
FROM tbl_pedido p
INNER JOIN tbl_estado_pedido actual ON actual.id_estado = p.id_estado_actual
INNER JOIN tbl_estado_pedido confirmado ON confirmado.nombre = 'Confirmado'
WHERE actual.nombre IN ('Confirmado', 'En produccion', 'Terminado', 'Entregado')
   OR (actual.nombre = 'Cancelado' AND CAST(RIGHT(p.codigo_pedido, 3) AS UNSIGNED) >= 19);

-- Inicio de producción.
INSERT INTO tbl_historial_pedido (
    id_pedido, id_estado, id_usuario, fecha_hora, observacion
)
SELECT
    p.id_pedido,
    produccion.id_estado,
    p.id_usuario_registro,
    DATE_ADD(p.fecha_pedido, INTERVAL 6 HOUR),
    'Pedido enviado al área de producción'
FROM tbl_pedido p
INNER JOIN tbl_estado_pedido actual ON actual.id_estado = p.id_estado_actual
INNER JOIN tbl_estado_pedido produccion ON produccion.nombre = 'En produccion'
WHERE actual.nombre IN ('En produccion', 'Terminado', 'Entregado');

-- Finalización de la preparación.
INSERT INTO tbl_historial_pedido (
    id_pedido, id_estado, id_usuario, fecha_hora, observacion
)
SELECT
    p.id_pedido,
    terminado.id_estado,
    p.id_usuario_registro,
    TIMESTAMP(p.fecha_requerida, '08:00:00'),
    'Preparación finalizada y pedido revisado'
FROM tbl_pedido p
INNER JOIN tbl_estado_pedido actual ON actual.id_estado = p.id_estado_actual
INNER JOIN tbl_estado_pedido terminado ON terminado.nombre = 'Terminado'
WHERE actual.nombre IN ('Terminado', 'Entregado');

-- Entregas: la mayoría dentro de fecha y algunas con un día de retraso.
INSERT INTO tbl_historial_pedido (
    id_pedido, id_estado, id_usuario, fecha_hora, observacion
)
SELECT
    p.id_pedido,
    entregado.id_estado,
    p.id_usuario_registro,
    TIMESTAMP(
        DATE_ADD(
            p.fecha_requerida,
            INTERVAL (CASE
                WHEN MOD(CAST(RIGHT(p.codigo_pedido, 3) AS UNSIGNED), 7) = 0 THEN 1
                ELSE 0
            END) DAY
        ),
        '10:30:00'
    ),
    CASE
        WHEN MOD(CAST(RIGHT(p.codigo_pedido, 3) AS UNSIGNED), 7) = 0
            THEN 'Pedido entregado con un día de retraso'
        ELSE 'Pedido entregado dentro de la fecha acordada'
    END
FROM tbl_pedido p
INNER JOIN tbl_estado_pedido actual ON actual.id_estado = p.id_estado_actual
INNER JOIN tbl_estado_pedido entregado ON entregado.nombre = 'Entregado'
WHERE actual.nombre = 'Entregado';

-- Cancelaciones realizadas antes o después de la confirmación.
INSERT INTO tbl_historial_pedido (
    id_pedido, id_estado, id_usuario, fecha_hora, observacion
)
SELECT
    p.id_pedido,
    cancelado.id_estado,
    p.id_usuario_registro,
    DATE_ADD(p.fecha_pedido, INTERVAL 3 HOUR),
    CASE
        WHEN CAST(RIGHT(p.codigo_pedido, 3) AS UNSIGNED) >= 19
            THEN 'Pedido cancelado después de la confirmación del cliente'
        ELSE 'Pedido cancelado a solicitud del cliente'
    END
FROM tbl_pedido p
INNER JOIN tbl_estado_pedido actual ON actual.id_estado = p.id_estado_actual
INNER JOIN tbl_estado_pedido cancelado ON cancelado.nombre = 'Cancelado'
WHERE actual.nombre = 'Cancelado';

DROP TEMPORARY TABLE tmp_sabores;
DROP TEMPORARY TABLE tmp_tamanos;
DROP TEMPORARY TABLE tmp_tortas;
DROP TEMPORARY TABLE tmp_usuarios;
DROP TEMPORARY TABLE tmp_sedes;
DROP TEMPORARY TABLE tmp_clientes;
DROP TEMPORARY TABLE tmp_secuencia_noviembre;

COMMIT;
