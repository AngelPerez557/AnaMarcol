-- ════════════════════════════════════════════════════════════════
-- Límite de citas pendientes por dispositivo — Fecha: 2026-08-29
--
-- Antes: cualquiera podía agendar citas sin límite desde la tienda
-- pública. Ahora: si un mismo dispositivo (cookie + IP) ya tiene 2
-- citas en estado 'Pendiente' sin confirmar, la 3ra queda bloqueada
-- hasta que el panel confirme/cancele alguna de las 2 anteriores.
--
-- No se bloquea la IP en sí (una IP compartida — muy común con
-- datos móviles en Honduras — bloquearía a clientes reales que no
-- tienen nada que ver). Se cuenta contra el ORIGEN (cookie del
-- navegador + IP juntos) y solo mientras esas citas sigan en
-- 'Pendiente' — en cuanto el panel las marca como Confirmada,
-- Cancelada o Completada, dejan de contar solas, sin tocar nada más.
-- ════════════════════════════════════════════════════════════════

USE anamarcol;

CREATE TABLE IF NOT EXISTS citas_origen (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cita_id    INT UNSIGNED NOT NULL,
    device_id  VARCHAR(64)  NOT NULL,
    ip         VARCHAR(45)  NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_citas_origen_device (device_id),
    KEY idx_citas_origen_ip (ip),
    CONSTRAINT fk_citas_origen_cita FOREIGN KEY (cita_id) REFERENCES citas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$

-- Cuenta cuántas citas de este origen (mismo device_id O misma IP)
-- siguen esperando confirmación ahora mismo — se recalcula en vivo
-- en cada intento, así que "se desbloquea" solo en cuanto el panel
-- cambia el estado de las anteriores.
DROP PROCEDURE IF EXISTS sp_citas_contarPendientesPorOrigen$$
CREATE PROCEDURE sp_citas_contarPendientesPorOrigen(
    IN p_device_id VARCHAR(64),
    IN p_ip        VARCHAR(45)
)
BEGIN
    SELECT COUNT(DISTINCT o.cita_id) AS pendientes
    FROM citas_origen o
    JOIN citas c ON c.id = o.cita_id
    WHERE (o.device_id = p_device_id OR o.ip = p_ip)
      AND c.estado = 'Pendiente';
END$$

DROP PROCEDURE IF EXISTS sp_citas_insertOrigen$$
CREATE PROCEDURE sp_citas_insertOrigen(
    IN p_cita_id   INT UNSIGNED,
    IN p_device_id VARCHAR(64),
    IN p_ip        VARCHAR(45)
)
BEGIN
    INSERT INTO citas_origen (cita_id, device_id, ip) VALUES (p_cita_id, p_device_id, p_ip);
END$$

DELIMITER ;

-- Verificación:
--   CALL sp_citas_contarPendientesPorOrigen('test-device', '127.0.0.1');
