-- ════════════════════════════════════════════════════════════════
-- Migración: Tienda sin login + Cotizaciones por WhatsApp
-- Fecha: 2026-08-28
--
-- Contexto: la tienda deja de generar PEDIDOS. El carrito ahora
-- produce una COTIZACIÓN que el cliente envía por WhatsApp.
-- El sistema NO descuenta stock ni registra ventas desde la web:
-- AnaMarcolPOS (app local) sigue siendo el único punto de venta.
--
-- Sin CHARSET/COLLATE explícitos a propósito: las tablas heredan los de
-- la base. Fijar utf8mb4_unicode_ci rompe con ERROR 1267 (illegal mix of
-- collations) al comparar codigo/estado contra los parámetros del SP.
--
-- Sin claves foráneas a propósito: cliente_id / zona_id se guardan
-- como referencia suave para no fallar por diferencias de tipo o
-- collation con las tablas existentes. La integridad se valida en
-- el Model (CotizacionModel).
--
-- Idempotente: se puede correr varias veces sin romper nada.
-- ════════════════════════════════════════════════════════════════

USE anamarcol;

-- ────────────────────────────────────────────────────────────────
-- 1. TABLAS
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cotizaciones (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(10)  NOT NULL,
    cliente_id      INT          NULL,
    nombre_cliente  VARCHAR(120) NOT NULL,
    wa_numero       VARCHAR(25)  NOT NULL,
    tipo_entrega    VARCHAR(10)  NOT NULL DEFAULT 'Retiro',
    direccion_envio TEXT         NULL,
    zona_id         INT          NULL,
    subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0,
    costo_envio     DECIMAL(10,2) NOT NULL DEFAULT 0,
    total           DECIMAL(10,2) NOT NULL DEFAULT 0,
    nota            TEXT         NULL,
    estado          VARCHAR(15)  NOT NULL DEFAULT 'Nueva',
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cotizaciones_codigo (codigo),
    KEY idx_cotizaciones_cliente (cliente_id),
    KEY idx_cotizaciones_estado  (estado),
    KEY idx_cotizaciones_fecha   (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cotizacion_detalle (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id   INT          NOT NULL,
    producto_id     INT          NULL,
    variante_id     INT          NULL,
    nombre_producto VARCHAR(150) NOT NULL,
    variante_nombre VARCHAR(100) NULL,
    precio_unit     DECIMAL(10,2) NOT NULL DEFAULT 0,
    cantidad        INT          NOT NULL DEFAULT 1,
    subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0,
    KEY idx_cotdet_cotizacion (cotizacion_id)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────
-- 2. SPs — ESCRITURA
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_cotizaciones_insert;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_insert(
    IN p_codigo          VARCHAR(10),
    IN p_cliente_id      INT,
    IN p_nombre_cliente  VARCHAR(120),
    IN p_wa_numero       VARCHAR(25),
    IN p_tipo_entrega    VARCHAR(10),
    IN p_direccion_envio TEXT,
    IN p_zona_id         INT,
    IN p_subtotal        DECIMAL(10,2),
    IN p_costo_envio     DECIMAL(10,2),
    IN p_total           DECIMAL(10,2),
    IN p_nota            TEXT
)
BEGIN
    INSERT INTO cotizaciones
        (codigo, cliente_id, nombre_cliente, wa_numero, tipo_entrega,
         direccion_envio, zona_id, subtotal, costo_envio, total, nota, estado)
    VALUES
        (p_codigo, p_cliente_id, p_nombre_cliente, p_wa_numero, p_tipo_entrega,
         p_direccion_envio, p_zona_id, p_subtotal, p_costo_envio, p_total, p_nota, 'Nueva');

    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_cotizaciones_insertDetalle;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_insertDetalle(
    IN p_cotizacion_id   INT,
    IN p_producto_id     INT,
    IN p_variante_id     INT,
    IN p_nombre_producto VARCHAR(150),
    IN p_variante_nombre VARCHAR(100),
    IN p_precio_unit     DECIMAL(10,2),
    IN p_cantidad        INT,
    IN p_subtotal        DECIMAL(10,2)
)
BEGIN
    INSERT INTO cotizacion_detalle
        (cotizacion_id, producto_id, variante_id, nombre_producto,
         variante_nombre, precio_unit, cantidad, subtotal)
    VALUES
        (p_cotizacion_id, p_producto_id, p_variante_id, p_nombre_producto,
         p_variante_nombre, p_precio_unit, p_cantidad, p_subtotal);
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_cotizaciones_updateEstado;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_updateEstado(
    IN p_id     INT,
    IN p_estado VARCHAR(15)
)
BEGIN
    UPDATE cotizaciones SET estado = p_estado WHERE id = p_id;
END$$
DELIMITER ;

-- ────────────────────────────────────────────────────────────────
-- 3. SPs — LECTURA
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_cotizaciones_findAll;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_findAll()
BEGIN
    SELECT c.*,
           z.nombre AS zona_nombre,
           (SELECT COUNT(*) FROM cotizacion_detalle d WHERE d.cotizacion_id = c.id) AS total_items
    FROM cotizaciones c
    LEFT JOIN zonas_envio z ON z.id = c.zona_id
    ORDER BY c.created_at DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_cotizaciones_findByEstado;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_findByEstado(IN p_estado VARCHAR(15))
BEGIN
    SELECT c.*,
           z.nombre AS zona_nombre,
           (SELECT COUNT(*) FROM cotizacion_detalle d WHERE d.cotizacion_id = c.id) AS total_items
    FROM cotizaciones c
    LEFT JOIN zonas_envio z ON z.id = c.zona_id
    WHERE c.estado = p_estado
    ORDER BY c.created_at DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_cotizaciones_findById;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_findById(IN p_id INT)
BEGIN
    SELECT c.*, z.nombre AS zona_nombre
    FROM cotizaciones c
    LEFT JOIN zonas_envio z ON z.id = c.zona_id
    WHERE c.id = p_id
    LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_cotizaciones_findDetalle;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_findDetalle(IN p_cotizacion_id INT)
BEGIN
    SELECT * FROM cotizacion_detalle
    WHERE cotizacion_id = p_cotizacion_id
    ORDER BY id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_cotizaciones_existeCodigo;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_existeCodigo(IN p_codigo VARCHAR(10))
BEGIN
    SELECT COUNT(*) AS total FROM cotizaciones WHERE codigo = p_codigo;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_cotizaciones_countByEstado;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_countByEstado(IN p_estado VARCHAR(15))
BEGIN
    SELECT COUNT(*) AS total FROM cotizaciones WHERE estado = p_estado;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_cotizaciones_countHoy;
DELIMITER $$
CREATE PROCEDURE sp_cotizaciones_countHoy()
BEGIN
    SELECT COUNT(*) AS total FROM cotizaciones WHERE DATE(created_at) = CURDATE();
END$$
DELIMITER ;

-- ────────────────────────────────────────────────────────────────
-- 4. Clientes sin cuenta — se identifican por teléfono
--    La tienda ya no tiene registro/login: cada cotización o cita
--    crea (o reutiliza) el cliente a partir de su WhatsApp.
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_clientes_findByTelefono;
DELIMITER $$
CREATE PROCEDURE sp_clientes_findByTelefono(IN p_telefono VARCHAR(25))
BEGIN
    -- Se compara por los ÚLTIMOS 8 DÍGITOS: así '9999-9999', '9999 9999'
    -- y '+504 9999-9999' resuelven al mismo cliente y no se duplica el
    -- registro cada vez que alguien escribe el número distinto.
    -- Sin REGEXP_REPLACE a propósito — no existe en MySQL 5.7.
    SELECT * FROM clientes
    WHERE telefono IS NOT NULL AND telefono <> ''
      AND RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefono,   '-',''),' ',''),'+',''),'(',''),')',''), 8)
        = RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(p_telefono, '-',''),' ',''),'+',''),'(',''),')',''), 8)
    ORDER BY id
    LIMIT 1;
END$$
DELIMITER ;

-- Verificación:
--   CALL sp_cotizaciones_findAll();
--   CALL sp_clientes_findByTelefono('9999-9999');
