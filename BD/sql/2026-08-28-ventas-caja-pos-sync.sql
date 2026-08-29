-- ════════════════════════════════════════════════════════════════
-- Sync de Ventas y Cierres de Caja desde AnaMarcolPOS (local) → Web
-- Fecha: 2026-08-28
--
-- El POS local es ahora el único que vende (la Caja del sistema web
-- está deshabilitada — ver CajaController::bloquearOperativo). Este
-- script agrega lo necesario para que cada venta y cada cierre de
-- turno hechos en el POS aparezcan también aquí, en /Ventas y
-- /Caja/historial, para poder ver recibos y cierres desde la web.
--
-- Como `ventas.user_id` y `caja_sesiones.user_id` son NOT NULL (FK a
-- `users`), y las ventas del POS no las hace ningún usuario del panel
-- web, todo lo sincronizado se asocia al primer usuario con rol admin
-- activo — se resuelve dentro del propio procedimiento, no hay que
-- fijar un id a mano.
-- ════════════════════════════════════════════════════════════════

USE anamarcol;

-- ────────────────────────────────────────────────────────────────
-- Columnas nuevas — para no duplicar al reintentar un envío y para
-- distinguir en las vistas qué vino del POS vs. del web (histórico).
-- ────────────────────────────────────────────────────────────────
ALTER TABLE ventas
    ADD COLUMN pos_venta_id INT UNSIGNED NULL UNIQUE AFTER id,
    ADD COLUMN origen VARCHAR(10) NOT NULL DEFAULT 'Web' AFTER pos_venta_id;

ALTER TABLE caja_sesiones
    ADD COLUMN pos_sesion_id INT UNSIGNED NULL UNIQUE AFTER id,
    ADD COLUMN origen VARCHAR(10) NOT NULL DEFAULT 'Web' AFTER pos_sesion_id;

DELIMITER $$

-- ────────────────────────────────────────────────────────────────
-- sp_ventas_insertDesdePos — crea (o devuelve la existente) una venta
-- que llegó del POS. Idempotente por pos_venta_id: si el POS reintenta
-- el mismo envío, no duplica.
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_ventas_insertDesdePos$$
CREATE PROCEDURE sp_ventas_insertDesdePos(
    IN p_pos_venta_id    INT UNSIGNED,
    IN p_metodo_pago     VARCHAR(20),
    IN p_subtotal        DECIMAL(10,2),
    IN p_total           DECIMAL(10,2),
    IN p_monto_recibido  DECIMAL(10,2),
    IN p_cambio          DECIMAL(10,2),
    IN p_nota            TEXT,
    IN p_correlativo     INT UNSIGNED,
    IN p_created_at      DATETIME
)
BEGIN
    DECLARE v_id      INT UNSIGNED DEFAULT NULL;
    DECLARE v_user_id INT UNSIGNED DEFAULT NULL;

    SELECT id INTO v_id FROM ventas WHERE pos_venta_id = p_pos_venta_id LIMIT 1;

    IF v_id IS NULL THEN
        SELECT u.id INTO v_user_id
        FROM users u
        JOIN roles r ON r.id = u.rol_id
        WHERE r.slug = 'admin' AND u.activo = 1
        ORDER BY u.id
        LIMIT 1;

        INSERT INTO ventas (
            pos_venta_id, origen, cliente_id, user_id, metodo_pago,
            subtotal, descuento, total, monto_recibido, cambio, nota,
            anulada, correlativo, created_at
        ) VALUES (
            p_pos_venta_id, 'POS', NULL, v_user_id, p_metodo_pago,
            p_subtotal, 0, p_total, p_monto_recibido, p_cambio, p_nota,
            0, p_correlativo, p_created_at
        );
        SET v_id = LAST_INSERT_ID();
    END IF;

    SELECT v_id AS id;
END$$

