-- ════════════════════════════════════════════════════════════════
-- Reporte de Inventario v2 — SPs adicionales
-- Fecha: 2026-05-17
--
-- Agrega 2 SPs nuevos para que el reporte de inventario sea completo:
--   • sp_reportes_inventarioCompleto      — todos los productos con su stock
--   • sp_reportes_inventarioPorCategoria  — totalizado agrupado por categoría
-- ════════════════════════════════════════════════════════════════

USE anamarcol;

-- ────────────────────────────────────────────────────────────────
-- Catálogo completo con stock, precio y valor del inventario
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_reportes_inventarioCompleto;
DELIMITER $$
CREATE PROCEDURE sp_reportes_inventarioCompleto()
BEGIN
    SELECT
        p.id,
        p.codigo_barras,
        p.nombre,
        c.nombre               AS categoria_nombre,
        p.precio_base,
        p.tiene_variantes,
        p.stock,
        p.activo,
        p.visible_tienda,
        -- Si tiene variantes, sumar el stock de todas; si no, usar p.stock
        CASE
            WHEN p.tiene_variantes = 1
                THEN COALESCE((SELECT SUM(stock) FROM producto_variantes WHERE producto_id = p.id AND activo = 1), 0)
            ELSE p.stock
        END                    AS stock_total,
        -- Valor del stock = stock * precio_base
        CASE
            WHEN p.tiene_variantes = 1 THEN
                COALESCE((
                    SELECT SUM(COALESCE(v.precio, p.precio_base) * v.stock)
                    FROM producto_variantes v
                    WHERE v.producto_id = p.id AND v.activo = 1
                ), 0)
            ELSE COALESCE(p.precio_base, 0) * p.stock
        END                    AS valor_inventario,
        -- Estado descriptivo
        CASE
            WHEN p.activo = 0 THEN 'Inactivo'
            WHEN p.tiene_variantes = 1 AND COALESCE((SELECT SUM(stock) FROM producto_variantes WHERE producto_id = p.id AND activo = 1), 0) = 0 THEN 'Sin stock'
            WHEN p.tiene_variantes = 0 AND p.stock = 0 THEN 'Sin stock'
            WHEN p.tiene_variantes = 0 AND p.stock <= 5 THEN 'Stock bajo'
            ELSE 'OK'
        END                    AS estado
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    ORDER BY c.nombre, p.nombre;
END$$
DELIMITER ;

-- ────────────────────────────────────────────────────────────────
-- Totalizado por categoría
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_reportes_inventarioPorCategoria;
DELIMITER $$
CREATE PROCEDURE sp_reportes_inventarioPorCategoria()
BEGIN
    SELECT
        c.id                AS categoria_id,
        c.nombre             AS categoria_nombre,
        COUNT(p.id)          AS total_productos,
        SUM(CASE WHEN p.activo = 1 THEN 1 ELSE 0 END) AS activos,
        SUM(
            CASE
                WHEN p.tiene_variantes = 1
                    THEN COALESCE((SELECT SUM(stock) FROM producto_variantes WHERE producto_id = p.id AND activo = 1), 0)
                ELSE p.stock
            END
        ) AS stock_total,
        SUM(
            CASE
                WHEN p.tiene_variantes = 1 THEN
                    COALESCE((
                        SELECT SUM(COALESCE(v.precio, p.precio_base) * v.stock)
                        FROM producto_variantes v
                        WHERE v.producto_id = p.id AND v.activo = 1
                    ), 0)
                ELSE COALESCE(p.precio_base, 0) * p.stock
            END
        ) AS valor_inventario
    FROM categorias c
    LEFT JOIN productos p ON p.categoria_id = c.id
    GROUP BY c.id, c.nombre
    ORDER BY c.nombre;
END$$
DELIMITER ;

-- Verificación:
--   CALL sp_reportes_inventarioCompleto();
--   CALL sp_reportes_inventarioPorCategoria();
