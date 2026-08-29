-- Limpieza de datos de prueba antes de salir a producción.
-- NO toca catálogo, categorías, descuentos ni clientes.
-- Respeta el orden por llaves foráneas (detalle antes que venta).

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE venta_detalle;
TRUNCATE TABLE ventas;
TRUNCATE TABLE caja_sesiones;

SET FOREIGN_KEY_CHECKS = 1;

-- Verificación:
SELECT
  (SELECT COUNT(*) FROM ventas)        AS ventas,
  (SELECT COUNT(*) FROM venta_detalle) AS detalles,
  (SELECT COUNT(*) FROM caja_sesiones) AS caja_sesiones;