-- ────────────────────────────────────────────────────────────────
-- sp_ventas_insertDetalleDesdePos — una línea de una venta del POS.
-- p_producto_id es el RemoteId del producto en esta BD web — puede
-- venir NULL si ese producto todavía no se sincronizó al catálogo web.
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_ventas_insertDetalleDesdePos$$
CREATE PROCEDURE sp_ventas_insertDetalleDesdePos(
    IN p_venta_id        INT UNSIGNED,
    IN p_producto_id     INT UNSIGNED,
    IN p_nombre_producto VARCHAR(255),
    IN p_precio_unit     DECIMAL(10,2),
    IN p_cantidad        INT,
    IN p_subtotal        DECIMAL(10,2)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM venta_detalle
        WHERE venta_id = p_venta_id
          AND nombre_producto = p_nombre_producto
          AND cantidad = p_cantidad
          AND precio_unit = p_precio_unit
    ) THEN
        INSERT INTO venta_detalle (venta_id, producto_id, variante_id, nombre_producto, precio_unit, cantidad, subtotal)
        VALUES (p_venta_id, p_producto_id, NULL, p_nombre_producto, p_precio_unit, p_cantidad, p_subtotal);
    END IF;
END$$

-- ────────────────────────────────────────────────────────────────
-- sp_caja_insertDesdePos — un cierre de turno hecho en el POS.
-- Siempre llega ya cerrado (el POS solo sincroniza al cerrar caja),
-- así que estado se guarda directo como 'cerrada'.
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_caja_insertDesdePos$$
CREATE PROCEDURE sp_caja_insertDesdePos(
    IN p_pos_sesion_id       INT UNSIGNED,
    IN p_monto_apertura      DECIMAL(10,2),
    IN p_monto_cierre        DECIMAL(10,2),
    IN p_monto_sistema       DECIMAL(10,2),
    IN p_diferencia          DECIMAL(10,2),
    IN p_total_ventas        DECIMAL(10,2),
    IN p_total_efectivo      DECIMAL(10,2),
    IN p_total_tarjeta       DECIMAL(10,2),
    IN p_total_transferencia DECIMAL(10,2),
    IN p_nota_apertura       TEXT,
    IN p_nota_cierre         TEXT,
    IN p_abierta_at          DATETIME,
    IN p_cerrada_at          DATETIME
)
BEGIN
    DECLARE v_id      INT UNSIGNED DEFAULT NULL;
    DECLARE v_user_id INT UNSIGNED DEFAULT NULL;

    SELECT id INTO v_id FROM caja_sesiones WHERE pos_sesion_id = p_pos_sesion_id LIMIT 1;

    IF v_id IS NULL THEN
        SELECT u.id INTO v_user_id
        FROM users u
        JOIN roles r ON r.id = u.rol_id
        WHERE r.slug = 'admin' AND u.activo = 1
        ORDER BY u.id
        LIMIT 1;

        INSERT INTO caja_sesiones (
            pos_sesion_id, origen, user_id, monto_apertura, monto_cierre,
            monto_sistema, diferencia, total_ventas, total_efectivo,
            total_tarjeta, total_transferencia, total_anuladas,
            nota_apertura, nota_cierre, estado, abierta_at, cerrada_at
        ) VALUES (
            p_pos_sesion_id, 'POS', v_user_id, p_monto_apertura, p_monto_cierre,
            p_monto_sistema, p_diferencia, p_total_ventas, p_total_efectivo,
            p_total_tarjeta, p_total_transferencia, 0,
            p_nota_apertura, p_nota_cierre, 'cerrada', p_abierta_at, p_cerrada_at
        );
        SET v_id = LAST_INSERT_ID();
    END IF;

    SELECT v_id AS id;
END$$

DELIMITER ;

-- Verificación:
--   CALL sp_ventas_insertDesdePos(9999, 'Efectivo', 100.00, 100.00, 100.00, 0.00, NULL, 5752, NOW());
--   SELECT * FROM ventas WHERE pos_venta_id = 9999;
--   DELETE FROM ventas WHERE pos_venta_id = 9999; -- limpiar la prueba
